<?php
//Makes updates camps or deletes or creates new ones
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();
global $wpdb;

if (isset($obj["deleteid"])) {
    //If this is set then we are deleting a camp
	$wpdb->delete( $GLOBALS['srbc_camps'], array( 'camp_id' => $obj["deleteid"] ) );
	echo "Camp Deleted and Data Saved Sucessfully";
	exit;
}
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
		'closed_to_registrations' =>  $obj["closed_to_registrations"]
	), 
	array( 'camp_id' => $obj["camp_id"]), 
	array( 
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
		'cost' => $obj["cost"],	
		'horse_cost' => $obj["horse_cost"],	
		'horse_opt_cost' => $obj["horse_opt_cost"],
		'horse_list_size' => $obj["horse_list_size"],
		'waiting_list_size' => $obj["waiting_list_size"],
		'horse_waiting_list_size' => $obj["horse_waiting_list_size"],
		'boy_registration_size' => $obj["boy_registration_size"],
		'girl_registration_size' => $obj["girl_registration_size"],
		'overall_size' => $obj["overall_size"],
		'grade_range' => $obj["grade_range"]
	), 
	array( 
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
		'%s'
	) 
);
echo "Data Saved Sucessfully";

?>