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
			'counselor' => $_GET['counselor'],
			'assistant_counselor' => $_GET['assistant_counselor']
		), 
		array( 'registration_id' => $_GET['registration_id']), 
		array( 
			'%s',
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
"Lakeside"    => ["Augustine" => 12, "Spurr" => 12, "Susitna" => 12, "Illiamna" => 10, "Redoubt" => 12, "Denali" => 0
					, "Spruce/Willow" => 12, "Birch/Aspen" => 12, "Tustumena" => 16, "Skilak" => 16, "Beluga"=>12, "Kenai" =>12],
"Wagon Train" => ["Wagon 1" => 8, "Wagon 2"=>8, "Wagon 3"=>8, "Wagon 4"=>8],
"Wilderness"  => ["Guys Tent"=>"∞", "Girls Tent"=>"∞"] ];

$lodgesCanHold = $lodging[$_GET['area']];
$lodgeNames = array_keys($lodgesCanHold);
$count = 0;
foreach($lodgesCanHold as $lodgeCanHold)
{
	echo '<table name="cabins">
			<tr>
				<th colspan="2">' . $lodgeNames[$count] . "</th>
			</tr>";
	$campers = $wpdb->get_results($wpdb->prepare("SELECT camper_first_name,camper_last_name 
									FROM srbc_campers INNER JOIN srbc_registration
									ON srbc_campers.camper_id=srbc_registration.camper_id
									WHERE srbc_registration.camp_id=%d AND srbc_registration.cabin=%s",$_GET['camp_id'],$lodgeNames[$count]));
	echo '<tr><td colspan="2"><input type="text" name="counselor" placeholder="Counselor"><br>
							  <input type="text" name="assistant_counselor" placeholder="Assistant Counselor"></td></tr>';
	//Show total in this lodge
	echo '<tr><td style="color:red">Total: ' . count($campers) . "</td><td>Max:" . $lodgeCanHold ."</td></tr>";					
	//Add camper button
	echo '<tr><td colspan="2"><button class="big_button" style="display:block;margin:auto;" onclick="updateCamperLodging(\''. $lodgeNames[$count] . '\',' . $count . ')">Add Camper</button></td></tr>';	
	
	foreach($campers as $camper)
	{
		//TODO I am cheating deleting campers out of a cabin
		//BODY instead I am just deleting in javascript and assuming they will assign this camper somewhere else.  Make sure this works well 
		echo '<tr><td style="color:blue;font-size:medium;">' . $camper->camper_first_name . " " . $camper->camper_last_name . '</td>
			<td><button class="big_button" style="background:red;" onclick="removeCamper(this.parentNode.parentNode.rowIndex,this.parentNode.parentNode.parentNode.parentNode);">Delete</button></td></tr>';
	}
	
	echo "</table>";
	$count++;
}




?>