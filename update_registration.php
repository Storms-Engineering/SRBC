<?php
//Makes registration edits to campers
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);
$arrayKeys = array_keys($obj);
//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");
global $wpdb;

//Pull in our payments class
require_once __DIR__ . '/requires/payments.php';


if (isset($obj["delete_payment_id"]))
{
	if(wp_verify_nonce( $obj["wp_nonce"], 'delete_payment_'.$obj["delete_payment_id"]  ))
	{
		$wpdb->query($wpdb->prepare("DELETE FROM " . $GLOBALS['srbc_payments']. " WHERE payment_id=%d;",$obj["delete_payment_id"]));
		echo "Payment Sucessfully Deleted!";
	}
	else
		wp_nonce_ays("");
}
else if (isset($obj["reactivate_id"]))
{
	//Move inactive registration to active registration database
	$query = $wpdb->query($wpdb->prepare("INSERT INTO " . $GLOBALS['srbc_registration'] . " SELECT * FROM " . $GLOBALS['srbc_registration_inactive']. 
	" WHERE registration_id=%d;",$obj["reactivate_id"]));
	if($query === 0 || $query === false)
	{
		exit("Something went wrong with the moving query");
	}
	else
	{
	$wpdb->query($wpdb->prepare("DELETE FROM " . $GLOBALS['srbc_registration_inactive']. " WHERE registration_id=%d;",$obj["reactivate_id"]));
	echo "Reactivated Registration and Saved Sucessfully";
	}
}
else if (isset($obj["deactivate_id"]))
{
	//Delete the requested registration
	$query = $wpdb->query($wpdb->prepare("INSERT INTO " . $GLOBALS['srbc_registration_inactive']. " SELECT * FROM " .
	$GLOBALS['srbc_registration']. "  WHERE registration_id=%d;",$obj["deactivate_id"]));
	if($query === false || $query === 0 )
	{
		//Thanks so much to :https://dba.stackexchange.com/questions/75532/query-to-compare-the-structure-of-two-tables-in-mysql/75651#75651?newreg=52c95cc1e3144bc287f834a1a25b2923
		//TODO add this to peter hawke health report.
		//Checks if the srbc_registration and srbc_registration_inactive database have the same structure.
		$error_msg = $wpdb->get_var("SELECT IF(COUNT(1)>0,'Differences','No Differences') Comparison FROM
							(
								SELECT
									column_name,ordinal_position,
									data_type,column_type,COUNT(1) rowcount
								FROM information_schema.columns
								WHERE table_schema=DATABASE()
								AND table_name IN ('srbc_registration','srbc_registration_inactive')
								GROUP BY
									column_name,ordinal_position,
									data_type,column_type
								HAVING COUNT(1)=1
							) A;");
		/*Saving this code for the Peter Hawke console update
		$error_verbose = $wpdb->get_results("SELECT column_name,ordinal_position,data_type,column_type FROM
									(
										SELECT
											column_name,ordinal_position,
											data_type,column_type,COUNT(1) rowcount
										FROM information_schema.columns
										WHERE table_schema=DATABASE()
										AND table_name IN ('srbc_registration','srbc_registration_inactive')
										GROUP BY
											column_name,ordinal_position,
											data_type,column_type
										HAVING COUNT(1)=1
									) A;");		*/			
		echo "\rDatabase " . $error_msg;
		//echo "\rDatabase message: " . var_dump($error_verbose);
		exit("\rSomething went wrong with the moving query");
	}
	else
	{
	$wpdb->query($wpdb->prepare("DELETE FROM " . $GLOBALS['srbc_registration']. " WHERE registration_id=%d;",$obj["deactivate_id"]));
	echo "Deactivated Registration and Saved Sucessfully";
	}
}
else if(isset($obj["registration_id"])){
	//Change registration to another camp
	
	//Check if they didn't actually change the camp
	if ($obj["change_to_id"] == "none"){
		echo "Please specify a camp to change to!";
		return;
	}
	
	$wpdb->update( 
		$GLOBALS['srbc_registration'], 
		array( 
			'camp_id' => $obj["change_to_id"], 
		), 
		array( 'registration_id' => $obj["registration_id"] ), 
		array( 
			'%d', 
		), 
		array( '%d' ) 
	);
	echo "Change Successful";
	//Also change over all the payments made for that camp
}
else {
	//TODO: Use an array or something so I don't have to manually enter a new field everytime I add one
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
			'notes' => $obj["camper"]["notes"],
			'tshirt_size' => $obj["camper"]["tshirt_size"]
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
		//Current registration key for database
		$key = $arrayKeys[$i];
		//Update camper first - essential to payments since we check this stuff.  So make sure we write this stuff first
		$wpdb->update( 
			$GLOBALS['srbc_registration'], 
			array( 
				'assistant_counselor' => $obj[$key]["assistant_counselor"],	
				'counselor' => $obj[$key]["counselor"],	
				'lodging' => $obj[$key]["lodging"],	
				'horse_opt' => $obj[$key]["horse_opt"],	
				'busride' => $obj[$key]["busride"],	
				'discount' => $obj[$key]["discount"],
				'discount_type' => $obj[$key]["discount_type"],
				'scholarship_amt' => $obj[$key]["scholarship_amt"],
				'scholarship_type' => $obj[$key]["scholarship_type"],
				'checked_in' => $obj[$key]["checked_in"],
				'health_form' => $obj[$key]["health_form"],
				'waitlist' => $obj[$key]["waitlist"],
				'horse_waitlist' => $obj[$key]["horse_waitlist"],
				'packing_list_sent' => $obj[$key]["packing_list_sent"],
				'registration_notes' => $obj[$key]["registration_notes"]
			), 
			array( 'registration_id' => $key ), 
			array( 
				'%s',	
				'%s',	
				'%s',
				'%d',	
				'%s',	
				'%f',
				'%s',
				'%f',
				'%s',	
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s'
			), 
			array( '%d' ) 
		);
		if ($obj[$key]["snackshop"] != "")
		{
			Payments::makePayment($key,$obj[$key]['snackshop_payment_type'],$obj[$key]["snackshop"],
				"","Store");
		}
		if($obj[$key]["auto_payment_amt"] != "")
		{
			Payments::autoPayment($key,$obj[$key]["auto_payment_amt"],$obj[$key]["auto_payment_type"],
									$obj[$key]["auto_note"]);
		}
		else if ($obj[$key]["payment_type"] != "none"){
			Payments::makePayment($key,$obj[$key]["payment_type"],$obj[$key]["payment_amt"],
				$obj[$key]["note"],$obj[$key]["fee_type"]);
		}
		
	}
	echo "Data Saved Sucessfully";
}



?>