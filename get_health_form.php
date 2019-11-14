<?php
$camper_id = null;
$camperIdsObj = null;

//This should handle a json object with multiple camper_ids and a single camper id
if(isset($_GET['c_id']))
    $camper_id = $_GET['c_id'];
else
    $camper_ids = explode($_POST["camper_ids"]);
//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

require_once 'requires/health_form.php';

HealthForm::before();
//Just one camper id so generate one health form
if($camper_id != null)
    HealthForm::generateHealthForm($camper_id);
else
{
    //Now we have multiple loop through
    echo "Not implemented";
    foreach($camperIdsObj as $id)
    {

    }
}
HealthForm::after();
?>
