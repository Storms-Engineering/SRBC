<?php
//Collection of various random functions that come in handy
define('srbc_email', 'info@solidrockbiblecamp.com');

//Echoes an error msg to the user with red
function error_msg($msg)
{
	echo '<h2 style="color:red;text-align:center">' .$msg.'</h2>';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';

//Sends mail just a bit easier to use than declaring the class everytime
function sendMail($to,$subject,$msg,$attachment = ""){

	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {

		//Recipients
		$mail->CharSet  = "UTF-8";
		$mail->setFrom('info@solidrockbiblecamp.com', 'Solid Rock Bible Camp');
		$mail->addAddress($to);     // Add a recipient
		//Attachments
		if ($attachment != "")
			$mail->addAttachment($attachment);         // Add attachments
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = stripslashes($msg);
		$mail->DKIM_domain = "solidrockbiblecamp.com";
		$mail->DKIM_private = $_SERVER['DOCUMENT_ROOT']. '/files/emailkeys.prv'; //path to file on the disk.
		$mail->DKIM_selector = "mainkey";// change this to whatever you set during step 2
		$mail->DKIM_passphrase = "";
		$mail->DKIM_identifier = $mail->From;
		$mail->send();
	} catch (Exception $e) {
		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	}
	
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