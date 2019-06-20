<?php
//Import $wpdb for wordpress
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Security check - kinda
if (!is_user_logged_in() && !isset($_GET["camp_numbers"])) exit("Thus I refute thee.... P.H.");
global $wpdb;
//Check these values first because it doesn't follow a normal report query format
if(isset($_GET["inactive_registrations"]))
{
	$campers = $wpdb->get_results("SELECT *	FROM " . $GLOBALS['srbc_registration_inactive'] . 
									" INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration_inactive'] .
									".camper_id=srbc_campers.camper_id");
	echo "<table><tr><th>First Name</th><th>Last Name</th><th>Camp</th><th>Amount Due</th></tr>";
	//Start new row and put in name since that always happens - most of the time
	foreach($campers as $camper)
	{	
	$amountDue = amountDueInactive($camper->registration_id);
	$camp = $wpdb->get_results("SELECT * FROM " . $GLOBALS['srbc_camps'] . 
									" WHERE camp_id=$camper->camp_id")[0];
	echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>' . $camper->camper_last_name ."</td><td> " . $camper->camper_first_name. "</td>".
	"<td>" . $camp->area . " " . $camp->name . "</td><td>$" . $amountDue . "</td>";
	}
	echo "</table>";
	exit();
}
else if(isset($_GET["mailing_list"]))
{
	$campers = $wpdb->get_results($wpdb->prepare("SELECT *
		FROM ((" . $GLOBALS['srbc_registration'] . " 
		INNER JOIN " . $GLOBALS["srbc_camps"] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS["srbc_camps"] . ".camp_id)
		INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE 
		" . $GLOBALS["srbc_camps"] . ".start_date='%s'",$_GET["start_date"]));
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
	exit();
}
else if (isset($_GET["camp_numbers"]))
{
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
	exit;
}
else if(isset($_GET["signout_sheets"]))
{
	$campers = $wpdb->get_results($wpdb->prepare("SELECT *
									FROM ((" . $GLOBALS['srbc_registration'] . "
								 INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
								 ".camp_id)
								 INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
								 WHERE " . $GLOBALS["srbc_camps"] . ".start_date BETWEEN '%s' AND '%s' 
								 ORDER BY srbc_registration.cabin DESC",$_GET['start_date'],$_GET['end_date']));
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
		echo "<td></td></tr>";
		$oldCabin = $camper->cabin;
	}
	//Close out the table
	echo "</table>";
	exit;
}
else if(isset($_GET["program_camper_sheets"]))
{
	$campers = $wpdb->get_results($wpdb->prepare("SELECT *
									FROM ((" . $GLOBALS['srbc_registration'] . "
								 INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
								 ".camp_id)
								 INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
								 WHERE " . $GLOBALS["srbc_camps"] . ".camp_id=%d
								 ORDER BY srbc_registration.cabin DESC",$_GET['camp']));
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
	exit;
}
else if (isset($_GET["registration_day"]))
{
	$newFormat = date("m/d/Y",strtotime( $_GET["start_date"]));
	//$time_end = date("m/d/Y G:i",strtotime($_GET["start_date"] . " " . $_GET["time"] . " +8 hours"));
	//TODO might need to readd wpdb prepare here
	//BODY for security purposes.
	$campers = $wpdb->get_results($wpdb->prepare("SELECT *
									FROM ((" . $GLOBALS['srbc_payments'] . " 
									INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
									INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
									WHERE " . $GLOBALS['srbc_payments'] . ".payment_date LIKE %s AND " . $GLOBALS['srbc_payments'] . ".registration_day=1
									ORDER BY srbc_campers.camper_id, " . $GLOBALS['srbc_payments'] . ".registration_id ASC",$newFormat . "%"));
									
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
	//ID is for multiple campers that were payed for at once
	foreach ($campers as $camper)
	{
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
	//Close out the table
	echo "</table>";
	$keys = array_keys($totals);
	for($i=0;$i<count($keys);$i++)
	{
		echo "<h3>Total ".$keys[$i]. ":$";
		echo number_format($totals[$keys[$i]],2) . "</h3>";
	}
	/*
	echo "<h3>Total Cash:$";
	echo (isset($totals["cash"]))?number_format($totals["cash"],2):'0';
	echo "<h3>Total Check:$";
	echo (isset($totals["check"]))?number_format($totals["check"],2):'0';
	echo "<h3>Total Card:$";
	echo (isset($totals["card"]))?number_format($totals["card"],2):'0';*/
	exit;
	
}
else if(isset($_GET["snackshop"]))
{
	echo "<h3>Snackshop (Store) fees collected:</h3>";
	echo '<table id="report_table">';
	echo "<tr><th>Last name</th><th>First Name</th><th>Amount</th></tr>";
	$campers = $wpdb->get_results($wpdb->prepare("SELECT *
													FROM ((" . $GLOBALS['srbc_payments'] . " 
													INNER JOIN " . $GLOBALS['srbc_registration'] . " ON " . $GLOBALS['srbc_registration'] . ".registration_id=" . $GLOBALS['srbc_payments'] . ".registration_id)
													INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
													WHERE " . $GLOBALS['srbc_payments'] . ".fee_type='Store' AND " . $GLOBALS['srbc_registration'] . ".camp_id=%d",$_GET["camp"]));
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
	exit();
}
else if (isset($_GET["transactions"]))
{
	$newFormat = date("m/d/Y",strtotime( $_GET["start_date"]));
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
	exit;
	
}
//TODO: fix not payed code, probably haven't updated since payment database was added 
//$not_payed = NULL;//$_GET['not_payed'];

//Combining all of the databases so that we can pull all the data that we need from it
//TODO this is an incredibly expensive query.
//BODY Should make a dynamic column selection.
$query = "SELECT *
		FROM ((" . $GLOBALS['srbc_registration'] . "
		INNER JOIN " . $GLOBALS['srbc_camps']. " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . ".camp_id)
		INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE ";
		
$values = array();
//Setup table and then we will add headers based on the query
//Only default for most queries.  Isn't for camp_numbers report_table
if (isset($_GET["backup_registration"])){
	$query .= $GLOBALS['srbc_registration'] . ".waitlist=0 ";
	echo '<table id="report_table"><tr><th>Last Name</th><th>First Name</th>';
	echo '<th>Parent Name</th><th>Camp</th>';
	echo '<th>Phone #</th><th>Paid</th>';
	echo '<th>Amount Due</th><th>Payment Type</th><th>Payment Amount</th>';
}
else if(isset($_GET["emails"]))
	//Do nothing
	echo "";
else {
	echo '<table id="report_table"><tr><th>Last Name</th><th>First Name</th>';
}

if(isset($_GET["camper_report"]))
{
	echo '<th>Waitlist</th>';
}
//TODO see if we really need areas or not?
//BODY possibly redo a lot of this code as well
if (isset($_GET['area']) && $_GET["area"] == "") {
	//Checks to see if we need to add an and
	if (isset($_GET["backup_registration"]))
		$query .= " AND ";
	$query .= $GLOBALS['srbc_camps'] . ".area LIKE '%' ";
}
else {
	$values = array($_GET['area']);
	if (isset($_GET["backup_registration"]))
		$query .= " AND ";
	$query .= $GLOBALS['srbc_camps'] . ".area='%s' ";
}

//New Buslist grabs all campers heading to anchorage or camp and also selects campers that are going both ways
//Puts them into both reports
if (isset($_GET['buslist'])){
	$query .= "AND (" . $GLOBALS['srbc_registration'] . ".busride='".$_GET['buslist_type']."' OR " . $GLOBALS['srbc_registration'] . ".busride='both') 
				AND waitlist=0 ";
	echo '<th>Camp</th>';
	echo '<th>Primary Phone</th>';
	echo '<th>Secondary Phone</th>';
	echo '<th>Parent/Guardian Signature</th>';
	echo '<th>Total Due</th>';
}
if (isset($_GET["horsemanship"])){
	$query .= "AND (NOT " . $GLOBALS['srbc_registration'] . ".horse_opt=0 OR NOT " . $GLOBALS['srbc_registration'] . ".horse_waitlist=0)";
	echo '<th>Horse WaitingList</th>';
}
//TODO this seems entirely unecessary and seems to be old code
/*if (isset($_GET["camp_numbers"])){
	$query .= "AND NOT " . $GLOBALS['srbc_registration'] . ".horse_opt=0 ";
}*/
if (isset($_GET['scholarship'])){
	$query .= "AND NOT " . $GLOBALS['srbc_registration'] . ".scholarship_amt=0 ";
	echo '<th>Scholarship Type</th><th>Scholarship Amount</th>';
}
//TODO delete this?
if (isset($_GET['not_payed'])){
	echo '<th>Amount Due</th>';
}

if (isset($_GET["camp_report"]))
{
	if ($_GET["camp"] == "none")
	{
		error_msg("Please select a camp you would like a report for.  Thanks - P.H.");
		exit(0);
	}
	$query .= "AND " . $GLOBALS['srbc_camps'] . ".camp_id=". $_GET["camp"]. " AND " . $GLOBALS['srbc_registration'] . ".waitlist=0 ";
	echo '<th>Gender</th><th>Age</th>';
	echo '<th>Counselor</th>';
}
else if (isset($_GET["camp"]) && $_GET["camp"] != "none")
{
	$query .= "AND " . $GLOBALS['srbc_camps'] . ".camp_id=". $_GET["camp"]. " ";
}
if (isset($_GET['discount'])){
	$query .= "AND NOT " . $GLOBALS['srbc_registration'] . ".discount=0 ";
	echo '<th>Discount Type</th>';
	echo '<th>Discount</th>';
}
if ( isset($_GET['start_date']) && $_GET["start_date"] != "" && isset($_GET["end_date"]) && $_GET["end_date"] != ""){
	$query .= "AND " . $GLOBALS['srbc_camps'] . ".start_date BETWEEN '%s' AND '%s' ";
	array_push($values,$_GET['start_date']);
	array_push($values,$_GET['end_date']);
}
if (isset($_GET['not_checked_in'])){
	$query .= "AND NOT " . $GLOBALS['srbc_registration'] . ".checked_in=1 AND waitlist=0";
}

if (isset($_GET['packing_list_sent'])){
	echo '<th>Packing List Sent</th>';
}

//TODO amount_due deprecated
//@body needs to be redone with proper SQl query
/*
if ($not_payed == "true"){
	$query .= "AND NOT srbc_registration.amount_due=0 ";
}*/
//close the row
echo "</tr>";
$information = $wpdb->get_results(
	$wpdb->prepare( $query, $values));
//TODO redo this code.  It has gotten really confusing
//Show the correct row based on what the user was searching for
foreach ($information as $info){
	//If emails we don't need any of the tables
	if(isset($_GET["emails"]))
	{
		echo $info->email . ",<br>";
		continue;
	}
	
	if(!isset($_GET["not_payed"]))
	{
		//Start new row and put in name since that always happens - most of the time
		echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
		if ($info->waitlist == 1 && isset($_GET["camper_report"])) 
		{
			echo '<td class="stickout">(waitlisted)</td>';
		}
		else if(isset($_GET["camper_report"]))
			echo "<td></td>";

		if(($info->horse_opt == 1 || $info->horse_waitlist == 1) && isset($_GET["horsemanship"]))
		{
			if ($info->horse_waitlist == 1 && isset($_GET["horsemanship"])) 
				echo '<td>(waitlisted)</td>';
			else
				echo '<td></td>';
		}



	}
	//We don't need a isset for this because it is always being sent?
	if (isset($_GET['buslist'])){
		if($info->busride == $_GET['buslist_type'] || $info->busride == "both"){
			//TODO Change name to camp_name
			//BODY this will cause confusion in the future when making these types of queries.
			echo "<td>" . $info->area . " " . $info->name . "</td>";
			echo "<td>" . $info->phone. "</td>";
			echo "<td>" . $info->phone2. "</td>";
			echo "<td></td>";
			$amountDue = amountDue($info->registration_id);
			echo "<td>$" . ($amountDue) . "</td>";
		}
	}
	else if (isset($_GET["backup_registration"])){
		echo "<td>" . $info->parent_first_name . " " . $info->parent_last_name . "</td>";
		echo "<td>" . $info->area . " ".  $info->name . "</td>";
		echo "<td>" . $info->phone . "</td>";
		$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
								FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s",$info->registration_id));
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
									WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d ",$info->registration_id));
		//Little hack so that is shows 0 if they are no payments
		if ($totalPayed == NULL)
			$totalPayed = 0;
		echo "<td>$" . number_format($totalPayed,2) . "</td>";
		echo "<td>$" . number_format(($cost - $totalPayed),2) . "</td>";
		//Empty cells
		echo "<td></td><td></td>";
	}
	else if(isset($_GET["not_payed"]))
	{
		$amountDue = amountDue($info->registration_id);
		if($amountDue <= 0)
			continue;
		echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
		echo "<td>$" . $amountDue . "</td>";
		
	}
	else if(isset($_GET["camp_report"]))
	{
		echo "<td>" . $info->gender . "</td>";
		echo "<td>" . $info->age . "</td>";
		echo "<td>" . $info->counselor . "</td>";
	}
	else if (isset($_GET['scholarship'])){
		echo "<td>" . $info->scholarship_type . "</td><td>$" . $info->scholarship_amt . "</td>";
	}
	else if (isset($_GET['discount'])){
		echo "<td>" . $info->discount_type . "</td>";
		echo "<td>$" . $info->discount . "</td>";
	}
	else if (isset($_GET['packing_list_sent']))
	{
		echo "<td>";
		echo ($info->packing_list_sent == 1 ? "Sent" : "Not Sent"),"</td>";
	}
	echo "</tr>";
}
echo "</table>";
echo "<br>Campers Count: " . count($information);

function amountDue($registration_id)
{
	global $wpdb;
	$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS["srbc_payments"] . " WHERE registration_id=%s AND NOT " . $GLOBALS["srbc_payments"] .
									".fee_type='Store' ",$registration_id));
	$cost = $wpdb->get_var($wpdb->prepare("
							SELECT SUM(" . $GLOBALS["srbc_camps"] . ".cost +
							(CASE WHEN " . $GLOBALS["srbc_registration"] . ".horse_opt = 1 THEN " . $GLOBALS["srbc_camps"] .".horse_opt_cost
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
							INNER JOIN " . $GLOBALS["srbc_camps"] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id
							WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d",$registration_id));
	return $cost - $totalPayed;
}
//TODO Redo this and probably get rid of seperate database for inactive registrations?
//BODY also waitlisted campers?  IDK i think that is okay but perhaps we should do something with these inactive registrations.  They seem to be as good as inactive registrations.
function amountDueInactive($registration_id)
{
	global $wpdb;
	$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS["srbc_payments"] . " WHERE registration_id=%s AND NOT " . $GLOBALS["srbc_payments"] .
									".fee_type='Store' ",$registration_id));
	$cost = $wpdb->get_var($wpdb->prepare("
							SELECT SUM(" . $GLOBALS["srbc_camps"] . ".cost +
							(CASE WHEN " . $GLOBALS["srbc_registration_inactive"] . ".horse_opt = 1 THEN " . $GLOBALS["srbc_camps"] .".horse_opt_cost
							ELSE 0
							END) +
							(CASE WHEN " . $GLOBALS['srbc_registration_inactive'] . ".busride = 'to' THEN 35
							WHEN " . $GLOBALS['srbc_registration_inactive'] . ".busride = 'from' THEN 35
							WHEN " . $GLOBALS['srbc_registration_inactive'] . ".busride = 'both' THEN 60
							ELSE 0
							END) 
							- IF(" . $GLOBALS['srbc_registration_inactive'] . ".discount IS NULL,0," . $GLOBALS['srbc_registration_inactive'] . ".discount)
							- IF(" . $GLOBALS['srbc_registration_inactive'] . ".scholarship_amt IS NULL,0," . $GLOBALS['srbc_registration_inactive'] . ".scholarship_amt)		
							)								
							FROM " . $GLOBALS['srbc_registration_inactive'] . "
							INNER JOIN " . $GLOBALS["srbc_camps"] . " ON " . $GLOBALS['srbc_registration_inactive'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id
							WHERE " . $GLOBALS['srbc_registration_inactive'] . ".registration_id=%d",$registration_id));
	return $cost - $totalPayed;
}
?>