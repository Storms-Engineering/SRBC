<?php
//For testing so it will email me on the localsite
//if ($_SERVER['SERVER_NAME'] == "127.0.0.1"  )
//{
    define('srbc_email', 'armystorms@gmail.com');
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
?>