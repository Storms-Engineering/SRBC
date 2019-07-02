<?php
//Load wordpress database to use wpdb
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require('requires/email.php');
global $wpdb;
Email::sendConfirmationEmail($_GET['r_id']);

?>