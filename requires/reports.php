<?php
Class Report
{
	private $start_date;
	private $end_date;
	private $camp_id;
	private $buslist_type;
	
	function __construct($startDate,$endDate,$campId,$buslist_typ) 
	{
		$this->start_date = $startDate;
		$this->end_date = $endDate;
	    $this->camp_id = $campId;
	    $this->buslist_type = $buslist_typ;
	}
	
	//Gets campers using a generic query that we can add on too.  Also takes into the date and camp_id restrictions
	private function getCampers($query = NULL)
	{
		global $wpdb;
		//Search for everything
		if($this->camp_id == "none" && $this->start_date == NULL)
		{
			$campers = $wpdb->get_results("SELECT *
									FROM ((" . $GLOBALS['srbc_registration'] . "
								 INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
								 ".camp_id)
								 INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
		WHERE " . $GLOBALS['srbc_registration'] . ".waitlist=0 ". $query );
			}
		else
		{
			$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_registration'] . "
									INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
									".camp_id)
									INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
		WHERE " . $GLOBALS['srbc_registration'] . ".waitlist=0 AND (" . $GLOBALS["srbc_camps"] . ".start_date='%s' OR " . $GLOBALS['srbc_camps'] . ".camp_id=%d) " . $query ,
									$this->start_date,$this->camp_id));
		}
		return $campers;
	}
	
	//Calculates that amount due for a registration.  
	//2nd parameter is a bool to determine whether we are looking at the inactive_registration database.
	private function amountDue($registration_id,$inactive_registration)
	{
		//Determines which registration_database we are looking at
		$database = $GLOBALS["srbc_registration"];
		if ($inactive_registration)
			$database = $GLOBALS["srbc_registration_inactive"] ;
		global $wpdb;
		$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
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
		return $cost - $totalPayed;
	}
	private function printHeader($camp = NULL)
	{
		//Get calling method, we will use this to print a Header
		$header = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
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
			echo '<span style="font-size:large">' . $header . "</span>" . " - " . $campsString . "<br><br>";
	}
	//Displays all inactive registrations
	public function inactive_registrations()
	{
		$this->printHeader();
		global $wpdb;
		$campers = $wpdb->get_results("SELECT *	FROM " . $GLOBALS['srbc_registration_inactive'] . 
									" INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration_inactive'] .
									".camper_id=srbc_campers.camper_id");
		echo "<table><tr><th>First Name</th><th>Last Name</th><th>Camp</th><th>Amount Due</th></tr>";
		//Start new row and put in name since that always happens - most of the time
		foreach($campers as $camper)
		{	
			$amountDue = $this->amountDue($camper->registration_id,true);
			$camp = $wpdb->get_results("SELECT * FROM " . $GLOBALS['srbc_camps'] . 
											" WHERE camp_id=$camper->camp_id")[0];
			echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>' . $camper->camper_last_name ."</td><td> " . $camper->camper_first_name. "</td>".
			"<td>" . $camp->area . " " . $camp->name . "</td><td>$" . $amountDue . "</td>";
		}
		echo "</table>";
	}
	
	//Pops up a mailing list that the user is asked to download in a .csv file

	public function mailing_list()
	{
		$campers = $this->getCampers();
		$csvArray = array();
		$csvArray[] = array("First_name","Last_name","Address","City","State","Zipcode","Cabin", "Camp");
		foreach($campers as $camper)
		{
			//Remove any line breaks from an address
			$csvArray[] = array($camper->camper_first_name,$camper->camper_last_name,preg_replace( "/\r|\n/", "", $camper->address)
			,$camper->city,$camper->state,$camper->zipcode,$camper->cabin,$camper->area . " " . $camper->name);
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
	

	public function camp_numbers()
	{
		$this->printHeader();
		global $wpdb;
		$camps = $wpdb->get_results("SELECT * FROM " . $GLOBALS["srbc_camps"]);
		$totalRegistrations = 0;
		foreach ($camps as $camp)
		{
			$male_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
											WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id)); 
			$female_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
											WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id)); 
			echo "<h3>" . $camp->area . " " . $camp->name . "</h3>			" . $camp->start_date . "<br>";
			echo "		Male: " . $male_registered . "<br>";
			echo "		Female: " . $female_registered . "<br>";
			echo "		Total: " . ($male_registered + $female_registered) . "<br>"; 
			$totalRegistrations += $male_registered + $female_registered;
		}
		echo "<br><br>Overall Camp Total: " . $totalRegistrations;
	}
	
	public function signout_sheets()
	{
		$campers = $this->getCampers("ORDER BY srbc_registration.cabin DESC");
		$this->printHeader($campers);
		//This variable keeps track of if we have changed cabin group
		//Initialized to 0 so we don't compare to null and get true
		$oldCabin = 0;
		//echo '<table id="report_table">';
		foreach ($campers as $camper)
		{
			//Start a new table
			if ($camper->cabin != $oldCabin || $oldCabin === 0)
			{
				//Don't do this for the first table, but do it for every new table
				if($oldCabin != NULL)
				{
					echo '</table>';	
				}
					if($camper->cabin === "" || $camper->cabin === NULL)
						echo "<h3>No Cabin Assigned</h3>";
					else
						echo "<h3>$camper->cabin</h3>";
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
			$oldCabin = $camper->cabin;
		}
		//Close out the table
		echo "</table>";
	}
	
	public function program_camper_sheets()
	{
		global $wpdb;
		$campers = $this->getCampers("ORDER BY srbc_registration.cabin DESC");
		$this->printHeader($campers);
		//This variable keeps track of if we have changed cabin group
		//Initialized to 0 so we don't compare to null and get true
		$oldCabin = 0;
		//echo '<table id="report_table">';
		foreach ($campers as $camper)
		{
			//Start a new table
			if ($camper->cabin != $oldCabin || $oldCabin === 0)
			{
				//Don't do this for the first table, but do it for every new table
				if($oldCabin != NULL)
				{
					echo "</table><br><br><br>";	
				}
				if($camper->cabin === "" || $camper->cabin === NULL)
					echo "<h3>No Cabin Assigned</h3>";
				else
				{
					echo '<h3 style="display:inline">' . $camper->cabin . '</h3><br><b>Counselor: ' . $camper->counselor . '</b>';
					echo '&nbsp|&nbsp<b>Assistant Counselor: ' . $camper->assistant_counselor . '</b>';
				}
				echo '<table id="report_table">';
				echo '<tr><th>Camper</th><th>Phone #</th><th style="width:300px;">Notes:</th></tr>';			
			}			
			echo "<tr><td>". $camper->camper_first_name . " " . $camper->camper_last_name . "</td>";
			echo "<td>". $camper->phone . "</td>";
			echo "<td>". $camper->registration_notes . "</td>";
			echo "</tr>";
			$oldCabin = $camper->cabin;
		}
		//Close out the table
		echo "</table>";
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
										INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
										WHERE (" . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s OR " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s OR " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s) AND " . $GLOBALS['srbc_payments'] . ".registration_day=1
										ORDER BY srbc_campers.camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC",$newFormat . "%",$newFormat2 . "%",$newFormat3 . "%"));
										
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
		//TODO investigate this:
		//ID is for multiple campers that were payed for at once?
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
				echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name . "</td><td>" . $camper->camper_first_name . "</td>";
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
										INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
										WHERE " . $GLOBALS['srbc_camps'] . ".start_date=%s
										ORDER BY srbc_campers.camper_id",$_GET['start_date'] ));
		//Show campers who are signed up for these camps, but didn't pay anything
		foreach($campers as $camper)
		{
			//TODO make this use sql
			//BODY that will be faster
			if(!in_array($camper->camper_id,$camper_ids))
			{
				echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name . "</td><td>" . $camper->camper_first_name . "</td>";
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
		echo "<tr><th>Last name</th><th>First Name</th><th>Amount</th></tr>";
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
														FROM ((" . $GLOBALS['srbc_payments'] . " 
														INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
														INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id
														WHERE " . $GLOBALS['srbc_payments'] . ".fee_type='Store' AND " . $GLOBALS['srbc_registration'] . ".camp_id=%d",$this->camp_id));
		$totalFees = 0;
		foreach ($campers as $camper)
		{
			echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>'. $camper->camper_last_name
			. "</td><td>" . $camper->camper_first_name . "</td>";
			//Extra cell for checkbox so office can check off when they are done with one
			echo "<td>$" . $camper->payment_amt . '</td><td><input type="checkbox" onclick="event.stopPropagation();"></td></tr>';
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
														INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
														WHERE " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s AND " . $GLOBALS['srbc_payments'] . ".registration_day=1
														ORDER BY srbc_campers.camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC",$newFormat . "%"));
														
		echo "<h3>Transactions</h3>";
		echo '<table id="report_table">';
		echo "<tr><th>Last name</th><th>First Name</th><th>Payment Type</th><th>Fee Type</th>
				<th>Amount</th></tr>";

		//ID is for multiple campers that were payed for at once
		foreach ($campers as $camper)
		{
			

				echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>'. $camper->camper_first_name . "</td><td>" . $camper->camper_last_name . "</td>";
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
			echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>' . $camper->camper_last_name ."</td><td> " . $camper->camper_first_name. "</td>";
			echo "<td>" . $camper->parent_first_name . " " . $camper->parent_last_name . "</td>";
			echo "<td>" . $camper->area . " ".  $camper->name . "</td>";
			echo "<td>" . $camper->phone . "</td>";
			$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s",$camper->registration_id));
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
			if ($totalPayed == NULL)
				$totalPayed = 0;
			echo "<td>$" . number_format($totalPayed,2) . "</td>";
			echo "<td>$" . number_format(($cost - $totalPayed),2) . "</td>";
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
		INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE " .
			$GLOBALS["srbc_camps"] . ".camp_id=%d ", $this->camp_id));
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Waitlist</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
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
		error_msg("Area report currently doesn't work");
		if (isset($_GET['area']) && $_GET["area"] == "") {
		$query .= $GLOBALS['srbc_camps'] . ".area LIKE '%' ";
		}
		else {
			$values = array($_GET['area']);
			$query .= $GLOBALS['srbc_camps'] . ".area='%s' ";
		}
	}
	
	public function scholarships()
	{
		$campers = $this->getCampers("AND NOT " . $GLOBALS['srbc_registration'] . ".scholarship_amt=0");
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Scholarship Type</th><th>Scholarship Amount</th>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . $info->scholarship_type . "</td><td>$" . $info->scholarship_amt . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function discounts()
	{
		$campers = $this->getCampers("AND NOT " . $GLOBALS['srbc_registration'] . ".discount=0 ");
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Discount Type</th><th>Discount</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . $info->discount_type . "</td>";
			echo "<td>$" . $info->discount . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function not_checked_in()
	{
		$campers = $this->getCampers("AND " . $GLOBALS['srbc_registration']. ".checked_in=0");
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function balance_due()
	{
		$campers = $this->getCampers();
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Amount Due</th></tr>';
		foreach($campers as $info)
		{
			$amountDue = $this->amountDue($info->registration_id,false);
			if($amountDue <= 0)
				continue;
			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>$" . $amountDue . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	//New Buslist grabs all campers heading to anchorage or camp and also selects campers that are going both ways
	//Puts them into both reports
	public function buslist()
	{
		$campers = $this->getCampers("AND (" . $GLOBALS['srbc_registration'] . ".busride='".$this->buslist_type."' OR " . $GLOBALS['srbc_registration'] . ".busride='both')" );
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th>';
		echo '<th>Camp</th>';
		echo '<th>Primary Phone</th>';
		echo '<th>Secondary Phone</th>';
		echo '<th>Parent/Guardian Signature</th>';
		echo '<th>Total Due</th></tr>';
		foreach($campers as $info)
		{
			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
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
	}
	
	public function camp_report()
	{
		$campers = $this->getCampers();
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Gender</th><th>Age</th><th>Counselor</th></tr>';
		foreach($campers as $info)
		{

			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . $info->gender . "</td>";
			echo "<td>" . $info->age . "</td>";
			echo "<td>" . $info->counselor . "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	public function horsemanship()
	{
		$campers = $this->getCampers("AND (NOT " . $GLOBALS['srbc_registration'] . ".horse_opt=0 OR NOT " . $GLOBALS['srbc_registration'] . ".horse_waitlist=0)");
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Horse WaitingList</th></tr>';
		foreach($campers as $info)
		{

			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
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
		$this->printHeader();
		echo '<table id=""><tr><th>Last Name</th><th>First Name</th><th>Packing List Sent</th></tr>';
		foreach($campers as $info)
		{

			echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
			echo "<td>" . ($info->packing_list_sent == 1 ? "Sent" : "Not Sent") . "</td></tr>";
			echo "</tr>";
		}
		echo "</table>";
	}
}

?>