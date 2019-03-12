<?php
//TODO:  This will probably be tore out and totally redone.  Might have to comb through and optimize

$area = $_GET['area'];
$buslist = $_GET['buslist'];
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
//Setup table and then we will add headers based on the query

echo '<table id="report_table"><tr><th onclick="sortTable(0)">Last Name</th><th onclick="sortTable(1)">First Name</th>';
//Keeps track of how many sort headers we have
$sortnum = 2;
if ($area == "") {
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

//New Buslist
if ($buslist == "true"){
	$query .= "AND NOT srbc_registration.busride='none' ";
	echo '<th onclick="sortTable('.$sortnum.')">Primary Phone</th>';
	echo '<th onclick="sortTable('.$sortnum.')">Secondary Phone</th>';
	echo '<th onclick="sortTable('.$sortnum.')">Parent/Guardian Signature</th>';
	echo '<th onclick="sortTable('.$sortnum.')">Total Due</th>';
	$sortnum++;
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
if ($not_payed == "true"){
	$query .= "AND NOT srbc_registration.amount_due=0 ";
}
//close the row
echo "</tr>";

require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

$information = $wpdb->get_results(
	$wpdb->prepare( $query, $values));
//echo $values[0];
$busrides =	array (
        'none' => 0,
        'to' => 30,
        'from' => 30,
        'both' => 60
    );
foreach ($information as $info){
	//Start new row and put in name since that always happens
	echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name . "</td>";

	if ($buslist == "true"){
		echo "<td>" . $info->phone. "</td>";
		echo "<td>" . $info->phone2. "</td>";
		echo "<td></td>";
		echo "<td>$" . $busrides[$info->busride] . "</td>";
	}
	if ($scholarship == "true"){
		echo "<td>" . $info->scholarship_type . "</td><td>$" . $info->scholarship_amt . "</td>";
	}
	if ($discount == "true"){
		echo "<td>$" . $info->discount . "</td>";
	}
	
}


?>