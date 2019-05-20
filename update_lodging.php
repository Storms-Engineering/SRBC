<?php
//Camper Lodging Assignment - This lets program assign campers to specific lodging and a counselor
//Can also be queried to show current lodging and counselor assignemnts

//Import wordpress stuff we need
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
//Security check - kinda
if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");

//Check if we are updating and do so
if(isset($_GET['registration_id']))
{
	$wpdb->update( 
		'srbc_registration', 
		array( 
			'cabin' => $_GET['lodge'],
			'counselor' => $_GET['counselor']
		), 
		array( 'registration_id' => $_GET['registration_id']), 
		array( 
			'%s',
			'%s'
		), 
		array( '%d' ) 
	);
	exit();
}


//TODO Notice these sql queries are not archive ready. 
//BODY Basically they will only query the current database.  AKA using the $GLOBALS keyword

$lodging = [
"Lakeside"    => ["Augustine", "Spurr", "Susitna", "Illiamna", "Redoubt", "Denali", "Spruce/Aspen", "Birch/Willow", "Tustumena", "Skilak", "Beluga", "Kenai"],
"Wagon Train" => ["Wagon 1", "Wagon 2", "Wagon 3", "Wagon 4"],
"Wilderness"  => ["Guys Tent", "Girls Tent"] ];

$lodges = $lodging[$_GET['area']];
$count = 0;
foreach($lodges as $lodge)
{
	echo '<table name="cabins">
			<tr>
				<th>' . $lodge . "</th>
			</tr>";
	$campers = $wpdb->get_results($wpdb->prepare("SELECT camper_first_name,camper_last_name 
									FROM srbc_campers INNER JOIN srbc_registration
									ON srbc_campers.camper_id=srbc_registration.camper_id
									WHERE srbc_registration.camp_id=%d AND srbc_registration.cabin=%s",$_GET['camp_id'],$lodge));
	echo '<tr><td><input type="text" name="counselor" placeholder="Counselor"></td></tr>';
	//Show total in this lodge
	echo '<tr><td style="color:red">Total: ' . count($campers) . "</td></tr>";					
	//Add camper button
	echo '<tr><td><button onclick="updateCamperLodging(\''. $lodge . '\',' . $count . ')">Add Camper</button></td></tr>';	
	
	foreach($campers as $camper)
	{
		echo '<tr><td style="color:blue;font-size:medium;">' . $camper->camper_first_name . " " . $camper->camper_last_name . "</td></tr>";
	}
	
	echo "</table>";
	$count++;
}




?>