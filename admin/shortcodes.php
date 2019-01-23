<?php

/*
-------------------------------------------------------------------------
SHORTCODE HOOKS
*/
function srbc_volunteer_contact_form_email($atts){
	if (!isset($_POST['contact_name'])){
		//They put nothing in so just exit.
		//This will usually happen when they load the page
		return;
	}
	$body = $_POST['contact_name'] . " has some questions:<br>" . $_POST['questions'] . "<br>Contact info: " .
		$_POST['contact_info'] . "<br><br>- Peter Hakwe SRBC Ancilla";
	sendMail(srbc_email,
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
	sendMail(srbc_email,
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
	
	$finalText = '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Camp Description</th>
				<th>Cost</th>
				<th>Start Date</th>
				<th>Camper Spots Available</th>
				<th>Waitling List Spots Available</th> 
				</tr>';				
	foreach ($camps as $camp){
		$finalText .=  '<tr><td>' . $camp->area . " " . $camp->camp_description . '		<a href="../register-for-a-camp/?campid=' . $camp->camp_id . '">(Register)</a>';
		$finalText .=  "</td><td>$" . $camp->cost;
		$finalText .=  "</td><td>" . date("M j",strtotime($camp->start_date));
		$total_registered = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										WHERE camp_id=%s AND waitlist=0",$camp->camp_id), ARRAY_N)[0][0]; 
		$finalText .=  "</td><td>" . ($camp->overall_size - $total_registered); 
		$finalText .=  "</td><td>";
		$waitlistsize = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										WHERE camp_id=%s AND NOT waitlist=0",$camp->camp_id), ARRAY_N)[0][0]; 
		$finalText .=  $camp->waiting_list_size - $waitlistsize . "</td></tr>";
	}
	$finalText .=  "</table> ";
	return $finalText;
	return ob_get_clean() . $finalText;
}

//Sends the application as an email Solid Rock
function srbc_application_complete($atts){
   //Generate text for body
   $body = NULL;
   $keys = array_keys($_POST);
   $i = 0;
   //Loop through all of the parameters and join them together in one big text block
   foreach ($_POST as $val){
	   //Position is a nested array so we spit out stuff
	   if ($keys[$i] == "Position"){
		   $body .= $keys[$i] . ": ";
			foreach ($val as $v){
				if ($v != "")
					$body .= $v . ", ";
			}
			$body .= "<br>";
	   }
	   else
			$body .= $keys[$i] . ": " . $val . "<br>";
	   $i++;
   }
   //Email applicant
   sendMail($_POST["email"], 'You applied to work at Solid Rock Bible Camp ',
   "Dear" . $_POST["Firstname"] . ",<br>Thanks for applying to work at Solid Rock Bible Camp!
   <br>Our camps wouldn't happen without people like you and others making Solid Rock Bible Camp Possible.
   <br>If you have any questions or need to talk to someone feel free to call us at 907-262-4741.<br>-Solid Rock Bible Camp", "From: " . srbc_email);
   /* Set the mail message body. */
	sendMail(srbc_email, 'Application For ' . $_POST["Firstname"] . " " . $_POST["Lastname"],$body);

echo "Application submitted sucessfully!
  You should be receiving a call soon from Solid Rock Bible Camp.  Thanks for applying with us!";
}

//Shortcode for [srbc_registration]
//This listens for the camp_id parameter and gets that parameter and lets the user sign up for that camp
function srbc_registration( $atts )
{
	ob_start();
	?> 
	<style>
	input[type="text"], input[type="email"], input[type="password"], input[type="search"], input[type="tel"], select, textarea
	{
		padding:2px;
		margin:2px;
	}
	.registration_box
	{
		padding:10px;
		margin:auto;
		width:75%;
		border: 2px solid #a6a6a6;
		border-radius: 20px;
	}
	table, td
	{
		
		border-collapse:collapse;
		border-style:none;
		
	}
	.inputs
	{
		width:50%;
		float:left;
	}
	.lefting
	{
		float:left;
	}
	.bigtext
	{
		width:50%;
		height:50%;
		word-break: break-word;
	}
	textarea
	{
		width:auto;
	}
	</style>
	<script>
	function validateForm(){
		if (document.getElementById("cc_number").value != "" || document.getElementById("use_check").checked)
			return true;
		else
		{
			alert("Please use a credit card or check!");
			return false;
		}
	}
	</script>
	<div class="registration_box">
	<div style="overflow-x:auto;">
	<form action="../registration-complete/" method="post" style="margin:auto;" onsubmit="return validateForm()">
	 <table style="width:100%;">
		<tr>
			<td>Camp you wish to register for:</td>
			<td>
				<select class="inputs" name="campid">
				<?php
				global $wpdb;
				//Get list of camp ids and then populate the options box since the user just found this page
				//$campids = $wpdb->get_col( "SELECT camp_id FROM srbc_camps");
				//foreach ($campids as $cmpid)
				$camp = NULL;
				if(isset($_GET['campid']))
				{
					$cmpid = $_GET['campid'];
					$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_camps WHERE camp_id=%s",$cmpid));
					echo '<option value="'.$cmpid.'" selected>' .$camp->area . " " . $camp->camp_description . '</option></select>';
					echo '<input type="hidden" name="camp_desc" value = "' .$camp->area . " " . $camp->camp_description . '">'; 
					if($camp->horse_opt != 0)
					{
						echo '<input type="checkbox" name="horse_opt" value="true"> Horse Option $' .$camp->horse_opt. '<br>';
					}
				}
				else
				{
					echo "</select><br><br>";
				error_msg("Please use the Camp Finder page to select a camp or go to the correct program area and find your camp there.  You should'nt acess this page directly");
				}
				
				
				//
				?>

				<br>
				<br>
				<!--TODO make this responsive.  Not gonna worry about it right now.  Use w3schools responsive forms-->
				<span style="float:left;">Busride:</span>
				<select class="inputs" name="busride">
					<option value="none" selected>No bus ride needed</option>
					<option value="both">Round-Trip $60</option>
					<option value="to">One-way to Camp $35</option>
					<option value="from">One-way to Anchorage $35</option>
				</select>
				<br>
				<br>
				<p>The bus will depart from and return to the Sports Authority parking lot on the Old Seward Highway near Dimond Blvd. in Anchorage.
				The exact times will be sent you in your confirmation email or letter.</p>
			</td>
		</tr>
		<tr>
			<td>Camper:</td>
			<td>
			<input class="inputs" type="text" name="camper_first_name" placeholder="First Name" required pattern="[A-Za-z]{1,}" title="Please enter only letters">
			<input class="inputs" type="text" name="camper_last_name" placeholder="Last Name" required pattern="[A-Za-z]{1,}" title="Please enter only letters">
			Birthday: <input  type="date" name="birthday"><br>Gender:
			<input type="radio" name="gender" value="male" checked> Male
			<input type="radio" name="gender" value="female">Female
			<select class="inputs" name="grade">
				<option value="5-7yrs">5 to 7 years old</option>
				<option value="2nd to 3rd">Going into 2nd or 3rd Grade</option>
				<option value="4th to 6th">Going into 4th to 6th Grade</option>
				<option value="Adult">Adult</option>
			</select>	
			</td>
		</tr>
		<tr>
			<td>Parent/Guardian</td>
			<td>
			<input class="inputs" type="text" name="parent_first_name" required placeholder="First Name" pattern="[A-Za-z]{1,}" title="Please enter only letters">
			<input class="inputs" type="text" name="parent_last_name" required placeholder="Last Name" pattern="[A-Za-z]{1,}" title="Please enter only letters">
			Email:<input type="email" name="email" required><br>
			Phone including area code (Numbers only please):<input type="tel" required pattern="[0-9]{7,}" title="Please enter a valid phone number" name="phone">
			</td>
		</tr>
		<tr>
			<td>Street Address:</td>
			<td><textarea class="inputs" required name="address" rows="5" cols="20"></textarea>
				City:<input type="text" required name="city"><br>
				State:<input type="text" required name="state"><br>
				Zipcode:<input type="text" required pattern="[0-9]{5}" title="Please enter a 5 digit zipcode" name="zipcode" >
			</td>
		</tr>
		<tr>
			<td></td>
			<td>Parental Notice and Release - Agreement is required for camper admittance</td>
		</tr>
		<tr>
			<td>
				<select required title="You must agree to register for camp" class="legal">
					<option value="">Disagree</option>
					<option value="agree">Agree</option>
				</select>
			</td>
			<td>I/We, the undersigned, understand that while attending Solid Rock Bible Camp of Soldotna, Alaska (camp),
			the below-named child may be involved in various activities including but not limited to: horseback riding,
			water-skiing, the waterslide, swimming, boating, the Blob, riflery, archery, rope swing, the obstacle course,
			and other traditional camp activities. I/We have familiarized ourselves with these programs and activities included in,
			but not limited to, the Camp brochure.</td>
		</tr>
		<tr>
			<td>
			<select required title="You must agree to register for camp" class="legal">
				<option value ="">Disagree</option>
				<option value="agree">Agree</option>
			</select>
			</td>
			<td>
			In consideration of Solid Rock Ministries, Inc. allowing the child to attend Camp for the period specified
			and to participate in the activities of the Camp, I/we do hereby grant permission for the child to attend
			and to participate fully in said activities. I/We understand and accept the risks and dangers involved in
			such activities and do hereby release Solid Rock Ministries, Inc., its officers and directors, its employees,
			agents, and the Camp staff, from any and all claims, demands, actions, causes of actions of any sort,
			for injuries or death sustained by myself/ourselves or the child due to negligence or any other fault during 
			the period covered by this release, whether such an injury occurred on or off the Camp property. 
			</td>
		</tr>
		<tr>
			<td>
			<select required title="You must agree to register for camp" class="legal">
				<option value="">Disagree</option>
				<option value="agree">Agree</option>
			</select>
			</td>
			<td>
				I/We have instructed my/our son/daughter to obey the rules of Solid Rock Bible Camp.
				This waiver is effective only for the week(s) for which the camper is registered.
			</td>		
		</tr>
	</table> 
	</div>
	<span style="color:red">Note: Your registration is not valid until the $50 non-refundable registration fee is received.</span><br>
	You must at least pay $50, but you may pay up to the full amount of the camp.  Any remaining amount will be due the day of registration.
	<br>
	<br>
	
	
	<h3>Use a credit card:</h3>
	Amount to pay: $ <input type="number" name="cc_amount"><br>
		<br>		
		Name on Credit Card: <input type="text" name="cc_name">
		Credit Card Billing Zip <input type="text" name="cc_zipcode">
		Credit Card Type:
		<select name="cc_type" size="1">
			<option value="Visa" selected="">Visa</option>
			<option value="MasterCard">MasterCard</option>
			<option value="Discover">Discover</option>
		</select><br>
		Credit Card # <input type="text" id="cc_number" name="cc_number">
		Credit Card Verification Code: <input type="text" name="cc_vcode" style="width:5%">
		Credit Card Expiration: <select name="cc_month" size="1">
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
									<option value="19">2019</option>
									<option value="20">2020</option>
									<option value="21">2021</option>
									<option value="22">2022</option>
									<option value="23">2023</option>
									<option value="24">2024</option>
									<option value="25">2025</option>
									<option value="26">2026</option>
									<option value="27">2027</option>
								</select>
								<br>
		<h2>OR</h2>
		Send a check <input type="checkbox" id="use_check" name="using_check">
		<br>
		<br>
		<input type="submit" value="Submit">
	</form> 
	</div>
	<?php
	return ob_get_clean();
}


function srbc_registration_complete($atts)
{
	$campid = $_POST["campid"];
	$camper_first_name = $_POST["camper_first_name"];
	$camper_last_name = $_POST["camper_last_name"];
	$birthday = $_POST["birthday"];
	$gender = $_POST["gender"];
	$grade = $_POST["grade"];
	$parent_first_name = $_POST["parent_first_name"];
	$parent_last_name = $_POST["parent_last_name"];
	$email = $_POST["email"];
	$phone = $_POST["phone"];
	$address = $_POST["address"];
	$city = $_POST["city"];
	$state = $_POST["state"];
	$zipcode = $_POST["zipcode"];
	$busride = $_POST["busride"];
	
	//Horse option is just a boolean because I will pull the price from the camps database so we don't people changing prices
	$horse_opt = 0;
	if (isset($_POST["horse_opt"]))
		$horse_opt = 1;
	$busride = $_POST["busride"];
	
	//Calculate the campers age
	$d1 = new DateTime(date("Y/m/d"));
	$d2 = new DateTime($birthday);
	$diff = $d2->diff($d1);
	$age = $diff->y;
	
	global $wpdb;
	//Update camper info or create new camper if camper doesn't exist
	//Using prepare to sanitize input
	$camper = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_campers WHERE camper_first_name=%s AND camper_last_name=%s AND birthday=%s",
	$camper_first_name,$camper_last_name,$birthday));
	$camper_id = 0;
	if ($camper!=NULL)
	{
		//Camper already exists so use their ID
		$camper_id = $camper->camper_id;
	}
	//Replace existing camper information or create a new one if camper doesn't exist
	$wpdb->replace( 
	'srbc_campers', 
	array( 
        'camper_id' =>$camper_id,
		'camper_first_name' => $camper_first_name, 
		'camper_last_name' => $camper_last_name,
		'birthday' => $birthday,
		'age' => $age,
		'gender' => $gender,
		'grade' => $grade,
		'parent_first_name' => $parent_first_name,
		'parent_last_name' => $parent_last_name,
		'email' => $email,
		'phone' => $phone,
		'address' => $address,
		'city' => $city,
		'state' => $state,
		'zipcode' => $zipcode
	), 
	array( 
        '%d',
		'%s', 
		'%s',
		'%s',
		'%d',
		'%s',
		'%d',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%d'		
		
	) 
	);
	$camper_id = $wpdb->insert_id;
	$waitlistsize = 0;
	//Calculate if this camper needs to go on a waiting list
	//If not then update how many people are registered for this camp
	$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_camps WHERE camp_id=%s",$campid));
	//Check if they are already signed up for this camp:
	$count = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camper_id) FROM srbc_registration WHERE camper_id=%s AND camp_id=%s",$camper_id
	,$campid), ARRAY_N)[0][0]; 
	if ($count > 0)
	{
		error_msg("Sorry you are already registered for this camp");
		return;
	}
	
	$count = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id) FROM srbc_registration WHERE camp_id=%s AND waitlist=0",$campid), ARRAY_N); 
	//Check if this person is already signed up for a camp
	if($count[0][0] < $camp->overall_size)
	{
		//This camp is not overall full check gender specific cap
		if ($gender == "male")
		{
			$count = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$campid), ARRAY_N); 
			if ($count[0][0] >= $camp->boy_registration_size)
			{
				error_msg("Unfortunately we cannot register you because the boys section of this camp is full.");
				goto waitinglist;
			}
		}
		else if($gender == "female")
		{
			$count = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$campid), ARRAY_N); 
			if ($count[0][0] >= $camp->girl_registration_size)
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
		
		
		//Count overall waitlist size for this camp
		$waitlistsize = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id) FROM srbc_registration WHERE NOT waitlist=0 AND camp_id=%s",$campid), ARRAY_N)[0][0]; 
		//Check if the waiting list is full
		if ($waitlistsize < $camp->waiting_list_size)
		{
			error_msg("You have been put on the waiting list for this camp because registration is full.");	
			$waitlistsize++;
		}
		else
		{
			//We can't continue registration because waiting list is full
			error_msg("Sorry we are unable to add you to the waiting list because the waiting list is full");
			return;
		}
	}
	$wpdb->insert(
			'srbc_registration', 
			array( 
				'registration_id' =>0,
				'camp_id' => $campid, 
				'camper_id' => $wpdb->insert_id,
				'horse_opt' => $horse_opt,
				'busride' => $busride,
				'waitlist' => $waitlistsize
			), 
			array( 
				'%d',
				'%d', 
				'%d',
				'%d',
				'%s',
				'%s'
			) 
			);
			
	if (isset($_POST["using_check"])){
		sendMail(srbc_email,"$parent_first_name $parent_last_name is sending a check ",
		"Hi,\r\n$parent_first_name $parent_last_name is sending a check for $camper_first_name $camper_last_name<br>Thanks!<br>-Peter Hawke SRBC Ancilla");
	}
	else
	{
		//Credit Card Stuff
		//Append all the data together so we only have to encrypt one string
		$data = $_POST["cc_name"] . "	" . $_POST["cc_type"] . "	" . $_POST["cc_number"] . "	" . $_POST["cc_vcode"] . "	" . $_POST["cc_month"]
		. "/" . $_POST["cc_year"] . "	" . $_POST["cc_zipcode"];
		if ($waitlistsize > 0)
		{	//Make sure to let the credit card processer that this is on the waitlist, so we might not need to process it
			$data .= '   USER IS WAITLISTED, MAKE SURE THEY ARE NOT ON THE WAITLIST BEFORE PROCESSING';
		}
		//Encrypt using ssl
		$fp=fopen($_SERVER['DOCUMENT_ROOT']. '/files/public.pem',"r");
		$pub_key=fread($fp,8192);
		fclose($fp);
		openssl_get_publickey($pub_key);
		openssl_public_encrypt($data,$edata,$pub_key);
		$date = new DateTime("now", new DateTimeZone('America/Anchorage'));
		$wpdb->insert(
			'srbc_cc', 
			array( 
				'cc_id' =>0,
				//Use base64 so the database can handle it properly since we are just using text
				'data' => base64_encode($edata), 
				'amount' => $_POST["cc_amount"],
				'camper_name' => $camper_first_name . " " . $camper_last_name,
				'camp' => ($camp->area . " " . $camp->camp_description),
				'payment_date' => $date->format("m/j/Y")
			), 
			array( 
				'%d',
				'%s', 
				'%d',
				'%s',
				'%s',
				'%s'
			) 
			);
		
	}
	
	$message = "Hi ". $parent_first_name . ",<br><br>Thanks for signing up " . $camper_first_name . " for " . $_POST["camp_desc"] . "!  Camp starts " .date("D M j",strtotime($camp->start_date)) . " and ends " . 
	date("D M j",strtotime($camp->end_date)) . "!  If you have any questions feel free to check ". 
	'our <a href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.  If you want to know what your child should pack for camp, check out our <a href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>!'.
	"<br> One last thing is that we ask that you print out this health form and fill it out to speed up the registration process.<br>Thanks!<br> -Solid Rock Bible Camp";
	sendMail($email,"Thank you for signing up for a Solid Rock Camp!",$message,$_SERVER['DOCUMENT_ROOT']. '/attachments/healthform.pdf');
	return "Registration Sucessful!<br>  We sent you a confirmation email with some frequently asked questions and what camp you signed up for. (If you don't see the email check your spam box and please mark it not spam)";
}

function srbc_camps($atts){
	$query = $atts["area"];
	$finalText = "*If a camp is full but there is still waitlist spots available then continue registration and it will put you on the waitlist";
	$finalText .= '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Camp Description</th>
				<th>Cost</th>
				<th>Start/End Date</th>
				<th>Grade Range</th>
				<th>Spots Available</th>
				<th>Waiting List Spots Available*</th> 
				</tr>';
	global $wpdb;
	$camps = $wpdb->get_results("SELECT * FROM srbc_camps WHERE area='$query' ORDER BY start_date");	
	if (count($camps) == 0)
		return "<h2>There is currently no camps scheduled for this area at this time.  Please check back later!</h2>";
	foreach ($camps as $camp){
		$finalText .=  '<tr><td>' . $camp->camp_description . '		<a href="../register-for-a-camp/?campid=' . $camp->camp_id . '">(Register)</a>';
		$finalText .=  "</td><td>$" . $camp->cost;
		$finalText .=  "</td><td>" . date("M j",strtotime($camp->start_date)) . "/" . date("M j - Y",strtotime($camp->end_date));
		$finalText .=  "</td><td>" . $camp->grade_range;
		
										
		$boycount = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id), ARRAY_N)[0][0];
		$girlcount = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id), ARRAY_N)[0][0]; 
										
		$total_registered = $boycount + $girlcount;
		$finalText .=  "</td><td>";
		if (($camp->overall_size - $total_registered) == 0){
			$finalText .= "Camp is full,<br> see if waitlist is full.";
		}	
		else if($boycount >= $camp->boy_registration_size && $camp->boy_registration_size != 0){
			$finalText .= "Boy's section is full,<br>girls can still register!";
		}
		else if($girlcount >= $camp->girl_registration_size && $camp->girl_registration_size != 0){
			$finalText .= "Girl's section is full,<br>boys can still register!.";
		}
		else
			$finalText .= ($camp->overall_size - $total_registered); 
		$finalText .=  "</td><td>";
		$waitlistsize = $wpdb->get_results("SELECT COUNT(camp_id)
										FROM srbc_registration
										WHERE camp_id=$camp->camp_id AND NOT waitlist=0", ARRAY_N)[0][0]; 
		$finalText .=  ($camp->waiting_list_size - $waitlistsize) . "</td></tr>";
	}
	$finalText .=  "</table> ";
	return $finalText;
}


?>