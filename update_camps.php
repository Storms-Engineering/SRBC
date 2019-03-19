<?php
//Makes updates camps or deletes or creates new ones
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

if (isset($obj["deleteid"])) {
    //If this is set then we are deleting a camp
	$wpdb->delete( 'srbc_camps', array( 'camp_id' => $obj["deleteid"] ) );
	echo "Camp Deleted and Data Saved Sucessfully";
	exit;
}
else if (isset($obj["camp_id"])) {
    //If this is set then we are updating a camp
	
	//TODO: Need to add logic here to catch if they are increasing the size of horses waitlist
	//And then magic shuffle the waitlist
	//echo "MAGICK SHUFFLE";
	
	
	$wpdb->update( 
	'srbc_camps', 
	array( 
		'area' => $obj["area"],	
		'name' => $obj["name"],	
		'description' => $obj["description"],	
		'start_date' => $obj["start_date"],	
		'end_date' => $obj["end_date"],	
		'cost' => $obj["cost"],	
		'horse_opt' => $obj["horse_opt"],
		'horse_list_size' => $obj["horse_list_size"],
		'waiting_list_size' => $obj["waiting_list_size"],
		'horse_waiting_list_size' => $obj["horse_waiting_list_size"],
		'boy_registration_size' => $obj["boy_registration_size"],
		'girl_registration_size' => $obj["girl_registration_size"],
		'overall_size' => $obj["overall_size"],
		'grade_range' => $obj["grade_range"]
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
		'%s'
	),
	array( '%d' ) 
	);
	echo "Camp Updated and Data Saved Sucessfully";
	exit;
}

//Current key for database
$wpdb->insert( 
	'srbc_camps', 
	array( 
		'area' => $obj["area"],	
		'name' => $obj["name"],	
		'description' => $obj["description"],	
		'start_date' => $obj["start_date"],	
		'end_date' => $obj["end_date"],	
		'cost' => $obj["cost"],	
		'horse_opt' => $obj["horse_opt"],
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
		'%s'
	) 
);
echo "Data Saved Sucessfully";

?>