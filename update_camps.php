<?php
//Makes updates camps or deletes or creates new ones
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Security check - kinda
if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");
global $wpdb;

if (isset($obj["deleteid"])) {
    //If this is set then we are deleting a camp
	$wpdb->delete( $GLOBALS['srbc_camps'], array( 'camp_id' => $obj["deleteid"] ) );
	echo "Camp Deleted and Data Saved Sucessfully";
	exit;
}
else if (isset($obj["camp_id"])) {
    //If this is set then we are updating a camp
	
	$registrations = $wpdb->get_results($wpdb->prepare("SELECT registration_id,waitlist FROM " . $GLOBALS['srbc_registration'] . "
						WHERE waitlist=1 AND camp_id=%s ORDER BY registration_id ASC",$obj["camp_id"]));
	$oldOverallSize = $wpdb->get_var($wpdb->prepare("SELECT overall_size FROM " . $GLOBALS["srbc_camps"] . " WHERE camp_id=%d",$obj["camp_id"]));
	echo "Old size" . $oldOverallSize;
	$howMany = $obj["overall_size"] - $oldOverallSize;
	echo "How many:" . $howMany;
	//Change the first registration	and check that there are actually people on the waitlist
	if (count($registrations) != 0 && $obj["overall_size"] > $oldOverallSize)
	{
		$oldOverallSize = $wpdb->get_var($wpdb->prepare("SELECT overall_size FROM " . $GLOBALS["srbc_camps"] . " WHERE camp_id=%d",$obj["camp_id"]));
		$howMany = $obj["overall_size"] - $oldOverallSize;
		//So we don't go over how many spots we have available
		if ($howMany > count($registrations))
			$howMany = count($registrations);
			
		for ($i=0;$i<$howMany;$i++)
		{
			$wpdb->update( 
			$GLOBALS['srbc_registration'], 
			array( 
				'waitlist' => 0
			), 
			array( 'registration_id' => $registrations[$i]->registration_id ), 
			array( 
				'%d'	
			), 
			array( '%d' ) 
			);
			//Resend confirmation email
			$_GET["r_id"] = $registrations[$i]->registration_id;
			include 'resend_email.php';
		}
	}
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
	$GLOBALS['srbc_camps'], 
	array( 
		'area' => $obj["area"],	
		'name' => $obj["name"],	
		'description' => $obj["description"],	
		'start_date' => $obj["start_date"],	
		'end_date' => $obj["end_date"],	
		'cost' => $obj["cost"],	
		'horse_cost' => $obj["horse_cost"],	
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
		'%d',
		'%s'
	) 
);
echo "Data Saved Sucessfully";

?>