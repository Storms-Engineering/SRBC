<?php

require __DIR__ . '/../vendor/autoload.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class Payments
{
	//Creates a credit card transaction with authorize.net
	public static function createCCTransaction($amount, $vars, $desc, $camper_id, $registration_id)
	{
		/* Create a merchantAuthenticationType object with authentication details
		   retrieved from the constants file */
		require_once $_SERVER['DOCUMENT_ROOT'] . '/files/authorizedotnetcreds.php';
		require_once __DIR__ .  '/../requires/payments.php';
		$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
		$merchantAuthentication->setName(MERCHANT_NAME);
		$merchantAuthentication->setTransactionKey(MERCHANT_TRANSACTION_KEY);
	
		// Set the transaction's refId
		$refId = 'ref' . time();
	
		// Create the payment data for a credit card
		$creditCard = new AnetAPI\CreditCardType();
		$creditCard->setCardNumber($vars["cc_number"]);
		$creditCard->setExpirationDate($vars["cc_year"] . "-" . $vars["cc_month"]);
		$creditCard->setCardCode($vars["cc_vcode"]);
	
		// Add the payment data to a paymentType object
		$paymentOne = new AnetAPI\PaymentType();
		$paymentOne->setCreditCard($creditCard);
	
		// Create order information
		$order = new AnetAPI\OrderType();
		//The invoice number will be the same as the registration number
		global $wpdb;
		//Get the latest registration id and add one to as the invoice number
		$invoiceNumber = $registration_id;
		$order->setInvoiceNumber($invoiceNumber);
		//If is a string then it is description
		if(is_string($desc))
			$order->setDescription($desc);
		//It is a camp object otherwise
		else
			$order->setDescription($desc->area . " " . $desc->name);
	
		// Set the customer's Bill To address
		$customerAddress = new AnetAPI\CustomerAddressType();
		$customerAddress->setFirstName($vars["parent_first_name"]);
		$customerAddress->setLastName($vars["parent_last_name"]);
		$customerAddress->setCompany("");
		$customerAddress->setAddress($vars["cc_address"]);
		$customerAddress->setCity($vars["cc_city"]);
		$customerAddress->setState($vars["cc_state"]);
		$customerAddress->setZip($vars["cc_zipcode"]);
		
		//TODO add other countries
		$customerAddress->setCountry("USA");
	
		// Set the customer's identifying information
		$customerData = new AnetAPI\CustomerDataType();
		$customerData->setType("individual");
		$customerData->setId($camper_id);
		$customerData->setEmail($vars["email"]);
	
		// Add values for transaction settings
		$duplicateWindowSetting = new AnetAPI\SettingType();
		$duplicateWindowSetting->setSettingName("duplicateWindow");
		$duplicateWindowSetting->setSettingValue("60");
	
		// Create a TransactionRequestType object and add the previous objects to it
		$transactionRequestType = new AnetAPI\TransactionRequestType();
		$transactionRequestType->setTransactionType("authCaptureTransaction");
		$transactionRequestType->setAmount($amount);
		$transactionRequestType->setOrder($order);
		$transactionRequestType->setPayment($paymentOne);
		$transactionRequestType->setBillTo($customerAddress);
		$transactionRequestType->setCustomer($customerData);
		$transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
	
		// Assemble the complete transaction request
		$request = new AnetAPI\CreateTransactionRequest();
		$request->setMerchantAuthentication($merchantAuthentication);
		$request->setRefId($refId);
		$request->setTransactionRequest($transactionRequestType);
	
		// Create the controller and get the response
		$controller = new AnetController\CreateTransactionController($request);
	
		//If we are on localhost use the sandbox else use production
		if($_SERVER['SERVER_NAME'] === "localhost" || $_SERVER['SERVER_NAME'] === "127.0.0.1")
			$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
		else
			$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
		
	
		if ($response != null) {
			// Check to see if the API request was successfully received and acted upon
			if ($response->getMessages()->getResultCode() == "Ok") {
				// Since the API request was successful, look for a transaction response
				// and parse it to display the results of authorizing the card
				$tresponse = $response->getTransactionResponse();
			
				if ($tresponse != null && $tresponse->getMessages() != null) 
					return true;
				else 
				{
					echo "Transaction Failed \n";
					if ($tresponse->getErrors() != null) {
						echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
						echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
					}
				}
				// Or, print errors if the API request wasn't successful
			} else {
				echo "Transaction Failed \n";
				$tresponse = $response->getTransactionResponse();
			
				if ($tresponse != null && $tresponse->getErrors() != null) {
					echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
					echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
				} else {
					echo " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
					echo " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
				}
			}
		} else {
			echo  "No response returned \n";
		}
		return false;
	}

	//Echos HTML code for credit card info
	//$sameAsAbove is for whether the checkbox for same info as above will be shown
	public static function setupCreditCardHTML($sameAsAbove = false)
	{
		echo '<h3>Use a credit card:</h3>	
			Name on Credit Card: <input type="text" name="parent_first_name" placeholder="First Name"> <input type="text" name="parent_last_name" placeholder="Last Name"> <br>
			Email: <input type="text" name="email">
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

	//Note this function uses the old database whenever it is selected!
	//Might have unintended consequences especially during registration?
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