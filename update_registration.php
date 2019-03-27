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
		//Add payment info to payment database
		$o = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_registration WHERE registration_id=%d ",$key));

		if($obj[$key]["auto_payment"] != "")
		{
			
			//TODO: Ugh getting ugly might need to put this into a function or something
			$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
												FROM srbc_payments WHERE camp_id=%s AND camper_id=%s",$o->camp_id,$o->camper_id));
			echo "<br>Total Payed:" . $totalPayed;
			//Check if they have payed the base camp amount which is (camp cost - horse cost)
			$camp = $wpdb->get_row("SELECT * FROM srbc_camps WHERE camp_id=$o->camp_id");
			$baseCampCost = $camp->cost - $camp->horse_cost;
			echo "<br>BaseCampCost:" . $baseCampCost;				
			echo "<br> TotalPayed: $totalPayed";
			if ($totalPayed < $baseCampCost)
			{
				//We still need to pay some on the base camp cost
				$needToPayAmount = $baseCampCost - $totalPayed;
				$paymentAmt = 0;
				if ($obj[$key]["auto_payment"] < $needToPayAmount)
					$paymentAmt = $needToPayAmount - $obj[$key]["auto_payment"];
				else if($obj[$key]["auto_payment"] > $needToPayAmount)
					$paymentAmt = $needToPayAmount;
				else
					//They are the same amount
					$paymentAmt = $obj[$key]["auto_payment"];
				$area = $camp->area;
				//For fees Sports should go to lakeside
				if ($area == "Sports")
					$area = "Lakeside";
				makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["payment_type"],$paymentAmt,
				$obj[$key]["note"],$area);
			}
			
			//Check horse_cost (aka WT Horsemanship Fee
			if(($totalPayed - $baseCampCost) < $camp->horse_cost) 
			{
				//We still need to pay some on the base camp cost
				$needToPayAmount = ($totalPayed - $baseCampCost) - $camp->horse_cost;
				$paymentAmt = 0;
				if ($obj[$key]["auto_payment"] < $needToPayAmount)
					$paymentAmt = $needToPayAmount - $obj[$key]["auto_payment"];
				else if($obj[$key]["auto_payment"] > $needToPayAmount)
					$paymentAmt = $needToPayAmount;
				else
					//They are the same amount
					$paymentAmt = $obj[$key]["auto_payment"];
				makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["payment_type"],$paymentAmt,$obj[$key]["note"],"WT Horsemanship");
			}
			
			//Horse option check aka LS Horsemanship
			if(($totalPayed - $camp->cost) < $camp->horse_opt) 
			{
				//We still need to pay some on the horse option
				$needToPayAmount = ($totalPayed - $camp->cost) - $camp->horse_opt;
				$paymentAmt = 0;
				if ($obj[$key]["auto_payment"] < $needToPayAmount)
					$paymentAmt = $needToPayAmount - $obj[$key]["auto_payment"];
				else if($obj[$key]["auto_payment"] > $needToPayAmount)
					$paymentAmt = $needToPayAmount;
				else
					//They are the same amount
					$paymentAmt = $obj[$key]["auto_payment"];
				makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["payment_type"],$paymentAmt,
					$obj[$key]["note"],"LS Horsemanship");
			}
			
			
			$busfee = 0;
			if ($o->busride == "both")
				$busfee = 60;
			else if($o->busride == "to" || $o->busride == "from")
				$busfee = 35;
			//@todo This is still bad so much code duplication
			//Bus fee
			if(($totalPayed - ($camp->cost + $camp->horse_opt)) <$busfee) 
			{
				//We still need to pay some on the horse option
				$needToPayAmount = ($totalPayed - ($camp->cost + $camp->horse_opt)) - $busfee;
				$paymentAmt = 0;
				if ($obj[$key]["auto_payment"] < $needToPayAmount)
					$paymentAmt = $needToPayAmount - $obj[$key]["auto_payment"];
				else if($obj[$key]["auto_payment"] > $needToPayAmount)
					$paymentAmt = $needToPayAmount;
				else
					//They are the same amount
					$paymentAmt = $obj[$key]["auto_payment"];
				makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["payment_type"],$paymentAmt,
					$obj[$key]["note"],"LS Horsemanship");
			}
			
		}
		else if ($obj[$key]["payment_type"] != "none"){
			makePayment($key,$o->camp_id,$o->camper_id,$obj[$key]["payment_type"],$obj[$key]["payment_amt"],
				$obj[$key]["note"],$obj[$key]["fee_type"]);
		}
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
				'amount_due' => $obj[$key]["amount_due"],
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
				'%f',
				'%d',
			), 
			array( '%d' ) 
		);
	}
	echo "Data Saved Sucessfully";
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
			//TODO: Should be getting rid of this code because we will simply be grabbing all of this from the payment database
			//using SUM and searching by registration_id
			/*$paymentType = NULL;
			$add = 0;
			if ($obj[$key]["payment_type"] == "card") {
				$paymentType = "payed_card";
				$add = $o->payed_card;
			}
			else if($obj[$key]["payment_type"] == "check"){
				$paymentType = "payed_check";
				$add = $o->payed_check;
			}
			else if($obj[$key]["payment_type"] == "cash"){
				$paymentType = "payed_cash";
				$add = $o->payed_cash;
			}
			else 
			{
				error_msg("Please notify admin that there was a problem with the payment type");
				return;
			}
			$wpdb->update( 
			'srbc_registration', 
			array( 
				$paymentType => ($obj[$key]["payment_amt"] + $add),	
			), 
			array( 'registration_id' => $key ), 
			array( 
				'%s',	
			), 
			array( '%d' ) 
		);*/
}
?>