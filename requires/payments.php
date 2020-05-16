<?php
class Payments
{
	//Echos HTML code for credit card info
	//$sameAsAbove is for whether the checkbox for same info as above will be shown
	public static function setupCreditCardHTML($sameAsAbove = false)
	{
		echo 'Use a credit card:</h3>	
			Name on Credit Card: <input type="text" name="cc_name"><br>
			Billing Address:<br>';
		echo ($sameAsAbove ? 'Same as above <input type="checkbox" id="same_cc_address" onclick="moveAddress()">' : "" );
		echo '		<textarea class="inputs" style="width:auto;" name="cc_address" rows="1" cols="30"></textarea>
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
									<br>';
	}

	//Calculates the amount due for a registration.  
	//2nd parameter is a bool to determine whether we are looking at the inactive_registration database.
	public static function amountDue($registration_id,$inactive_registration = false)
	{
		//Determines which registration_database we are looking at
		$database = $GLOBALS["srbc_registration"];
		if ($inactive_registration)
			$database = $GLOBALS["srbc_registration_inactive"] ;
		global $wpdb;
		$totalpaid = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
										FROM " . $GLOBALS["srbc_payments"] . " WHERE registration_id=%s AND NOT " . $GLOBALS["srbc_payments"] .
										".fee_type='Store' ",$registration_id));
		$cost = $wpdb->get_var($wpdb->prepare("
								SELECT SUM(" . $GLOBALS["srbc_camps"] . ".cost +
								(CASE WHEN " . $database . ".horse_opt = 1 THEN " . $GLOBALS["srbc_camps"] .".horse_opt_cost
								ELSE 0
								END) +
								(CASE WHEN " . $database . ".busride = 'to' THEN 35
								WHEN " . $database . ".busride = 'from' THEN 35
								WHEN " . $database . ".busride = 'both' THEN 60
								ELSE 0
								END) 
								- IF(" . $database . ".discount IS NULL,0," . $database . ".discount)
								- IF(" . $database . ".scholarship_amt IS NULL,0," . $database . ".scholarship_amt)		
								)								
								FROM " . $database . "
								INNER JOIN " . $GLOBALS["srbc_camps"] . " ON " . $database . ".camp_id=" . $GLOBALS['srbc_camps'] . ".camp_id
								WHERE " . $database . ".registration_id=%d",$registration_id));
		return $cost - $totalpaid;
	}

    //Puts a payment into the database and also updates payment_card payment_cash etc...
    public static function makePayment($registration_id,$payment_type,$payment_amt,$note,$fee_type)
    {
        global $wpdb;
        //Get the current date time
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;
        $is_registration = 0;
        if (strpos($username, 'registration') !== false)
            $is_registration = 1;

        $date = new DateTime("now", new DateTimeZone('America/Anchorage'));
        global $wpdb;
        $wpdb->insert(
                $GLOBALS['srbc_payments'], 
                array( 
                    'payment_id' =>0,
                    'registration_id' => $registration_id,
                    'payment_type' => $payment_type,
                    'payment_amt' => $payment_amt,
                    'payment_date' =>  $date->format("m/d/Y G:i"),
                    'note' => $note ,
                    'fee_type' => $fee_type,
                    'registration_day' => $is_registration,
                    'entered_by' => $current_user->display_name
                ), 
                array( 
                    '%d',
                    '%d', 
                    '%s',
                    '%f',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s'				
                ) 
            );
    }

    //Calculates how much they need to pay and makes the payment
    public static function calculatePaymentAmt($autoPaymentAmt, $needToPayAmount)
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

    public static function autoPayment($registration_id,$autoPaymentAmt,$paymentType,$note)
    {
        global $wpdb;
        $o = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_registration'] . " WHERE registration_id=%d ",$registration_id));
		$totalpaid = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
								FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s AND NOT fee_type='Store'",$registration_id));
		
		//Make the scholarships and discounts add to total paid so we take it out of the base camp fee
		$totalpaid += $o->discount + $o->scholarship_amt;
		if($totalpaid == NULL)
			$totalpaid = 0;
		//Check if they have paid the base camp amount which is (camp cost - horse cost)
		$camp = $wpdb->get_row("SELECT * FROM " . $GLOBALS['srbc_camps'] ." WHERE camp_id=$o->camp_id");
		$baseCampCost = $camp->cost - $camp->horse_cost;
		$needToPayAmount = 0;
		$feeType = NULL;
		//Counts how many times we looped through
		$loops = 0;
		//Calculate bus fee based on type of busride
		$busfee = 0;
		if ($o->busride == "both")
			$busfee = 60;
		else if($o->busride == "to" || $o->busride == "from")
			$busfee = 35;
		
		$horseOpt = 0;
		if ($o->horse_opt == 1)
			$horseOpt = $camp->horse_opt_cost;
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
			list ($autoPaymentAmt,$paid) = self::calculatePaymentAmt($autoPaymentAmt,$needToPayAmount);
			self::makePayment($registration_id,$paymentType,$paid,
				$note,$feeType);
			$totalpaid += $paid;
			$loops++;
			if ($loops > 10)
			{
				error_msg("Error: Autopayment failed!  Infinite loop detected.... Please let Website administrator know. - Peter H.");
				break;
				
			}
		}
    }
}
?>