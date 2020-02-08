<?php
//Collection of various random functions that come in handy
define('srbc_email', 'solidrockbiblecamp@gmail.com');
define('workcrew_email', 'n_mcgilvra@alaska.net');
define('wit_email', 'courtneyprocter@alaska.net');
define('developer_email', 'armystorms@gmail.com');

$GLOBALS['srbc_camps'] = "srbc_camps" . get_option("srbc_database_year");
$GLOBALS['srbc_payments'] = "srbc_payments" . get_option("srbc_database_year");
$GLOBALS['srbc_registration'] = "srbc_registration" . get_option("srbc_database_year");
$GLOBALS['srbc_registration_inactive'] = "srbc_registration_inactive" . get_option("srbc_database_year");

//Echoes an error msg to the user with red
function error_msg($msg)
{
	echo '<h2 style="color:red;text-align:center">' .$msg.'</h2>';
}

//Checks if the user is logged in when viewing this file
function securityCheck()
{
	if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");
}

//Sets up the HTML necessary to use the modal as the javascript
function modalSetup(){	
	echo '<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/modal.css">
			
		<!--Modal box Example fom W3schools-->
		<!-- The Modal -->
		<div id="myModal" class="modal">
			<!-- Modal content -->
			<div id="modal-content" class="modal-content">
				
			</div>
		</div>
		 <script src="../wp-content/plugins/SRBC/admin/modal.js"></script>';
	
}
?>