<?php
//Import $wpdb for wordpress
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Custom Security Check
if (!is_user_logged_in() && !isset($_GET["camp_numbers"])) exit("Thus I refute thee.... P.H.");
global $wpdb;
//Check these values first because it doesn't follow a normal report query format
require 'requires/reports.php';

$thisReport = new Report($_GET['start_date'],$_GET['end_date'],$_GET['camp']);
$thisReport->{$_GET['report']}();



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
echo '<table id="report_table"><tr><th>Last Name</th><th>First Name</th>';


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
	
}
if ( isset($_GET['start_date']) && $_GET["start_date"] != "" && isset($_GET["end_date"]) && $_GET["end_date"] != ""){
	$query .= "AND " . $GLOBALS['srbc_camps'] . ".start_date BETWEEN '%s' AND '%s' ";
	array_push($values,$_GET['start_date']);
	array_push($values,$_GET['end_date']);
}

if (isset($_GET['packing_list_sent'])){
	echo '<th>Packing List Sent</th>';
}

//close the row
echo "</tr>";
$information = $wpdb->get_results(
	$wpdb->prepare( $query, $values));
//TODO redo this code.  It has gotten really confusing
//Show the correct row based on what the user was searching for
foreach ($information as $info){
	
		//Start new row and put in name since that always happens - most of the time
		echo '<tr class="'.$info->gender.'" onclick="openModal('.$info->camper_id.');"><td>' . $info->camper_last_name ."</td><td> " . $info->camper_first_name. "</td>";
		if(($info->horse_opt == 1 || $info->horse_waitlist == 1) && isset($_GET["horsemanship"]))
		{
			if ($info->horse_waitlist == 1 && isset($_GET["horsemanship"])) 
				echo '<td>(waitlisted)</td>';
			else
				echo '<td></td>';
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
			$amountDue = amountDue($info->registration_id,false);
			echo "<td>$" . ($amountDue) . "</td>";
		}
	}
	else if(isset($_GET["camp_report"]))
	{
		echo "<td>" . $info->gender . "</td>";
		echo "<td>" . $info->age . "</td>";
		echo "<td>" . $info->counselor . "</td>";
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


?>