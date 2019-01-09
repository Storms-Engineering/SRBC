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
			'payed_check' => $obj[$key]["payed_check"],
			'payed_cash' => $obj[$key]["payed_cash"],
			'payed_card' => $obj[$key]["payed_card"],
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