<?php
Class Tables
{
	//Creates a table based on the properties given it.
	//Both the properties and tablen headers must be in the correct order
	public static function createTable($campers, $columnHeaders = array(), $properties = array(), $showWaitlist = false)
	{
		$defaultHeaders = array("Last Name", "First Name");
		$defaultProperties = array("camper_last_name", "camper_first_name");
		
		$columnHeaders  = array_merge($defaultHeaders, $columnHeaders);
		$properties  = array_merge($defaultProperties, $properties);
		echo ' <table id="results_table">';
		echo '<tr>';
		foreach($columnHeaders as $ch)
		{
			echo '<th>' . $ch . '</th>';
		}
		//Show a waitlist column if we have it available.
		if(property_exists($campers[0],'waitlist') && $showWaitlist)
			echo '<th>Waitlist</th>';
		echo '</tr>';
		//List campers
		foreach($campers as $camper)
		{
			//Allows user to click on camper and get modal
			echo '<tr onclick="openCamperModal('.$camper->camper_id.')" class="'. $camper->gender .'">';
			foreach($properties as $prop)
			{
				if($prop === "email")
				    echo '<td><a style="color:#1043d5;" href="mailto:' . $camper->email . '">'.$camper->email.'</a></td>';
				else
					echo '<td>' . $camper->{$prop} . '</td>';
			}
			//Show a waitlist column if we have it available.
			if(property_exists($camper,'waitlist') && $showWaitlist)
				echo " " . (($camper->waitlist == 1) ? "<td>waitlisted</td>" : "<td></td>");
			echo '</tr>';
		}
		echo "</table>";
		echo "<br>Campers Count: " . count($campers);
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