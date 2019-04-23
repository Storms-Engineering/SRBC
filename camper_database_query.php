<?php 
//This file searches for campers broadly and returns a table of campers matching the criteria
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Security check - kinda
if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");

global $wpdb;
$campers = NULL;

//Search for campers in specific areas and specific camps. 
$areas = array("Lakeside", "Wagon Train", "Wilderness", "Workcrew", "Sports", "Fall Retreat", "Winter Camp");
$cs = $wpdb->get_results("SELECT area,name FROM srbc_camps",ARRAY_N);
$camps=NULL;
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
							FROM ((srbc_registration 
							INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
							INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
							WHERE srbc_camps.area=%s ORDER BY srbc_registration.registration_id ASC",$area));
	}
}

foreach($camps as $camp){
	if ($camp == $_GET['query']){
		$specificQuery = true;
		$q = explode("~",$camp);
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
							FROM ((srbc_registration 
							INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
							INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
							WHERE srbc_camps.area=%s AND srbc_camps.name=%s ORDER BY srbc_registration.registration_id ASC",$q[0],$q[1]));
	}
}

if (!$specificQuery)
{
	$name = explode(" ",$_GET['query']);
	
	//This query searches for first name or last name of the camper and orders it by first name
	//Also protected against sql injection by prepare
	//See if they typed a first name and last name
	if (count($name) == 2){
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

?>
	 <table style="width:100%;">
		<tr>
			<th onclick="sortTable(0);">Firstname</th>
			<th onclick="sortTable(1);">Lastname</th>
			<th onclick="sortTable(2);">Age</th>
			<th onclick="sortTable(3);">Parent Name</th>
			<th onclick="sortTable(4);">Email</th>
			<th onclick="sortTable(5);">Phone</th>
		</tr>
		<?php
	foreach ($campers as $camper)
	{
		echo '<tr onclick="openModal('.$camper->camper_id.')" class="'. $camper->gender .'">';
		echo "<td>";
		echo $camper->camper_first_name . "</td>";
		echo "<td>" . $camper->camper_last_name . "</td>";
		echo "<td>" . $camper->age . "</td>"; 
		//Show camp descriptions
		/*
		$camp_ids = explode(",",$camper->camps);
		echo "<td>";
		foreach($camp_ids as $campid)
		{
			$camps = $wpdb->get_row( "SELECT * FROM srbc_camps WHERE camp_id=$campid");
			echo $camps->camp_description . "<br>";
		}*/
		echo "</td><td>" . $camper->parent_first_name ." ". $camper->parent_last_name . "</td>";
		echo '<td><a style="color:#1043d5;" href="mailto:' . $camper->email . '">'.$camper->email.'</a></td>';
		echo "<td>" . $camper->phone . "</td>";
		echo "</td></tr>";
	}
	?>
	</table> 