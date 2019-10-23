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
                             WHERE srbc_campers.camper_id=%s",$camperId));
        echo '<div class="health-form">';
        echo '<input id="IV" value="'. $healthForm->IV . '">';
        echo '<input id="aesKey" value="'. $healthForm->IV . '">';
        
        
        echo '<br>
        Parent/Guardian
        <input class="inputs" type="text" name="parent_first_name" required placeholder="First Name">
        <input class="inputs" type="text" name="parent_last_name" required placeholder="Last Name"><br>
        Email:<input type="email" name="email" required><br>
        Phone including area code (Numbers only please):<input type="tel" required pattern="[0-9]{7,}" title="Please enter a valid phone number" name="phone">
        Secondary Phone: <input type="tel" pattern="[0-9]{7,}" title="Please enter a valid phone number" name="phone2"><br>
        Street Address:<br>
            <textarea class="inputs" required name="address" rows="2" cols="30"></textarea>
            City:<input type="text" style="width:100px;" required name="city">
            State:<input type="text" style="width:50px;" required name="state">
            Zipcode:<input type="text"  style="width:100px;" required pattern="[0-9]{5}" title="Please enter a 5 digit zipcode" name="zipcode" >
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
					Any recent injury or illness?
					<select name="recent_injury_illness">
							<option value="No">No</option>
							<option value="Yes">Yes</option>
					</select>
				<br>
				Frequency ear infections?
				<select name="ear_infections">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Any Skin Problems?
				<select name="skin_problems">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Problems with sleepwalking?
				<select name="sleepwalking">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				A Chronic or Recurring Illness?
				<select name="chronic_recurring_illness">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Wear glasses or contacts?
				<select name="glassses_contacts">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				An orthodontic appliance?
				<select name="orthodontic_appliance">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Mono in last year?
				<select name="mono">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Any current medications?
				<select name="current_medications">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Frequent Headaches?
				<select name="frequent_headaches">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
			</div>
			<div id="rightSide">
				Frequent stomach aches?
				<select name="stomach_aches">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				A head injury?
				<select name="head_injury">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				High blood pressure?
				<select name="high_blood_pressure">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Asthma?
				<select name="asthma">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				<div> Emotional difficulties for which professional help was sought?
				<select name="emotional_difficulties">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select></div>
				<br>
				Seizures?
				<select name="seizures">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Diabetes?
				<select name="diabetes">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				History of bed wetting?
				<select name="bed_wetting">
						<option value="No">No</option>
						<option value="Yes">Yes</option>
				</select>
				<br>
				Immunizations current?
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
	<textarea name="explanations"></textarea>
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
	<img id="signature_img">';
        echo '</div>';
                             
    }
}
?>