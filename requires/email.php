<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'email_template.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';

class Email
{
	//Emails parents how much they owe on a camp and a link to let them pay
	public static function emailParentRemainingBalance($registration_id)
	{
		//Get camper information
		$info = self::getInfo($registration_id);
		require_once __DIR__ . '/payments.php';
		$owed = Payments::amountDue($registration_id);


		//bus stuff
		$busInfo = "";
		if($info->busride != "none")
		{
			if($info->busride == "both")
				$busInfo = "round trip bus ride";
			else if($info->busride == "to")
				$busInfo = "<b>one-way</b> bus ride from Anchorage to Camp ";
			else if($info->busride == "from")
				$busInfo = "<b>one-way</b> bus ride from Camp to Anchorage";
		}


		$msg = "Hello ". $info->parent_first_name . ",<br><br>" . $info->camper_first_name . " is registered for " . $info->area . " " . $info->name . "!" .
		"<br><br><b>Camp Start Date:</b> " . date("l, F j, Y",strtotime($info->start_date)) . "<br>
		<b>Drop-Off Time</b>: " . $info->dropoff_time . "<br><br>" .
		"<b>Camp End Date:</b> " . date("l, F j, Y",strtotime($info->end_date)) . "<br>
		<b>Pick-Up Time</b>: " . $info->pickup_time . "<br><br>" . 
		//Extra bus info only if they registered for any bus rides
		($info->busride != "none" ? "<b>BUS:</b> You have signed up for a " . $busInfo . "." : "") .
		($info->busride != "none" ? "<br><br><b>Bus Times:</b><br>
									Opening Day from Anchorage to Camp: Please arrive between 2 – 2:30 PM <br>
									Closing Day from Camp to Anchorage:  Bus will return between 1 – 1:30 PM
									<br><br><b>Location of bus stop</b><br>
									Duluth Trading Company Parking Lot<br>
									8931 Old Seward Hwy Suite A<br>
									Anchorage, AK 99515<br><br>" : "") .
		"<b>Amount due: $$owed</b><br><br>" . 
		'<b>To pay for any camp fees and snack shop, click <a style="color:#688df2" href="http://' . $_SERVER['SERVER_NAME'] . '/make-payment?r_id=' . $registration_id . '">here</a>.</b>
		<br><br>
		For your Lakeside and Wagon Train packing lists, visit our packing list page <a href="https://solidrockbiblecamp.com/camps/packing-lists/">here</a>.
		If this is a Wilderness camp, you should have received a camp-specific packing list from the Wilderness Program Managers.
		<br><br>

		For updates on what our summer will look like, follow us on Facebook for the most current camp news!<br><br>

		Contact our office with any concerns or questions.<br><br>

		We look forward to seeing you and your camper soon!<br><br>

		Thank you!';
		self::sendMail($info->email,"Camp Remaining Balance Due", $msg);
		//TODO fix this for registrations because it looks corny
		//BODY but it is also returned on ajax requests for resending email
		echo "Email Sent!";
	}


	public static function emailDeveloper($information)
	{
		sendMail($GLOBALS['developer_email'],"Message From Peter Hawke", "Greetings Brayden, 
		It seems we are having some problems with the website.  Here is the information I have:\r" . $information);
	}

	//Queries database for camper information
	private static function getInfo($registration_id)
	{
		global $wpdb;
		$info = $wpdb->get_results($wpdb->prepare("SELECT *
			FROM ((srbc_registration 
			INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
			INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id) WHERE srbc_registration.registration_id=%d", 
			$registration_id)); 	
		//We only need the first object
		return $info[0];
	}
	
	//Requires the registration_id
	public static function sendWaitlistEmail($registration_id)
	{
		$info = self::getInfo($registration_id);
		$msg = "Hi ". $info->parent_first_name . ",<br><br>This is an email letting you know that you sucessfully put " . $info->camper_first_name . " on the waitlist for " . $info->area . " " . $info->name . "." . 
		"<br>We will email you and let you know if a spot opens up for the camp.<br>Thanks!";
		self::sendMail($info->email,"Waitlist Confirmation",$msg);
	}
	
	//Sends confirmation email.
	//Info is the information that we need in an object
	public static function sendConfirmationEmail($registration_id)
	{
		$info = self::getInfo($registration_id);

		//Determine if we need to add bus info to this email
		$busInfo = "";
		if($info->busride != "none")
		{
			if($info->busride == "both")
				$busInfo = "round trip bus ride";
			else if($info->busride == "to")
				$busInfo = "<b>one-way</b> bus ride from Anchorage to Camp ";
			else if($info->busride == "from")
				$busInfo = "<b>one-way</b> bus ride from Camp to Anchorage";
		}

		//Begin body of email
		$msg = "Hello ". $info->parent_first_name . ",<br><br>Thanks for signing up " . $info->camper_first_name . " for <b>" . $info->area . " " . $info->name . "</b>!  <br><br>
		<b>Camp Start Date</b>: " .date("l, M j, Y",strtotime($info->start_date)) . '<br><pre style="display:inline">	</pre><b>Camp Drop-off time</b>: ' . $info->dropoff_time . 
		"<br><br><b>Camp End Date</b>: " .date("l, M j, Y",strtotime($info->end_date)) . '<br><pre style="display:inline">	</pre><b>Camp Pick-up time</b>: '. $info->pickup_time . '<br><br>' .
		//Extra bus info only if they registered for any bus rides
		($info->busride != "none" ? "<b>BUS:</b> You have signed up for a " . $busInfo . "." : "") .
		($info->busride != "none" ? "<br><br><b>Bus Times</b><br>
									Opening Day from Anchorage to Camp: Please arrive between 2 - 2:30 PM <br>
									Closing Day from Camp to Anchorage:  Bus will return between 1 – 1:30 PM
									<br><br><b>Location of bus stop</b><br>
									Duluth Trading Company Parking Lot<br>
									8931 Old Seward Hwy Suite A<br>
									Anchorage, AK 99515<br><br>" : "") .
		'If you have any other questions please refer to our <a style="color:#688df2" href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.<br><br>' .
		'If you want to know what your camper should pack for camp, check out our <a style="color:#688df2" href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>.'.
		"<br><br>See you on the opening day of camp!";
		self::sendMail($info->email,"Thank you for signing up for a Solid Rock Camp!", $msg);
		//TODO fix this for registrations because it looks corny
		//BODY but it is also returned on ajax requests for resending email
		echo "Email Sent!";
	}

	//Sends day camp confirmation email.
	//Info is the information that we need in an object
	public static function sendDayCampConfirmationEmail($registration_id)
	{
		$info = self::getInfo($registration_id);
		$msg = "Hello ". $info->parent_first_name . ",<br><br>Thanks for signing up " . $info->camper_first_name . " for <b>" . $info->area . " " . $info->name . "</b>!  <br><br>
		<b>Camp Start Date</b>: " .date("l, M j, Y",strtotime($info->start_date)) . '<br><b>Camp End Date</b>: ' .date("l, M j, Y",strtotime($info->end_date)) . '<br><br><b>Daily Drop-off time</b>: ' . $info->dropoff_time . 
		'<br><b>Daily Pick-up time</b>: '. $info->pickup_time . '<br><br>' . 
		'If you have any other questions please refer to our <a style="color:#688df2" href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.<br><br>' .
		'If you want to know what your camper should pack for camp, check out our <a style="color:#688df2" href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>.'.
		'<br>Follow us on Facebook for current updates regarding camp changes for Summer 2020.' . 
		"<br><br>See you on the opening day of camp!";
		self::sendMail($info->email,"Thank you for signing up for a Solid Rock Camp!", $msg);
		//TODO fix this for registrations because it looks corny
		//BODY but it is also returned on ajax requests for resending email
		echo "Email Sent!";
	}

	//Sends an email to parents letting them know their application was submitted
	//Also sends an email to workcrew manager with questions that they answered
	public static function sendWorkcrewEmail($camper_id,$postdata,$isWit)
	{
		global $wpdb;
		$info = $wpdb->get_row($wpdb->prepare("SELECT *
			FROM " . $GLOBALS['srbc_campers'] . "
			 WHERE " . $GLOBALS['srbc_campers'] . ".camper_id=%d", 	$camper_id)); 	
			 
		$wcwit = ($isWit) ? "WIT" : "Workcrew";
		//Email section for emailing the workcrew/wit leaders the questionaire that was filled out
		if($postdata !== NULL)
		{
			//Generate text for body
			$body = "Here is the questions answered for this application";
			$keys = ['camper_first_name', 'camper_last_name', 'parent_first_name', 'email', 
					'phone', 'camp_1', 'camp_2', 'camp_3', 'camp_4', 'camp_5',
					 'friends', 'activities' ,'school', 'jobs', 'church', 'bible_beliefs',
					  'jesus_beliefs', 'prayer_beliefs'];
			//WIT's have extra question
			if($isWit)
				array_push($keys, 'horse_experience');
			//Loop through all of the parameters and join them together in one big text block
			foreach($keys as $key)
			{
				//Grab the camp title not just the camp id
				if(strpos($key, "camp_") !== false)
				{
					$camp = $wpdb->get_row($wpdb->prepare("SELECT *
											FROM srbc_camps
											 WHERE srbc_camps.camp_id=%d", 	$postdata[$key])); 
					$body .= '<br><b style="font-size:20px">' . $key . '</b>: ' . $camp->name . "";						 

				}
				else
					$body .= '<br><b style="font-size:20px">' . $key . '</b>: ' . $postdata[$key] . "";
			}
			if($isWit)
				self::sendMail(wit_email, 'WIT Application For ' . $info->camper_first_name . " " . $info->camper_last_name,$body,"",false);
			else
				self::sendMail(srbc_email, 'Workcrew Application For ' . $info->camper_first_name . " " . $info->camper_last_name,$body,"",false);
		}
		
		//Email applicant
		self::sendMail($info->email, $wcwit . ' Application ',
		"Dear " . $info->camper_first_name . ",<br>Thanks for applying for $wcwit at Solid Rock Bible Camp!
		<br>Please use the code <code>warden</code> when you register as a camper.<bt>
		<br>You will get an email confirming the weeks that you are working.
		<br>Our camps wouldn't happen without people like you and others making Solid Rock Bible Camp Possible.
		<br>If you have any questions or need to talk to someone feel free to call us at 907-262-4741.");
		//This is mostly for the toast that pops up when using the Resend Confirmation Email
		echo "Email Sent!";
	}

	//Sends mail just a bit easier to use than declaring the class everytime
	//Fancy parameter is if we want the fancy emails or not.  Sometimes for back end emails we done want this
	public static function sendMail($to,$subject,$msg,$attachment = "",$fancy = true)
	{
		//Put msg inside of fancy html template
		if($fancy)
		{
			global $emailTempPt1, $emailTempPt2;
			$msg = $emailTempPt1 . $msg . $emailTempPt2;
		}		
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
