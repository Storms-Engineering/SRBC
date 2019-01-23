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
	<button class="save_button" style="float:right;" onclick="saveInfo();">Save Info & Close</button>
			<div class="modal-body">
			<?php
				echo '<span style="visible:hidden" id="camper_id">' . $camper->camper_id . '</span>';
				echo '<span class="info">Parent: ' . $camper->parent_first_name . ' ' . $camper->parent_last_name . '</span>';
				echo '<span class="info">Phone: '. $camper->phone . '</span>';
				echo '<span class="info">Email: '. $camper->email . '</span><br><br>';
				echo '<span class="info">Birthday: '. $camper->birthday . '</span>';
				echo '<span class="info">Grade: ' . $camper->grade;		
				echo '</span>';
				echo '<span class="info">Age: '. $camper->age . '</span>';
				echo '<span class="info">Gender: '. $camper->gender . '</span><br><br>';
				echo '<br><br><span class="info">Address: ';
				echo $camper->address . ' ' . $camper->city . ' ' . $camper->state . ' ' . $camper->zipcode . '</span>';
				echo '<br><h3>Notes:<h3> <br><textarea id="notes" rows="4" cols="50">' . $camper->notes . '</textarea>';
				echo '<h3>Camps signed up for:</h3><br>';
				$registrations = $wpdb->get_results($wpdb->prepare("SELECT * FROM srbc_registration WHERE camper_id=%s",$camper->camper_id));
				if (count($registrations) == 0)
					echo "Camper is not signed up for any camps";
			
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
					$waitlist = NULL;
					if ($registration->waitlist != 0)
					{
						$waitlist = ' <span style="color:red;">(Waitlisted)</span>';
					}
					
					
					echo '<button class="collapsible">'.$camp->area . ' ' . $camp->camp_description . $waitlist . '</button><div class="content">';
					echo '<span class="financial_info"><h3>Camp Cost:   $<span id="camp_cost">' . $camp->cost . '</span></h3></span>';
					echo 'Counselor: <input name="counselor" type="text" value="' . $registration->counselor . '">';
					echo 'Cabin: <input name="cabin" type="text" value="' . $registration->cabin . '"><br>';
					echo '<span class="financial_info">(Put a zero here if you want to take them off the Horse Option) Horse Option Cost: $<input class="financial" name="horse_opt" type="text" value="' . $camp->horse_opt . '"></span>';
					echo '<span class="financial_info">Busride ' . $busride .  ': $<input class="financial" name="busride_cost" type="text" value="' . $busride_cost .'"></span>';
					echo '<span class="financial_info">Discount: $<input class="financial" type="text" name="discount" value="' . $registration->discount . '"></span>';
					echo '<span class="financial_info">Scholarship Amount: $<input class="financial" name="scholarship_amt" type="text" value="' . $registration->scholarship_amt . '"></span>';
					echo '<span class="financial_info">Scholarship Type: <input type="text" name="scholarship_type" value="' . $registration->scholarship_type . '"><br></span>';
					echo '<span class="financial_info">Payed Check: $<input class="financial" name="payed_check" type="text" value="' . $registration->payed_check . '" readonly></span>';
					echo '<span class="financial_info">Payed Cash: $<input class="financial" name="payed_cash" type="text" value="' . $registration->payed_cash . '" readonly></span>';
					echo '<span class="financial_info">Payed Card: $<input class="financial" name="payed_card" type="text" value="' . $registration->payed_card . '" readonly></span>';
					echo '<span class="financial_info"><h3>Amount Due: $<span id="amount_due">' . $registration->amount_due . '</span></h3></span>';
					
					
					$checked = "";
					if ($registration->checked_in == 1)
						$checked = "checked";
					echo '<h3>Camper checked in:</h3><label class="switch"><input name="checked_in" type="checkbox" ' . $checked .'><span class="slider"></span></label>';
					echo '<button onclick="deleteRegistration(' . $registration->registration_id . ',' . $registration->camper_id . ',' . $registration->camp_id . ')">Delete Registration</button>';
					echo '<span><h2>Make a payment:</h3>Payment type: <select class="inputs" id="payment_type">
					<option value="none" selected></option>
					<option value="card">Credit Card</option>
					<option value="check">Check</option>
					<option value="cash">Cash</option>
					</select>
					Amount: $<input type="text" name="payment_amt"><br>
					Note (Check # or Last 4 of CC): <input type="text" name="note"></span></div>';
				}
				//Show payment history:
				$payments = $wpdb->get_results( $wpdb->prepare("SELECT * FROM srbc_payments WHERE camper_id=%s",$camper->camper_id));
				$paymentHistory = NULL;
				foreach ($payments as $payment) {
					$paymentHistory .= $payment->payment_type . " $" . $payment->payment_amt . " " . $payment->note . " " . $payment->payment_date . "\r\n";
				}
				echo '<h3>Payment History</h3><br><textarea rows="4" cols="50">' . $paymentHistory . '</textarea>'
			?>
			</div>
			<div class="modal-footer"><button onclick="saveInfo()" class="save_button">Save Info & Close</button></div>
