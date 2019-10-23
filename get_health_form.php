<?php
$obj = json_decode( stripslashes($_POST["x"]), true);

//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require_once 'requires/health_form.php';
echo '<html>
<head>
<link rel="stylesheet" type="text/css" href="/wp-content/plugins/SRBC/admin/registration.css">
</head>
<body>';

HealthForm::generateHealthForm($obj);

echo "</html></body>";
?>
