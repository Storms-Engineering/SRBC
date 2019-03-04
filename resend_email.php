<?php
//Load wordpress database to use wpdb
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//require 'PHPMailer/src/Exception.php';
//require 'PHPMailer/src/PHPMailer.php';
global $wpdb;

	$info = $wpdb->get_results($wpdb->prepare( $query = "SELECT *
		FROM ((srbc_registration 
		INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
		INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id) WHERE srbc_registration.registration_id=%d", $_GET["r_id"])); 	
	//We only need the first object
	$info = $info[0];
	$msg = "Hi ". $info->parent_first_name . ",<br><br>Thanks for signing up " . $info->camper_first_name . " for " . $info->area . ' ' . $info->name . "!  Camp starts " .date("D M j",strtotime($info->start_date)) . " and ends " . 
	date("D M j",strtotime($info->end_date)) . "!  If you have any questions feel free to check ". 
	'our <a href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.  If you want to know what your child should pack for camp, check out our <a href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>!'.
	"<br> One last thing is that we ask that you print out this health form and fill it out to speed up the registration process.<br>Thanks!<br> -Solid Rock Bible Camp";
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {

		//Recipients
		$mail->setFrom('info@solidrockbiblecamp.com', 'Solid Rock Bible Camp');
		$mail->addAddress($info->email);     // Add a recipient
		//Attachments
		$mail->addAttachment($_SERVER['DOCUMENT_ROOT'].'/attachments/healthform.pdf');         // Add attachments
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = "Thank you for signing up for a Solid Rock Camp!";
		$mail->Body    = $msg;

		$mail->send();
	} catch (Exception $e) {
		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
		return;
	}
	echo "Email Sent!";
?>