<?php
function error_msg($msg)
{
	echo '<p style="color:red;text-align:center">' .$msg.'</p>';
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to,$subject,$msg,$attachment = ""){
	require 'PHPMailer/src/Exception.php';
	require 'PHPMailer/src/PHPMailer.php';
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {

		//Recipients
		$mail->setFrom('info@solidrockbiblecamp.com', 'Mailer');
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