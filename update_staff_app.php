<?php
//Makes camp edits or deletes or creates new ones them
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();
global $wpdb;
//Check if we are deleting a camp
if (isset($obj["deleteid"])) {
    //If this is set then we are deleting a camp
    if(wp_verify_nonce($obj["wp_nonce"], 'delete_ssn_'.$obj["deleteid"] ))
    {
        $wpdb->delete( 'srbc_staff_app', array( 'staff_app_id' => $obj["deleteid"] ) );
        echo "Camp Deleted and Data Saved Sucessfully";
    }
    else
        wp_nonce_ays("");
	
	exit;
}
?>