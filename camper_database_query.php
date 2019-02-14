<?php 
//This file searches for campers broadly and returns a table of campers matching the criteria
$campers = NULL;
$name = explode(" ",$_GET['name']);

require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
//This query searches for first name or last name of the camper and orders it by first name
//Also protected against sql injection by prepare
//TODO ADD search queries for areas and specific camps.  Probably will use exact matching
//See if they typed a first name and last name

if (count($name) == 2){
	$fname = $name[0];
	$campers = $wpdb->get_results(
	$wpdb->prepare( "SELECT * FROM srbc_campers WHERE (camper_first_name 
	LIKE %s AND camper_last_name LIKE %s )OR (parent_first_name LIKE %s AND parent_last_name LIKE %s)
	ORDER BY camper_first_name", 
	$name[0]."%",$name[1]."%",$name[0]."%",$name[1]."%"));
}
else{
	$name = $name[0];
	$campers = $wpdb->get_results(
	$wpdb->prepare( "SELECT * FROM srbc_campers WHERE camper_first_name 
	LIKE %s OR camper_last_name LIKE %s OR parent_first_name LIKE %s OR parent_last_name LIKE %s
	ORDER BY camper_first_name", 
	$name."%",$name."%",$name."%",$name."%"));
}

?>
	 <table style="width:100%;">
		<tr>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Age</th>
			<th>Parent Name</th>
			<th>Phone</th>
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
		echo "<td>" . $camper->phone . "</td>";
		echo "</td></tr>";
	}
	?>
	</table> 