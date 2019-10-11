<?php
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require_once 'requires/health_form.php';

HealthForm::generateHealthForm($obj);
?>
