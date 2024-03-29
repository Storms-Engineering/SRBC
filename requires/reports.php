<?php
require_once 'tables.php';
Class Report
{
	private $start_date;
	private $end_date;
	private $camp_id;
	private $buslist_type;
	private $area;
	
	function __construct($startDate,$campId,$buslist_typ,$area) 
	{
		$this->start_date = $startDate;
	    $this->camp_id = $campId;
	    $this->buslist_type = $buslist_typ;
	    $this->area = $area;
	}
	
	//Gets campers using a generic query that we can add on too.  Also takes into the date and camp_id restrictions
	private function getCampers($query = NULL, $printHeader = true)
	{

		global $wpdb;
		//Search for everything
		if($this->camp_id == "none" && $this->start_date == NULL)
		{
			$campers = $wpdb->get_results("SELECT *
									FROM ((" . $GLOBALS['srbc_registration'] . "
								 INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
								 ".camp_id)
								 INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
		WHERE " . $GLOBALS['srbc_registration'] . ".waitlist=0 ". $query );
		}
		//
		else
		{
			$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_registration'] . "
									INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
									".camp_id)
									INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
		WHERE " . $GLOBALS['srbc_registration'] . ".waitlist=0 AND (" . $GLOBALS["srbc_camps"] . ".start_date='%s' OR " . $GLOBALS['srbc_camps'] . ".camp_id=%d) " . $query ,
									$this->start_date,$this->camp_id));
		}
		//We want to traceback 3 functions
		if($printHeader)
			$this->printHeader($campers,true);
		return $campers;
	}
	
	//Helper function for parentalReleaseForms
	private function selectFromArray($array, $data) {
		foreach($array as $row) {
		   if($row->agreement_id == $data ) return $row;
		}
		return NULL;
	}
	public function parentalReleaseForms()
	{
		global $wpdb;
		$signatures = $wpdb->get_results("SELECT * FROM 
										((" . $GLOBALS['srbc_campers'] . " INNER JOIN srbc_parental_agreements_sig ON srbc_parental_agreements_sig.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										 INNER JOIN srbc_registration ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										 GROUP BY srbc_registration.date ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_last_name ASC");
		$agreements = (array)$wpdb->get_results("SELECT * FROM srbc_parental_agreements_versions");
		//var_dump($signatures);
		foreach($signatures as $signature)
		{	
			echo '<div style="page-break-after: always;"> <h1>Agreement for ' . $signature->camper_first_name . " " . $signature->camper_last_name . "</h1>";
			echo $this->selectFromArray($agreements, $signature->agreement_id)->agreement_text;
			echo "<br><p><b>" . $signature->parent_first_name . " " . $signature->parent_last_name . " " . $signature->date . "</b></p>";
			echo '<img src="' . $signature->signature_img . '" width="500" height="200"></div>';
		}
	}

	public function healthForms()
	{
		//Get campers but don't print header
		$campers = $this->getCampers(" ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_last_name ASC",false);
		require_once 'health_form.php';
		HealthForm::before();
		foreach($campers as $camper)
		{
			HealthForm::generateHealthForm($camper->camper_id,$campers);
		}
		HealthForm::after();
	}

	//TODO make this use the amountDue function from the payment class
	//Calculates that amount due for a registration.  
	//2nd parameter is a bool to determine whether we are looking at the inactive_registration database.
	private function amountDue($registration_id,$inactive_registration)
	{
		//Determines which registration_database we are looking at
		$database = $GLOBALS["srbc_registration"];
		if ($inactive_registration)
			$database = $GLOBALS["srbc_registration_inactive"] ;
		global $wpdb;
		$totalpaid = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
										FROM " . $GLOBALS["srbc_payments"] . " WHERE registration_id=%s AND NOT " . $GLOBALS["srbc_payments"] .
										".fee_type='Store' ",$registration_id));
		$cost = $wpdb->get_var($wpdb->prepare("
								SELECT SUM(" . $GLOBALS["srbc_camps"] . ".cost +
								(CASE WHEN " . $database . ".horse_opt = 1 THEN " . $GLOBALS["srbc_camps"] .".horse_opt_cost
								ELSE 0
								END) +
								(CASE WHEN " . $database . ".busride = 'to' THEN 35
								WHEN " . $database . ".busride = 'from' THEN 35
								WHEN " . $database . ".busride = 'both' THEN 60
								ELSE 0
								END) 
								- IF(" . $database . ".discount IS NULL,0," . $database . ".discount)
								- IF(" . $database . ".scholarship_amt IS NULL,0," . $database . ".scholarship_amt)		
								)								
								FROM " . $database . "
								INNER JOIN " . $GLOBALS["srbc_camps"] . " ON " . $database . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id
								WHERE " . $database . ".registration_id=%d",$registration_id));
		return $cost - $totalpaid;
	}
	
	//Creates a Title for each report
	private function printHeader($camp = NULL, $traceBack3 = false)
	{
		//Get calling method, we will use this to print a Header
		//Traceback is how many functions back we want to trace back to
		if ($traceBack3 === true)
			$traceBackAmt = 2;
		else 
			$traceBackAmt = 1;
		$header = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[$traceBackAmt]['function'];
		//Format - remove _ and uppercase
		$header = ucwords(str_replace("_"," ",$header));
		$campInfo = array();

		//Get camps we are pulling info from
		if ($camp !== NULL)
		{
			//We were passed an array of camps
			foreach($camp as $c)
			{
				$info = $c->area . " " . $c->name;
				if(!in_array($info,$campInfo))
					$campInfo[] = $info;
			}
		}
		
		//Make pretty camps string
		$campsString = implode(", ",$campInfo);
		if ($camp === NULL)
			echo "<h1>" . $header . "</h1>";
		else
			echo '<h1 style="display:inline;">' . $header . "</h1>" . " - " . $campsString . "<br><br>";
	}

	//Shows all campers for a specific camp and where they are lodged
	public function lodging()
	{
		$campers = $this->getCampers(" ORDER BY camper_last_name ASC");
		$headers = array("Lodging", "Counselor");
		$props = array("lodging", "counselor");
		Tables::createTable($campers, $headers, $props);
	}

	//Shows all campers for a specific camp and where they are lodged
	public function tshirts()
	{
		$campers = $this->getCampers(" ORDER BY camper_last_name ASC");
		$headers = array("T-shirt size");
		$props = array("tshirt_size");
		Tables::createTable($campers, $headers, $props);
		$totalShirts = ["None" => 0];
		foreach($campers as $camper)
		{
			//Some campers don't have a tshirt size
			if($camper->tshirt_size == "")
				$totalShirts["None"] += 1;
			//We have to initialize the array key with a value
			else if(!isset($totalShirts[$camper->tshirt_size]))
				$totalShirts[$camper->tshirt_size] = 1;
			else
				$totalShirts[$camper->tshirt_size] += 1;
		}
		$keys = array_keys($totalShirts);
		echo "<br>";
		foreach($keys as $key)
		{
			echo "<b>$key: $totalShirts[$key] <b><br>";
		}
	}

	//Displays all inactive registrations
	public function inactive_registrations()
	{
		global $wpdb;
		$campers = $wpdb->get_results("SELECT *	FROM " . $GLOBALS['srbc_registration_inactive'] . 
									" INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration_inactive'] .
									".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id");
		echo "<table><tr><th>First Name</th><th>Last Name</th><th>Camp</th><th>Amount Due</th></tr>";
		//Start new row and put in name 
		foreach($campers as $camper)
		{	
			$amountDue = $this->amountDue($camper->registration_id,true);
			$camp = $wpdb->get_results("SELECT * FROM " . $GLOBALS['srbc_camps'] . 
											" WHERE camp_id=$camper->camp_id")[0];
			echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>' . $camper->camper_last_name ."</td><td> " . $camper->camper_first_name. "</td>".
			"<td>" . $camp->area . " " . $camp->name . "</td><td>$" . $amountDue . "</td>";
		}
		echo "</table>";
	}
	
	//Pops up a mailing list that the user is asked to download in a .csv file

	public function mailing_list()
	{
		//Get campers but don't print header
		$campers = $this->getCampers("",false);
		$csvArray = array();
		$csvArray[] = array("First_name","Last_name","Address","City","State","Zipcode","Cabin", "Camp");
		foreach($campers as $camper)
		{
			//Remove any line breaks from an address
			$csvArray[] = array($camper->camper_first_name,$camper->camper_last_name,preg_replace( "/\r|\n/", "", $camper->address)
			,$camper->city,$camper->state,$camper->zipcode,$camper->lodging,$camper->area . " " . $camper->name);
		}

		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="content.csv"');
		//I think this is some kind of temp stream
		$file = fopen('php://output','w');

		foreach ($csvArray as $fields) {
			fputcsv($file, $fields);
		}
		fclose($file);
	}
	
	//Similar to mailing list but designed to be merged in an excel spread sheet
	function kids_mailingList()
	{
		//Get campers but don't print header
		$campers = $this->getCampers(NULL,false);
		$csvArray = array();
		$csvArray[] = array("FirstName","LastName","Address","City","State", "ZipCode","Birthdate","Kenai Penin?", "Parent/Guardian", "EmailAddress");
		$kPenZipcodes = array(99611,99605,	99631,	99664, 99572,	99672,	99669,	99610,	99568,	99639,	99556,	99603);
		foreach($campers as $camper)
		{
			//See if there are on the kenai peninsula
			$onKenaiPenin = 'FALSE';
			if (in_array($camper->zipcode,$kPenZipcodes))
				$onKenaiPenin = 'TRUE';

			//Remove any line breaks from an address
			$csvArray[] = array($camper->camper_first_name,$camper->camper_last_name,preg_replace( "/\r|\n/", "", $camper->address)
			,$camper->city,$camper->state,$camper->zipcode,$camper->birthday, $onKenaiPenin ,$camper->parent_first_name . " " . $camper->parent_last_name,$camper->email);
		}

		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="content.csv"');
		//I think this is some kind of temp stream
		$file = fopen('php://output','w');

		foreach ($csvArray as $fields) 
		{
			fputcsv($file, $fields);
		}
		fclose($file);
	}
	public function all_camp_totals()
	{
		$this->printHeader();
		global $wpdb;
		$camps = $wpdb->get_results("SELECT * FROM " . $GLOBALS["srbc_camps"] . " WHERE NOT area='Winter Camp' ORDER BY area DESC");
		$totalRegistrationsPerArea = [ "Wagon Train" => 0, "Lakeside" => 0, "Wilderness" => 0, "Sports" => 0 ];
		$oldArea = NULL;
		$totalRegistrations = 0;
		foreach ($camps as $camp)
		{
			$totalRegistered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
											WHERE camp_id=%s AND waitlist=0",$camp->camp_id)); 
			$totalRegisteredBoys = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
											WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='male'",$camp->camp_id)); 
			$totalRegisteredGirls = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
											WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='female'",$camp->camp_id)); 
			if ($oldArea != $camp->area)
			{
				if ($oldArea !== NULL)
				{
					echo "</table>";
					echo $oldArea . " total: " . $totalRegistrationsPerArea[$oldArea];
				}
				//Start new table
				echo "<br><h3>" . $camp->area . "</h3>";
				echo "<table>
						<tr>
							<th>Start Date</th>
							<th>Camp</th>
							<th>Male</th>
							<th>Female</th>
							<th>Total</th>
						</tr>";
				$oldArea = $camp->area;
			}
			echo "<tr><td>" . $camp->start_date . "</td><td>" . $camp->area . " " . $camp->name . "</td><td>" . $totalRegisteredBoys . "/" . $camp->boy_registration_size . 
					"</td><td>" . $totalRegisteredGirls . "/" . $camp->girl_registration_size . "</td><td>" . $totalRegistered . "/" . $camp->overall_size . "</td></tr>";
			$totalRegistrationsPerArea[$camp->area] += $totalRegistered;
			
		}
		echo "</table>";
		echo $oldArea . " total: " . $totalRegistrationsPerArea[$oldArea];
		foreach($totalRegistrationsPerArea as $areaTotal)
		{
			$totalRegistrations += $areaTotal;
		}
		echo "<br><br>Overall Total: " . $totalRegistrations;
	}

	public function camp_numbers()
	{
		$this->printHeader();
		global $wpdb;
		$camps = $wpdb->get_results("SELECT * FROM " . $GLOBALS["srbc_camps"] . " WHERE NOT area='Workcrew' AND NOT area='WIT'");
		$totalRegistrations = 0;
		foreach ($camps as $camp)
		{
			$male_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
											WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='male'",$camp->camp_id)); 
			$female_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
											WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='female'",$camp->camp_id)); 

			$waitingListAmt = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
											WHERE camp_id=%s AND waitlist=1",$camp->camp_id)); 
			echo "<h3>" . $camp->area . " " . $camp->name . "</h3>			" . $camp->start_date . "<br>";
			echo "		Male: " . $male_registered . "/" . $camp->boy_registration_size . "<br>";
			echo "		Female: " . $female_registered . "/" . $camp->boy_registration_size .  "<br>";
			echo "		Total: " . ($male_registered + $female_registered) . "/" . $camp->overall_size . "<br>"; 
			echo "		Waiting List: " . $waitingListAmt . "/" . $camp->waiting_list_size . "<br>"; 
			$totalRegistrations += $male_registered + $female_registered;
		}
		echo "<br><br>Overall Camp Total: " . $totalRegistrations;
	}
	
	public function signout_sheets()
	{
		$campers = $this->getCampers("ORDER BY " . $GLOBALS['srbc_registration'] . ".lodging DESC");
		//This variable keeps track of if we have changed lodging group
		//Initialized to 0 so we don't compare to null and get true
		$oldLodging = 0;
		//echo '<table id="report_table">';
		foreach ($campers as $camper)
		{
			//Start a new table
			if ($camper->lodging != $oldLodging || $oldLodging === 0)
			{
				//Don't do this for the first table, but do it for every new table
				if($oldLodging != NULL)
				{
					echo '</table>';	
				}
				echo "<h1>Signout Sheet for " . $camper->area . " " . $camper->name . "</h1>";
				
				if($camper->lodging === "" || $camper->lodging === NULL)
					echo "<h3>No Lodging Assigned</h3>";
				else
					echo "<h3>$camper->lodging</h3>";
				
				//Dont put a page-break on the last table
				echo '<table style="page-break-after: always;" id="report_table">';
				echo '<tr><th>Camper</th><th>Parent/Guardian</th><th style="width:200px;">Signature</th></tr>';			
			}			
			echo "<tr><td>". $camper->camper_first_name . " " . $camper->camper_last_name . "</td>";
			echo "<td>". $camper->parent_first_name . " " . $camper->parent_last_name . "</td>";
			
			//Show bus in the signature column if the camper is riding the bus when leaving camp
			if ($camper->busride == "from" || $camper->busride == "both")
				echo "<td><b>ON THE BUS</b></td></tr>";
			else
				echo "<td></td></tr>";
			$oldLodging = $camper->lodging;
		}
		//Close out the table
		echo "</table>";
		echo '<style type="text/css"> table:last-of-type {page-break-after:unset !important;}</style>';
	}
	
	//TODO Similar to signout sheets?
	//Prints out a list of campers for program
	public function program_camper_sheets()
	{
		global $wpdb;
		$campers = $this->getCampers("ORDER BY srbc_registration.lodging DESC");
		//This variable keeps track of if we have changed lodge 
		//Initialized to 0 so we don't compare to null and get true
		$oldLodging = 0;
		//echo '<table id="report_table">';
		foreach ($campers as $camper)
		{
			//Start a new table
			if ($camper->lodging != $oldLodging || $oldLodging === 0)
			{
				//Don't do this for the first table, but do it for every new table
				if($oldLodging != NULL)
				{
					echo "</table><br><br><br>";	
				}
				if($camper->lodging === "" || $camper->lodging === NULL)
					echo "<h3>No Cabin Assigned</h3>";
				else
				{
					echo '<h3 style="display:inline">' . $camper->lodging . '</h3><br><b>Counselor: ' . $camper->counselor . '</b>';
					echo '&nbsp|&nbsp<b>Assistant Counselor: ' . $camper->assistant_counselor . '</b>';
				}
				echo '<table id="report_table">';
				echo '<tr><th>Camper</th><th>Phone #</th><th style="width:300px;">Notes:</th></tr>';			
			}			
			echo "<tr><td>". $camper->camper_first_name . " " . $camper->camper_last_name . "</td>";
			echo "<td>". $camper->phone . "</td>";
			echo "<td>". $camper->registration_notes . "</td>";
			echo "</tr>";
			$oldCabin = $camper->lodging;
		}
		//Close out the table
		echo "</table>";
	}

	//Enter a start date to grab fees from
	public function breakdown_fees()
	{
		global $wpdb;
		$newFormat = date("Y/m/d",strtotime( $this->start_date));
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_payments'] . " 
										INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
										INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										WHERE (STR_TO_DATE(" . $GLOBALS['srbc_payments'] . ".payment_date, '%c/%%d/%Y %T') > %s AND " . $GLOBALS['srbc_payments'] . ".payment_type = 'card' AND " . $GLOBALS['srbc_payments'] . ".note='Online')
										ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC",$newFormat ));
		echo "<h3>Registration day fees collected:</h3>";
		echo '<table id="report_table">';
		echo "<tr><th>Parent Last name</th><th>Parent Last name</th><th>Camper Last name</th><th>Camper First Name</th><th>Camp fee</th><th>Program Area</th>
				<th>Horse fee (WT)</th><th>Horse Option(LS)</th><th>Bus Fee</th><th>Store</th><th>Total</th><th>Payment Dates</th></tr>";
				
		//Set this to default Because some camps are free so we say none for program area
		$program_area = "None";
		//Declare variables to sum up together in one row
		$horse_fee = $horse_opt_cost = $bus_fee = $camp_fee  = $store = $next_id = $next_reg_id = $total = 0;
		$pointer = 1;
		$totals = ["card" => 0,"check" => 0, "cash" => 0, "Bus" => 0, "Store" => 0, "LS Horsemanship" => 0, "WT Horsemanship" => 0,
		"Lakeside" => 0, "Wagon Train" => 0, "Wilderness" => 0, "None" => 0, "Refund" => 0, "Fall Retreat" => 0];
		$camper_ids = [];
		foreach ($campers as $camper)
		{
			$camper_ids[] = $camper->camper_id;
			$totals[$camper->payment_type] += $camper->payment_amt;
			$totals[$camper->fee_type] += $camper->payment_amt;

			//Add all the payment dates into a string
			$dates = NULL;
			$dates .= $camper->payment_date . "<br>";

			if ($camper->fee_type == "Bus")
				$bus_fee += $camper->payment_amt;
			else if($camper->fee_type == "Store")
				$store += $camper->payment_amt;
			else if($camper->fee_type == "LS Horsemanship")
				$horse_opt_cost += $camper->payment_amt;
			else if($camper->fee_type == "WT Horsemanship")
				$horse_fee += $camper->payment_amt;
			else
			{
				$camp_fee += $camper->payment_amt;	
				
				if($program_area == "None")
					$program_area = $camper->fee_type;
				if ($program_area != $camper->fee_type)
					$program_area .= "," . $camper->fee_type;
			}
			
			$total += $camper->payment_amt;
			$last_id = $camper->camper_id;
			
			if ($pointer < count($campers))
			{
				$nextid = $campers[$pointer]->camper_id;
				$next_reg_id = $campers[$pointer]->registration_id;
			}
			else 
			{
				//If this is the last camper then just force the row to print.
				$nextid = 0;
				$next_reg_id = 0;
			}
			//Write out data
			if ($camper->camper_id != $nextid || $camper->registration_id != $next_reg_id)
			{
				echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->parent_first_name . '</td><td>' . $camper->parent_last_name . '</td><td>'. $camper->camper_last_name . "</td><td>" . $camper->camper_first_name . "</td>";
				echo "<td>$". $camp_fee . "</td>";
				echo "<td>". $program_area . "</td>";
				echo "<td>$". $horse_fee . "</td>";
				echo "<td>$". $horse_opt_cost . "</td>";
				echo "<td>$". $bus_fee . "</td>";
				echo "<td>$". $store . "</td>";
				echo "<td>$". $total . "</td>";
				echo "<td>". $dates . "</td>";
				echo "</tr>";
				//Then reset the variables
				$horse_fee = $horse_opt_cost = $bus_fee = $camp_fee = $store = $last_id = $total = 0;
				$dates = "";
				$program_area = "None";
			}
			$pointer++;
		}
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_camps'] . " 
										INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id)
										INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										WHERE " . $GLOBALS['srbc_camps'] . ".start_date=%s
										ORDER BY srbc_campers.camper_id",$_GET['start_date'] ));
		//Show campers who are signed up for these camps, but didn't pay anything
		foreach($campers as $camper)
		{
			//TODO make this use sql
			//BODY that will be faster
			if(!in_array($camper->camper_id,$camper_ids))
			{
				echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name . "</td><td>" . $camper->camper_first_name . "</td>";
				echo "<td>$0</td>";
				echo "<td>None</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "</tr>";
			}
		}
		//Close out the table
		echo "</table>";
		$this->printTotals($totals);
	}

	public function registration_day()
	{
		global $wpdb;
		$newFormat = date("m/d/Y",strtotime( $this->start_date));
		$newFormat2 = date("m/d/Y",strtotime( $this->start_date . " + 1 days"));
		$newFormat3 = date("m/d/Y",strtotime( $this->start_date . " + 2 days"));
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_payments'] . " 
										INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
										INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										WHERE (" . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s OR " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s OR " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s) AND " . $GLOBALS['srbc_payments'] . ".registration_day=1
										ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC",$newFormat . "%",$newFormat2 . "%",$newFormat3 . "%"));
										
		echo "<h3>Registration day fees collected:</h3>";
		echo '<table id="report_table">';
		echo "<tr><th>Last name</th><th>First Name</th><th>Camp fee</th><th>Program Area</th>
				<th>Horse fee (WT)</th><th>Horse Option(LS)</th><th>Bus Fee</th><th>Store</th><th>Total</th></tr>";
				
		//Set this to default Because some camps are free so we say none for program area
		$program_area = "None";
		//Declare variables to sum up together in one row
		$horse_fee = $horse_opt_cost = $bus_fee = $camp_fee  = $store = $next_id = $next_reg_id = $total = 0;
		$pointer = 1;
		$totals = ["card" => 0,"check" => 0, "cash" => 0, "Bus" => 0, "Store" => 0, "LS Horsemanship" => 0, "WT Horsemanship" => 0,
		"Lakeside" => 0, "Wagon Train" => 0, "Wilderness" => 0, "None" => 0, "Refund" => 0];
		$camper_ids = [];
		foreach ($campers as $camper)
		{
			$camper_ids[] = $camper->camper_id;
			$totals[$camper->payment_type] += $camper->payment_amt;
			$totals[$camper->fee_type] += $camper->payment_amt;
			if ($camper->fee_type == "Bus")
				$bus_fee += $camper->payment_amt;
			else if($camper->fee_type == "Store")
				$store += $camper->payment_amt;
			else if($camper->fee_type == "LS Horsemanship")
				$horse_opt_cost += $camper->payment_amt;
			else if($camper->fee_type == "WT Horsemanship")
				$horse_fee += $camper->payment_amt;
			else
			{
				$camp_fee += $camper->payment_amt;	
				
				if($program_area == "None")
					$program_area = $camper->fee_type;
				if ($program_area != $camper->fee_type)
					$program_area .= "," . $camper->fee_type;
			}
			
			$total += $camper->payment_amt;
			$last_id = $camper->camper_id;
			
			if ($pointer < count($campers))
			{
				$nextid = $campers[$pointer]->camper_id;
				$next_reg_id = $campers[$pointer]->registration_id;
			}
			else 
			{
				//If this is the last camper then just force the row to print.
				$nextid = 0;
				$next_reg_id = 0;
			}
			//Write out data
			if ($camper->camper_id != $nextid || $camper->registration_id != $next_reg_id)
			{
				echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name . "</td><td>" . $camper->camper_first_name . "</td>";
				echo "<td>$". $camp_fee . "</td>";
				echo "<td>". $program_area . "</td>";
				echo "<td>$". $horse_fee . "</td>";
				echo "<td>$". $horse_opt_cost . "</td>";
				echo "<td>$". $bus_fee . "</td>";
				echo "<td>$". $store . "</td>";
				echo "<td>$". $total . "</td>";
				echo "</tr>";
				//Then reset the variables
				$horse_fee = $horse_opt_cost = $bus_fee = $camp_fee = $store = $last_id = $total = 0;
				$program_area = "None";
			}
			$pointer++;
		}
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_camps'] . " 
										INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id)
										INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										WHERE " . $GLOBALS['srbc_camps'] . ".start_date=%s
										ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_id",$_GET['start_date'] ));
		//Show campers who are signed up for these camps, but didn't pay anything
		foreach($campers as $camper)
		{
			//TODO make this use sql
			//BODY that will be faster
			if(!in_array($camper->camper_id,$camper_ids))
			{
				echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name . "</td><td>" . $camper->camper_first_name . "</td>";
				echo "<td>$0</td>";
				echo "<td>None</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "<td>$0</td>";
				echo "</tr>";
			}
		}
		//Close out the table
		echo "</table>";
		$this->printTotals($totals);
	}
	
	private function printTotals($totals)
	{
		$keys = array_keys($totals);
		for($i=0;$i<count($keys);$i++)
		{
			echo "<h3>Total ".$keys[$i]. ":$";
			echo number_format($totals[$keys[$i]],2) . "</h3>";
		}
	}
	
	public function snackshop()
	{
		global $wpdb;
		echo "<h3>Snackshop (Store) fees collected:</h3>";
		echo '<table id="report_table">';
		echo "<tr><th>Last name</th><th>First Name</th><th>Amount</th><th>Payment Type</th></tr>";
		$campers = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_registration'] . "
														INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id
														WHERE " . $GLOBALS['srbc_registration'] . ".camp_id=%d",$this->camp_id));
		
		$totalFees = 0;
		foreach ($campers as $camper)
		{
			echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name
			. "</td><td>" . $camper->camper_first_name . "</td>";
			$paymentData = $wpdb->get_results($wpdb->prepare("SELECT payment_amt, payment_type
										FROM ((" . $GLOBALS['srbc_payments'] . " 
										INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
										INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
										WHERE " . $GLOBALS['srbc_payments'] . ".fee_type='Store' AND " . $GLOBALS['srbc_registration'] . ".camp_id=%d AND " . $GLOBALS['srbc_campers'] . ".camper_id=%d ",$this->camp_id,$camper->camper_id));
			//No snackshop payments
			if(empty($paymentData))
			{
				echo "<td>$0</td>";
				echo "<td>NONE</td>";
			}
			else
			{
				echo "<td>$" . $paymentData[0]->payment_amt . '</td>';
				echo "<td>" . $paymentData[0]->payment_type . '</td>';
			}
			
			//Extra cell for checkbox so office can check off when they are done with one
			echo '<td><input type="checkbox" onclick="event.stopPropagation();"></td></tr>';
		}
		echo "</table>";
		echo "<br>Total fees: $" . $totalFees;
	}
	
	public function transactions()
	{
		global $wpdb;
		$newFormat = date("m/d/Y",strtotime($this->start_date));
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
														FROM ((" . $GLOBALS['srbc_payments'] . " 
														INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
														INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
														WHERE " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s AND " . $GLOBALS['srbc_payments'] . ".registration_day=1
														ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC",$newFormat . "%"));
		$this->printHeader();												
		echo '<table id="report_table">';
		echo "<tr><th>Last name</th><th>First Name</th><th>Payment Type</th><th>Fee Type</th>
				<th>Amount</th></tr>";

		//ID is for multiple campers that were paid for at once
		foreach ($campers as $camper)
		{
			

				echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->camper_first_name . "</td><td>" . $camper->camper_last_name . "</td>";
				echo "<td>". $camper->payment_type . "</td>";
				echo "<td>". $camper->fee_type . "</td>";
				echo "<td>$". $camper->payment_amt . "</td>";
				echo "</tr>";
		}
		//Close out the table
		echo "</table>";
	}
	
	public function emails()
	{
		$campers = $this->getCampers();
		foreach($campers as $camper)
			echo $camper->email . ",<br>";
	}
	
	public function backup_registration()
	{
		$campers = $this->getCampers();
		global $wpdb;
		echo '<table id="results_table"><tr><th>Last Name</th><th>First Name</th>';
		echo '<th>Parent Name</th><th>Camp</th>';
		echo '<th>Phone #</th><th>Paid</th>';
		echo '<th>Amount Due</th><th>Payment Type</th><th>Payment Amount</th></tr>';
		foreach($campers as $camper)
		{
			echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>' . $camper->camper_last_name ."</td><td> " . $camper->camper_first_name. "</td>";
			echo "<td>" . $camper->parent_first_name . " " . $camper->parent_last_name . "</td>";
			echo "<td>" . $camper->area . " ".  $camper->name . "</td>";
			echo "<td>" . $camper->phone . "</td>";
			$totalpaid = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s  AND NOT srbc_payments.fee_type='store'",$camper->registration_id));
			$cost = $wpdb->get_var($wpdb->prepare("
									SELECT SUM(srbc_camps.cost +
										(CASE WHEN " . $GLOBALS['srbc_registration'] . ".horse_opt = 1 THEN " . $GLOBALS['srbc_camps'] . ".horse_opt_cost
										ELSE 0
										END) +
										(CASE WHEN " . $GLOBALS['srbc_registration'] . ".busride = 'to' THEN 35
										WHEN " . $GLOBALS['srbc_registration'] . ".busride = 'from' THEN 35
										WHEN " . $GLOBALS['srbc_registration'] . ".busride = 'both' THEN 60
										ELSE 0
										END) 
										- IF(" . $GLOBALS['srbc_registration'] . ".discount IS NULL,0," . $GLOBALS['srbc_registration'] . ".discount)
										- IF(" . $GLOBALS['srbc_registration'] . ".scholarship_amt IS NULL,0," . $GLOBALS['srbc_registration'] . ".scholarship_amt)		
										)										
										FROM " . $GLOBALS['srbc_registration'] . " 
										INNER JOIN srbc_camps ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id
										WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d ",$camper->registration_id));
			//Little hack so that it shows 0 if they are no payments
			if ($totalpaid == NULL)
				$totalpaid = 0;
			echo "<td>$" . number_format($totalpaid,2) . "</td>";
			echo "<td>$" . number_format(($cost - $totalpaid),2) . "</td>";
			//Empty cells
			echo "<td></td><td></td></tr>";
		}
		echo "</table>";
		echo "<br>Campers Count: " . count($campers);
	}
	
	public function camper_report()
	{
		//We can't use getCampers in this case because of the non waitlisted campers we want.
		//Though I could just edit the function, but we shall see if we need it more
		global $wpdb;
		$campers = $wpdb->get_results($wpdb->prepare( "SELECT *
		FROM ((" . $GLOBALS['srbc_registration'] . "
		INNER JOIN " . $GLOBALS['srbc_camps']. " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . ".camp_id)
		INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id) WHERE " .
			$GLOBALS["srbc_camps"] . ".camp_id=%d ", $this->camp_id));
		$this->printHeader($campers);
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Waitlist</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			if ($info->waitlist == 1 ) 
			{
				echo '<td>(waitlisted)</td>';
			}
			else
				echo "<td></td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function area_report()
	{
		$query = null;
		//All Query
		if ($this->area == "")
			$query = "AND " . $GLOBALS['srbc_camps'] . ".area LIKE '%' " . " AND NOT ". $GLOBALS['srbc_camps'] . ".area='Winter Camp' ";
		//Specific query
		else 
			$query .= "AND " . $GLOBALS['srbc_camps'] . ".area='$this->area' ";

		$campers = $this->getCampers($query);

		global $wpdb;
		echo '<table id="results_table"><tr>
		<th>Date Registered</th>
		<th>First Name</th><th>Last Name</th><th>Camp</th>';
		echo '<th>Parent Name</th>';
		echo '<th>Phone #</th>';
		echo '<th>Payment Type</th><th>Payment Amount</th></tr>';
		foreach($campers as $camper)
		{
			echo '<tr class="' . $camper->gender . '" onclick="openCamperModal(' . $camper->camper_id . ');"><td>' . $camper->date . '</td>
			<td>' . $camper->camper_first_name ."</td><td> " . $camper->camper_last_name. "</td>";
			echo "<td>" . $camper->area . " ".  $camper->name . "</td>";
			echo "<td>" . $camper->parent_first_name . "</td>";
			echo "<td>" . $camper->phone . "</td>";
			$totalpaid = $wpdb->get_results($wpdb->prepare("SELECT SUM(payment_amt) AS amount, CONCAT(payment_type, ',') AS type
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s",$camper->registration_id))[0];
			$cost = $wpdb->get_var($wpdb->prepare("
									SELECT SUM(srbc_camps.cost +
										(CASE WHEN " . $GLOBALS['srbc_registration'] . ".horse_opt = 1 THEN " . $GLOBALS['srbc_camps'] . ".horse_opt_cost
										ELSE 0
										END) +
										(CASE WHEN " . $GLOBALS['srbc_registration'] . ".busride = 'to' THEN 35
										WHEN " . $GLOBALS['srbc_registration'] . ".busride = 'from' THEN 35
										WHEN " . $GLOBALS['srbc_registration'] . ".busride = 'both' THEN 60
										ELSE 0
										END) 
										- IF(" . $GLOBALS['srbc_registration'] . ".discount IS NULL,0," . $GLOBALS['srbc_registration'] . ".discount)
										- IF(" . $GLOBALS['srbc_registration'] . ".scholarship_amt IS NULL,0," . $GLOBALS['srbc_registration'] . ".scholarship_amt)		
										)										
										FROM " . $GLOBALS['srbc_registration'] . " 
										INNER JOIN srbc_camps ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id
										WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d ",$camper->registration_id));
			//Little hack so that it shows 0 if they are no payments
			if ($totalpaid == NULL)
				$totalpaid = 0;
			echo "<td>" . $totalpaid->type . "</td>";
			echo "<td>$" . number_format($totalpaid->amount,2) . "</td>";
			//Empty cells
			echo "</tr>";
		}
		echo "</table>";
		echo "<br>Campers Count: " . count($campers);
	}
	
	//Prints all the campers with Scholarships and the type and the totals
	public function scholarships()
	{
		$campers = $this->getCampers("AND NOT " . $GLOBALS['srbc_registration'] . ".scholarship_amt=0 ORDER BY " . $GLOBALS['srbc_registration'] . ".scholarship_type ASC");
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Scholarship Type</th><th>Scholarship Amount</th>';
		$totals = array();
		$grandTotal = 0;
		foreach($campers as $info)
		{
			//If the key doesn't exist create it
			if(!array_key_exists($info->scholarship_type,$totals))
				$totals[$info->scholarship_type] = 0;
			//Add to our total
			$totals[$info->scholarship_type] += $info->scholarship_amt;
			$grandTotal += $info->scholarship_amt;
			
			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . $info->scholarship_type . "</td><td>$" . $info->scholarship_amt . "</td>";
			echo "</tr>";
		}
		echo "</table>";
		$this->printTotals($totals);
		echo "<h1>Grand Total: $" . number_format($grandTotal,2) . "</h1>";
	}
	
	public function discounts()
	{
		$campers = $this->getCampers("AND NOT " . $GLOBALS['srbc_registration'] . ".discount=0 ");
		//TODO we could use the default table function, but I want to add $ to the beginning of certain lines so idk how to do that yet.
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Discount Type</th><th>Discount</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . $info->discount_type . "</td>";
			echo "<td>$" . $info->discount . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function not_checked_in()
	{
		$campers = $this->getCampers("AND " . $GLOBALS['srbc_registration']. ".checked_in=0");
		$headers = array("Busride");
		$props = array("busride");
		Tables::createTable($campers, $headers, $props);
	}
	
	public function balance_due()
	{
		$query = null;
		//All Query, excluding winter camp
		if ($this->area == "")
			$query = "AND " . $GLOBALS['srbc_camps'] . ".area LIKE '%' " . " AND NOT ". $GLOBALS['srbc_camps'] . ".area='Winter Camp' ";
		//Specific query
		else 
			$query .= "AND " . $GLOBALS['srbc_camps'] . ".area='$this->area' ";

		$campers = $this->getCampers($query);
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Amount Due</th><th>Camp</th></tr>';
		foreach($campers as $info)
		{
			$amountDue = $this->amountDue($info->registration_id,false);
			if($amountDue <= 0)
				continue;
			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>$" . $amountDue . "</td>";
			echo "<td>" . $info->area . " " . $info->name . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
		
		
	//Shows all the emails for campers that have a balance due.
	public function balance_due_emails()
	{
		$this->printHeader();
		$database = $GLOBALS["srbc_registration"];
		/*if ($inactive_registration)
			$database = $GLOBALS["srbc_registration_inactive"] ;*/
		global $wpdb;
		$balanceDueCampers = $this->getAmountDueArray();
		$campersInformation = $wpdb->get_results("SELECT * FROM " . $database . " INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_campers'] . ".camper_id=" . $database . ".camper_id",OBJECT_K);

		
		foreach($balanceDueCampers as $camper)
		{
			echo $campersInformation[$camper->registration_id]->email . "<br>";
		}
	}
	
			
	//Shows all the emails for campers that have a balance due.
	//Is also redundant see above function comment
	public function balance_due_addresses()
	{
		//$this->printHeader();
		$database = $GLOBALS["srbc_registration"];
		/*if ($inactive_registration)
			$database = $GLOBALS["srbc_registration_inactive"] ;*/
		global $wpdb;
		$balanceDueCampers = $this->getAmountDueArray();
		$campersInformation = $wpdb->get_results("SELECT * FROM " . $database . " INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_campers'] . ".camper_id=" . $database . ".camper_id",OBJECT_K);

		$csvArray = array();
		$csvArray[] = array("Parent_first_name","parent_last_name","Address","City","State","Zipcode");
		foreach($balanceDueCampers as $info)
		{
			$camper = $campersInformation[$info->registration_id];
			//Remove any line breaks from an address
			$csvArray[] = array($camper->parent_first_name,$camper->parent_last_name,preg_replace( "/\r|\n/", "", $camper->address)
			,$camper->city,$camper->state,$camper->zipcode);
		}

		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="content.csv"');
		//I think this is some kind of temp stream
		$file = fopen('php://output','w');

		foreach ($csvArray as $fields) {
			fputcsv($file, $fields);
		}
		fclose($file);
		
		

		
	}
	
	//Return an array of amountDue by registration number
	private function getAmountDueArray()
	{
		//TODO inactive_registration support?
		//BODY I seemed to implement this for inactive registration report but do we really need it?  Look into it.
		//Determines which registration_database we are looking at
		$database = $GLOBALS["srbc_registration"];
		/*if ($inactive_registration)
			$database = $GLOBALS["srbc_registration_inactive"] ;*/
		global $wpdb;
		$arrayOfTotalPayed = $wpdb->get_results("SELECT 	owedTble.registration_id,SUM(owe - payed) as amountDue
						FROM (SELECT registration_id,
						SUM(
                                " . $GLOBALS['srbc_camps'] . " .cost + (CASE WHEN " . $database . ".horse_opt = 1 THEN " . $GLOBALS['srbc_camps']. ".horse_opt_cost
								ELSE 0
								END)
                                 +
								(CASE WHEN " . $database . ".busride = 'to' THEN 35
								WHEN " . $database . ".busride = 'from' THEN 35
								WHEN " . $database . ".busride = 'both' THEN 60
								ELSE 0
								END) 
								- IF(" . $database . ".discount IS NULL,0," . $database . ".discount)
								- IF(" . $database . ".scholarship_amt IS NULL,0," . $database . ".scholarship_amt)		
								) AS owe
								
					FROM " . $GLOBALS['srbc_camps'] . " INNER JOIN " . $database . " ON " . $GLOBALS['srbc_camps'] . ".camp_id=" . $database . ".camp_id
					GROUP BY " . $database. ".registration_id
					) as owedTble
				INNER JOIN (SELECT payment_type,registration_id,SUM(" . $GLOBALS['srbc_payments'] . ".payment_amt) as payed FROM " . $GLOBALS['srbc_payments'] . "
							WHERE NOT " . $GLOBALS['srbc_payments'] . ".fee_type='Store'
							GROUP BY " . $GLOBALS['srbc_payments']. ".registration_id) as payedTble ON owedTble.registration_id = payedTble.registration_id
							/*amount due greater than 0*/
							WHERE (owe - payed) > 0
				GROUP BY registration_id",OBJECT_K);
			return $arrayOfTotalPayed;	
	}
	
	//Generates a report for all the overpaid campers.
	//This can also show the same campers more than once if they owe for multiple camps they are signed up for
	public function overpaid()
	{
		$campers = $this->getCampers();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Amount Due</th><th>Camp</th></tr>';
		foreach($campers as $info)
		{
			//TODO Change amount_due to a complete sql query
			//BODY this is a super heavy load.
			$amountDue = $this->amountDue($info->registration_id,false);
			//We want only amounts less than 0
			if(!($amountDue < 0))
				continue;
			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>$" . $amountDue . "</td>";
			echo "<td>" . $info->area . " " . $info->name . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	//Displays all campers who were refunded money - general report
	public function refunds()
	{
		global $wpdb;
		$newFormat = date("m/d/Y",strtotime($this->start_date));
		$campers = $wpdb->get_results("SELECT *
														FROM ((" . $GLOBALS['srbc_payments'] . " 
														INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
														INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id)
														WHERE " . $GLOBALS['srbc_payments'] . ".fee_type='Refund'
														ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC");
		$this->printHeader();												
		echo '<table id="report_table">';
		echo "<tr><th>Last name</th><th>First Name</th><th>Payment Type</th><th>Fee Type</th>
				<th>Amount</th></tr>";

		//ID is for multiple campers that were paid for at once
		foreach ($campers as $camper)
		{
			

				echo '<tr class="'.$camper->gender.'" onclick="openCamperModal('.$camper->camper_id.');"><td>'. $camper->camper_first_name . "</td><td>" . $camper->camper_last_name . "</td>";
				echo "<td>". $camper->payment_type . "</td>";
				echo "<td>". $camper->fee_type . "</td>";
				echo "<td>$". $camper->payment_amt . "</td>";
				echo "</tr>";
		}
		//Close out the table
		echo "</table>";
	}

	
	//New Buslist grabs all campers heading to anchorage or camp and also selects campers that are going both ways
	//Puts them into both reports
	public function buslist()
	{
		$campers = $this->getCampers("AND (" . $GLOBALS['srbc_registration'] . ".busride='".$this->buslist_type."' OR " . $GLOBALS['srbc_registration'] . ".busride='both')" );
		echo '<h1>' . ($this->buslist_type == "to" ? "Anchorage to camp" : "Camp to Anchorage");
		if($this->buslist_type == "from")
			echo ", " . date ("l",strtotime( $campers[0]->end_date)) . " , " .  date("n/j", strtotime($campers[0]->end_date)) . "</h1>";
		else if($this->buslist_type == "to")
			echo ", " . date ("l",strtotime( $campers[0]->start_date)) . " , " .  date("n/j", strtotime($campers[0]->start_date)) . "</h1>";
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th>';
		echo '<th>Camp</th>';
		echo '<th>Primary Phone</th>';
		echo '<th>Secondary Phone</th>';
		echo '<th>Parent/Guardian Signature</th>';
		echo '<th>Total Due</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			//TODO Change name to camp_name
			//BODY this will cause confusion in the future when making these types of queries.
			echo "<td>" . $info->area . " " . $info->name . "</td>";
			echo "<td>" . $info->phone. "</td>";
			echo "<td>" . $info->phone2. "</td>";
			echo "<td></td>";
			$amountDue = $this->amountDue($info->registration_id,false);
			echo "<td>$" . ($amountDue) . "</td></tr>";
			
		}
		echo "</table>";
		echo "<br>Campers Count: " . count($campers);
	}
	
	public function camp_report()
	{
		$campers = $this->getCampers();
		$headers = array("Gender", "Age", "Counselor");
		$props = array("gender", "age", "counselor");
		Tables::createTable($campers, $headers, $props);
		$genderTotals = array();
		foreach($campers as $camper)
		{
			if(!array_key_exists($camper->gender,$genderTotals))
				$genderTotals[$camper->gender] = 0;
			$genderTotals[$camper->gender] += 1;
		}
		$keys = array_keys($genderTotals);
		for($i=0;$i<count($keys);$i++)
		{
			echo "<br>Total ".$keys[$i]. ": ";
			echo $genderTotals[$keys[$i]];
		}
	}
	
	public function horsemanship()
	{
		$campers = $this->getCampers("AND (NOT " . $GLOBALS['srbc_registration'] . ".horse_opt=0 OR NOT " . $GLOBALS['srbc_registration'] . ".horse_waitlist=0)");
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Horse WaitingList</th></tr>';
		foreach($campers as $info)
		{

			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			if ($info->horse_waitlist == 1) 
				echo '<td>(waitlisted)</td>';
			else
				echo '<td></td>';
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function packing_list_sent()
	{
		$campers = $this->getCampers();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Packing List Sent</th></tr>';
		foreach($campers as $info)
		{

			echo '<tr class="'.$info->gender.'" onclick="openCamperModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . ($info->packing_list_sent == 1 ? "Sent" : "Not Sent") . "</td></tr>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function no_health_form()
	{
		$campers = $this->getCampers("AND " . $GLOBALS["srbc_registration"] . ".health_form=0");
		Tables::createTable($campers);

	}
}

?>