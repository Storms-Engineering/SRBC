<div class="modal-header">

<span onclick="closeModal();" class="close">&times;</span>
	<h2>
	<?php
	//This returns the data for one specific camper.
	require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	global $wpdb;
	$camper = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_campers WHERE camper_id = %d",$_GET['camper_id'] ));
	echo "Camper info for " . $camper->camper_first_name . " " . $camper->camper_last_name;
	?></h2>
	
	</div>
	<button class="save_button" style="float:right;" onclick="saveInfo();closeModal();">Save Info & Close</button>
			<div class="modal-body">
			<?php
				echo '<div id="information"><span style="display:none;" id="camper_id">' . $camper->camper_id . '</span>';
				echo '<span class="info">Camper: <input type="text" name="camper_first_name" value="' . $camper->camper_first_name . '"> ';
				echo '<input type="text" name="camper_last_name" value="' . $camper->camper_last_name . '"></span>';
				echo '<span class="info">Parent: <input type="text" name="parent_first_name" value="' . $camper->parent_first_name . '"> ' 
					. '<input type="text" name="parent_last_name" value="' . $camper->parent_last_name . '"></span>';
				echo '<span class="info">Phone: <input type="text" name="phone" value="'. $camper->phone . '"></span>';
				echo '<span class="info">Phone2: <input type="text" name="phone2" value="'. $camper->phone2 . '"></span>';
				echo '<span class="info">Email: <input type="text" name="email" value="'. $camper->email . '"></span><br><br>';
				echo '<span class="info">Birthday: <input type="date" name="birthday" value="'. $camper->birthday . '"></span>';
				echo '<span class="info">Grade: <input type="text" class="financial" name="grade" value="' . $camper->grade . '"></span>';
				echo '<span class="info">Age: <input type="text" class="financial" name="age" value="'. $camper->age . '"></span>';
				echo '<span class="info">Gender: <input type="text" class="financial" name="gender" value="'. $camper->gender . '"></span><br><br>';
				echo '<br><br><span class="info">Address: <input type="text" name="address" value="' . $camper->address . '">';
				echo '<input type="text" name="city" value="' . $camper->city . '"> ' .
					'<input type="text" name="state" class="financial" value="' . $camper->state . '"> ' .
					'<input type="text" name="zipcode" value="' . $camper->zipcode . '"></span>';
				echo '<br><h3>Notes:<h3> <br><textarea id="notes" rows="4" cols="50">' . $camper->notes . '</textarea></div>';
				echo '<h3>Camps signed up for:</h3><br>';
				$registrations = $wpdb->get_results($wpdb->prepare("SELECT * FROM srbc_registration WHERE camper_id=%s",$camper->camper_id));
				if (count($registrations) == 0)
					echo "Camper is not signed up for any camps";
				//Create code for making a selection box
				$camps = $wpdb->get_results("SELECT area,name,camp_id FROM srbc_camps");
				$camp_selection = '<select id="~" name="camps"><option value="none">none</option>';
				foreach ($camps as $camp){
					$camp_selection .= '<option value='.$camp->camp_id .'>'.$camp->area . ' ' . $camp->name .'</option>';
				}
				$camp_selection .= '</select>';
				//Display each camp that they are registered for in a collapsible
				foreach ($registrations as $registration)
				{
					//Grab the camp since we need some info from it
					$camp = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_camps WHERE camp_id=%s",$registration->camp_id));
					echo '<span id="registration_id" style="display: none;">' . $registration->registration_id . '</span>';
					//Calculate the busfee
					$busride = $registration->busride;
					$busride_cost = 0;
					//TODO: UPDATE THIS
					if($busride == "both")
					{
						$busride= '<select class="inputs" name="busride">
					<option value="none">No bus ride needed</option>
					<option value="both" selected>Round-Trip $60</option>
					<option value="to">One-way to Camp $35</option>
					<option value="from">One-way to Anchorage $35</option>
					</select>';
						$busride_cost = 60;
					}
					else if(!($busride == "none"))
					{
						if($busride == "to"){
							$busride= '<select class="inputs" name="busride">
							<option value="none">No bus ride needed</option>
							<option value="both">Round-Trip $60</option>
							<option value="to" selected>One-way to Camp $35</option>
							<option value="from">One-way to Anchorage $35</option>
							</select>';
						}
						else
						{
							$busride= '<select class="inputs" name="busride">
							<option value="none">No bus ride needed</option>
							<option value="both">Round-Trip $60</option>
							<option value="to">One-way to Camp $35</option>
							<option value="from" selected>One-way to Anchorage $35</option>
							</select>';
						}
						$busride_cost = 35;
					}
					else
					{
						$busride= '<select class="inputs" name="busride">
					<option value="none"selected>No bus ride needed</option>
					<option value="both">Round-Trip $60</option>
					<option value="to">One-way to Camp $35</option>
					<option value="from">One-way to Anchorage $35</option>
					</select>';
					}
					
					//TODO: do buslist like this
					//An array for holding which option is selected
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
					
					echo '<button class="collapsible">'.$camp->area . ' ' . $camp->name . $campWaitlistHTML . 
					'<span style="float:right;">Registered:'. $registration->date . '</span></button><div class="content">';
					echo '<span class="financial_info"><h3>Camp Cost:   $<span id="camp_cost">' . $camp->cost . '</span></h3></span>';
					echo 'Counselor: <input name="counselor" type="text" value="' . $registration->counselor . '">';
					echo 'Cabin: <input name="cabin" type="text" value="' . $registration->cabin . '"><br>';
					echo '<span class="financial_info">'.$horsesWaitlistHTML.'Horse Option '.$horseHTML.' $<input class="financial" name="horse_opt" type="text" value="0" readonly></span>';
					echo '<span class="financial_info">Busride ' . $busride .  ': $<input class="financial" name="busride_cost" type="text" value="' . $busride_cost .'" readonly></span>';
					echo '<span class="financial_info">Discount: $<input class="financial" type="text" name="discount" value="' . $registration->discount . '"></span>';
					echo '<span class="financial_info">Discount Type: <input type="text" name="discount_type" value="' . $registration->discount_type . '"></span>';
					echo '<span class="financial_info">Scholarship Amount: $<input class="financial" name="scholarship_amt" type="text" value="' . $registration->scholarship_amt . '"></span>';
					echo '<span class="financial_info">Scholarship Type: <input type="text" name="scholarship_type" value="' . $registration->scholarship_type . '"><br></span>';
					echo '<span class="financial_info">Payed Check: $<input class="financial" name="payed_check" type="text" value="' . $registration->payed_check . '" readonly></span>';
					echo '<span class="financial_info">Payed Cash: $<input class="financial" name="payed_cash" type="text" value="' . $registration->payed_cash . '" readonly></span>';
					echo '<span class="financial_info">Payed Card: $<input class="financial" name="payed_card" type="text" value="' . $registration->payed_card . '" readonly></span>';
					echo '<span class="financial_info"><h3>Amount Due: $<span class="amount_due"></span></h3></span>';
					//TODO we aren't really using amount due.  It was only for reports so I will need to restructure the database at somepoint
					//. $registration->amount_due .
					
					$checked = "";
					if ($registration->checked_in == 1)
						$checked = "checked";
					echo '<h3 style="display:inline;">Camper checked in:</h3> <label class="switch"><input name="checked_in" type="checkbox" ' . $checked .'><span class="slider"></span></label>';
					echo '<span><h2>Make a payment:</h3>Payment type: <select class="inputs payment_type">
					<option value="none" id="default" selected></option>
					<option value="card">Credit Card</option>
					<option value="check">Check</option>
					<option value="cash">Cash</option>
					</select>
					Amount: $<input type="text" name="payment_amt"><br>
					Note (Check # or Last 4 of CC): <input type="text" name="note"></span>
					<br>Fee Type<select class="inputs fee_type">
					<option value="none" selected>None</option>
					<option value="Lakeside" >Lakeside</option>
					<option value="Wagon Train">Wagon Train</option>
					<option value="Wilderness">Wilderness</option>
					<option value="LS Horsemanship">LS Horsemanship</option>
					<option value="WT Horsemanship">WT Horsemanship</option>
					<option value="Bus">Bus</option>
					<option value="Store">Store</option>
					</select>';
					
					//Print out the different fees that have been payed
					$fees = $wpdb->get_results( $wpdb->prepare("SELECT fee_type,payment_amt FROM srbc_payments WHERE camper_id=%s AND camp_id=%s",$camper->camper_id,$camp->camp_id));
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
					echo "<br><h3>Fees payed:</h3>";
					for($i=0;$i<count($keys);$i++){
						$finalText .= $keys[$i] . ": $" . $f[$keys[$i]] . "<br>";
					}
					echo $finalText;
					echo '<b>Auto split payment (Currently in beta, sorry kelly):</b> $<input type="text" name="auto_payment" >';
					echo '<br><br><button class="save_button" onclick="saveInfo();" >Save</button>';
					//Replace the id with a unique id for this option based on which registration
					echo ' <button class="save_button" onclick="changeCamp('.$registration->registration_id.','.$camper->camper_id.','.$camp->camp_id.')">Change Camp To</button>'
					//Replace the ~ with the registration id
					.str_replace("~",$registration->registration_id,$camp_selection);
					
					echo '<br><br><button class="save_button" style="background:#009933" onclick="resendEmail('.$registration->registration_id.');" >Resend Email</button>	<button class="save_button" style="background:red" onclick="deleteRegistration(' . $registration->registration_id . ',' . $registration->camper_id . ',' . $registration->camp_id . ')">Delete Registration</button>';
					//Modal end div
					echo "</div>";
				}
				//Show payment history:
				$payments = $wpdb->get_results( $wpdb->prepare("SELECT * FROM srbc_payments WHERE camper_id=%s",$camper->camper_id));
				$paymentHistory = NULL;
				foreach ($payments as $payment) {
					$paymentHistory .= $payment->payment_type . " $" . $payment->payment_amt . " " . $payment->note . " " . $payment->payment_date . " " . $payment->fee_type . "\r\n";
				}
				echo '<h3>Payment History</h3><br><textarea rows="4" cols="55">' . $paymentHistory . '</textarea>'
			?>
			</div>
			<div class="modal-footer"><button onclick="saveInfo();closeModal();" class="save_button">Save Info & Close</button></div>
