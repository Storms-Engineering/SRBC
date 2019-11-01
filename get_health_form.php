<?php
$camper_id = null;
$camperIdsObj = null;

//This should handle a json object with multiple camper_ids and a single camper id
if(isset($_GET['c_id']))
    $camper_id = $_GET['c_id'];
else
    $camperIdsObj = json_decode( stripslashes($_POST["x"]), true);
//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require_once 'requires/health_form.php';
echo '<html>
<head>
<link rel="stylesheet" type="text/css" href="/wp-content/plugins/SRBC/css/health_form.css">
</head>
<body>
<div id="no-print">
<input type="password" id="pwd"> Decryption Progress <progress value="0" id="progress"></progress>
<div ondrop="drop(event)" ondragover="allowDrop(event)" style="background:lightblue;height:50px;width:400px;float:right;">
Drop key file here</div>
</div>';
//Just one camper id so generate one health form
if($camper_id != null)
    HealthForm::generateHealthForm($camper_id);
else
{
    //Now we have multiple loop through
    echo "Not implemented";
}
echo '<script src="/wp-content/plugins/SRBC/requires/js/forge.min.js"></script>
    <script src="/wp-content/plugins/SRBC/JSEncrypt/jsencrypt.min.js"></script>
	<script src="/wp-content/plugins/SRBC/Jsrsasign/jsrsasign-all-min.js"></script>
	<script src="/wp-content/plugins/SRBC/js/health_form.js"></script>';
echo "</html></body>";
?>
