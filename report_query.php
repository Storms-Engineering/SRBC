<?php
//Import $wpdb for wordpress
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
//Check this value first because it doesn't follow a normal report query format
if ($_GET["camp_numbers"] == "true")
{
	$date = new DateTime("now", new DateTimeZone('America/Anchorage'));
	$date = $date->format("Y-m-d");
	$camps = $wpdb->get_results("SELECT * FROM srbc_camps WHERE start_date >= '$date'");
	$totalRegistrations = 0;
	foreach ($camps as $camp)
	{
		$male_registered = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id), ARRAY_N)[0][0]; 
		$female_registered = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id), ARRAY_N)[0][0]; 
		echo "<h3>" . $camp->area . " " . $camp->name . "</h3>			" . $camp->start_date . "<br>";
		echo "		Male: " . $male_registered . "<br>";
		echo "		Female: " . $female_registered . "<br>";
		echo "		Total: " . ($male_registered + $female_registered) . "<br>"; 
		$totalRegistrations += $male_registered + $female_registered;
	}
	echo "<br><br>Overall Camp Total: " . $totalRegistrations;
	exit;
}




//TODO:  This will probably be tore out and totally redone.  Might have to comb through and optimize
//@body Probably gonna get rid of these too
$area = $_GET['area'];
$buslist = $_GET['buslist'];
$buslist_type = $_GET['buslist_type'];
$scholarship = $_GET['scholarship'];
$discount = $_GET['discount'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$not_checked_in = $_GET['not_checked_in'];
//TODO: fix not payed code, probably haven't updated since payment database was added 
$not_payed = NULL;//$_GET['not_payed'];

//Combining all of the databases so that we can pull all the data that we need from it
$query = "SELECT *
		FROM ((srbc_registration 
		INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
		INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id) WHERE ";
		
$values = array();
//Keeps track of how many sort headers we have
$sortnum = 0;
//Setup table and then we will add headers based on the query
//Only default for most queries.  Isn't for camp_numbers report_table
if ($_GET["backup_registration"] == "true"){
	echo '<table id="report_table"><tr><th onclick="sortTable(0)">Last Name</th><th onclick="sortTable(1)">First Name</th>';
	echo '<th onclick="sortTable(2)">Parent Name</th><th onclick="sortTable(3)">Camp</th>';
	echo '<th onclick="sortTable(4)">Phone #</th><th onclick="sortTable(5)">Payed</th>';
	echo '<th onclick="sortTable(6)">Amount Due</th><th>Payment Type</th><th>Payment Amount</th>';
	$sortnum = 7;
}
else {
	echo '<table id="report_table"><tr><th onclick="sortTable(0)">Last Name</th><th onclick="sortTable(1)">First Name</th>';
	$sortnum = 2;
}


if ($area == "null") {
	$query .= "srbc_camps.area LIKE '%' ";
}
else {
	$values = array($area);
	$query .= "srbc_camps.area='%s' ";
}

/*
Old Buslist
if ($buslist != "all"){
	$query .= "AND srbc_registration.busride='$buslist' ";
	echo '<th onclick="sortTable('.$sortnum.')">Busride</th>';
	$sortnum++;
}*/

//New Buslist grabs all campers heading to anchorage or camp and also selects campers that are going both ways
//Puts them into both reports
if ($buslist == "true"){
	$query .= "AND srbc_registration.busride='$buslist_type' OR srbc_registration.busride='both' ";
	echo '<th onclick="sortTable('.$sortnum.')">Primary Phone</th>';
	echo '<th onclick="sortTable('.$sortnum.')">Secondary Phone</th>';
	echo '<th onclick="sortTable('.$sortnum.')">Parent/Guardian Signature</th>';
	echo '<th onclick="sortTable('.$sortnum.')">Total Due</th>';
	$sortnum++;
}
if ($_GET["horsemanship"] == "true"){
	$query .= "AND NOT srbc_registration.horse_opt=0 ";
}
if ($_GET["camp_numbers"] == "true"){
	$query .= "AND NOT srbc_registration.horse_opt=0 ";
}
if ($scholarship == "true"){
	$query .= "AND NOT srbc_registration.scholarship_amt=0 ";
	echo '<th onclick="sortTable('.$sortnum.')">Scholarship Type</th><th onclick="sortTable('.$sortnum.')">Scholarship Amount</th>';
	$sortnum++;
}
if ($discount == "true"){
	$query .= "AND NOT srbc_registration.discount=0 ";
	echo '<th onclick="sortTable('.$sortnum.')">Discount</th>';
	$sortnum++;
}
if ($start_date != "" && $end_date != ""){
	$query .= "AND srbc_camps.start_date BETWEEN '%s' AND '%s' ";
	array_push($values,$start_date);
	array_push($values,$end_date);
}
if ($not_checked_in == "true"){
	$query .= "AND NOT srbc_registration.checked_in=1 ";
}
if ($_GET["backup_registration"] == "true"){
	
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
//Show the correct row based on what the user was searching for
foreach ($information as $info){
	//Start new row and put in name since that always happens
	echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name . "</td>";

	if ($buslist == "true"){
		if($info->busride == $buslist_type || $info->busride == "both"){
			echo "<td>" . $info->phone. "</td>";
			echo "<td>" . $info->phone2. "</td>";
			echo "<td></td>";
			
			//TODO camp_id and camper id dependence
			//@body another backwards compatible dependency
			$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM srbc_payments WHERE camp_id=%s AND camper_id=%s",$info->camp_id,$info->camper_id));
			$cost = $wpdb->get_var($wpdb->prepare("
									SELECT SUM(srbc_camps.cost +
									(CASE WHEN srbc_registration.horse_opt = 1 THEN srbc_camps.horse_opt
									ELSE 0
									END) +
									(CASE WHEN srbc_registration.busride = 'to' THEN 35
									WHEN srbc_registration.busride = 'from' THEN 35
									WHEN srbc_registration.busride = 'both' THEN 60
									ELSE 0
									END) 
									- srbc_registration.discount
									- srbc_registration.scholarship_amt)								
									FROM srbc_registration 
									INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id
									WHERE srbc_registration.camp_id=%d AND srbc_registration.camper_id=%d",$info->camp_id,$info->camper_id));
			//TODO Buslist broken! -HIGH PRIORITY
			//@body amount due needs to be in the buslist report
			echo "<td>$" . ($cost - $totalPayed) . "</td>";
		}
	}
	else if ($_GET["backup_registration"] == "true"){
		echo "<td>" . $info->parent_first_name . " " . $info->parent_last_name . "</td>";
		echo "<td>" . $info->area . " ".  $info->name . "</td>";
		echo "<td>" . $info->phone . "</td>";
		//TODO camp_id and camper id dependence
		//@body another backwards compatible dependency
		$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
								FROM srbc_payments WHERE camp_id=%s AND camper_id=%s",$info->camp_id,$info->camper_id));
		$cost = $wpdb->get_var($wpdb->prepare("
								SELECT SUM(srbc_camps.cost +
									(CASE WHEN srbc_registration.horse_opt = 1 THEN srbc_camps.horse_opt
									ELSE 0
									END) +
									(CASE WHEN srbc_registration.busride = 'to' THEN 35
									WHEN srbc_registration.busride = 'from' THEN 35
									WHEN srbc_registration.busride = 'both' THEN 60
									ELSE 0
									END) 
									- srbc_registration.discount
									- srbc_registration.scholarship_amt)								
									FROM srbc_registration 
									INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id
									WHERE srbc_registration.camp_id=%d AND srbc_registration.camper_id=%d",$info->camp_id,$info->camper_id));
		//Little hack so that is shows 0 if they are no payments
		echo "<td>$" . number_format($totalPayed,2) . "</td>";
		echo "<td>$" . number_format(($cost - $totalPayed),2) . "</td>";
		//Empty cells
		echo "<td></td><td></td>";
		
	}
	else if ($scholarship == "true"){
		echo "<td>" . $info->scholarship_type . "</td><td>$" . $info->scholarship_amt . "</td>";
	}
	else if ($discount == "true"){
		echo "<td>$" . $info->discount . "</td>";
	}
	
}
echo "</table>";
echo "<br>Campers Count: " . count($information);
?>