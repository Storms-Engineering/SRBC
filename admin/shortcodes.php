<?php

/*
-------------------------------------------------------------------------
SHORTCODE HOOKS
*/
require_once __DIR__ . '/../requires/email.php';

function srbc_make_donation($atts)
{
	require_once __DIR__ . '/../requires/payments.php';
	if(isset($_POST["amount"]))
	{

		//All donations have a invoice ID of 0 and customer id
		$result = Payments::createCCTransaction($_POST["amount"], $_POST, "Donation to " . $_POST["fund"], 0, 0);

		if(!$result)
		{
			error_msg("It seems like their was a problem with your credit card.
			  Please use the back button and double check your credit card information");
			exit();
		}
		echo "Payment successful!";
		return;
	}

	echo '<h3>Pick a fund to donate to</h3>
	<form method="post"> 
	<select name="fund">
		<option value="General Fund">General Fund</option>
		<option value="Counselor Honorarium Fund">Counselor Honorarium Fund</option>
		<option value="Baber">Kelly Baber</option>
		<option value="Dicksons">Dicksons Family</option>
		<option value="Synders">Synder Family</option>
		<option value="Runion">Ryann Runion</option>
		<option value="Proctors">Proctor Family</option>
		<option value="Fishers">Fisher Family</option>
		<option value="Minnichs">Minnich Family</option>
		<option value="Eaton">James Eaton</option>
		<option value="McKenney">Val McKenney</option>
		<option value="Gottlob">Joanna Gottlob</option>
		<option value="Mcgilvra">Mcgilvra Family</option>
		<option value="Rehbein">Rachel Rehbein</option>
	</select>
	<br>
	<br>
	Amount:
	<input type="number" name="amount" value="0">
	<br>
	<br>
	';

	
	Payments::setupCreditCardHTML();

	echo '<input type="submit" value="Submit">
	</form>';

}

function srbc_make_payment_on_camper($atts)
{
	$registration_id = $_GET["r_id"];
	
	global $wpdb;
	require_once __DIR__ . '/../requires/payments.php';
 	$amountDue = Payments::amountDue($registration_id);
	$camper = $wpdb->get_row($wpdb->prepare( "SELECT *
		FROM ((" . $GLOBALS['srbc_registration'] . "
		INNER JOIN " . $GLOBALS['srbc_camps']. " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . ".camp_id)
		INNER JOIN " . $GLOBALS['srbc_campers'] . " ON " . $GLOBALS['srbc_registration'] . ".camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id) WHERE " .
			$GLOBALS["srbc_registration"] . ".registration_id=%d ", $registration_id));
	
	//User submitted payment charge
	if(isset($_POST['cc_amount']))
	{
		$result = false;
		if($amountDue == 0 && $_POST["cc_amount"] != 0)
		{
			error_msg("You have already payed all of your camp amount.  If you are trying to pay for snackshop, please change amount to pay for camp to 0. ");
			return;
		}
		else if($_POST["cc_amount"] != 0 ) 
			//Charge credit card for both camp fees and snackshop
			$result = Payments::createCCTransaction($_POST["snackshop_amt"] + $_POST["cc_amount"], $_POST ,$camper, $camper->camper_id, $registration_id);
		else
			//Just pay for snackshop
			$result = Payments::createCCTransaction(($_POST["snackshop_amt"]), $_POST ,$camper, $camper->camper_id, $registration_id);
			
		
		if($result)
		{
			//Make autopayment for camp fees
			Payments::autoPayment($registration_id,$_POST["cc_amount"],"card","Online");

			if($_POST["snackshop_amt"] != "0")
				//Make seperate payment for snackshop
				Payments::makePayment($registration_id,"card",$_POST["snackshop_amt"],
				"Online","Store");

			echo '<span style="color:green">Payment Successful!</span>';
			return;
		}
	}
	echo "<h1>Make a payment for " . $camper->camper_first_name .  " " . $camper->camper_last_name . "</h1>";

	
	
	echo '<h2>Amount due: $' . $amountDue . "</h2>";

	echo '<form method="post"> 
	Amount to pay for camp: $<input id="cc_amount" onchange="calculateTotal();" type="number" min="0" name="cc_amount" value="0">
	<br><br>
	Amount to put on snackshop card* <input type="number" onchange="calculateTotal();" min="0" name="snackshop_amt" id="snackshop_amt" value="0"> (Recommended amount $40 for week-long camp) 
	<br><br>
	*Note: Snack Shop payment is not refundable
	<br>
	<input type="hidden" name="r_id" value="' . $registration_id . '">
	<br>
	<h1>Total: $<span id="total"></span></h1>';

	Payments::setupCreditCardHTML();

	echo '<br><input type="submit" value="Submit">
	</form>
	<script src="../wp-content/plugins/SRBC/admin/js/payment.js"></script>
	';
	
}

function srbc_health_form_generate($atts)
{
	require_once __DIR__ . '/../requires/health_form.php';
	$finalText = '<link rel="stylesheet" type="text/css" href="../../wp-content/plugins/SRBC/admin/registration.css">';
	return $finalText . HealthForm::generateSubmitForm();
}

//Creates a table of current lakeside camps for workcrew to choose from
function srbc_workcrew_workschedule($isWit)
{
	$area = "Workcrew";
	if($isWit)
		$area = "WIT";
	
	$finalText = '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Preference</th>
				<th>Week to work</th>';
	//Don't show bus options for WIT's
	if($isWit)
		$finalText .= "</tr>";
	else
		$finalText .= '<th>Bus</th></tr>';
	global $wpdb;
	$camps = $wpdb->get_results("SELECT * FROM srbc_camps WHERE area='$area' ORDER BY start_date");	
	//If no camps then give a message
	if (count($camps) == 0)
		return "<h2>There is currently no workcrew weeks scheduled for this area at this time.  Please check back later!</h2>";
	
	
	//Create table of camps
	for($i = 1; $i <= 5; $i++)
	{
		$finalText .=  '<tr>';
		$finalText .=  '<td>#' . $i;
		$finalText .= "<td>" . createCampSelect($i,$camps) . "</td>";
		if(!$isWit)
			$finalText .= '<td>' .  createBusSelect($i) . '</td>';
	}
	$finalText .=  "</table>";
	return $finalText;
}

function createCampSelect($number,$camps)
{
	
	$select = '<select name="camp_' . $number . '">';
	$select .= '<option value="0">None</option>';
	foreach($camps as $camp)
	{
		global $wpdb;
		$camperCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0",$camp->camp_id)); 
		if($camperCount >= $camp->overall_size)
			$select .= '<option value="' . $camp->camp_id . '">' . $camp->area . " " . $camp->name . '~ Week is full! </option>';
		else
			$select .= '<option value="' . $camp->camp_id . '">' . $camp->area . " " . $camp->name . '</option>';
	}
	$select .= '</select>';
	
	return $select;
}

function createBusSelect($number)
{
	$select = '<select name="busride_' . $number . '">
					<option value="none" selected>No bus ride needed</option>
					<option value="both">Round-Trip $60</option>
					<option value="to">One-way to Camp $35</option>
					<option value="from">One-way to Anchorage $35</option>
				</select>';
	return $select;
}


//Email about volunteering
function srbc_volunteer_contact_form_email($atts){
	if (!isset($_POST['contact_name'])){
		//They put nothing in so just exit.
		//This will usually happen when they load the page
		return;
	}
	$body = $_POST['contact_name'] . " has some questions:<br>" . $_POST['questions'] . "<br>Contact info: " .
		$_POST['phone'] . ' ' . $_POST['email'] . "<br>Area of interest:" . $_POST['area_of_interest'] . "<br><br>- Peter Hakwe SRBC Ancilla";
	Email::sendMail(srbc_email,
	$_POST['contact_name'] . ' is interested at working at Solid Rock' ,$body);
	return "<h1>Info Sent Sucessfully!</h1>";
}

//Lets people email solid rock about renting the facility
function srbc_contact_form_email($atts){
	if (!isset($_POST['contact_name'])){
		//They put nothing in so just exit.
		//This will usually happen when they load the page
		return;
	}
	//EMAIL STUFF
	 //Generate text for body
   $body = NULL;
   $keys = array_keys($_POST);
   $i = 0;
   //Loop through all of the parameters and join them together in one big text block
   foreach ($_POST as $val){
	   //Position is a nested array so we spit out stuff
		$body .= $keys[$i] . ": " . $val . "<br>";
	   $i++;
   }
   /* Set the mail message body. */
	Email::sendMail(srbc_email,
	'Retreat request for ' . $_POST["organization"],$body);

	return "Request submitted sucessfully!";
}

//Search page for parents to search through camps
function srbc_camp_search($atts){
	ob_start();
	?>
	<form action="/camps/search-camps" method="get" >
	Camp Area: <select class="inputs" name="area">
					<option value="">All Program Areas</option>
					<option value="Lakeside">Lakeside</option>
					<option value="Wagon Train">Wagon Train</option>
					<option value="Wilderness">Wilderness</option>
					<option value="WIT">WIT</option>
					<option value="Workcrew">Workcrew</option>
					<option value="Sports">Sports Camp</option>
					<option value="Fall Retreat">Fall Retreat</option>
					<option value="Winter Camp">Winter Camp</option>
					</select>
			Camp Start Date:<input  type="date" name="start_date">
			Camp End Date: <input  type="date" name="end_date">
	<input type="submit" value="Search">
	</form>
	<?php
	
	if (!isset($_GET['area'])){
		//They aren't actually searching so don't try and search stuff
		return ob_get_clean();
	}
	
	//Values that we are passing onto the sql statement
	$values = array();
	$query = "SELECT *	FROM srbc_camps WHERE ";
	if ($_GET['area'] == "") {
		$query .= "srbc_camps.area LIKE '%' ";
	}
	else {
		$values = array($_GET["area"]);
		$query .= "srbc_camps.area='%s' ";
	}
	if (isset($_GET['start_date']) && isset($_GET['end_date']) && $_GET['end_date']!="" && isset($_GET['start_date']) != "" ){
		$query .= "AND srbc_camps.start_date BETWEEN '%s' AND '%s' ";
		array_push($values,$_GET['start_date']);
		array_push($values,$_GET['end_date']);
	}
	global $wpdb;
	$camps = $wpdb->get_results(
	$wpdb->prepare( $query, $values));
	//Initialize variable for the html code after the table with descriptions of camps
	$descriptions = NULL;
	$finalText = '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Area</th>
				<th>Camp</th>
				<th>Cost</th>
				<th>Start/End Date</th>
				<th>Going into Grades</th>
				<th>Camp Availability</th>
				</tr>';			
	//TODO Make a camp dislpay a function or something where we can pass a sql query or the array of camps and have it display correctly.
		foreach ($camps as $camp){
		$finalText .=  '<tr><td>' . $camp->area . '</td><td>' . $camp->name . '		<a href="../register-for-a-camp/?campid='.$camp->camp_id .'">(Register)</a><a href="#'.$camp->camp_id.'"> (More Info)</a>';
		//See if horsemanship is full
		$horsemanshipCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(registration_id)
										FROM srbc_registration
										WHERE camp_id=%s AND horse_opt=1",$camp->camp_id));

		if ($horsemanshipCount >= $camp->horse_list_size && $camp->horse_list_size != 0) 
			$finalText .= '<span style="color:red;"> (Horsemanship Full)</span>';
		
		$finalText .=  "</td><td>$" . $camp->cost;
		$finalText .=  "</td><td>" . date("M j",strtotime($camp->start_date)) . "/" . date("M j",strtotime($camp->end_date));
		$finalText .=  "</td><td>" . $camp->grade_range;
		
										
		$boycount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration']
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='male'",$camp->camp_id));
		$girlcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='female'",$camp->camp_id)); 
										
		
										
		$total_registered = $boycount + $girlcount;
		//TODO Duplicate code for camp_search
		//BODY need to make this another function possibly.
		$finalText .=  "</td><td>";
		if($camp->closed_to_registrations == 1)
			$finalText .= '<span style="color:red">Closed</span>';
		else if (($camp->overall_size - $total_registered) <= 0){
			$finalText .= '<span style="color:red">Camp is full,<br> register to be put on waiting list</span>';
		}	
		else if($boycount >= $camp->boy_registration_size && $camp->boy_registration_size != 0){
			$finalText .= "Boy's section is full,<br>girls can still register!";
		}
		else if($girlcount >= $camp->girl_registration_size && $camp->girl_registration_size != 0){
			$finalText .= "Girl's section is full,<br>boys can still register!.";
		}
		else
			$finalText .= "Camp is open for registrations"; 
		$finalText .=  "</td>";
		//Add a title to the description
		$descriptions .= "<h3 id=".$camp->camp_id.">".$camp->area . " " . $camp->name.", ". date("M j",strtotime($camp->start_date)) . "/" . date("M j",strtotime($camp->end_date)).", Grades ".$camp->grade_range."</h3>";
		$descriptions .= "<ul><li>". urldecode($camp->description) ."</li></ul>";
	}
	$finalText .=  "</table>*If a camp is full but there is still waitlist spots available then continue registration and it will put you on the waitlist";
	$finalText .= "<h1>Camp Descriptions:</h1><br>$descriptions";
	return ob_get_clean() . $finalText;
}

//Sends the application as an email Solid Rock
function srbc_application_complete($atts){
   //Generate text for body
   if (!isset($_POST["Firstname"]))
	   return;
   global $wpdb;
   $body = NULL;
   $keys = array_keys($_POST);
   $i = 0;
   //Loop through all of the parameters and join them together in one big text block
   foreach ($_POST as $val)
   {
	    //Position is a nested array of values
	    if ($keys[$i] == "Position")
		{
		    $body .= $keys[$i] . ": ";
			foreach ($val as $v)
			{
				if ($v != "")
					$body .= '<b style="font-size:20px">' . $v . "</b> " . ", ";
			}
			$body .= "<br>";
	    }
	    else if($keys[$i] != "ssn" && $keys[$i] != "Middlename")
			$body .= '<b style="font-size:20px">' . $keys[$i] . '</b>: ' . $val . "<br>";			
	   $i++;
    }
	//TODO implement encryption class
    $pub_key=file_get_contents($_SERVER['DOCUMENT_ROOT']. '/files/ssn_public.pem');//fread($fp,8192);
	openssl_get_publickey($pub_key);
	openssl_public_encrypt($_POST["ssn"],$edata,$pub_key);
	
	
    $wpdb->insert(
			"srbc_staff_app", 
			array( 
				'staff_app_id' => 0,
				'Firstname' => $_POST["Firstname"], 
				'Lastname' => $_POST["Lastname"],
				'Middlename' => $_POST["Middlename"],
				'ssn' => base64_encode($edata)
			), 
			array( 
				'%s',
				'%s',
				'%s',
				'%s'
			) 
			);
   
   
   //Email applicant
   Email::sendMail($_POST["email"], 'You applied to work at Solid Rock Bible Camp ',
   "Dear " . $_POST["Firstname"] . ",<br>Thanks for applying to work at Solid Rock Bible Camp!
   <br>Our camps wouldn't happen without people like you and others making Solid Rock Bible Camp Possible.
   <br>If you have any questions or need to talk to someone feel free to call us at 907-262-4741.");
   /* Set the mail message body. */
	Email::sendMail(srbc_email, 'Application For ' . $_POST["Firstname"] . " " . $_POST["Lastname"],$body,"",false);

echo "Application submitted sucessfully!
  You should be receiving a call soon from Solid Rock Bible Camp.  Thanks for applying with us!";
}

//Shortcode for [srbc_registration]
//This listens for the camp_id parameter and gets that parameter and lets the user sign up for that camp
function srbc_registration( $atts )
{
	ob_start();
	?> 
	<link rel="stylesheet" type="text/css" href="/wp-content/plugins/SRBC/admin/registration.css">
	<div class="registration_box">
	<form action="../registration-complete/" method="post" style="margin:auto;" onsubmit="return validateForm()">
		<?php
			
				echo '<h3>Camp registering for:';
				global $wpdb;
				//Get list of camp ids and then populate the options box since the user just found this page
				$camp = NULL;
				//Make sure user didn't just navigate here from google
				if(isset($_GET['campid']))
				{
					$cmpid = $_GET['campid'];
					$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_camps WHERE camp_id=%s",$cmpid));

					//TODO add a check if the camp is past the signup date
					//BODY also could add a check if the camp is completely full
					if($camp->closed_to_registrations === "1")
					{
						echo '</select><br><br><h1 style="color:red;text-align:center;">Camp is not open to registrations</h1>';
						return;
					}
					if ($camp == "")
					{
						echo "</select><br><br>";
						error_msg("Please use the Camp Finder page to select a camp or go to the correct program area and find your camp
						there.  You shouldn't acess this page directly");
						return;
					}
					else
					{
						echo '<select style="width:275px;" name="campid">';
						echo '<option value="'.$cmpid.'" selected>' .$camp->area . " " . $camp->name . '</option></select>';
						echo '<input type="hidden" name="camp_desc" value = "' .$camp->area . " " . $camp->name . '">'; 
						if($camp->horse_opt_cost != 0)
						{
							echo ' <input type="checkbox" id="horse_opt" onchange="calculateTotal();" name="horse_opt" value="true"> Horse Option $<span id="horse_opt_cost">' .$camp->horse_opt_cost. '</span><br>';
						}
					}
				}
				//Unless they are workcrew then let them through because they signup differently
				else if(isset($_GET['workcrew']))
				{
					//put workcrew in the title
					echo " Lakeside Workcrew";
				}
				//Unless they are workcrew then let them through because they signup differently
				else if(isset($_GET['wit']))
				{
					//put workcrew in the title
					echo " Lakeside WIT (Wrangler in Training)";
				}
				else if(!isset($_GET['workcrew']) && !isset($_GET['wit']))
				{
					echo "</select><br><br>";
					error_msg("Please use the Camp Finder page to select a camp or go to the correct program area and find your camp there.
					You shouldn't acess this page directly");
				}
				
				
				echo "</h3><br>";
				//We aren't showing busrides here for workcrew or wit or day camps
				if(!isset($_GET['workcrew']) && !isset($_GET['wit']) && $camp->day_camp !== "1")
				{
					echo '<span>Busride*:</span>
					<!-- TODO remove busride option for Winter and Teen Camps -->
					<select onchange="calculateTotal();" class="inputs" id="busride" name="busride">
						<option value="none" selected>No bus ride needed</option>
						<option value="both">Round-Trip $60</option>
						<option value="to">One-way to Camp $35</option>
						<option value="from">One-way to Anchorage $35</option>
					</select>
	
					<p>*The bus will depart from and return to the Duluth Trading Company parking lot at 8931 Old Seward Hwy., Suite A Anchorage, AK 99515.
					The exact times will be sent you in your confirmation email or letter.</p>';
				}
				else if($camp->day_camp === "1")
				{
					echo '<span>Busride*:</span>
					<select class="inputs" id="busride" name="busride">
					<option value="none" selected>No bus ride available for day camps</option>
						</select><br><br>';
				}
				?>
			Camper:
			<input class="inputs" type="text" name="camper_first_name" placeholder="First Name" required>
			<input class="inputs" type="text" name="camper_last_name" placeholder="Last Name" required>
			Birthday: <input  type="date" name="birthday" required><br>
			Gender:	<input type="radio" name="gender" value="male" required> 			Male
			<input type="radio" name="gender" value="female" required> 	Female<br>
			T-Shirt Size:
			<select name="tshirt_size" required>
			<option value="">None</option>
			<option value="Youth_small">Youth Small</option>
			<option value="Youth_Medium">Youth Medium</option>
			<option value="Youth_large">Youth Large</option>
			<option value="Adult_small">Adult Small</option>
			<option value="Adult_medium">Adult Medium</option>
			<option value="Adult_large">Adult Large</option>
			<option value="Adult_XL">Adult XL</option>
			<option value="Adult_XXL">Adult XXL</option>
			<option value="Adult_XXXL">Adult XXXL</option>
			</select>
			Going into Grade: 
			<select class="inputs" name="grade" required>
				<option value="">None</option>
				<option value="K">Kindergarten</option>
				<option value="1">1st</option>
				<option value="2">2nd</option>
				<option value="3">3rd</option>
				<option value="4">4th</option>
				<option value="5">5th</option>
				<option value="6">6th</option>
				<option value="7">7th</option>
				<option value="8">8th</option>
				<option value="9">9th</option>
				<option value="10">10th</option>
				<option value="11">11th</option>
				<option value="12">12th</option>
				<option value="Adult">Adult</option>
			</select>	
			<br>
			Parent/Guardian
			<input class="inputs" type="text" name="parent_first_name" required placeholder="First Name">
			<input class="inputs" type="text" name="parent_last_name" required placeholder="Last Name"><br>
			Email:<input type="email" name="email" required><br>
			Retype Email: <input type="email" id="retyped_email" required><br>
			Phone including area code (Numbers only please):<input type="tel" required pattern="[0-9]{7,}" title="Please enter a valid phone number" name="phone">
			Secondary Phone: <input type="tel" pattern="[0-9]{7,}" title="Please enter a valid phone number" name="phone2"><br>
			Mailing Address:<br>
				<textarea class="inputs" required name="address" rows="2" cols="30"></textarea>
				City:<input type="text" style="width:100px;" required name="city">
				State:<input type="text" style="width:50px;" required name="state">
				Zipcode:<input type="text"  style="width:100px;" required pattern="[0-9]{5}" title="Please enter a 5 digit zipcode" name="zipcode" >
				<br>
			<hr>
			<?php
				if(isset($_GET['workcrew']) || isset($_GET['wit']))
				{
					echo '<h3>Please select the first 3 weeks that you would like to work by order of preference</h3>
					(Please note that weeks 4 & 5 are considered alternate weeks if the week you requested is full)
					<p>*Please note that final schedules will be confirmed by email</p>';
					echo srbc_workcrew_workschedule(isset($_GET['wit']));
					echo srbc_workcrew_questions(isset($_GET['wit']));
					//This hidden field is for letting the workcrew complete that this is a wit application
					if(isset($_GET['wit']))
						echo '<input type="hidden" name="wit">';
				}
			
			?>
			<hr>
			
			<?php 
			//Parent Agreement Section
			//Print latest version from the database
			echo $wpdb->get_row("SELECT * FROM srbc_parental_agreements_versions ORDER BY agreement_id DESC LIMIT 1")->agreement_text;		
			//Signature pad for parental agreement
			echo '<br>
			I 
			<select required>
				<option value="">do not agree</option>
				<option value="true">agree</option>
			</select>			
			to the above statements by signing below
			<br><br>
			<canvas id="pa_canvas" style="border:1px solid black" height="200" width="500"></canvas>
			<input type="hidden" name="pa_signature_img">
			<br>
			<button type="button" onclick="pa_signaturePad.clear()">Clear</button>
			<button type="button" onclick="pa_undo()">Undo</button>';


			//Extra agreement for workcrew for payments
			if(isset($_GET['workcrew']))
			{
				echo '<p>I/we understand that we will be responsible for all the fee\'s incurred by registering for camp if there is not room available for WorkCrew in the weeks that you have chosen, or if your child is removed from WorkCrew because of outstanding circumstances by the parent or if the WorkCrew program coordinator takes disciplinary action as to remove them from that week of workcrew.
				<select class="legal" title="You must agree to register for camp" required="">
				<option value="">Disagree</option>
				<option value="agree">Agree</option>
				</select></p>';
			}
			?>
	<hr>

	<?php
	require_once __DIR__ . '/../requires/health_form.php';
	echo HealthForm::generateSubmitForm();
	if(!isset($_GET['workcrew']) && !isset($_GET['wit']))
	{
		echo '<hr style="clear:both;">
		<h1>Payment:</h1>';
		if($camp->area == "Fall Retreat" || $camp->area == "Winter Camp")
		{
			echo 'Please enter amount to pay: <input type="text" name="cc_amount" id="cc_amount" value="'.$camp->cost.'"><br>';
			echo 'Please enter name of friend that you are bringing: <input type="text" name="registration_notes">';
		}
		else
		{
			echo 'Workcrew Code: <input type="text" id="code" name="code">
			<hr>
			<span style="color:red">Note: Your registration is not valid until the $50 non-refundable registration fee is received unless you are workcrew*.  (This $50 DOES go towards the cost of the camp)</span><br>
			You must pay $50, or pay the full amount of the camp, unless you a are registering for the waitlist then you don\'t have to pay a registration fee.  Any remaining amount will be due the day of registration.
			<br>
			*If you are workcrew please enter the code received in your email and after registering in the box above and your registration will be allowed.
			<br>
			<br>
			<h3>Amount to pay*: </h3>
			<label class="container">$50
				<input type="radio" name="cc_amount" checked="checked" value="50">
				<span class="checkmark"></span>
			</label>
			<label class="container">$<span id="total">';
			echo $camp->cost;
			echo '</span><input type="radio" name="cc_amount" id="cc_amount" value="'.$camp->cost.'">';
			echo '<span style="display:none" id="camp_cost">' . $camp->cost . '</span>';
			echo '<span class="checkmark"></span>
			</label>
			<label class="container">$0 Registering for waiting list
				<input type="radio"  name="cc_amount" id="waitlist" value="">
				<span class="checkmark"></span>
			</label>
			*Disregard this section if are workcrew and have put in your code.<br>
			<input type="hidden" name="registration_notes">';
		}
		echo '
		<hr>
		<h3>Use a credit card:</h3>	
			Name on Credit Card: <input type="text" name="cc_name"><br>
			Billing Address:<br>
			Same as above <input type="checkbox" id="same_cc_address" onclick="moveAddress()">
				<textarea class="inputs" name="cc_address" rows="1" cols="30"></textarea>
				City:<input type="text" style="width:100px;" name="cc_city">
				State:<input type="text" style="width:50px;" name="cc_state">
				Zipcode:<input type="text"  style="width:100px;" pattern="[0-9]{5}" title="Please enter a 5 digit zipcode" name="cc_zipcode" >
				<br>
			Credit Card # <input type="text" id="cc_number" name="cc_number"><br>
			Verification Code: <input type="text" name="cc_vcode" style="width:5%">
			<!--TODO make these computer generated -->
			Expiration: <select name="cc_month" size="1">
										<option value="">Pick</option>
										<option value="01">01</option>
										<option value="02">02</option>
										<option value="03">03</option>
										<option value="04">04</option>
										<option value="05">05</option>
										<option value="06">06</option>
										<option value="07">07</option>
										<option value="08">08</option>
										<option value="09">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
									</select>/
									<select name="cc_year" size="1">
										<option value="">Pick</option>
										<option value="2019">2019</option>
										<option value="2020">2020</option>
										<option value="2121">2021</option>
										<option value="2022">2022</option>
										<option value="2023">2023</option>
										<option value="2024">2024</option>
										<option value="2025">2025</option>
										<option value="2026">2026</option>
										<option value="2027">2027</option>
									</select>
									<br>
			<h3>OR</h3>
			<h3 style="display:inline">Send a check</h3> <input type="checkbox" id="use_check" name="using_check">
			<p>Please make checks out to Solid Rock Bible Camp and send to 36251 Solid Rock Road #1, Soldotna, Alaska 99669, with campers name in the memo.</p>';
	}
	
		?>
		<br>
		<input type="submit" value="Submit">
	</form> 
	</div>
	<script src="../wp-content/plugins/SRBC/admin/registration.js"></script>
	<?php
	return ob_get_clean();
}

//Generates workcrew/wit questions
//Parameter is for WIT specific question
function srbc_workcrew_questions($isWit)
{
	echo '<hr>Please list any friends you would like to work the same week as: <input type="text" name="friends">';
	echo '<br><h3>Help us get to know you by answering these questions:</h3>';

	echo 'Do you have any hobbies or play any sports?<br><textarea rows="2" cols="45" name="activities"></textarea><br>
	What school do you go to?<br><textarea rows="2" cols="45" name="school"></textarea><br>
	Have you ever had a job before? (Babysitting, yard work, etc?)<br><textarea rows="2" cols="45" name="jobs"></textarea><br>';
	if($isWit)
		echo 'Do you have any experience with horses? Please describe<br><textarea rows="2" cols="45" name="horse_experience"></textarea><br>';
	echo 'If you attend church or a youth group regularly, what church do you attend?<br><textarea rows="2" cols="45" name="church"></textarea><br>
	What do you believe about the Bible?<br><textarea rows="2" cols="45" name="bible_beliefs"></textarea><br>
	What do you believe about who Jesus is?<br><textarea rows="2" cols="45" name="jesus_beliefs"></textarea><br>
	What do you believe about Prayer?<br><textarea rows="2" cols="45" name="prayer_beliefs"></textarea><br>';
}


function srbc_registration_complete($atts)
{
	//Validate that this is a form post
	if($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_POST['camper_first_name']))
	{
		return;
	}
		

	require __DIR__ .  '/../requires/Camper.php';	
	require __DIR__ .  '/../requires/health_form.php';	
	
	//Creates a camper and returns the camper ID.  If the camper already exists then it returns that ID.
	//$_POST contains all of the data that we need.
	$camper_id = Camper::createCamper($_POST);
	global $wpdb;
	//Store signature for parental agreement in database
	$agreement_id = $wpdb->get_row("SELECT * FROM srbc_parental_agreements_versions ORDER BY agreement_id DESC LIMIT 1")->agreement_id;
	$wpdb->insert( 
		'srbc_parental_agreements_sig', 
		array( 
			'signature_img' => $_POST['pa_signature_img'], 
			'camper_id' => $camper_id,
			'agreement_id' => $agreement_id
		), 
		array( 
			'%s', 
			'%d', 
			'%d' 
		) 
	);

	HealthForm::healthFormSubmit($camper_id);
	//Normal registration signup
	if(isset($_POST['campid']))
		signUpCamper($_POST,$camper_id,false);
	//Registration is for workcrew or wit
	else
	{
		for($i = 1; $i <= 5; $i++)
		{
			$_POST['campid'] = $_POST['camp_' . $i]; 
			if(isset($_POST['busride_' . $i]))
				$_POST['busride'] = $_POST['busride_' . $i];
			else
				$_POST['busride'] = 'none';
			//Means that the parent selected none for this week of camp so skip over it
			if($_POST['campid'] == 0)
				continue;
			if($i > 3)
				//For weeks over 3 we just put them on the waitlist
				//so it isn't filling up more spots in the normal camp
				signUpCamper($_POST,$camper_id,true,1);
			else
				signUpCamper($_POST,$camper_id,true);
		}
		Email::sendWorkcrewEmail($camper_id,$_POST,isset($_POST['wit']));
		return 'Workcrew/WIT request submitted sucessfully!  <span style="color:red">Important note: Please register for the week of camp that you specified.  Please enter the code 
			<code>warden</code> on the registration page when it asks you for a code.</span>
  			You should be receiving a call soon from Solid Rock Bible Camp.  Thanks for applying with us!';
	}	
	return 'Registration Sucessful!<br>  We sent you a confirmation email with some frequently asked questions and what camp you signed up for. <span style="color:red">(If you don\'t see the email check your spam box and please mark it not spam)';
}

//Signs up a camper for a camp
//Waitlist can force waitlist for a camper which is what we want for workcrew
function signUpCamper($vars,$camper_id,$isWorkcrew,$waitlist = 0)
{
	//Horse option is just a boolean because I will pull the price from the camps database so we don't people changing prices
	$horse_opt = 0;
	if (isset($_POST["horse_opt"]))
		$horse_opt = 1;

	global $wpdb;
	
	$waitlistsize = 0;	
	//Calculate if this camper needs to go on a waiting list
	//If not then update how many people are registered for this camp
	$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_camps WHERE camp_id=%s",$vars["campid"]));
	//Check if they are already signed up for this camp:
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camper_id) FROM srbc_registration WHERE camper_id=%s AND camp_id=%s",$camper_id
	,$vars["campid"])); 
	if ($count > 0)
	{
		error_msg("Sorry you are already registered for this camp");
		return;
	}
	//Check if this camp is already full
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM srbc_registration WHERE camp_id=%s AND waitlist=0",$vars["campid"])); 
	if($count < $camp->overall_size && $camp->closed_to_registrations == 0)
	{
		//This camp is not overall full check gender specific caps
		if ($vars['gender'] == "male")
		{
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='male'",$vars["campid"])); 
			if ($count >= $camp->boy_registration_size)
			{
				error_msg("Unfortunately we cannot register you because the boys section of this camp is full.");
				goto waitinglist;
			}
		}
		else if($vars['gender'] == "female")
		{
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='female'",$vars["campid"])); 
			if ($count >= $camp->girl_registration_size)
			{
				error_msg("Unfortunately we cannot register you because the girls section of this camp is full.");
				goto waitinglist;
			}
		}
		else
		{
			error_msg("gender not specified in registration");
			return;
		}
	}
	else
	{
		//This camp is full, check waiting list size and see if we can add them
		//Check that this camper isn't already on a waiting list
		waitinglist:
		
		if ($camp->boy_registration_size == 0 && $vars["gender"] == "male")
		{
			error_msg("Sorry this is a girls only camp!");
			return;
		}
		
		//Count overall waitlist size for this camp
		$waitlistsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM srbc_registration WHERE NOT waitlist=0 AND camp_id=%s",$vars["campid"])); 
		//Check if the waiting list is full
		if ($waitlistsize < $camp->waiting_list_size)
		{	
			$waitlist = 1;
			error_msg("You have been put on the waiting list for this camp because registration is full.");	
		}
		else
		{
			//We can't continue registration because waiting list is full
			error_msg("Sorry we are unable to add you to the waiting list because the waiting list is full");
			return;
		}
	}
	//Initially set this to 0 and if they need to be put on the horses waitlist we will update this to one
	$horse_waitlist = 0;
	if ($horse_opt == 1)
	{
		$listsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM srbc_registration WHERE horse_waitlist=0 AND horse_opt=1 AND camp_id=%s ",$vars["campid"])); 
		//If we have too many people in horses
		if($listsize >= $camp->horse_list_size)
		{
			//We have exceeded our horse list so turn this option to 0
			$horse_opt = 0;
			$waitlistsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM srbc_registration WHERE horse_waitlist=1 AND camp_id=%s ",$vars["campid"])); 
			if($waitlistsize < $camp->horse_waiting_list_size)
			{
				$horse_waitlist = 1;
				error_msg("Unfortunately the horse option is full.  You have been put on a waiting list for horses.");
			}
			else
				error_msg("Unfortunately the horse option is full.");
		}
			
	}
	$currentDate = new DateTime("now", new DateTimeZone('America/Anchorage'));
	//Check that they aren't trying to cheat the system by saying they are signing up for a camp that isn't waitlisted and paying nothing
	if($waitlist == 0 && $camp->area != "Workcrew" && $camp->area != "WIT" && $vars["cc_amount"] == "")
	{
		//Let workcrew through though
		if($vars["code"] != "warden")
		{
		error_msg("Please enter credit card information or check the 'Send a check' option.
		This camp is not currently full and therefore you aren't being put on the waiting list.
		Please hit the back button and try again. Thanks!");
		exit();
		}
	}

	

	//Insert camper registration into database
	try
	{
		$wpdb->insert(
				'srbc_registration', 
				array( 
					'registration_id' =>0,
					'camp_id' => $vars["campid"], 
					'camper_id' => $camper_id,
					'date' => $currentDate->format("m/d/Y h:i A"),
					'horse_opt' => $horse_opt,
					'busride' => $vars['busride'],
					'waitlist' => $waitlist,
					'horse_waitlist' => $horse_waitlist,
					'registration_notes' => (isset($vars['registration_notes']) ? $vars['registration_notes'] : "")
				), 
				array( 
					'%d',
					'%d', 
					'%d',
					'%s',
					'%d',
					'%s',
					'%d',
					'%d',
					'%s'
				) 
				);
	}
	catch(Exception $e)
	{
		Email::emailDeveloper($e->getMessage());
	}
	$registration_id = $wpdb->insert_id;

	if($waitlist != 1 && isset($_POST["cc_amount"]) && $_POST["cc_amount"] !== "0" && !isset($_POST["using_check"]))
	{
		require_once __DIR__ . '/../requires/payments.php';
		$result = Payments::createCCTransaction($vars["cc_amount"], $vars, $camp, $camper_id, $registration_id);

		//Payment successful add it to our payment database
		if($result)
			Payments::autoPayment($registration_id,$vars["cc_amount"],"card","Online");
		else
		{
			//Delete the registration since the credit card didn't work :(
			$wpdb->delete( 'srbc_registration', array( 'registration_id' => $registration_id ) );

			error_msg("It seems like their was a problem with your credit card.
			  Please use the back button and double check your credit card information");
			exit();
		}
	}

	
	//We don't want to send 3 confirmation emails for workcrew
	if ($waitlist == 1 && !$isWorkcrew)
	{
		Email::sendWaitlistEmail($registration_id);
	}
	else if(!$isWorkcrew)
	{
		if($camp->day_camp == 0)
			Email::sendConfirmationEmail($registration_id);
		else
			Email::sendDayCampConfirmationEmail($registration_id);
	}
}

//From: https://www.php.net/manual/en/function.openssl-encrypt.php
function aesEncrypt($plaintext,$key)
{
	$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
	//IV length is also only 16 characters
	$iv = substr(base64_encode(openssl_random_pseudo_bytes($ivlen)),0,16);

	$ciphertext = openssl_encrypt($plaintext, $cipher, $key, null, $iv);

	$object = (object) [
		'IV' => $iv,
		'cipherText' => $ciphertext
	  ];
	return $object;
}

function autoSplit($cc_amount,$campid,$registration_id,$busride,$horseOpt)
{
	global $wpdb;
	$totalpaid = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM srbc_payments WHERE registration_id=%s",$registration_id));
			
	//Make the scholarships and discounts add to total paid so we take it out of the base camp fee
	//Not using this because this is the first time they are signing up for the camp
	//$totalpaid += $o->discount + $o->scholarship_amt;
	if($totalpaid == NULL)
		$totalpaid = 0;
	//Check if they have paid the base camp amount which is (camp cost - horse cost)
	$camp = $wpdb->get_row("SELECT * FROM srbc_camps WHERE camp_id=$campid");
	$baseCampCost = $camp->cost - $camp->horse_cost;
	$needToPayAmount = 0;
	$feeType = NULL;
	//Counts how many times we looped through
	$loops = 0;
	$autoPaymentAmt = $cc_amount;
	//Calculate bus fee based on type of busride
	$busfee = 0;
	if ($busride == "both")
		$busfee = 60;
	else if($busride == "to" || $busride == "from")
		$busfee = 35;
	
	if ($horseOpt == 1)
		$horseOpt = $camp->horse_opt_cost;
	
	$comments = "";
	//Create seperate payments based on different fees until autoPaymentAmt is used up
	//or an overpayment happens which stores it in the database
	while ($autoPaymentAmt != 0)
	{
		if ($totalpaid < $baseCampCost)
		{
			//We still need to pay some on the base camp cost
			$needToPayAmount = $baseCampCost - $totalpaid;
			if ($camp->area == "Sports")
				$feeType = "Lakeside";
			else
				$feeType = $camp->area;
		}				
		//$totalpaid comes first because this also checks that they have paid more than we are currently looking atan
		//If we flip it then it becomes a negative number if the totalpaid is greater than the value we are checking
		//Check horse_cost (aka WT Horsemanship Fee
		else if(($totalpaid - $baseCampCost) < $camp->horse_cost) 
		{
			//We still need to pay some on the base camp cost
			$needToPayAmount = $camp->horse_cost - ($baseCampCost - $totalpaid);
			$feeType = "WT Horsemanship";
		}				
		//Horse option check aka LS Horsemanship
		else if(($totalpaid - $camp->cost) < $horseOpt) 
		{
			//We still need to pay some on the horse option
			$needToPayAmount = $horseOpt - ($totalpaid - $camp->cost);
			$feeType = "LS Horsemanship";
		}
		else if(($totalpaid - ($camp->cost + $horseOpt)) < $busfee) 
		{
			//We still need to pay some on the bus option
			$needToPayAmount = $busfee - ($totalpaid - ($camp->cost + $horseOpt));
			$feeType = "Bus";
		}
		else
		{
			//Overpaid
			$needToPayAmount = $autoPaymentAmt;
			$feeType= "Overpaid";
		}
		//Also updates autoPaymentAmt
		list ($autoPaymentAmt,$paid) = calcPaymentAmt($autoPaymentAmt,$needToPayAmount);
		$comments .= $feeType . ":$" . $paid . ",";
		$totalpaid += $paid;
		$loops++;
		if ($loops > 5)
		{
			error_msg("Error: Autopayment failed!  Infinite loop detected.... Please let Website administrator know. - Peter H.");
			break;
			
		}
	}
	return $comments;
}

//TODO this seems to be redeclared when I run update_registration
//@body Fatal error</b>: Cannot redeclare calculatePaymentAmt() (previously declared in E:\xampp\htdocs\wp-content\plugins\SRBC\update_registration.php:246) in <b>E:\xampp\htdocs\wp-content\plugins\SRBC\admin\shortcodes.php</b> on line <b>747</b><br />
//Calculates how much they need to pay and makes the payment
function calcPaymentAmt($autoPaymentAmt, $needToPayAmount)
{
	$paymentAmt = 0;
	if ($autoPaymentAmt <= $needToPayAmount)
		$paymentAmt = $autoPaymentAmt;
	else if($autoPaymentAmt > $needToPayAmount)
		$paymentAmt = $needToPayAmount;
	//this is how much money is left so subtract what we just paid
	$autoPaymentAmt -= $paymentAmt;
	return array($autoPaymentAmt,$paymentAmt);
}

//Lists all camps by area
function srbc_camps($atts)
{
	$query = $atts["area"];
	
	$finalText = '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Camp</th>
				<th>Cost</th>
				<th>Start-End Date</th>
				<th>Going into Grades</th>
				<th>Camp Availability</th>
				</tr>';
	global $wpdb;
	$camps = $wpdb->get_results("SELECT * FROM srbc_camps WHERE area='$query' ORDER BY start_date");	
	//If no camps then give a message
	if((get_option("srbc_summer_camps_disable") == "true" && ($query !== "Fall Retreat" && $query !== "Winter Camp")))
	{
		date('Y', strtotime('+1 years'));
		//Says next summer year based on which month it is
		return '<h2 style="text-align:center">Registration for Summer '. ((date("m") < 8) ? date('Y') : date('Y', strtotime('+1 years'))) . ' has not opened yet.  Please check back later!<h2>';
	}
	if (count($camps) == 0)
		return "<h2>There is currently no camps scheduled for this area at this time.  Please check back later!</h2>";
	
	
	//Initialize variable for the html code after the table with descriptions of camps
	$descriptions = NULL;
	//Create the table of camps
	foreach ($camps as $camp){
		$finalText .=  '<tr><td>' . $camp->name . '		<a href="../register-for-a-camp/?campid='.$camp->camp_id .'">(Register)</a><a href="#'.$camp->camp_id.'"> (More Info)</a>';
		//See if horsemanship is full
		$horsemanshipCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(registration_id)
										FROM srbc_registration
										WHERE camp_id=%s AND horse_opt=1",$camp->camp_id));

		if ($horsemanshipCount >= $camp->horse_list_size && $camp->horse_list_size != 0) 
			$finalText .= '<span style="color:red;"> (Horsemanship Full)</span>';
		
		if($camp->name == "Family")
			$finalText .=  "</td><td>TBD";
		else
			$finalText .=  "</td><td>$" . $camp->cost;
		$finalText .=  "</td><td>" . date("M j",strtotime($camp->start_date)) . "-" . date("M j",strtotime($camp->end_date));
		$finalText .=  "</td><td>" . $camp->grade_range;
		
										
		$boycount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='male'",$camp->camp_id));
		$girlcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_registration.camper_id = " . $GLOBALS['srbc_campers'] . ".camper_id
										WHERE camp_id=%s AND waitlist=0 AND " . $GLOBALS['srbc_campers'] . ".gender='female'",$camp->camp_id)); 
										
		
										
		$total_registered = $boycount + $girlcount;
		//TODO Duplicate code for camp_search
		//BODY need to make this another function possibly.
		//TODO Also possibly closing camps based on date?
		$finalText .=  "</td><td>";
		if($camp->closed_to_registrations == 1)
			$finalText .= '<span style="color:red">Closed</span>';
		else if (($camp->overall_size - $total_registered) <= 0){
			$finalText .= '<span style="color:red">Camp is full,<br> register to be put on waiting list</span>';
		}	
		else if($boycount >= $camp->boy_registration_size && $camp->boy_registration_size != 0){
			$finalText .= "Boy's section is full,<br>girls can still register!";
		}
		else if($girlcount >= $camp->girl_registration_size && $camp->girl_registration_size != 0){
			$finalText .= "Girl's section is full,<br>boys can still register!.";
		}
		else
			$finalText .= "Camp is open for registrations"; 
		$finalText .=  "</td>";
		//Add a title to the description
		$descriptions .= "<h3 id=".$camp->camp_id.">".$camp->name.", ". date("M j",strtotime($camp->start_date)) . "/" . date("M j",strtotime($camp->end_date)).", Grades ".$camp->grade_range."</h3>";
		$descriptions .= "<ul><li>". urldecode($camp->description) ."</li></ul>";
	}
	$finalText .=  "</table>*If a camp is full but there is still waitlist spots available then continue registration and it will put you on the waitlist";
	$finalText .= "<h1>Camp Descriptions:</h1><br>$descriptions";
	return $finalText;
}


?>