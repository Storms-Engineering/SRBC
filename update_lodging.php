<?php
//Camper Lodging Assignment - This lets program assign campers to specific lodging and a counselor
//Can also be queried to show current lodging and counselor assignemnts

//Import wordpress stuff we need
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
securityCheck();

//Check if we are updating and do so
if(isset($_GET['registration_id']))
{
	$values = array_keys($_GET);
	$vals = array();
	foreach($values as $value)
	{
		if ($value !== "registration_id")
		{
			$vals[$value] = $_GET[$value];
		}
	}
	$checked_in = $wpdb->get_var($wpdb->prepare("SELECT checked_in FROM " . $GLOBALS['srbc_registration'] . " WHERE registration_id=%d",$_GET['registration_id']));
	if($checked_in == 0)
	{
		exit("Please make sure that the camper checks in with registration before getting assigned to a cabin.
					They are currently not checked in according to my records...");
	}
	$wpdb->update( 
		'srbc_registration', 
		$vals, 
		array( 'registration_id' => $_GET['registration_id']), 
		array( 
			'%s',
			'%s',
			'%s'
		), 
		array( '%d' ) 
	);
	exit("Sucessfully added camper");
}


//TODO Notice these sql queries are not archive ready. 
//BODY Basically they will only query the current database.  AKA using the $GLOBALS keyword

$lodging = [
"Lakeside"    => ["Augustine - 1" => 12, "Spurr - 3" => 12, "Susitna - 4" => 12, "Illiamna - 2" => 10, "Redoubt - 5" => 12, "Denali - 0" => 0
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
	$campers = $wpdb->get_results($wpdb->prepare("SELECT camper_first_name,camper_last_name,counselor,assistant_counselor, registration_id
									FROM srbc_campers INNER JOIN srbc_registration
									ON srbc_campers.camper_id=srbc_registration.camper_id
									WHERE srbc_registration.camp_id=%d AND srbc_registration.cabin=%s",$_GET['camp_id'],$lodgeNames[$count]));
	
	$counselor = (count($campers) == 0) ? null : $campers[0]->counselor;
	$assistant_counselor = (count($campers) == 0) ? null : $campers[0]->assistant_counselor;
	
	
	echo '<tr><td colspan="2"><input type="text" name="counselor" placeholder="Counselor" value="'. $counselor . '"><br>
							  <input type="text" name="assistant_counselor" placeholder="Assistant Counselor" value="'.$assistant_counselor .'">
							  <button class="big_button" style="margin:auto;" onclick="updateCamperLodging(\''. $lodgeNames[$count] . '\',' . $count . ')">Add Camper</button><br>
							  <span style="color:red;text-align:center;">Total: ' . count($campers) . "/" . $lodgeCanHold . '</span>
							  </td></tr>';	
	
	foreach($campers as $camper)
	{
		//TODO I am cheating deleting campers out of a cabin
		//BODY instead I am just deleting in javascript and assuming they will assign this camper somewhere else.  Make sure this works well 
		echo '<tr><td style="color:blue;font-size:medium;">' . $camper->camper_first_name . " " . $camper->camper_last_name . '</td>
			<td><button class="big_button" style="background:red;" onclick="removeCamper(this.parentNode.parentNode.rowIndex,this.parentNode.parentNode.parentNode.parentNode);">Delete</button>
			<button class="big_button" onclick="addNoteToCamper(' . $camper->registration_id . ')">Add Note</button></td></tr>';
	}
	
	echo "</table>";
	$count++;
}




?>