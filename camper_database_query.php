<?php 
//This file searches for campers broadly and returns a table of campers matching the criteria
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Security check - kinda
if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");

global $wpdb;
$campers = NULL;

//Search for campers in specific areas and specific camps. 
$areas = array("Lakeside", "Wagon Train", "Wilderness", "Workcrew", "Sports", "Fall Retreat", "Winter Camp");
$cs = $wpdb->get_results("SELECT area,name FROM " . $GLOBALS['srbc_camps'],ARRAY_N);
$camps=array();
$specificQuery = false;
//Add all the camps and areas together seperated by ~
for ($i = 0;$i< count($cs);$i++){
	$camps[] = $cs[$i][0] . '~' . $cs[$i][1];
}
foreach($areas as $area){
	if ($area == $_GET['query']){
		$specificQuery = true;
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
							FROM ((". $GLOBALS['srbc_registration'] . " 
							INNER JOIN " . $GLOBALS['srbc_camps']." ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id)
							INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
							WHERE " . $GLOBALS['srbc_camps'] . ".area=%s ORDER BY " . $GLBOALS['srbc_registration'] . ".registration_id ASC",$area));
	}
}
//Catches if we have an emtpy database
if($cs == NULL)
	error_msg("It seems that there is no camps in the database");
foreach($camps as $camp){
	if ($camp == $_GET['query']){
		$specificQuery = true;
		$q = explode("~",$camp);
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
							FROM ((" . $GLOBALS['srbc_registration'] . " 
							INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS['srbc_registration'] . '.camp_id= ' . $GLOBALS['srbc_camps'] . '.camp_id)
							INNER JOIN srbc_campers ON ' . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
							WHERE " . $GLOBALS['srbc_camps'] . ".area=%s AND " . $GLOBALS['srbc_camps'] . ".name=%s ORDER BY " .
							$GLOBALS['srbc_registration'] . ".registration_id ASC",$q[0],$q[1]));
	}
}

if (!$specificQuery)
{
	$name = explode(" ",$_GET['query']);
	
	//This query searches for first name or last name of the camper and orders it by first name
	//Also protected against sql injection by prepare
	//See if they typed a first name and last name
	if(isset($_GET['inner']))
	{
		$name = $name[0];
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM srbc_campers
			INNER JOIN srbc_registration ON srbc_campers.camper_id=srbc_registration.camper_id
			WHERE (camper_first_name 
			LIKE %s OR camper_last_name LIKE %s OR parent_first_name LIKE %s OR parent_last_name LIKE %s)
			AND srbc_registration.camp_id=%d
			ORDER BY srbc_campers.camper_last_name ASC", 
			$name."%",$name."%",$name."%",$name."%",$_GET['camp_id']));
	}
	else if (count($name) == 2){
		$fname = $name[0];
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM srbc_campers WHERE (camper_first_name 
			LIKE %s AND camper_last_name LIKE %s )OR (parent_first_name LIKE %s AND parent_last_name LIKE %s)
			ORDER BY camper_id ASC", 
			$name[0]."%",$name[1]."%",$name[0]."%",$name[1]."%"));
	}
	else{
		$name = $name[0];
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM srbc_campers WHERE camper_first_name 
			LIKE %s OR camper_last_name LIKE %s OR parent_first_name LIKE %s OR parent_last_name LIKE %s
			ORDER BY camper_id ASC", 
			$name."%",$name."%",$name."%",$name."%"));
	}
}
	echo ' <table style="width:100%;" id="results_table">
		<tr>
			<th>Firstname</th>
			<th>Lastname</th>';
		//Custom table
		if (!isset($_GET['inner']))
		{
			echo '<th>Age</th>
			<th>Parent Name</th>
			<th>Email</th>
			<th>Phone</th>';
		}
		else
			echo '<th>Select</th>';
	echo '</tr>';
	foreach ($campers as $camper)
	{
		if(isset($_GET['inner']))
			echo '<tr>';
		else
			echo '<tr onclick="openModal('.$camper->camper_id.')" class="'. $camper->gender .'">';
		echo "<td>";
		echo $camper->camper_first_name . "</td>";
		echo "<td>" . $camper->camper_last_name . "</td>";
		if(!isset($_GET['inner']))
		{
		echo "<td>" . $camper->age . "</td>"; 
		echo "</td><td>" . $camper->parent_first_name ." ". $camper->parent_last_name . "</td>";
		echo '<td><a style="color:#1043d5;" href="mailto:' . $camper->email . '">'.$camper->email.'</a></td>';
		echo "<td>" . $camper->phone . "</td>";
		}
		else
			echo '<td><input type="radio" name="nameToAdd" value="' . $camper->registration_id . '"></td>';
		echo "</tr>";
	}
	?>
	</table> 