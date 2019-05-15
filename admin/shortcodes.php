<?php

/*
-------------------------------------------------------------------------
SHORTCODE HOOKS
*/
//Email about volunteering
function srbc_volunteer_contact_form_email($atts){
	if (!isset($_POST['contact_name'])){
		//They put nothing in so just exit.
		//This will usually happen when they load the page
		return;
	}
	$body = $_POST['contact_name'] . " has some questions:<br>" . $_POST['questions'] . "<br>Contact info: " .
		$_POST['phone'] . ' ' . $_POST['email'] . "<br>Area of interest:" . $_POST['area_of_interest'] . "<br><br>- Peter Hakwe SRBC Ancilla";
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
	$query = "SELECT *	FROM ". $GLOBALS['srbc_camps']  ." WHERE ";
	if ($_GET['area'] == "") {
		$query .= $GLOBALS['srbc_camps'] .".area LIKE '%' ";
	}
	else {
		$values = array($_GET["area"]);
		$query .= $GLOBALS['srbc_camps'].".area='%s' ";
	}
	if (isset($_GET['start_date']) && isset($_GET['end_date']) && $_GET['end_date']!="" && isset($_GET['start_date']) != "" ){
		$query .= "AND ". $GLOBALS['srbc_camps'] . ".start_date BETWEEN '%s' AND '%s' ";
		array_push($values,$_GET['start_date']);
		array_push($values,$_GET['end_date']);
	}
	global $wpdb;
	$camps = $wpdb->get_results(
	$wpdb->prepare( $query, $values));
	
	$finalText = '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Camp</th>
				<th>Cost</th>
				<th>Start Date</th>
				<th>Camp Availability</th>
				</tr>';				
	foreach ($camps as $camp){
		$finalText .=  '<tr><td>' . $camp->area . " " . $camp->name . '		<a href="../register-for-a-camp/?campid=' . $camp->camp_id . '">(Register)</a>';
		$finalText .=  "</td><td>$" . $camp->cost;
		$finalText .=  "</td><td>" . date("M j",strtotime($camp->start_date));
		$boycount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id));
		$girlcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id)); 
										
		$total_registered = $boycount + $girlcount;
		$finalText .=  "</td><td>";
		if (($camp->overall_size - $total_registered) == 0){
			$finalText .= "Camp is full,<br> register to be put on waiting list";
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
		$finalText .=  "</tr>";
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
	   //Position is a nested array of values
	   if ($keys[$i] == "Position"){
		   $body .= $keys[$i] . ": ";
			foreach ($val as $v){
				if ($v != "")
					$body .= '<b style="font-size:20px">' . $v . "</b> " . ", ";
			}
			$body .= "<br>";
	   }
	   else
			$body .= '<b style="font-size:20px">' . $keys[$i] . '</b>: ' . $val . "<br>";
	   $i++;
   }
   //Email applicant
   sendMail($_POST["email"], 'You applied to work at Solid Rock Bible Camp ',
   "Dear" . $_POST["Firstname"] . ",<br>Thanks for applying to work at Solid Rock Bible Camp!
   <br>Our camps wouldn't happen without people like you and others making Solid Rock Bible Camp Possible.
   <br>If you have any questions or need to talk to someone feel free to call us at 907-262-4741.<br>-Solid Rock Bible Camp");
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
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/registration.css">
	<div class="registration_box">
	<form action="../registration-complete/" method="post" style="margin:auto;" onsubmit="return validateForm()">
			<h4>Camp you wish to register for:
				<select style="width:275px;" name="campid">
				<?php
				global $wpdb;
				//Get list of camp ids and then populate the options box since the user just found this page
				$camp = NULL;
				if(isset($_GET['campid']))
				{
					$cmpid = $_GET['campid'];
					$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_camps'] . " WHERE camp_id=%s",$cmpid));
					if ($camp == "")
					{
						echo "</select><br><br>";
						error_msg("Please use the Camp Finder page to select a camp or go to the correct program area and find your camp
						there.  You shouldn't acess this page directly");
					}
					else
					{
						echo '<option value="'.$cmpid.'" selected>' .$camp->area . " " . $camp->name . '</option></select>';
						echo '<input type="hidden" name="camp_desc" value = "' .$camp->area . " " . $camp->name . '">'; 
						if($camp->horse_opt_cost != 0)
						{
							echo ' <input type="checkbox" id="horse_opt" onchange="calculateTotal();" name="horse_opt" value="true"> Horse Option $<span id="horse_opt_cost">' .$camp->horse_opt_cost. '</span><br>';
						}
					}
				}
				else
				{
					echo "</select><br><br>";
					error_msg("Please use the Camp Finder page to select a camp or go to the correct program area and find your camp there.
					You shouldn't acess this page directly");
				}
				?>
				</h4>
				<br>
				<span>Busride*:</span>
				<select onchange="calculateTotal();" class="inputs" id="busride" name="busride">
					<option value="none" selected>No bus ride needed</option>
					<option value="both">Round-Trip $60</option>
					<option value="to">One-way to Camp $35</option>
					<option value="from">One-way to Anchorage $35</option>
				</select>

				<p>*The bus will depart from and return to the Duluth Trading Company parking lot at 8931 Old Seward Hwy., Suite A Anchorage, AK 99515.
				The exact times will be sent you in your confirmation email or letter.</p>
			Camper:
			<input class="inputs" type="text" name="camper_first_name" placeholder="First Name" required>
			<input class="inputs" type="text" name="camper_last_name" placeholder="Last Name" required>
			Birthday: <input  type="date" name="birthday" required><br>Gender:
			<input type="radio" name="gender" value="male" required> Male
			<input type="radio" name="gender" value="female" required> Female<br>
			Grade: 
			<select class="inputs" name="grade">
				<option value="Kindergarten to 1st">Kindergarten to 1st</option>
				<option value="2nd to 3rd">Going into 2nd or 3rd Grade</option>
				<option value="4th to 6th">Going into 4th to 6th Grade</option>
				<option value="4th to 6th">Going into 7th to 8th Grade</option>
				<option value="4th to 6th">Going into 9th to 12th Grade</option>
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
			Street Address:<br>
				<textarea class="inputs" required name="address" rows="2" cols="30"></textarea>
				City:<input type="text" style="width:100px;" required name="city">
				State:<input type="text" style="width:50px;" required name="state">
				Zipcode:<input type="text"  style="width:100px;" required pattern="[0-9]{5}" title="Please enter a 5 digit zipcode" name="zipcode" ><br>
				<h3>Parental Notice and Release - Agreement is required for camper admittance</h3>
				
				<p>I/We, the undersigned, understand that while attending Solid Rock Bible Camp of Soldotna, Alaska (camp),
			the below-named child may be involved in various activities including but not limited to: horseback riding,
			water-skiing, the waterslide, swimming, boating, the Blob, riflery, archery, rope swing, the obstacle course,
			and other traditional camp activities. I/We have familiarized ourselves with these programs and activities included in,
			but not limited to, the Camp brochure. 	<select required title="You must agree to register for camp" class="legal">
					<option value="">Disagree</option>
					<option value="agree">Agree</option>
				</select></p>

			
			<p>
			In consideration of Solid Rock Ministries, Inc. allowing the child to attend Camp for the period specified
			and to participate in the activities of the Camp, I/we do hereby grant permission for the child to attend
			and to participate fully in said activities. I/We understand and accept the risks and dangers involved in
			such activities and do hereby release Solid Rock Ministries, Inc., its officers and directors, its employees,
			agents, and the Camp staff, from any and all claims, demands, actions, causes of actions of any sort,
			for injuries or death sustained by myself/ourselves or the child due to negligence or any other fault during 
			the period covered by this release, whether such an injury occurred on or off the Camp property.
			<select required title="You must agree to register for camp" class="legal">
				<option value ="">Disagree</option>
				<option value="agree">Agree</option>
			</select> </p>
			
			<p>I/We have instructed my/our son/daughter to obey the rules of Solid Rock Bible Camp.
				This waiver is effective only for the week(s) for which the camper is registered.
			<select required title="You must agree to register for camp" class="legal">
				<option value="">Disagree</option>
				<option value="agree">Agree</option>
			</select></p>
	<hr>
	<span style="color:red">Note: Your registration is not valid until the $50 non-refundable registration fee is received.  (This $50 DOES go towards the cost of the camp)</span><br>
	You must pay $50, or pay the full amount of the camp, unless you a are registering for the waitlist then you don't have to pay a registration fee.  Any remaining amount will be due the day of registration.
	<br>
	<br>
	<h2>Amount to pay: </h2>
	<!--<input type="radio" name="cc_amount" value="50"> $50<br>
	<input type="radio" name="gender" value="0"> $<span id="total"></span>
	<br>	-->
	<label class="container">$50
		<input type="radio" name="cc_amount" checked="checked" value="50">
		<span class="checkmark"></span>
	</label>
	<label class="container">$<span id="total">
	<?php
	echo $camp->cost;
	echo '</span><input type="radio" name="cc_amount" id="cc_amount" value="'.$camp->cost.'">';
	echo '<span style="display:none" id="camp_cost">' . $camp->cost . '</span>';
	?>
		<span class="checkmark"></span>
	</label>
	<label class="container">$0 Registering for waiting list
		<input type="radio"  name="cc_amount" id="waitlist" value="">
		<span class="checkmark"></span>
	</label>
	<hr>
	<h2>Use a credit card:</h2>	
		Name on Credit Card: <input type="text" name="cc_name">
		Billing Zip <input style="width:100px;" type="text" name="cc_zipcode">
		Credit Card # <input type="text" id="cc_number" name="cc_number"><br>
		Verification Code: <input type="text" name="cc_vcode" style="width:5%">
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
		<h3>OR</h3>
		<h2 style="display:inline">Send a check</h2> <input type="checkbox" id="use_check" name="using_check">
		<p>Please make checks out to Solid Rock Bible Camp and send to 36251 Solid Rock Road #1, Soldotna, Alaska 99669, with campers name in the memo.</p>
		<input type="submit" value="Submit">
	</form> 
	</div>
	<script src="../wp-content/plugins/SRBC/admin/registration.js"></script>
	<?php
	return ob_get_clean();
}


function srbc_registration_complete($atts)
{
	//TODO: Bleh this is messy I don't know why I did this
	//TODO: GET RID OF THIS WASTED SPACE
	$camper_first_name = $_POST["camper_first_name"];
	$camper_last_name = $_POST["camper_last_name"];
	$birthday = $_POST["birthday"];
	$gender = $_POST["gender"];
	$grade = $_POST["grade"];
	$parent_first_name = $_POST["parent_first_name"];
	$parent_last_name = $_POST["parent_last_name"];
	$email = $_POST["email"];
	$phone = $_POST["phone"];
	$phone2 = $_POST["phone2"];
	$address = $_POST["address"];
	$city = $_POST["city"];
	$state = $_POST["state"];
	$zipcode = $_POST["zipcode"];
	$busride = $_POST["busride"];
	
	//Horse option is just a boolean because I will pull the price from the camps database so we don't people changing prices
	$horse_opt = 0;
	if (isset($_POST["horse_opt"]))
		$horse_opt = 1;
	
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
		'phone2' => $phone2,
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
		'%s',
		'%d'		
		
	) 
	);
	$camper_id = $wpdb->insert_id;
	$waitlistsize = 0;
	$waitlist = 0;
	//Calculate if this camper needs to go on a waiting list
	//If not then update how many people are registered for this camp
	$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $GLOBALS['srbc_camps'] . " WHERE camp_id=%s",$_POST["campid"]));
	//Check if they are already signed up for this camp:
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camper_id) FROM " . $GLOBALS['srbc_registration'] . " WHERE camper_id=%s AND camp_id=%s",$camper_id
	,$_POST["campid"])); 
	if ($count > 0)
	{
		error_msg("Sorry you are already registered for this camp");
		return;
	}
	//Check if this camp is already full
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM " . $GLOBALS['srbc_registration'] . " WHERE camp_id=%s AND waitlist=0",$_POST["campid"])); 
	if($count < $camp->overall_size)
	{
		//This camp is not overall full check gender specific caps
		if ($gender == "male")
		{
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$_POST["campid"])); 
			if ($count >= $camp->boy_registration_size)
			{
				error_msg("Unfortunately we cannot register you because the boys section of this camp is full.");
				goto waitinglist;
			}
		}
		else if($gender == "female")
		{
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$_POST["campid"])); 
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
		
		if ($camp->boy_registration_size == 0 && $_POST["gender"] == "male")
		{
			error_msg("Sorry this is a girls only camp!");
			return;
		}
		
		//Count overall waitlist size for this camp
		$waitlistsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM " . $GLOBALS['srbc_registration'] . " WHERE NOT waitlist=0 AND camp_id=%s",$_POST["campid"])); 
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
		$listsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM " . $GLOBALS['srbc_registration'] . " WHERE horse_waitlist=0 AND horse_opt=1 AND camp_id=%s ",$_POST["campid"])); 
		//If we have to many people in horses
		if($listsize >= $camp->horse_list_size)
		{
			//We have exceeded our horse list so turn this option to 0
			$horse_opt = 0;
			$waitlistsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id) FROM " . $GLOBALS['srbc_registration'] . " WHERE horse_waitlist=1 AND camp_id=%s ",$_POST["campid"])); 
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
	if($waitlist == 0 && $_POST["cc_amount"] == "")
	{
		error_msg("Please enter credit card information or check the 'Send a check' option.
		This camp is not currently full and therefore you aren't being put on the waiting list.
		Please hit the back button and try again. Thanks!");
		exit();
	}
	$wpdb->insert(
			$GLOBALS['srbc_registration'], 
			array( 
				'registration_id' =>0,
				'camp_id' => $_POST["campid"], 
				'camper_id' => $camper_id,
				'date' => $currentDate->format("m/d/Y"),
				'horse_opt' => $horse_opt,
				'busride' => $busride,
				'waitlist' => $waitlist,
				'horse_waitlist' => $horse_waitlist
			), 
			array( 
				'%d',
				'%d', 
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d'
			) 
			);
	//Notify office that this parent is sending a check	
	if (isset($_POST["using_check"])){
		sendMail(srbc_email,"$parent_first_name $parent_last_name is sending a check ",
		"Hi,\r\n$parent_first_name $parent_last_name is sending a check for $camper_first_name $camper_last_name<br>Thanks!<br>-Peter Hawke SRBC Ancilla");
	}
	else if($waitlist != 1 && $_POST["cc_amount"] != "")
	{
		//Credit Card Stuff
		//Make credit card easier to read
		$cc_number = str_split($_POST["cc_number"]);
		array_splice($cc_number,4,0,"-");
		array_splice($cc_number,9,0,"-");
		array_splice($cc_number,14,0,"-");
		//Append all the data together so we only have to encrypt one string
		$data = $_POST["cc_name"] .	"	" . implode($cc_number) . "	" . $_POST["cc_month"]
		. "/" . $_POST["cc_year"] . "	" . $_POST["cc_vcode"] . "	" . $_POST["cc_zipcode"];
		if ($waitlistsize > 0)
		{	//Make sure to let the credit card processer that this is on the waitlist, so we might not need to process it
			$data .= '   USER IS WAITLISTED, MAKE SURE THEY ARE NOT ON THE WAITLIST BEFORE PROCESSING';
		}
		//Show comments about buslist and horse option and horse_cost
		$comments = autoSplit($_POST["cc_amount"],$camp->camp_id,$wpdb->insert_id,$busride,$horse_opt);
		//Encrypt using ssl
		$fp=fopen($_SERVER['DOCUMENT_ROOT']. '/files/public.pem',"r");
		$pub_key=fread($fp,8192);
		fclose($fp);
		openssl_get_publickey($pub_key);
		openssl_public_encrypt($data,$edata,$pub_key);
		$wpdb->insert(
			'srbc_cc', 
			array( 
				'cc_id' =>0,
				//Use base64 so the database can handle it properly since we are just using text
				'data' => base64_encode($edata), 
				'amount' => $_POST["cc_amount"],
				'camper_name' => $camper_first_name . " " . $camper_last_name,
				'camp' => ($camp->area . " " . $camp->name),
				'comments' => $comments,
				'payment_date' => $currentDate->format("m/d/Y")
			), 
			array( 
				'%d',
				'%s', 
				'%f',
				'%s',
				'%s',
				'%s',
				'%s'
			) 
			);
		
	}

	if ($waitlist == 1)
	{
		$message = "Hi ". $parent_first_name . ",<br><br>This is an email letting you know that you sucessfully put " . $camper_first_name . " on the waitlist for " . $_POST["camp_desc"] . "." . 
		"<br>We will email you and let you know if a spot opens up for the camp.<br>Thanks!<br> Solid Rock Bible Camp";
		sendMail($email,"Waitlist Confirmation",$message);
	}
	else
	{
	$message = "Hello ". $parent_first_name . ",<br><br>Thanks for signing up " . $camper_first_name . " for <b>" . $_POST["camp_desc"] . "</b>!  <br><br>
	<b>Camp Start date</b>:" .date("l, M j,Y",strtotime($camp->start_date)) . '<br><pre style="display:inline">	</pre><b>Drop off time</b>:5pm' . 
	"<br><br><b>Camp End date</b>:" .date("l, M j,Y",strtotime($camp->end_date)) . '<br><pre style="display:inline">	</pre><b>Pick up time</b>:9am<br><br>' . 
	"A <b>health form</b> has been attached. Please fill it out and bring it on the opening day of camp.<br><br>" . 
	'If you have any questions please refer to our <a href="http://solidrockbiblecamp.com/FAQS">FAQ page</a>.<br><br>' .
	'If you want to know what your camper should pack for camp, check out our <a href=" http://solidrockbiblecamp.com/camps/packing-lists">packing lists page</a>!'.
	"<br><br>See you on the opening day of camp!<br><br> -Solid Rock Bible Camp<br><br>36251 Solid Rock Rd #1<br>
	Soldotna, AK 99669<br>
	phone: (907) 262-4741<br>
	fax: (907) 262-9088<br>
	srbc@alaska.net";
	sendMail($email,"Registration Confirmation",$message,$_SERVER['DOCUMENT_ROOT']. '/attachments/healthform.pdf');
	}
	
	return "Registration Sucessful!<br>  We sent you a confirmation email with some frequently asked questions and what camp you signed up for. (If you don't see the email check your spam box and please mark it not spam)";
}


function autoSplit($cc_amount,$campid,$registration_id,$busride,$horseOpt)
{
	global $wpdb;
	$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s",$registration_id));
			
	//Make the scholarships and discounts add to total payed so we take it out of the base camp fee
	//Not using this because this is the first time they are signing up for the camp
	//$totalPayed += $o->discount + $o->scholarship_amt;
	if($totalPayed == NULL)
		$totalPayed = 0;
	//Check if they have payed the base camp amount which is (camp cost - horse cost)
	$camp = $wpdb->get_row("SELECT * FROM " . $GLOBALS['srbc_camps'] . " WHERE camp_id=$campid");
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
		$horseOpt = $camp->horse_opt;
	
	$comments = "";
	//Create seperate payments based on different fees until autoPaymentAmt is used up
	//or an overpayment happens which stores it in the database
	while ($autoPaymentAmt != 0)
	{
		if ($totalPayed < $baseCampCost)
		{
			//We still need to pay some on the base camp cost
			$needToPayAmount = $baseCampCost - $totalPayed;
			if ($camp->area == "Sports")
				$feeType = "Lakeside";
			else
				$feeType = $camp->area;
		}				
		//$totalPayed comes first because this also checks that they have payed more than we are currently looking atan
		//If we flip it then it becomes a negative number if the totalPayed is greater than the value we are checking
		//Check horse_cost (aka WT Horsemanship Fee
		else if(($totalPayed - $baseCampCost) < $camp->horse_cost) 
		{
			//We still need to pay some on the base camp cost
			$needToPayAmount = $camp->horse_cost - ($baseCampCost - $totalPayed);
			$feeType = "WT Horsemanship";
		}				
		//Horse option check aka LS Horsemanship
		else if(($totalPayed - $camp->cost) < $horseOpt) 
		{
			//We still need to pay some on the horse option
			$needToPayAmount = $horseOpt - ($totalPayed - $camp->cost);
			$feeType = "LS Horsemanship";
		}
		else if(($totalPayed - ($camp->cost + $horseOpt)) < $busfee) 
		{
			//We still need to pay some on the bus option
			$needToPayAmount = $busfee - ($totalPayed - ($camp->cost + $horseOpt));
			$feeType = "Bus";
		}
		else
		{
			//Overpayed
			$needToPayAmount = $autoPaymentAmt;
			$feeType= "Overpaid";
		}
		//Also updates autoPaymentAmt
		list ($autoPaymentAmt,$payed) = calcPaymentAmt($autoPaymentAmt,$needToPayAmount);
		$comments .= $feeType . ":$" . $payed . ",";
		$totalPayed += $payed;
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
	//this is how much money is left so subtract what we just payed
	$autoPaymentAmt -= $paymentAmt;
	return array($autoPaymentAmt,$paymentAmt);
}
function srbc_camps($atts){
	$query = $atts["area"];
	
	$finalText = '<table style="width:100%;">
				<tr style="background:#51d3ff;">
				<th>Camp</th>
				<th>Cost</th>
				<th>Start/End Date</th>
				<th>Going into Grades</th>
				<th>Camp Availability</th>
				</tr>';
	global $wpdb;
	$camps = $wpdb->get_results("SELECT * FROM " . $GLOBALS['srbc_camps'] . " WHERE area='$query' ORDER BY start_date");	
	//If no camps then give a message
	if (count($camps) == 0)
		return "<h2>There is currently no camps scheduled for this area at this time.  Please check back later!</h2>";
	
	//Initialize variable for the html code after the table with descriptions of camps
	$descriptions = NULL;
	//Create the table of camps
	foreach ($camps as $camp){
		$finalText .=  '<tr><td>' . $camp->name . '		<a href="../register-for-a-camp/?campid='.$camp->camp_id .'">(Register)</a><a href="#'.$camp->camp_id.'"> (More Info)</a>';
		$finalText .=  "</td><td>$" . $camp->cost;
		$finalText .=  "</td><td>" . date("M j",strtotime($camp->start_date)) . "/" . date("M j",strtotime($camp->end_date));
		$finalText .=  "</td><td>" . $camp->grade_range;
		
										
		$boycount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id));
		$girlcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id)); 
										
		$total_registered = $boycount + $girlcount;
		$finalText .=  "</td><td>";
		if (($camp->overall_size - $total_registered) == 0){
			$finalText .= "Camp is full,<br> register to be put on waiting list";
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