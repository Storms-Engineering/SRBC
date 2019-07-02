<?php
//Load wordpress database to use wpdb
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require('requires/email.php');
global $wpdb;
//TODO move this to the functions file I think and then I can just call it from here
$info = $wpdb->get_results($wpdb->prepare( $query = "SELECT *
	FROM ((" . $GLOBALS['srbc_registration'] . " 
	INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id)
	INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d", 
	$_GET["r_id"])); 	
//We only need the first object
$info = $info[0];
//TODO this email needs to be in one place.  
//BODY probably put this in the functions file
Email::sendConfirmationEmail($info);

?>