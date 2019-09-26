<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';

class Email
{
	//Queries database for camper information
	private static function getInfo($registration_id)
	{
		global $wpdb;
		$info = $wpdb->get_results($wpdb->prepare( $query = "SELECT *
			FROM ((" . $GLOBALS['srbc_registration'] . " 
			INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id)
			INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE " . $GLOBALS['srbc_registration'] . ".registration_id=%d", 
			$registration_id)); 	
		//We only need the first object
		return $info[0];
	}
	
	//Requires the registration_id
	public static function sendWaitlistEmail($registration_id)
	{
		$info = self::getInfo($registration_id);
		$msg = "Hi ". $info->parent_first_name . ",<br><br>This is an email letting you know that you sucessfully put " . $info->camper_first_name . " on the waitlist for " . $info->area . " " . $info->name . "." . 
		"<br>We will email you and let you know if a spot opens up for the camp.<br>Thanks!<br><br> -Solid Rock Bible Camp<br><br>36251 Solid Rock Rd #1<br>
			Soldotna, AK 99669<br>
			phone: (907) 262-4741<br>
			fax: (907) 262-9088<br>
			srbc@alaska.net";
		self::sendMail($info->email,"Waitlist Confirmation",$msg);
	}
	
	//Sends confirmation email.
	//Info is the information that we need in an object
	public static function sendDaycampConfirmationEmail($registration_id)
	{
		$info = self::getInfo($registration_id);
		$msg = "<html><body>
		Hello ". $info->parent_first_name . ",<br><br>Thanks for signing up " . $info->camper_first_name . " for <b>" . $info->area . " " . $info->name . "</b>!  <br><br>
		<b>Camp Start date</b>:" .date("l, M j,Y",strtotime($info->start_date)) . '<br><pre style="display:inline">	</pre><b>Camp Drop off time</b>: 8:00am*' . 
		"<br><br><b>Camp End date</b>:" .date("l, M j,Y",strtotime($info->end_date)) . '<br><pre style="display:inline">	</pre><b>Camp Pick up time</b>: 5:00pm*<br><br>' . 
		"*Notify office about early dropoff or late pickup.<br>" . 
		"A <b>health form</b> has been attached. Please fill it out and bring it on the opening day of camp.<br><br>" .
		"<br><br>See you at camp!<br><br> -Solid Rock Bible Camp<br><br>36251 Solid Rock Rd #1<br>
		Soldotna, AK 99669<br>
		phone: (907) 262-4741<br>
		fax: (907) 262-9088<br>
		srbc@alaska.net
		</body>
		</html>";
		self::sendMail($info->email,"Thank you for signing up for a Solid Rock Camp!", $msg, $_SERVER['DOCUMENT_ROOT'].'/attachments/healthform2.pdf');
		echo "Email Sent!";
	}
	
	//Sends confirmation email.
	//Info is the information that we need in an object
	public static function sendConfirmationEmail($registration_id)
	{
		$info = self::getInfo($registration_id);
		$msg = "<html><body>
		Hello ". $info->parent_first_name . ",<br><br>Thanks for signing up " . $info->camper_first_name . " for <b>" . $info->area . " " . $info->name . "</b>!  <br><br>
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
		srbc@alaska.net
		</body>
		</html>";
		self::sendMail($info->email,"Thank you for signing up for a Solid Rock Camp!", $msg, $_SERVER['DOCUMENT_ROOT'].'/attachments/healthform.pdf');
		echo "Email Sent!";
	}
	//Sends mail just a bit easier to use than declaring the class everytime
	public static function sendMail($to,$subject,$msg,$attachment = "")
	{

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
		} 
		catch (Exception $e) 
		{
			echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
		}
	}
	

}
?>
