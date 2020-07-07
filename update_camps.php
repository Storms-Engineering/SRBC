<?php
//Updates camps or deletes or creates new ones
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();
require_once __DIR__ . '/requires/Camp.php';
global $wpdb;

if (isset($obj["deleteid"])) {
    //If this is set then we are deleting a camp
	//Check nonce and send them elsewhere if it fails
	if (!wp_verify_nonce( $obj['_wpnonce'], 'delete-camp_'.$obj["deleteid"]))
		wp_nonce_ays("");
	Camp::deleteCamp($obj["deleteid"]);
	exit;
}

//TODO Replace instead of 2 seperate queries?
//Can we just use replace for this?
//If this is set then we are updating a camp
else if (isset($obj["camp_id"])) {
//TODO update to an array and array keys
	
	//Update database entry
	$wpdb->update( 
	$GLOBALS['srbc_camps'], 
	array( 
		'area' => $obj["area"],	
		'name' => $obj["name"],	
		'description' => $obj["description"],	
		'start_date' => $obj["start_date"],	
		'end_date' => $obj["end_date"],	
		'dropoff_time' => $obj["dropoff_time"],	
		'pickup_time' => $obj["pickup_time"],	
		'cost' => $obj["cost"],	
		'horse_cost' => $obj["horse_cost"],	
		'horse_opt_cost' => $obj["horse_opt_cost"],
		'horse_list_size' => $obj["horse_list_size"],
		'waiting_list_size' => $obj["waiting_list_size"],
		'horse_waiting_list_size' => $obj["horse_waiting_list_size"],
		'boy_registration_size' => $obj["boy_registration_size"],
		'girl_registration_size' => $obj["girl_registration_size"],
		'overall_size' => $obj["overall_size"],
		'grade_range' => $obj["grade_range"],
		'closed_to_registrations' =>  $obj["closed_to_registrations"],
		'day_camp' =>  $obj["day_camp"]
	), 
	array( 'camp_id' => $obj["camp_id"]), 
	array( 
		'%s',	
		'%s',
		'%s',
		'%s',	
		'%s',	
		'%s',	
		'%s',	
		'%d',
		'%d',
		'%d',
		'%d',
		'%d',	
		'%d',	
		'%d',
		'%d',
		'%d',
		'%s',
		'%d',
		'%d'
	),
	array( '%d' ) 
	);
	echo "Camp Updated and Data Saved Sucessfully";
	exit;
}

//Current key for database
$wpdb->insert( 
	$GLOBALS['srbc_camps'], 
	array( 
		'area' => $obj["area"],	
		'name' => $obj["name"],	
		'description' => $obj["description"],	
		'start_date' => $obj["start_date"],	
		'end_date' => $obj["end_date"],	
		'dropoff_time' => $obj["dropoff_time"],	
		'pickup_time' => $obj["pickup_time"],	
		'cost' => $obj["cost"],	
		'horse_cost' => $obj["horse_cost"],	
		'horse_opt_cost' => $obj["horse_opt_cost"],
		'horse_list_size' => $obj["horse_list_size"],
		'waiting_list_size' => $obj["waiting_list_size"],
		'horse_waiting_list_size' => $obj["horse_waiting_list_size"],
		'boy_registration_size' => $obj["boy_registration_size"],
		'girl_registration_size' => $obj["girl_registration_size"],
		'overall_size' => $obj["overall_size"],
		'grade_range' => $obj["grade_range"],
		'day_camp' =>  $obj["day_camp"]
	),
	array( 
		'%s',	
		'%s',
		'%s',
		'%s',	
		'%s',	
		'%s',	
		'%s',	
		'%d',
		'%d',
		'%d',
		'%d',
		'%d',	
		'%d',	
		'%d',
		'%d',
		'%d',
		'%s',
		'%d',
		'%d'
	)
);
echo "Data Saved Sucessfully";

?>