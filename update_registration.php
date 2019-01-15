<?php
//Makes registration edits to campers
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);
$arrayKeys = array_keys($obj);
//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

if (array_key_exists("deleteid",$obj))
{
	//Delete the requested registration
	$wpdb->delete( 'srbc_registration', array( 'registration_id' => $obj["deleteid"] ) );
	//Check if deleting from waitlist
	$registration = $wpdb->get_results("SELECT registration_id,waitlist FROM srbc_registration
					WHERE NOT waitlist=0 AND camp_id=".$obj["camp_id"] . " ORDER BY waitlist ASC");
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
	return;
}
for($i = 0;$i < count($obj); $i++){
	//Current key for database
	$key = $arrayKeys[$i];
	//Add payment info to payment database
	if ($obj[$key]["payment_type"] != "none"){
		$o = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_registration WHERE registration_id=%d",$key));
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
					'note' =>  $obj[$key]["note"]
				), 
				array( 
					'%d',
					'%d', 
					'%d',
					'%s',
					'%d',
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
			'checked_in' => $obj[$key]["checked_in"]
		), 
		array( 'registration_id' => $key ), 
		array( 
			'%s',	
			'%s',
			'%d',	
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

?>