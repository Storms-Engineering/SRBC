<?php
//For testing so it will email me on the localsite
//if ($_SERVER['SERVER_NAME'] == "127.0.0.1"  )
//{
    define('srbc_email', 'info@solidrockbiblecamp.com');
//}
//else
//{
//    define('srbc_email', 'srbc@alaska.net');
//}



//Collection of random functions that might come in handy
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
		$mail->setFrom('info@solidrockbiblecamp.com', 'Solid Rock Bible Camp');
		$mail->addAddress($to);     // Add a recipient
		//Attachments
		if ($attachment != "")
			$mail->addAttachment($attachment);         // Add attachments
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $msg;

		$mail->send();
	} catch (Exception $e) {
		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	}
	
}

function resendConfirmationEmail($registration_id){
	global $wpdb;
	$camper = $wpdb->get_results($wpdb->prepare( "SELECT *
		FROM (srbc_registration 
		INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id) WHERE registration_id=%d", $registration_id)); 
		;
			
	$message = "Hi ". $parent_first_name . ",<br><br>Thanks for signing up " . $camper_first_name . " for " . $_POST["camp_desc"] . "!  Camp starts " .date("D M j",strtotime($camp->start_date)) . " and ends " . 
	date("D M j",strtotime($camp->end_date)) . "!  If you have any questions feel free to check ". 
	'our <a href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.  If you want to know what your child should pack for camp, check out our <a href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>!'.
	"<br> One last thing is that we ask that you print out this health form and fill it out to speed up the registration process.<br>Thanks!<br> -Solid Rock Bible Camp";
	sendMail($email,"Thank you for signing up for a Solid Rock Camp!",$message,$_SERVER['DOCUMENT_ROOT']. '/attachments/healthform.pdf');	
}

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