<?php
Class Reports
{
	private static function printHeader($camp = NULL)
	{
		//Get calling method, we will use this to print a Header
		$header = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
		//Format - remove _ and uppercase
		$header = ucwords(str_replace("_"," ",$header));
		
		$campInfo = NULL;
		//Get camps we are pulling info from
		if ($camp !== NULL)
		{
			//We were passed an array of camps
			/*if(is_object($camp[0]))
			{
				foreach($camp as $c)
				{
					$campInfo .= $c->area . " " . $c->name;
				}
			}
			//Just one camp
			else
			{*/
			//TODO Make this spit out multiple camps if that is what the user is calling.
			$campInfo = $camp[0]->area . " " . $camp[0]->name;
			//}
		}
		if ($camp === NULL)
			echo "<h1>" . $header . "</h1>";
		else
			echo "<h1>" . $header . "</h1>" . " - " . $campInfo;
	}
	//Displays all inactive registrations
	public static function inactive_registrations()
	{
		self::printHeader();
		global $wpdb;
		$campers = $wpdb->get_results("SELECT *	FROM " . $GLOBALS['srbc_registration_inactive'] . 
									" INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration_inactive'] .
									".camper_id=srbc_campers.camper_id");
		echo "<table><tr><th>First Name</th><th>Last Name</th><th>Camp</th><th>Amount Due</th></tr>";
		//Start new row and put in name since that always happens - most of the time
		foreach($campers as $camper)
		{	
		$amountDue = amountDue($camper->registration_id,true);
		$camp = $wpdb->get_results("SELECT * FROM " . $GLOBALS['srbc_camps'] . 
										" WHERE camp_id=$camper->camp_id")[0];
		echo '<tr class="'.$camper->gender.'" onclick="openModal('.$camper->camper_id.');"><td>' . $camper->camper_last_name ."</td><td> " . $camper->camper_first_name. "</td>".
		"<td>" . $camp->area . " " . $camp->name . "</td><td>$" . $amountDue . "</td>";
		}
		echo "</table>";
	}
	
	//Pops up a mailing list that the user is asked to download in a .csv file
	public static function mailing_list($start_date)
	{
		global $wpdb;
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
			FROM ((" . $GLOBALS['srbc_registration'] . " 
			INNER JOIN " . $GLOBALS["srbc_camps"] . " ON " . $GLOBALS['srbc_registration'] . ".camp_id=" . $GLOBALS["srbc_camps"] . ".camp_id)
			INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id) WHERE 
			" . $GLOBALS["srbc_camps"] . ".start_date='%s'",$start_date));
		$csvArray = array();
		$csvArray[] = array("First_name","Last_name","Address","City","State","Zipcode","Cabin", "Camp");
		foreach($campers as $camper)
		{
			//Remove any line breaks from an address
			$csvArray[] = array($camper->camper_first_name,$camper->camper_last_name,preg_replace( "/\r|\n/", "", $camper->address)
			,$camper->city,$camper->state,$camper->zipcode,$camper->cabin,$camper->area . " " . $camper->name);
		}

		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="content.csv"');
		//I think this is some kind of temp stream
		$file = fopen('php://output','w');

		foreach ($csvArray as $fields) {
			fputcsv($file, $fields);
		}
		fclose($file);
	}
	
	public static function camp_numbers()
	{
		self::printHeader();
		global $wpdb;
		$camps = $wpdb->get_results("SELECT * FROM " . $GLOBALS["srbc_camps"]);
		$totalRegistrations = 0;
		foreach ($camps as $camp)
		{
			$male_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
											WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id)); 
			$female_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
											FROM " . $GLOBALS['srbc_registration'] . "
											LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
											WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id)); 
			echo "<h3>" . $camp->area . " " . $camp->name . "</h3>			" . $camp->start_date . "<br>";
			echo "		Male: " . $male_registered . "<br>";
			echo "		Female: " . $female_registered . "<br>";
			echo "		Total: " . ($male_registered + $female_registered) . "<br>"; 
			$totalRegistrations += $male_registered + $female_registered;
		}
		echo "<br><br>Overall Camp Total: " . $totalRegistrations;
	}
	
	public static function signout_sheets($start_date,$end_date,$camp)
	{
		global $wpdb;
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
									FROM ((" . $GLOBALS['srbc_registration'] . "
								 INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
								 ".camp_id)
								 INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
								 WHERE " . $GLOBALS["srbc_camps"] . ".start_date BETWEEN '%s' AND '%s' OR " . $GLOBALS['srbc_camps'] . ".camp_id=%d
								 ORDER BY srbc_registration.cabin DESC",$start_date,$end_date,$camp));
		self::printHeader($campers);
		//This variable keeps track of if we have changed cabin group
		//Initialized to 0 so we don't compare to null and get true
		$oldCabin = 0;
		//echo '<table id="report_table">';
		foreach ($campers as $camper)
		{
			//Start a new table
			if ($camper->cabin != $oldCabin || $oldCabin === 0)
			{
				//Don't do this for the first table, but do it for every new table
				if($oldCabin != NULL)
				{
					echo '</table>';	
				}
					if($camper->cabin === "" || $camper->cabin === NULL)
						echo "<h3>No Cabin Assigned</h3>";
					else
						echo "<h3>$camper->cabin</h3>";
					echo '<table style="page-break-after: always;" id="report_table">';
					echo '<tr><th>Camper</th><th>Parent/Guardian</th><th style="width:200px;">Signature</th></tr>';			
				}			
			echo "<tr><td>". $camper->camper_first_name . " " . $camper->camper_last_name . "</td>";
			echo "<td>". $camper->parent_first_name . " " . $camper->parent_last_name . "</td>";
			
			//Show bus in the signature column if the camper is riding the bus when leaving camp
			if ($camper->busride == "from" || $camper->busride == "both")
				echo "<td><b>ON THE BUS</b></td></tr>";
			else
				echo "<td></td></tr>";
			$oldCabin = $camper->cabin;
		}
		//Close out the table
		echo "</table>";
	}
	
	public static function program_camper_sheets($camp)
	{
		global $wpdb;
		$campers = $wpdb->get_results($wpdb->prepare("SELECT *
										FROM ((" . $GLOBALS['srbc_registration'] . "
									 INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS["srbc_registration"] . ".camp_id=" . $GLOBALS["srbc_camps"] . 
									 ".camp_id)
									 INNER JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
									 WHERE " . $GLOBALS["srbc_camps"] . ".camp_id=%d
									 ORDER BY srbc_registration.cabin DESC",$camp));
		self::printHeader($campers);
		//This variable keeps track of if we have changed cabin group
		//Initialized to 0 so we don't compare to null and get true
		$oldCabin = 0;
		//echo '<table id="report_table">';
		foreach ($campers as $camper)
		{
			//Start a new table
			if ($camper->cabin != $oldCabin || $oldCabin === 0)
			{
				//Don't do this for the first table, but do it for every new table
				if($oldCabin != NULL)
				{
					echo "</table><br><br><br>";	
				}
				if($camper->cabin === "" || $camper->cabin === NULL)
					echo "<h3>No Cabin Assigned</h3>";
				else
				{
					echo '<h3 style="display:inline">' . $camper->cabin . '</h3><br><b>Counselor: ' . $camper->counselor . '</b>';
					echo '&nbsp|&nbsp<b>Assistant Counselor: ' . $camper->assistant_counselor . '</b>';
				}
				echo '<table id="report_table">';
				echo '<tr><th>Camper</th><th>Phone #</th><th style="width:300px;">Notes:</th></tr>';			
			}			
			echo "<tr><td>". $camper->camper_first_name . " " . $camper->camper_last_name . "</td>";
			echo "<td>". $camper->phone . "</td>";
			echo "<td>". $camper->registration_notes . "</td>";
			echo "</tr>";
			$oldCabin = $camper->cabin;
		}
		//Close out the table
		echo "</table>";
	}
}

?>