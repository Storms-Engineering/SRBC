<div class="modal-header">

<span onclick="closeModal();" class="close">&times;</span>
	<h2>
	<?php
	//This returns the data for one specific camper.
	require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	global $wpdb;
	$camp = $wpdb->get_row( $wpdb->prepare("SELECT * FROM srbc_camps WHERE camp_id = %d",$_GET['camp_id'] ));
	echo $camp->area . " " . $camp->camp_description;
	echo "</h2></div>";
	echo '<button class="save_button" style="float:right;" onclick="saveInfo(' . $camp->camp_id . ');">Save Info & Close</button>'
	?>
			<div class="modal-body">
			<?php
					echo 'Area: <input name="area" type="text" value="' . $camp->area . '">';
					echo 'Description: <input name="camp_description" type="text" value="' . $camp->camp_description . '"><br>';
					echo 'Start Date: <input name="start_date" type="date" value="' . $camp->start_date .'">';
					echo 'End Date: <input type="date" name="end_date" value="' . $camp->end_date . '">';
					echo 'Cost: $<input name="cost" type="text" value="' . $camp->cost . '">';
					echo 'Horse Option: $<input type="text" name="horse_opt" value="' . $camp->horse_opt . '"><br></span>';
					echo 'Waiting List Size: <input name="waiting_list_size" type="text" value="' . $camp->waiting_list_size . '">';
					echo 'Boys Allowed to Register: <input name="boy_registration_size" type="text" value="' . $camp->boy_registration_size . '"><br>';
					echo 'Girls Allowed to Register: <input name="girl_registration_size" type="text" value="' . $camp->girl_registration_size . '">';
					echo 'Overall Size: <input name="overall_size" type="text" value="' . $camp->overall_size . '">';
					echo 'Grade Range: <input name="grade_range" type="text" value="' . $camp->grade_range . '">';
			
			echo "</div>";
			echo '<div class="modal-footer"><button  onclick="saveInfo(' . $camp->camp_id . ');" class="save_button">Save Info & Close</button></div>';
