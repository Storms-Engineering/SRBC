<?php
require_once 'Encryption.php';
class HealthForm
{
    public static function generateHealthForm($camperId)
    {
		//Make this loop?  So only 1 sql query?
        global $wpdb;
        $healthForm = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
                             FROM srbc_health_form 
                             INNER JOIN srbc_campers ON srbc_health_form.camper_id=srbc_campers.camper_id
							 WHERE srbc_campers.camper_id=%s",$camperId));
		if($healthForm == NULL)
		{
			echo '<h1 id="no-print">No camper health information available</h1>';
			return;
		}

		$healthForm = $healthForm[0];
        echo '<div class="health_form">';
        echo '<input type="hidden" name="IV" value="'. $healthForm->IV . '">';
        echo '<input type="hidden" name="aesKey" value="'. $healthForm->aesKey . '">';
        echo '<input type="hidden" name="data" value="'. $healthForm->data . '">';
        //echo '<button onclick="decryptHealthForms();">Decrypt</button>';
		echo '<h3>Health History for: ' . $healthForm->camper_first_name .' ' . $healthForm->camper_last_name . '</h3>
		DOB:<span class="value">' . $healthForm->birthday . '</span>
		Age:<span class="value">' . $healthForm->age . '</span>
		Gender:<span class="value">' . $healthForm->gender . '</span>
        Parent/Guardian
        <span class="value">' . $healthForm->parent_first_name . ' 
        ' . $healthForm->parent_last_name . '</span><br>
		Email:<span class="value">' . $healthForm->email . '</span>
        Phone:<span class="value">' . $healthForm->phone . '</span>
        Secondary Phone:<span class="value">' . $healthForm->phone2 . '</span><br>
        Address:<span class="value">
            ' . $healthForm->address . ' ' . $healthForm->city . ' ' . $healthForm->state . ' ' . $healthForm->zipcode . '</span>
            <br>
            Emergency Contact:<span class="value" name="emergency_contact"></span><br>
		Home Phone:<span class="value" name="emergency_phone_home"></span>
		Cell Phone:<span class="value" name="emergency_phone_cell"></span>
		<h3>General Health Questions</h3>
		<div id="healthQuestions" style="display:block;">
			<div id="leftSide">
					<label for="recent_injury_illness">Any recent injury or illness?</label>
					<span class="value" name="recent_injury_illness">
					</span>
				<label for="ear_infections">Frequency ear infections?</label>
				<span class="value" name="ear_infections">
				</span>
				<label for="skin_problems">Any Skin Problems?</label>
				<span class="value" name="skin_problems">
				</span>
				<label for="sleepwalking">Problems with sleepwalking?</label>
				<span class="value" name="sleepwalking">
				</span>
				<label for="chronic_recurring_illness">A Chronic or Recurring Illness?</label>
				<span class="value" name="chronic_recurring_illness">
				</span>
				<label for="glasses_contacts">Wear glasses or contacts?</label>
				<span class="value" name="glassses_contacts">
				</span>
				<label for="otrthodontic_appliance">An orthodontic appliance?</label>
				<span class="value" name="orthodontic_appliance">
				</span>
				<label for="mono">Mono in last year?</label>
				<span class="value" name="mono">
				</span>
				<br>
				<label for="current_medications">Any current medications?</label>
				<span class="value" name="current_medications">
				</span>
				<label for="frequent_headaches">Frequent Headaches?</label>
				<span class="value" name="frequent_headaches">
				</span>
			</div>
			<div id="rightSide">
				<label for="stomach_aches">Frequent stomach aches?</label>
				<span class="value" name="stomach_aches">	</span>
				<label for="head_injury">A head injury?</label>
				<span class="value" name="head_injury">
				</span>
				<label for="high_blood_pressure">High blood pressure?</label>
				<span class="value" name="high_blood_pressure">
				</span>
				<label for="asthma">Asthma?</label>
					<span class="value" name="asthma"></span>
				<label for="emotional_difficulties">Emotional difficulties for which <br>professional help was sought?</label>
					<span class="value" name="emotional_difficulties"></span>
				<label for="seizures">Seizures?</label>
					<span class="value" name="seizures"></span>
				<label for="diabetes">Diabetes?</label>
					<span class="value" name="diabetes"></span>
				<label for="bed_wetting">History of bed wetting?</label>
					<span class="value" name="bed_wetting"></span>
				<label for="immunizations">Immunizations out of date?</label>
					<span class="value" name="immunizations"></span>
			</div>
		</div>
		<hr style="clear:both">
	<h3>Please explain any "Yes" answers from above</h3>
	<div style="word-wrap:break-word;"  class="value" name="explanations"></div>
	Carrier:<span name="carrier" class="value"></span>
	Policy Number:<span name="policy_number" class="value"></span>
	<br>
	Family Physician:<span name="physician" class="value"></span>
	Phone Number:<span name="physician_number" class="value"></span>
	<br>
	Family Dentist:<span name="family_dentist" class="value"></span>
	Phone Number:<span name="dentist_number" class="value"></span>
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
	
	//HTML setup before healthforms
	public static function before()
	{
		echo '<html>
		<head>
		<link rel="stylesheet" type="text/css" href="/wp-content/plugins/SRBC/css/health_form.css">
		</head>
		<body>
		<div id="no-print">
		<input type="password" id="pwd"> Decryption Progress <progress value="0" id="progress"></progress>
		<div ondrop="drop(event)" ondragover="allowDrop(event)" style="background:lightblue;height:50px;width:400px;float:right;">
		Drop key file here</div>
		</div>';
	}

	public static function after()
	{
		echo '<script src="/wp-content/plugins/SRBC/requires/js/forge.min.js"></script>
			<script src="/wp-content/plugins/SRBC/JSEncrypt/jsencrypt.min.js"></script>
			<script src="/wp-content/plugins/SRBC/Jsrsasign/jsrsasign-all-min.js"></script>
			<script src="/wp-content/plugins/SRBC/js/health_form.js"></script>';
		echo "</html></body>";
	}

	public static function generateSubmitForm()
	{
		?>
		<h1>Health Form</h1>
		Emergency Contact: <input type="text" name="emergency_contact" required>
		<br>
		Phone 1<input type="text" name="emergency_phone_home" required>
		Phone 2<input type="text" name="emergency_phone_cell" >
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
				Immunizations out of date?
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
		<li>The following over the counter medications are kept in the Health Center & are provided to campers under the standing orders provided by a local physician.
			These would include: Acetaminophen, Ibuprofen, Robitussin DM/CF, Sudafed, Tums, Mylanta, Benadryl, and Claritin
		</li>
		<li>
			<b>All prescription medications and vitamins must be in the original container with the correct name,
			 date physicians name, and instructions on the bottle.</b>  
			 The camp will not administer any prescribed medications that are improperly labeled.
		</li>
		<li>
			Phone calls will be made to parents and/or physicians concering any medications about which there are any questions.
		</li>
		<li>
				SRBC food service does not provide specialized diets.  
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
	<canvas id="canvas" style="border:1px solid black" height="200" width="300"></canvas>
	<input type="hidden" name="signature_img">
	<br>
	<button type="button" onclick="signaturePad.clear()">Clear</button>
	<button type="button" onclick="undo()">Undo</button>
	<br>
	<script src="../wp-content/plugins/SRBC/requires/js/signature_pad/signature_pad.min.js"></script>

		<?php
	}
}
?>