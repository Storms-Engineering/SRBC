<?php
//Makes registration edits to campers
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);
$arrayKeys = array_keys($obj);
//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

if (isset($obj["deleteid"]))
{
	//Delete the requested registration
	$iswaitlist = $wpdb->get_row( "SELECT waitlist FROM srbc_registration WHERE registration_id=" . $obj["deleteid"] )->waitlist;
	$wpdb->delete( 'srbc_registration', array( 'registration_id' => $obj["deleteid"] ) );
	
	//If this deleted registration was a camper not on the waitlist then we want to push a waitlisted person into this camp
	if ($iswaitlist == 0)
	{
		$registrations = $wpdb->get_results($wpdb->prepare("SELECT registration_id,waitlist FROM srbc_registration
						WHERE NOT waitlist=0 AND camp_id=%s ORDER BY registration_id ASC",$obj["camp_id"]));
		//Change the first registration	
		$wpdb->update( 
			'srbc_registration', 
			array( 
				'waitlist' => 0
			), 
			array( 'registration_id' => $registrations[0]->registration_id ), 
			array( 
				'%d'	
			), 
			array( '%d' ) 
			);
	}
	echo "Deleted Registration and Saved Sucessfully";
}
else if(isset($obj["registration_id"])){
	//Change registration to another camp
	
	//Check if they didn't actually change the camp
	if ($obj["change_to_id"] == "none"){
		echo "Please specify a camp to change to!";
		return;
	}
	
	$wpdb->update( 
		'srbc_registration', 
		array( 
			'camp_id' => $obj["change_to_id"], 
		), 
		array( 'registration_id' => $obj["registration_id"] ), 
		array( 
			'%d', 
		), 
		array( '%d' ) 
	);
	//Get all the payment id's that are tied to this camp and change the camp_id's assosiated with it
	$camps = $wpdb->get_results($wpdb->prepare("SELECT payment_id FROM srbc_payments WHERE camp_id=%d AND camper_id=%d",$obj["old_id"],$obj["camper_id"]));
	foreach($camps as $camp){
		$wpdb->update( 
			'srbc_payments', 
			array( 
				'camp_id' => $obj["change_to_id"], 
			), 
			array( 'payment_id' => $camp->payment_id ), 
			array( 
				'%d', 
			), 
			array( '%d' ) 
		);
	}
	echo "Change Successful";
	//Also change over all the payments made for that camp
}
else {
	//Update Camper
	$wpdb->update( 
		'srbc_campers', 
		array( 
			'camper_first_name' => $obj["camper"]["camper_first_name"], 
			'camper_last_name' => $obj["camper"]["camper_last_name"],
			'birthday' => $obj["camper"]["birthday"],
			'age' => $obj["camper"]["age"],
			'gender' => $obj["camper"]["gender"],
			'grade' => $obj["camper"]["grade"],
			'parent_first_name' => $obj["camper"]["parent_first_name"],
			'parent_last_name' => $obj["camper"]["parent_last_name"],
			'email' => $obj["camper"]["email"],
			'phone' => $obj["camper"]["phone"],
			'phone2' => $obj["camper"]["phone2"],
			'address' => $obj["camper"]["address"],
			'city' => $obj["camper"]["city"],
			'state' => $obj["camper"]["state"],
			'zipcode' => $obj["camper"]["zipcode"],
			'notes' => $obj["camper"]["notes"]
		), 
		array( 'camper_id' => $obj["camper"]["id"] ), 
		array( 
			'%s',
			'%s', 
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
			'%s',
			'%s',
			'%s'	
		), 
		array( '%d' ) 
	);
	//Update registrations
	//Make this one less because our last key is camper
	for($i = 0;$i < (count($obj)-1); $i++){
		//Current key for database
		$key = $arrayKeys[$i];
		
		//Update camper first - essential to payments since we check this stuff.  So make sure we write this stuff first
		$wpdb->update( 
			'srbc_registration', 
			array( 
				'counselor' => $obj[$key]["counselor"],	
				'cabin' => $obj[$key]["cabin"],	
				'horse_opt' => $obj[$key]["horse_opt"],	
				'busride' => $obj[$key]["busride"],	
				'discount' => $obj[$key]["discount"],	
				'scholarship_amt' => $obj[$key]["scholarship_amt"],
				'scholarship_type' => $obj[$key]["scholarship_type"],
				'checked_in' => $obj[$key]["checked_in"],
			), 
			array( 'registration_id' => $key ), 
			array( 
				'%s',	
				'%s',
				'%d',	
				'%s',	
				'%f',
				'%f',
				'%s',	
				'%d',
			), 
			array( '%d' ) 
		);
		
		//Add payment info to payment database
		$o = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_registration WHERE registration_id=%d ",$key));

		if($obj[$key]["auto_payment"] != "")
		{

			$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM srbc_payments WHERE camp_id=%s AND camper_id=%s",$o->camp_id,$o->camper_id));
			
			//Make the scholarships and discounts add to total payed so we take it out of the base camp fee
			$totalPayed += $o->discount + $o->scholarship_amt;
			if($totalPayed == NULL)
				$totalPayed = 0;
			//Check if they have payed the base camp amount which is (camp cost - horse cost)
			$camp = $wpdb->get_row("SELECT * FROM srbc_camps WHERE camp_id=$o->camp_id");
			$baseCampCost = $camp->cost - $camp->horse_cost;
			$needToPayAmount = 0;
			$feeType = NULL;
			$i = 0;
			$autoPaymentAmt = $obj[$key]["auto_payment"];
			//Calculate bus fee based on type of busride
			$busfee = 0;
			if ($o->busride == "both")
				$busfee = 60;
			else if($o->busride == "to" || $o->busride == "from")
				$busfee = 35;
			
			$horseOpt = 0;
			if ($o->horse_opt == 1)
				$horseOpt = $camp->horse_opt;
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
					$feeType= "Overpayed";
				}
				//Also updates autoPaymentAmt
				list ($autoPaymentAmt,$payed) = calculatePaymentAmt($autoPaymentAmt,$needToPayAmount);
				makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["auto_payment_type"],$payed,
					$obj[$key]["auto_note"],$feeType);
				$totalPayed += $payed;
				$i++;
				if ($i > 5)
				{
					error_msg("Error: Autopayment failed!  Infinite loop detected.... Please let Website administrator know. - Peter H.");
					break;
					
				}
			}
			
		}
		else if ($obj[$key]["payment_type"] != "none"){
			makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["payment_type"],$obj[$key]["payment_amt"],
				$obj[$key]["note"],$obj[$key]["fee_type"]);
		}
		
	}
	echo "Data Saved Sucessfully";
}
//Calculates how much they need to pay and makes the payment
function calculatePaymentAmt($autoPaymentAmt, $needToPayAmount)
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

//Puts a payment into the database and also updates payment_card payment_cash etc...
function makePayment($registration_id,$camp_id,$camper_id,$payment_type,$payment_amt,$note,$fee_type)
{
	//Get the current date time
			$date = new DateTime("now", new DateTimeZone('America/Anchorage'));
			global $wpdb;
			$wpdb->insert(
					'srbc_payments', 
					array( 
						'payment_id' =>0,
						'registration_id' => $registration_id,
						'camp_id' => $camp_id, 
						'camper_id' => $camper_id,
						'payment_type' => $payment_type,
						'payment_amt' => $payment_amt,
						'payment_date' =>  $date->format("m/j/Y G:i"),
						'note' => $note ,
						'fee_type' => $fee_type
					), 
					array( 
						'%d',
						'%d', 
						'%d',
						'%d',
						'%s',
						'%f',
						'%s',
						'%s',
						'%s'				
					) 
				);
}
?>