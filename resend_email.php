<?php
//Load wordpress database to use wpdb
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
securityCheck();

global $wpdb;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//TODO move this to the functions file I think and then I can just call it from here
global $wpdb;

	$info = $wpdb->get_results($wpdb->prepare( $query = "SELECT *
		FROM ((" . $GLOBALS['srbc_registration'] . " 
		INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id)
		INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d", $_GET["r_id"])); 	
	//We only need the first object
	$info = $info[0];
	//TODO this email needs to be in one place.  
	//BODY probably put this in the functions file
	$msg = "Hello ". $info->parent_first_name . ",<br><br>Thanks for signing up " . $info->camper_first_name . " for <b>" . $info->area . " " . $info->name . "</b>!  <br><br>
	<b>Camp Start date</b>:" .date("l, M j,Y",strtotime($info->start_date)) . '<br><pre style="display:inline">	</pre><b>Camp Drop off time</b>: 5:00pm' . 
	"<br><br><b>Camp End date</b>:" .date("l, M j,Y",strtotime($info->end_date)) . '<br><pre style="display:inline">	</pre><b>Camp Pick up time</b>: 9:00am<br><br>' . 
	"A <b>health form</b> has been attached. Please fill it out and bring it on the opening day of camp.<br><br>" .
	'If your camper is riding the bus please refer to our FAQ page for bus stop location and times.<br><br>' .
	'If you have any other questions please refer to our <a href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.<br><br>' .
	'If you want to know what your camper should pack for camp, check out our <a href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>.'.
	"<br><br>See you on the opening day of camp!<br><br> -Solid Rock Bible Camp<br><br>36251 Solid Rock Rd #1<br>
	Soldotna, AK 99669<br>
	phone: (907) 262-4741<br>
	fax: (907) 262-9088<br>
	srbc@alaska.net";
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