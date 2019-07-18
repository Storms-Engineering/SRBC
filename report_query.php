<?php
//Import $wpdb for wordpress
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Custom Security Check
if (!is_user_logged_in() && !isset($_GET["camp_numbers"])) exit("Thus I refute thee.... P.H.");
global $wpdb;
//Check these values first because it doesn't follow a normal report query format
require 'requires/reports.php';

$thisReport = new Report($_GET['start_date'],$_GET['end_date'],$_GET['camp'],$_GET['buslist_type']);
$thisReport->{$_GET['report']}();

?>