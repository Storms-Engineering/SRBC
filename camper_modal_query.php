<div class="modal-header">
<span onclick="closeModal();" class="close">&times;</span>
	<h2>
	<?php
	//This returns the data for one specific camper.
	require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	//Security check - kinda
	if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");
	global $wpdb;
	$camper = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_campers WHERE camper_id = %d",$_GET['camper_id'] ));
	echo "Camper info for " . $camper->camper_first_name . " " . $camper->camper_last_name;
	?></h2>
	
	</div>
	<button class="big_button" style="float:right;" onclick="saveInfo();closeModal();">Save Info & Close</button>
			<div class="modal-body">
			<?php
				//TODO  I might not need this span class
				//BODY Everything might be handled by the label now
				echo '<div id="information"><span style="display:none;" id="camper_id">' . $camper->camper_id . '</span>';
				echo '<span class="info"><label class="name_label">Camper: </label><input type="text" name="camper_first_name" value="' . $camper->camper_first_name . '"> ';
				echo '<input type="text" name="camper_last_name" value="' . $camper->camper_last_name . '"></span>';
				echo '<br><span class="info"><label class="name_label">Parent: </label><input type="text" name="parent_first_name" value="' . $camper->parent_first_name . '"> ' 
					. '<input type="text" name="parent_last_name" value="' . $camper->parent_last_name . '"></span>';
				echo '<br><span class="info"><label class="name_label">Phone #\'s:</label><input type="text" name="phone" value="'. $camper->phone . '">';
				echo ' <input type="text" name="phone2" value="'. $camper->phone2 . '"></span>';
				echo '<span class="info">Email: <input type="text" name="email" value="'. $camper->email . '"></span><br><br>';
				echo '<span class="info"><label class="name_label">Birthday:</label> <input type="date" name="birthday" value="'. $camper->birthday . '"></span>';
				echo '<span class="info">Grade: <input type="text" class="financial" name="grade" value="' . $camper->grade . '"></span>';
				echo '<span class="info">Age: <input type="text" class="financial" name="age" value="'. $camper->age . '"></span>';
				echo '<span class="info">Gender: <input type="text" class="financial" name="gender" value="'. $camper->gender . '"></span><br><br>';
				echo '<span class="info"><label class="name_label">Address:</label> <input type="text" name="address" value="' . $camper->address . '">';
				echo ' <input type="text" name="city" value="' . $camper->city . '"> ' .
					'<input type="text" name="state" class="financial" value="' . $camper->state . '"> ' .
					'<input type="text" name="zipcode" value="' . $camper->zipcode . '"></span>';
				echo '<br><h3>Camper Notes:<h3> <br><textarea id="notes" rows="4" cols="50">' . $camper->notes . '</textarea></div>';
				echo '<h3>Camps signed up for:</h3><br>';
				$registrations = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_registration'] . " WHERE camper_id=%s",$camper->camper_id));
				//Check that they have registrations
				if (count($registrations) == 0)
					echo '<h1 style="text-align:center;color:red">Camper is not signed up for any camps</h1>';
				else
				{
					//This is going away
					//Create code for making a selection box
					$camps = $wpdb->get_results("SELECT area,name,camp_id FROM " . $GLOBALS['srbc_camps'] . " ORDER BY area ASC");
					$camp_selection = '<div id="popup_camps_background"><div id="popup_camps">
					Pick what camp to change to: <select style="margin:auto;" id="camps" name="camps"><option value="none">none</option>';
					foreach ($camps as $camp){
						$camp_selection .= '<option value='.$camp->camp_id .'>'.$camp->area . ' ' . $camp->name .'</option>';
					}
					$camp_selection .= '</select><br><button class="big_button" id="popup_camps_button">OK</button></div></div>';
					echo $camp_selection;
				}
				$registration_ids = [];
				//Display each camp that they are registered for in a collapsible
				foreach ((array)$registrations as $registration)
				{
					$registration_ids[] = $registration->registration_id;
					
					//Grab the camp since we need some info from it
					$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_camps'] . " WHERE camp_id=%s",$registration->camp_id));
					echo '<span id="registration_id" style="display: none;">' . $registration->registration_id . '</span>';
					//Calculate the busfee
					$busride = $registration->busride;
					$busride_cost = 0;
					
					$busSelector = array(null,null,null,null);
					if($busride == "none")
						$busSelector[0] = "selected";
					else if($busride == "both")
						$busSelector[1] = "selected";
					else if($busride == "to")
						$busSelector[2] = "selected";
					else if($busride == "from")
						$busSelector[3] = "selected";
					else 
					{
						error_msg("Seems like you don't have a valid bus status....");
					}
					
					$busride = '<select class="inputs" name="busride">
					<option value="none"' . $busSelector[0] . '>No bus ride needed</option>
					<option value="both"' . $busSelector[1] . '>Round-Trip $60</option>
					<option value="to"' . $busSelector[2] . '>One-way to Camp $35</option>
					<option value="from"' . $busSelector[3] . '>One-way to Anchorage $35</option>
					</select>';

					$horseSelector = array(null,null);
					if($registration->horse_opt == 1)
						$horseSelector[1] = "selected";
					else
						$horseSelector[0] = "selected";
					$horseHTML = '<select class="inputs" name="horse_opt">
					<option value="0"' . $horseSelector[0] .'>No Horses</option>
					<option value="'.$camp->horse_opt.'"'. $horseSelector[1].'>Horses $'. $camp->horse_opt.'</option>
					</select>';
					
					//Shows a red waitlist
					$campWaitlistHTML = NULL;
					if ($registration->waitlist != 0)
						$campWaitlistHTML = ' <span style="color:red;">(Waitlisted for Camp)</span>';

					
					$horsesWaitlistHTML = NULL;
					if($registration->horse_waitlist == 1)
						$horsesWaitlistHTML = ' <span style="color:red;"><b>(Waitlisted for Horses)</b></span>';						
					
					$payedCard = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s AND payment_type='card'",$registration->registration_id));
					$payedCheck = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s AND payment_type='check'",$registration->registration_id));
					$payedCash = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s AND payment_type='cash'",$registration->registration_id));
					
					echo '<button class="collapsible">'.$camp->area . ' ' . $camp->name . $campWaitlistHTML . 
					'<span style="float:right;">Registered:'. $registration->date . '</span></button><div class="content">';
					
					echo 'Counselor: <input name="counselor" type="text" value="' . $registration->counselor . '">';
					echo 'Lodged in: <input name="cabin" list="lodging" type="text" value="' . $registration->cabin . '"><br>';
					echo '<datalist id="lodging">
							<option value="Girls Tent">
							<option value="Guys Tent">
							<option value="Spruce/Aspen">
							<option value="Birch/Willow">
							<option value="Tustumena">
							<option value="Redoubt">
							<option value="Wagon 1">
							<option value="Wagon 2">
							<option value="Wagon 3">
							<option value="Wagon 4">
							<option value="Susitna">
							<option value="Spurr">
							<option value="Illiamna">
							<option value="Augustine">
							<option value="Skilak">
							<option value="Beluga">
							</datalist>';
							
					//Checkboxes
					$checked = "";
					if ($registration->checked_in == 1)
						$checked = "checked";
					echo '<fieldset><legend>Registration Day</legend><h3 class="checkbox_header">Camper checked in:</h3> <input class="srbc_checkbox" name="checked_in" type="checkbox" ' . $checked .'>';
					$checked = "";
					if ($registration->health_form == 1)
						$checked = "checked";
					echo '<br><h3 class="checkbox_header">Camper has health form:</h3> <input class="srbc_checkbox" name="health_form" type="checkbox" ' . $checked .'></fieldset>';
					$checked = "";
					if ($registration->waitlist == 1)
						$checked = "checked";
					echo '<fieldset><legend>Office Use</legend><h3 class="checkbox_header">On Waitlist</h3> <input name="waitlist" type="checkbox" ' . $checked .'>';
					$checked = "";
					if ($registration->horse_waitlist == 1)
						$checked = "checked";
					echo '<h3 class="checkbox_header">Horse Waitlist</h3> <input name="horse_waitlist" type="checkbox" ' . $checked .'>';
					$checked = "";
					if ($registration->packing_list_sent == 1)
						$checked = "checked";
					echo '<br><h3 style="display:inline;">Packing List Sent</h3> <input name="packing_list_sent" type="checkbox" ' . $checked .'></fieldset>';		
							
					//Financial Inputs
					echo '<span class="financial_info"><h3>Camp Cost:   $<span id="camp_cost">' . $camp->cost . '</span></h3></span>';		
					echo '<span class="financial_info">'.$horsesWaitlistHTML.'Horse Option '.$horseHTML.' $<input class="financial" name="horse_opt" type="text" value="0" readonly></span>';
					echo '<span class="financial_info">Busride ' . $busride .  ': $<input class="financial" name="busride_cost" type="text" value="' . $busride_cost .'" readonly></span>';
					echo '<span class="financial_info">Discount: $<input class="financial" type="text" name="discount" value="' . $registration->discount . '"></span>';
					$discountSelector = array(null,null,null);
					if($registration->discount_type == "Multiple Child")
						$discountSelector[1] = "selected";
					else if($registration->discount_type == "Staff")
						$discountSelector[2] = "selected";
					else
						$discountSelector[0] = "selected";
					echo '<span class="financial_info">Discount Type:<select name="discount_type" class="inputs discount_type">
					<option value="" ' . $discountSelector[0] . '>None</option>
					<option value="Multiple Child"' . $discountSelector[1] . '>Multiple Child</option>
					<option value="Staff"' . $discountSelector[2] . '>Staff</option>
					</select></span>';
					echo '<span class="financial_info">Scholarship Amount: $<input class="financial" name="scholarship_amt" type="text" value="' . $registration->scholarship_amt . '"></span>';
					$scholSelector = array(null,null,null,null,null);
					if($registration->scholarship_type == "Need")
						$scholSelector[1] = "selected";
					else if($registration->scholarship_type == "Workcrew/WIT")
						$scholSelector[2] = "selected";
					else if($registration->scholarship_type == "Trade")
						$scholSelector[3] = "selected";
					else if($registration->scholarship_type == "Volunteer")
						$scholSelector[4] = "selected";
					else
						$scholSelector[0] = "selected";
					echo '<span class="financial_info">Scholarship Type: <select name="scholarship_type" class="inputs scholarship_type">
					<option value="" ' . $scholSelector[0] . '>None</option>
					<option value="Need"' . $scholSelector[1] . '>Need</option>
					<option value="Workcrew/WIT"' . $scholSelector[2] . '>Workcrew/WIT</option>
					<option value="Trade"' . $scholSelector[3] . '>Trade</option>
					<option value="Volunteer"' . $scholSelector[4] . '>Volunteer</option>
					</select><br></span>';
					echo '<span class="financial_info">Paid Check: $<input class="financial" type="text" value="' . $payedCheck . '" readonly></span>';
					echo '<span class="financial_info">Paid Cash: $<input class="financial" type="text" value="' . $payedCash . '" readonly></span>';
					echo '<span class="financial_info">Paid Card: $<input class="financial" type="text" value="' . $payedCard . '" readonly></span>';
					echo '<span class="financial_info"><h3>Amount Due: $<span class="amount_due"></span></h3></span>';


					
					//Payment Section
					echo '<span><h2>Make a payment:</h3>Payment type: <select name="payment_type" class="inputs payment_type">
					<option value="none" id="default" selected></option>
					<option value="card">Credit Card</option>
					<option value="check">Check</option>
					<option value="cash">Cash</option>
					</select>
					Amount: $<input type="text" name="payment_amt"><br>
					Note (Check # or Last 4 of CC): <input type="text" name="note"></span>
					<br>Fee Type<select name="fee_type" class="inputs fee_type">
					<option value="none" selected>None</option>
					<option value="Lakeside" >Lakeside</option>
					<option value="Wagon Train">Wagon Train</option>
					<option value="Wilderness">Wilderness</option>
					<option value="LS Horsemanship">LS Horsemanship</option>
					<option value="WT Horsemanship">WT Horsemanship</option>
					<option value="Bus">Bus</option>
					<option value="Store">Store</option>
					</select>';
					
					//Autopayment section
					echo "<br><h3>Make Autopayment</h3>";
					echo 'Payment type: <select name="auto_payment_type" class="inputs auto_payment_type">
					<option value="none" id="default" selected></option>
					<option value="card">Credit Card</option>
					<option value="check">Check</option>
					<option value="cash">Cash</option>
					</select>';
					echo 'Note (Check # or Last 4 of CC): <input type="text" name="auto_note"></span>';
					echo '<br><b>Auto split payment (Beta):</b> $<input type="text" name="auto_payment_amt" >';
					
					//Print out the different fees that have been paid
					$fees = $wpdb->get_results( $wpdb->prepare("SELECT fee_type,payment_amt FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s",$registration->registration_id));
					//Add duplicate fees to this array
					$f = array();
					foreach($fees as $fee){
						if (array_key_exists($fee->fee_type,$f))
							$f[$fee->fee_type] += $fee->payment_amt;
						else
							$f[$fee->fee_type] = $fee->payment_amt;
							
					}
					$finalText = NULL;
					$keys = array_keys($f);
					echo "<br><h3>Fees paid:</h3>";
					for($i=0;$i<count($keys);$i++){
						$finalText .= $keys[$i] . ": $" . $f[$keys[$i]] . "<br>";
					}
					echo $finalText;
					echo '<br><br><button class="big_button" onclick="saveInfo();" >Save</button>';
					echo ' <button class="big_button" onclick="changeCamp('.$registration->registration_id.','.$camper->camper_id.','.$camp->camp_id.')">Change Camp To</button>';
					
					echo '<br><br><button class="big_button" style="background:#009933" onclick="resendEmail('.$registration->registration_id.');" >Resend Email</button>	<button class="big_button" style="background:red" onclick="deleteRegistration(' . $registration->registration_id . ',' . $registration->camper_id . ',' . $registration->camp_id . ')">Delete Registration</button>';
					//Modal end div
					echo "</div>";
				}
				
				$paymentHistory = NULL;
				if (count($registrations) != 0)
				{
					//Thanks to coderwarll for this: https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
					// how many entries will we select?
					$how_many = count($registration_ids);
					// prepare the right amount of placeholders
					// if you're looing for strings, use '%s' instead
					$placeholders = array_fill(0, $how_many, '%d');

					// glue together all the placeholders...
					// $format = '%d, %d, %d, %d, %d, [...]'
					$format = implode(', ', $placeholders);
					//Show payment history:
					$payments = $wpdb->get_results( $wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id IN($format)",$registration_ids));
					
					foreach ($payments as $payment) {
						$paymentHistory .= $payment->payment_type . " $" . $payment->payment_amt . " " . $payment->note . " " . $payment->payment_date . " " . $payment->fee_type . "\r\n";
					}
				}

				echo '<h3>Payment History</h3><br><textarea rows="4" cols="55">' . $paymentHistory . '</textarea>'
			?>
			</div>
			<div class="modal-footer"><button onclick="saveInfo();closeModal();" class="big_button">Save Info & Close</button></div>
