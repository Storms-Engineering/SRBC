<?php
//Load wordpress database to use wpdb
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require_once 'requires/email.php';
global $wpdb;
$info = $wpdb->get_results($wpdb->prepare("SELECT *
			FROM ((srbc_registration 
			INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
			INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id) WHERE srbc_registration.registration_id=%d", 
            $_GET['r_id'])); 	

//For resending confirmation emails
if($_GET["type"] == "resend")
{
    //We only need the first object
    if($info[0]->area == "Workcrew" || $info[0]->area == "WIT")
    {
        $isWit = ($info[0]->area == "WIT");
        Email::sendWorkcrewEmail($info[0]->camper_id,null,$isWit);
    }
    else
    {
        if($info[0]->day_camp == 0)
            Email::sendConfirmationEmail($_GET['r_id']);
        else
            Email::sendDayCampConfirmationEmail($_GET['r_id']);
    }
}
//For sending balance due emails
else if($_GET['type'] == "balance_due")
{
    Email::emailParentRemainingBalance($_GET['r_id']);
}

    

?>