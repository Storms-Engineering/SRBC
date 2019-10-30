<?php
require_once 'Encryption.php';
class HealthForm
{
    public static function generateHealthForm($camperId)
    {
        global $wpdb;
        $healthForm = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
                             FROM srbc_health_form 
                             INNER JOIN srbc_campers ON srbc_health_form.camper_id=srbc_campers.camper_id
							 WHERE srbc_campers.camper_id=%s",$camperId))[0];
        echo '<div class="health_form">';
        echo '<input type="hidden" name="IV" value="'. $healthForm->IV . '">';
        echo '<input type="hidden" name="aesKey" value="'. $healthForm->aesKey . '">';
        echo '<input type="hidden" name="data" value="'. $healthForm->data . '">';
        echo '<button onclick="decryptHealthForms();">Decrypt</button>';
        
		echo '<br>
		Camper name <input type="text" name="camper_first_name" value="' . $healthForm->camper_first_name .'"> <input type="text" name="camper_last_name" value="' . $healthForm->camper_last_name . '">
		DOB <input class="small_input" type="text" name="birthday" value="' . $healthForm->birthday . '">
		Age <input class="small_input" type="text" name="age" value="' . $healthForm->age . '">
		Gender <input class="small_input" type="text" name="gender" value="' . $healthForm->gender . '">
		<br>
        Parent/Guardian
        <input class="inputs" type="text" name="parent_first_name" value="' . $healthForm->parent_first_name . '">
        <input class="inputs" type="text" name="parent_last_name" value="' . $healthForm->parent_last_name . '">
		Email:<input type="email" name="email" value="' . $healthForm->email . '">
		<br>
        Phone :<input type="tel" name="phone" value="' . $healthForm->phone . '">
        Secondary Phone: <input type="tel" name="phone2" value="' . $healthForm->phone2 . '"><br>
        Street Address:<br>
            <textarea class="inputs" required name="address" rows="2" cols="30">' . $healthForm->address . '</textarea>
            City:<input type="text" style="width:100px;" name="city" value="' . $healthForm->city . '">
            State:<input type="text" style="width:50px;" name="state" value="' . $healthForm->state . '">
            Zipcode:<input type="text"  style="width:100px;" name="zipcode" value="' . $healthForm->zipcode . '">
            <br>
            Emergency Contact: <input type="text" name="emergency_contact" required>
		<br>
		Home Phone <input type="text" name="emergency_phone_home" required>
		Cell Phone <input type="text" name="emergency_phone_cell" required>
		<br>
		<br>
		<h3>General Health Questions</h3>
		<div id="healthQuestions" style="display:block;">
			<div id="leftSide">
					<label for="recent_injury_illness">Any recent injury or illness?</label>
					<select name="recent_injury_illness">
							<option value="No">No</option>
							<option value="Yes">Yes</option>
					</select>

				<br>
				<label for="ear_infections">Frequency ear infections?</label>

				<select name="ear_infections">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="skin_problems">Any Skin Problems?</label>
				<select name="skin_problems">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="sleepwalking">Problems with sleepwalking?</label>
				<select name="sleepwalking">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="chronic_recurring_illness">A Chronic or Recurring Illness?</label>
				<select name="chronic_recurring_illness">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="glasses_contacts">Wear glasses or contacts?</label>
				<select name="glassses_contacts">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="otrthodontic_appliance">An orthodontic appliance?</label>
				<select name="orthodontic_appliance">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="mono">Mono in last year?</label>
				<select name="mono">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="current_medications">Any current medications?</label>
				<select name="current_medications">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="frequent_headaches">Frequent Headaches?</label>
				<select name="frequent_headaches">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
			</div>
			<div id="rightSide">
				<label for="stomach_aches">Frequent stomach aches?</label>
				<select name="stomach_aches">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="head_injury">A head injury?</label>
				<select name="head_injury">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="high_blood_pressure">High blood pressure?</label>
				<select name="high_blood_pressure">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="asthma">Asthma?</label>
				<select name="asthma">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				 <label for="emotional_difficulties">Emotional difficulties for which professional help was sought?</label>
				<select name="emotional_difficulties">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="seizures">Seizures?</label>
				<select name="seizures">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="diabetes">Diabetes?</label>
				<select name="diabetes">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="bed_wetting">History of bed wetting?</label>
				<select name="bed_wetting">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<label for="immunizations">Immunizations current?</label>
				<select name="immunizations">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<br>
			</div>
		</div>
		<hr style="clear:both">
	<h3>Please explain any "Yes" answers from above</h3>
	<div style="word-wrap:break-word;" name="explanations"></div>
	<br>
	<br>
	Carrier <input type="text" name="carrier">
	Policy Number<input type="text" name="policy_number">
	<br>
	Family Physician <input type="text" name="physician">
	Phone Number <input type="text" name="physician_number">
	<br>
	Family Dentist <input type="text" name="family_dentist">
	Phone Number <input type="text" name="dentist_number">
	<hr>
	<h3>Essential Medical Information</h3>
	<ul>
		<li>For the safety of everyone in camp and to comply with regulations, all medication will be stored in the Health Center</li>
		<li>The following OTCM are kept in the Health Center & are provided to campers under the standing orders provided by a local physician.
			These would include: Acetaminophen, Ibuprofen, Robitussin DM/CF, Sudafed, Tums, Mylanta, Benadryl, and Claritin
		</li>
		<li>
			<b>All prescriptoion medications and vitamins must be in the original container with the correct name,
			 date physicians name, and instructions on the bottle.</b>  
			 The camp will not administer any prescribed medications that are improperly labeled.
		</li>
		<li>
			Phone calls will be made to parents and/or physicians concering any medications about which there are any questions.
		</li>
		<li>
				SRBC food service is not providing specialized diets.  
				Campers with dietary restrictions or needs are welcome to bring their own food.
		</li>
	</ul>
	<h3>Permission to provide necessary treatment or emergency care</h3>
	<p>
		I, hereby give permission to the medical personnel selected by SRBC to give OTCM per our standing order physician and administer
		treatment to my child, and if the need arises, provide transportation to a medical provider or call 911 for EMS response.
		I give permission for medical care by a health provider or emergency care including hospitilization, x-rays, routine tests
		treatment, and release of records necessary for insurance purposes.  I also give permission to share health information on an
		 as needed to camp staff.  This completed form may be photocopied for trips out of camp.  Parents will be notified in the case of 
		 an emergency or the need for outside medical care arises.
	</p>
	<h3>Signature:</h3>
	<img name="signature_img" src="">';
    echo '</div>';
                             
    }
}
?>