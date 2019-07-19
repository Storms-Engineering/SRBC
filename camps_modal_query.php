<?php
	echo '<div class="modal-header"><span onclick="closeModal();" class="close">&times;</span>
	<h2>';
	
	//This returns the data for one specific camper.
	require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	securityCheck();
	global $wpdb;
	$camp = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_camps'] ." WHERE camp_id = %d",$_GET['camp_id'] ));
	echo $camp->area . " " . $camp->name;
	echo "</h2></div>";
	echo '<button class="big_button" style="float:right;" onclick="saveInfo(' . $camp->camp_id . ');">Save Info & Close</button>';
	echo '<div class="modal-body">';
	echo 'Area: <input name="area" type="text" value="' . $camp->area . '">';
	echo 'Camp: <input name="name" type="text" value="' . $camp->name . '"><br>';
	//We need this url decode so that we can pass on data to the server and qoutes and spaces don't get all messed up.
	//We need to decode it here so it isn't double encoded when they save it again and so they can read it properly
	echo 'Description: <textarea class="description" rows="2" cols="30">' . rawurldecode($camp->description) . '</textarea>';
	echo 'Start Date: <input name="start_date" type="date" value="' . $camp->start_date .'">';
	echo 'End Date: <input type="date" name="end_date" value="' . $camp->end_date . '"><br>';
	echo 'Cost: $<input name="cost" type="text" value="' . $camp->cost . '">';
	echo 'Horse Cost: $<input name="horse_cost" type="text" value="' . $camp->horse_cost . '">';
	echo 'Horse Option Cost: $<input type="text" name="horse_opt_cost" value="' . $camp->horse_opt_cost . '"><br></span>';
	echo 'Horse List Size: <input type="text" name="horse_list_size" value="' . $camp->horse_list_size . ' ">';
	echo 'Horse Waiting List Size: <input type="text" name="horse_waiting_list_size" value="'.$camp->horse_waiting_list_size.'"><br>';
	echo 'Waiting List Size: <input name="waiting_list_size" type="text" value="' . $camp->waiting_list_size . '">';
	echo 'Boys Allowed to Register: <input name="boy_registration_size" type="text" value="' . $camp->boy_registration_size . '"><br>';
	echo 'Girls Allowed to Register: <input name="girl_registration_size" type="text" value="' . $camp->girl_registration_size . '">';
	echo 'Overall Size: <input name="overall_size" type="text" value="' . $camp->overall_size . '">';
	echo 'Grade Range: <input name="grade_range" type="text" value="' . $camp->grade_range . '">';
	$closed = $camp->closed_to_registrations == 0 ? "" : "checked";
	echo '<br>Closed To Registrations <input type="checkbox" name="closed_to_registrations" ' . $closed . '><br><br>';
	
	require_once 'requires/tables.php';
	require_once 'requires/camper_search.php';
	$campers = CamperSearch::getCampersByCampID($camp->camp_id);
	Tables::createTable($campers);
	
	echo "</div>";
	echo '<div class="modal-footer"><button  onclick="saveInfo(' . $camp->camp_id . ');" class="big_button">Save Info & Close</button></div>';

	
?>