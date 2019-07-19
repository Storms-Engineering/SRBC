<?php
//Import $wpdb for wordpress
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
//Custom Security Check
if (!is_user_logged_in() && $_GET["report"] !== "camp_numbers") exit("Thus I refute thee.... P.H.");

require 'requires/reports.php';

$thisReport = new Report($_GET['start_date'],$_GET['camp'],$_GET['buslist_type']);
$thisReport->{$_GET['report']}();

?>