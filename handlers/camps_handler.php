<?php
//This is a handler file.  Basically for handling ajax javascript requests segregrated by operations
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();
global $wpdb;

require_once __DIR__ . '/../requires/email.php';
require_once __DIR__ . '/../requires/camper_search.php';

//Send balance due email to everyone in a camp (except waitlisted people)
if(isset($obj["emails_camp_id"]))
{
    //Grab all non waitlisted campers
    $campers = CamperSearch::getCampersByCampID($obj["emails_camp_id"],false);
    foreach($campers as $camper)
    {
        //Suppress the echo email sent!
        ob_start();
        Email::emailParentRemainingBalance($camper->registration_id);
        ob_end_clean();
    }
    echo "Emails Sent!";
}

?>