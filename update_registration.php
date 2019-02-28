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
	$wpdb->delete( 'srbc_registration', array( 'registration_id' => $obj["deleteid"] ) );
	//Check if deleting from waitlist
	$registration = $wpdb->get_results($wpdb->prepare("SELECT registration_id,waitlist FROM srbc_registration
					WHERE NOT waitlist=0 AND camp_id=%s ORDER BY waitlist ASC",$obj["camp_id"]));
	foreach($registration as $registration){
		$wpdb->update( 
		'srbc_registration', 
		array( 
			'waitlist' => ($registration->waitlist - 1)
		), 
		array( 'registration_id' => $registration->registration_id ), 
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
		if ($obj[$key]["payment_type"] != "none"){
			$o = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_registration WHERE registration_id=%d ",$key));
			//Get the current date time
			$date = new DateTime("now", new DateTimeZone('America/Anchorage'));
			$wpdb->insert(
					'srbc_payments', 
					array( 
						'payment_id' =>0,
						'camp_id' => $o->camp_id, 
						'camper_id' => $o->camper_id,
						'payment_type' => $obj[$key]["payment_type"],
						'payment_amt' => $obj[$key]["payment_amt"],
						'payment_date' =>  $date->format("m/j/Y G:i"),
						'note' =>  $obj[$key]["note"],
						'fee_type' => $obj[$key]["fee_type"]
					), 
					array( 
						'%d',
						'%d', 
						'%d',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s'				
					) 
				);
			$paymentType = NULL;
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
		);
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
				'%s',	
				'%s',	
				'%d',
				'%d',
				'%s',	
				'%d',
				'%d',
				'%d',
				'%d',
				'%d'
			), 
			array( '%d' ) 
		);
	}
	echo "Data Saved Sucessfully";
}
?>