<?php
require_once 'Encryption.php';
class HealthForm
{
    public static function generateHealthForm($camperId,$campers)
    {
		//Make this loop?  So only 1 sql query?
        global $wpdb;
        $healthForm = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
                             FROM srbc_health_form 
                             INNER JOIN " . $GLOBALS['srbc_campers'] . " ON srbc_health_form.camper_id=" . $GLOBALS['srbc_campers'] . ".camper_id
							 WHERE " . $GLOBALS['srbc_campers'] . ".camper_id=%s
							 ORDER BY " . $GLOBALS['srbc_campers'] . ".camper_last_name DESC ",$camperId));
		if($healthForm == NULL)
		{
			echo '<h1 id="no-print">No camper health information available</h1>';
			return;
		}

		$healthForm = $healthForm[0];
        echo '<div class="health_form">
        <input type="hidden" name="IV" value="'. $healthForm->IV . '">
        <input type="hidden" name="aesKey" value="'. $healthForm->aesKey . '">
        <input type="hidden" name="data" value="'. $healthForm->data . '">';
        //echo '<button onclick="decryptHealthForms();">Decrypt</button>';
		

		if($campers != null)
		{
			echo '<h3>Health History for: ' . $healthForm->camper_last_name . ', ' . $healthForm->camper_first_name;
			echo ' for ' . $campers[0]->area . " " . $campers[0]->name . '</h3>';
		}
		else 
			echo '<h3>Health History for: ' . $healthForm->camper_last_name . ', ' . $healthForm->camper_first_name . "</h3>";
			
			
		echo 'DOB:<span class="value">' . $healthForm->birthday . '</span>
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
				<label for="ear_infections">Frequent ear infections?</label>
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
				<label for="allergies">Allergies?</label>
				<span class="value" name="allergies">
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
		I, hereby give permission to the medical personnel selected by Solid Rock Bible Camp to give OTCM per our standing order physician and administer
		treatment to my child, and if the need arises, provide transportation to a medical provider or call 911 for EMS response.
		I give permission for medical care by a health provider or emergency care including hospitilization, x-rays, routine tests
		treatment, and release of records necessary for insurance purposes.  I also give permission to share health information on an
		 as need to know basis to camp staff.  This completed form may be photocopied for trips out of camp.  Parents will be notified in the case of 
		 an emergency or the need for outside medical care arises.
	</p>
	<h3>Signature:</h3>
	<img name="signature_img" src=""><span name="dateTime" ></span>
    </div>';
                             
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
		return '
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
				Allergies?
				<select name="allergies">
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
		<li>All prescription medications and over-the-counter (OTC) FDA approved medications must be in the original containers with the camper’s name and how it is to be given.
		 Please do not send homeopathic meds (such as melatonin), as Alaska law does not permit us to give them.
		  Vitamins will only be given with a doctor’s order. Inhalers and EpiPens need a prescription label.
		   A few OTC meds are kept in the nurse’s office and provided for campers as needed, such as Tylenol,
		    Ibuprofen, Tums, Benadryl, Robitussin, and Zyrtec, per our Standing Order Physician. 
		</li>
		<li>
			Phone calls will be made to parents and/or physicians concering any medications about which there are any questions.
		</li>
		<li>
		SRBC’s food service does not provide specialized diets, however we do try to provide healthy options for campers to choose.
		 Campers with dietary restrictions or needs are welcome to bring their own food. Please consult the nurse. 
		</li>
	</ul>
	<h3>Permission to provide necessary treatment or emergency care</h3>
	<p>
		I, hereby give permission to the medical personnel selected by Solid Rock Bible Camp to give OTCM per our standing order physician and administer
		treatment to my child, and if the need arises, provide transportation to a medical provider or call 911 for EMS response.
		I give permission for medical care by a health provider or emergency care including hospitilization, x-rays, routine tests
		treatment, and release of records necessary for insurance purposes.  I also give permission to share health information on an
		 as need to know basis to camp staff.  This completed form may be photocopied for trips out of camp.  Parents will be notified in the case of 
		 an emergency or the need for outside medical care arises.
	</p>
	<h3>Signature:</h3>
	<canvas id="canvas" style="border:1px solid black" height="200" width="500"></canvas>
	<input type="hidden" name="signature_img">
	<br>
	<button type="button" onclick="signaturePad.clear()">Clear</button>
	<button type="button" onclick="undo()">Undo</button>
	<br>
	<b>* Any medical information that needs to be discussed with the medical staff, please see them during camp registration </b>

	
	<br>
	<script src="/wp-content/plugins/SRBC/requires/js/signature_pad/signature_pad.min.js"></script>
	<script src="/wp-content/plugins/SRBC/admin/js/signature-pad-setup.js"></script>';
	}

	public static function healthFormSubmit($camper_id)
	{
			//Health form stuff
	//generate a random key for encrypting the signature_img
	$fp=fopen($_SERVER['DOCUMENT_ROOT']. '/files/health_form_public_key.pem',"r");
	$pub_key=fread($fp,8192);
	fclose($fp);
	openssl_get_publickey($pub_key);
	//Encrypt AES key only 16 characters because that is the key size
	$aesKey = substr(base64_encode(openssl_random_pseudo_bytes(16)),0,16);
	openssl_public_encrypt($aesKey,$encryptedKey,$pub_key);//,OPENSSL_PKCS1_OAEP_PADDING);
	$encryptedKey = base64_encode($encryptedKey);

	$currentDate = new DateTime("now", new DateTimeZone('America/Anchorage'));
	$healthInformation = array(
		"emergency_contact" => $_POST['emergency_contact'],
		"emergency_phone_home" => $_POST['emergency_phone_home'],
		"emergency_phone_cell" => $_POST['emergency_phone_cell'],
		"recent_injury_illness" => $_POST['recent_injury_illness'],
		"ear_infections" => $_POST['ear_infections'],
		"skin_problems" => $_POST['skin_problems'],
		"sleepwalking" => $_POST['sleepwalking'],
		"chronic_recurring_illness" => $_POST['chronic_recurring_illness'],
		"glassses_contacts" => $_POST['glassses_contacts'],
		"orthodontic_appliance" => $_POST['orthodontic_appliance'],
		"mono" => $_POST['mono'],
		"allergies" => $_POST['allergies'],
		"current_medications" => $_POST['current_medications'],
		"frequent_headaches" => $_POST['frequent_headaches'],
		"stomach_aches" => $_POST['stomach_aches'],
		"head_injury" => $_POST['head_injury'],
		"high_blood_pressure" => $_POST['high_blood_pressure'],
		"asthma" => $_POST['asthma'],
		"emotional_difficulties" => $_POST['emotional_difficulties'],
		"seizures" => $_POST['seizures'],
		"diabetes" => $_POST['diabetes'],
		"bed_wetting" => $_POST['bed_wetting'],
		"immunizations" => $_POST['immunizations'],
		"explanations" => $_POST['explanations'],
		"carrier" => $_POST['carrier'],
		"policy_number" => $_POST['policy_number'],
		"physician" => $_POST['physician'],
		"physician_number" => $_POST['physician_number'],
		"family_dentist" => $_POST['family_dentist'],
		"dentist_number" => $_POST['dentist_number'],
		"dateTime" => $currentDate->format("m/d/Y h:i A"),
		//Also removed all backspaces as it is just escaped characters.
		"signature_img" => str_replace("\\", "", $_POST['signature_img'])
	);
	$JSONhealthInformation = json_encode($healthInformation);
	
	//Data in encrypted with AES since it is too large to be directly encyrpted by RSA
	$encryptedJSONobj = aesEncrypt($JSONhealthInformation, $aesKey);
	
	global $wpdb;

	//Check if a camper already has a health form and replace it
	$prevCamperCheck = $wpdb->get_row( "SELECT * FROM srbc_health_form WHERE camper_id = $camper_id" );
	$healthFormId = 0;
	if($prevCamperCheck != NULL)
	{
		$healthFormId = $prevCamperCheck->health_form_id;
	}

	$wpdb->replace(
		'srbc_health_form', 
		array( 
			'health_form_id' => $healthFormId,
			'camper_id' => $camper_id,
			'IV' => $encryptedJSONobj->IV,
			'aesKey' => $encryptedKey,
			"data" => $encryptedJSONobj->cipherText
		), 
		array( 
			'%d',
			'%d',
			'%s', 
			'%s', 
			'%s'
		) 
		);
	}
}
?>
