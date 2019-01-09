<?php
//Makes camp edits or deletes them
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
//Check if we are deleting a camp
if (isset($obj["deleteid"])) {
    //If this is set then we are deleting a camp
	$wpdb->delete( 'srbc_camps', array( 'camp_id' => $obj["deleteid"] ) );
	echo "Camp Deleted and Data Saved Sucessfully";
	exit;
}

//Current key for database
$wpdb->insert( 
	'srbc_camps', 
	array( 
		'area' => $obj["area"],	
		'camp_description' => $obj["camp_description"],	
		'start_date' => $obj["start_date"],	
		'end_date' => $obj["end_date"],	
		'cost' => $obj["cost"],	
		'horse_opt' => $obj["horse_opt"],
		'waiting_list_size' => $obj["waiting_list_size"],
		'boy_registration_size' => $obj["boy_registration_size"],
		'girl_registration_size' => $obj["girl_registration_size"],
		'overall_size' => $obj["overall_size"]
	), 
	array( 
		'%s',	
		'%s',
		'%s',	
		'%s',	
		'%d',
		'%d',
		'%d',	
		'%d',
		'%d',
		'%d'
	) 
);
echo "Data Saved Sucessfully";

?>