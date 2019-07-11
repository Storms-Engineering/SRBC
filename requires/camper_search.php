<?php 
//This class is related to all queries for searching for campers and generating tables.
class CamperSearch
{
	//Search for either a camper name or last name or a combined query of first name last name
	public static function searchParentAndCamper($query)
	{
		global $wpdb;
		$name = explode(" ",$query);
		$campers = null;
		if (count($name) == 2)
		{
			$fname = $name[0];
			$campers = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM srbc_campers WHERE (camper_first_name 
				LIKE %s AND camper_last_name LIKE %s )OR (parent_first_name LIKE %s AND parent_last_name LIKE %s)
				ORDER BY camper_id ASC", 
				$name[0]."%",$name[1]."%",$name[0]."%",$name[1]."%"));
		}
		else
		{
			$name = $name[0];
			$campers = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM srbc_campers WHERE camper_first_name 
				LIKE %s OR camper_last_name LIKE %s OR parent_first_name LIKE %s OR parent_last_name LIKE %s
				ORDER BY camper_id ASC", 
				$name."%",$name."%",$name."%",$name."%"));
		}
		return $campers;
	}
	
	//Creates a table based on the properties given it.
	//Both the properties and tablen headers must be in the correct order
	public static function createTable($columnHeaders, $properties,$campers)
	{
		echo ' <table style="width:100%;" id="results_table">';
		echo '<tr>';
		foreach($columnHeaders as $ch)
		{
			echo '<th>' . $ch . '</th>';
		}
		//Show a waitlist column if we have it available.
		if(property_exists($campers[0],'waitlist'))
			echo '<th>Waitlist</th>';
		echo '</tr>';
		//List campers
		foreach($campers as $camper)
		{
			//Allows user to click on camper and get modal
			echo '<tr onclick="openModal('.$camper->camper_id.')" class="'. $camper->gender .'">';
			foreach($properties as $prop)
			{
				if($prop === "email")
				    echo '<td><a style="color:#1043d5;" href="mailto:' . $camper->email . '">'.$camper->email.'</a></td>';
				else
					echo '<td>' . $camper->{$prop} . '</td>';
			}
			//Show a waitlist column if we have it available.
			if(property_exists($camper,'waitlist'))
				echo " " . (($camper->waitlist == 1) ? "<td>waitlisted</td>" : "<td></td>");
			echo '</tr>';
		}
	}
	
	public static function createCheckboxTable($campers)
	{
		echo ' <table style="width:100%;" id="results_table">
		<tr>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Select</th>
		</tr>';
		foreach($campers as $camper)
		{
			//Allows user to click on camper and get modal
			echo '<tr>';
			echo "<td>";
			echo $camper->camper_first_name . "</td>";
			echo "<td>" . $camper->camper_last_name . "</td>";
			echo '<td><input type="checkbox" name="nameToAdd" value="' . $camper->registration_id . '"></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	
	
}
?>
