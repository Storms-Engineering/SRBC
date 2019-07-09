<?php
//Makes registration edits to campers
header("Content-Type: application/json; charset=UTF-8");
$obj = json_decode( stripslashes($_POST["x"]), true);
$arrayKeys = array_keys($obj);
//Database shtuff
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
if (!is_user_logged_in()) exit("Thus I refute thee.... P.H.");
global $wpdb;


if (isset($obj["delete_payment_id"]))
{
	$wpdb->query($wpdb->prepare("DELETE FROM " . $GLOBALS['srbc_payments']. " WHERE payment_id=%d;",$obj["delete_payment_id"]));
	echo "Payment Sucessfully Deleted!";
}
else if (isset($obj["reactivate_id"]))
{
	//Delete the requested registration
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
		//Current registration key for database
		$key = $arrayKeys[$i];
		//Update camper first - essential to payments since we check this stuff.  So make sure we write this stuff first
		$wpdb->update( 
			$GLOBALS['srbc_registration'], 
			array( 
				'assistant_counselor' => $obj[$key]["assistant_counselor"],	
				'counselor' => $obj[$key]["counselor"],	
				'cabin' => $obj[$key]["cabin"],	
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
			makePayment($key,$obj[$key]['snackshop_payment_type'],$obj[$key]["snackshop"],
				"","Store");
		}
		if($obj[$key]["auto_payment_amt"] != "")
		{
			$o = $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_registration'] . " WHERE registration_id=%d ",$key));
			$totalPayed = $wpdb->get_var($wpdb->prepare("SELECT SUM(payment_amt) 
									FROM " . $GLOBALS['srbc_payments'] . " WHERE registration_id=%s AND NOT fee_type='Store'",$key));
			
			//Make the scholarships and discounts add to total payed so we take it out of the base camp fee
			$totalPayed += $o->discount + $o->scholarship_amt;
			if($totalPayed == NULL)
				$totalPayed = 0;
			//Check if they have payed the base camp amount which is (camp cost - horse cost)
			$camp = $wpdb->get_row("SELECT * FROM " . $GLOBALS['srbc_camps'] ." WHERE camp_id=$o->camp_id");
			$baseCampCost = $camp->cost - $camp->horse_cost;
			$needToPayAmount = 0;
			$feeType = NULL;
			//Counts how many times we looped through
			$loops = 0;
			$autoPaymentAmt = $obj[$key]["auto_payment_amt"];
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
				makePayment($key,$obj[$key]["auto_payment_type"],$payed,
					$obj[$key]["auto_note"],$feeType);
				$totalPayed += $payed;
				$loops++;
				if ($loops > 5)
				{
					error_msg("Error: Autopayment failed!  Infinite loop detected.... Please let Website administrator know. - Peter H.");
					break;
					
				}
			}
			
		}
		else if ($obj[$key]["payment_type"] != "none"){
			makePayment($key,$obj[$key]["payment_type"],$obj[$key]["payment_amt"],
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
function makePayment($registration_id,$payment_type,$payment_amt,$note,$fee_type)
{
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
?>