<?php
Class Tables
{
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