<?php 
function srbc_credit_cards(){
	// check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
	?>
	<style>
	table, th, td
	{
	border: 1px solid black;
	border-collapse: collapse;
	word-wrap: break-word;
	max-width: 800px;
	}
	</style>
	<script src="../wp-content/plugins/SRBC/JSEncrypt/jsencrypt.min.js"></script>
	<script src="../wp-content/plugins/SRBC/Jsrsasign/jsrsasign-all-min.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/credit_card.js"></script>
	<h1>Credit Cards pending processing:</h1>
	Password :  <input type="password" id="pwd"> Decryption Progress <progress value="0" id="progress"></progress>
	<div ondrop="drop(event)" ondragover="allowDrop(event)" style="background:lightblue;height:50px;width:400px;float:right;">Drop key file here</div>
	<table style="width:100%;" >
		<tr>
			<th>Date</th>
			<th>Data</th>
			<th>Amount</th>
			<th>For Camper</th>
			<th>Camp</th>
			<th>Delete</th>
		</tr>
	<?php
	global $wpdb;
	$ccs = $wpdb->get_results("SELECT * FROM srbc_cc ORDER BY payment_date ASC");
	foreach ($ccs as $cc)
	{
		echo "<tr><td>" . $cc->payment_date;
		echo '</td><td>' . $cc->data;
		echo "</td><td>$" . $cc->amount;
		echo "</td><td>" . $cc->camper_name;
		echo "</td><td>" . $cc->camp;
		echo '</td><td><button onclick="' . "if(confirm('Are you sure you want to delete?')){postAjax(" . "{'deleteid':" . $cc->cc_id . '})}">Delete</button>';
		echo "</td></tr>";
	}
	echo "</table> ";
}

//Shows camper management page
function srbc_camper_management()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/camper_management.css">
    <div class="wrap">
        <h1>Camper Management</h1>
		<br>
		Search By First/Last Name<input id="search" list="suggestions" type="search"> 
		<input id="search_button" type="submit" onclick="search();">
		<datalist id="suggestions">
		<option value="Lakeside">
		<option value="Wagon Train">
		<option value="Wilderness">
		<option value="Workcrew">
		<option value="Sports">
		<option value="Fall Retreat">
		<option value="Winter Camp">
		<?php
		global $wpdb;
		$camps = $wpdb->get_results("SELECT area,camp_description FROM srbc_camps",ARRAY_N);
		for ($i = 0;$i< count($camps);$i++){
			echo '<option value="' . $camps[$i][0] . '~' . $camps[$i][1] . '">';
		}		
		?>
		</datalist>
		<div id="results">
		</div>
		<?php modalSetup() ?>
		<!-- The toast notification from w3schools -->
	<div id="snackbar"></div>
    </div>
	
	<script src="../wp-content/plugins/SRBC/admin/camper_management.js"></script>
    <?php
}

function listCamps($area)
{
	echo '<table style="width:100%;">
		<tr>
			<th>Camp Description</th>
			<th>Start Date</th>
			<th>Boys Registered</th>
			<th>Girls Registered</th>
			<th>Total Registered</th>
			<th>Waitlist</th>
			<th>Delete</th>
		</tr>';
	global $wpdb;
	$camps = $wpdb->get_results($wpdb->prepare("SELECT * FROM srbc_camps WHERE area='%s' ORDER BY start_date",$area));
	foreach ($camps as $camp)
	{
		$waitlistsize = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										WHERE camp_id=%s AND NOT waitlist=0",$camp->camp_id), ARRAY_N)[0][0]; 
		$male_registered = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id), ARRAY_N)[0][0]; 
		$female_registered = $wpdb->get_results($wpdb->prepare("SELECT COUNT(camp_id)
										FROM srbc_registration
										LEFT JOIN srbc_campers ON srbc_registration.camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id), ARRAY_N)[0][0]; 
		echo '<tr onclick="openModal(' . $camp->camp_id . ')"><td>' . $camp->camp_description;
		echo "</td><td>" . $camp->start_date . "</td>";
		echo "<td>" . $male_registered . "</td>";
		echo "<td>" . $female_registered . "</td>";
		echo "<td>" . ($male_registered + $female_registered) . "/" . $camp->overall_size . "</td>"; 
		echo "<td>" . $waitlistsize ."/" . $camp->waiting_list_size;
		echo '</td><td><button onclick="deleteCamp(event,' . $camp->camp_id . ');">Delete</button></td></tr>';
	}
	echo "</table> ";
}

function srbc_overview_page()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <?php 
		echo "Hello Peter.... what does it look like? The shape in the glass?";?>
    </div>
    <?php
}
function srbc_camps_management()
{
	// check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
	global $wpdb;
	?>
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/camps_management.css">
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <button class="tablink" onclick="openPage('Lakeside', this, '#b30000')" id="defaultOpen">Lakeside</button>
		<button class="tablink" onclick="openPage('Wilderness', this, 'green')" >Wilderness</button>
		<button class="tablink" onclick="openPage('WagonTrain', this, '#993d00')">Wagon Train</button>
		<button class="tablink" onclick="openPage('WorkCrew', this, 'orange')">Work Crew</button>
		<button class="tablink" onclick="openPage('Misc', this, '#0099cc ')">Misc</button>
		<button class="tablink" onclick="openPage('New', this, '#bfbfbf')">Add New Camp</button>
		
		
		<div id="Lakeside" class="tabcontent">
	 
		<?php
			listCamps("Lakeside");
		?>

		</div>
		
		<div id="Wilderness" class="tabcontent">
		<?php
			listCamps("Wilderness");
		?>
		</div>
		
		<div id="WagonTrain" class="tabcontent">
		<?php
			listCamps("Wagon Train");
		?>
		</div>
		
		<div id="WorkCrew" class="tabcontent">
			<?php
			listCamps("Workcrew");
			?>
		</div> 
		<div id="Misc" class="tabcontent">
	 
		<?php
			echo "<h2>Sports Camps</h2><br>";
			listCamps("Sports");
			echo "<h2>Fall Retreat</h2><br>";
			listCamps("Fall Retreat");
			echo "<h2>Winter Camp</h2><br>";
			listCamps("Winter Camp");
		?>

		</div>
		
		<div id="New" class="tabcontent">
			Camp Area: <select class="inputs" name="area">
					<option value="Lakeside">Lakeside</option>
					<option value="Wagon Train">Wagon Train</option>
					<option value="Wilderness">Wilderness</option>
					<option value="Workcrew">Workcrew</option>
					<option value="Sports">Sports Camp</option>
					<option value="Fall Retreat">Fall Retreat</option>
					<option value="Winter Camp">Winter Camp</option>
					</select>
			Camp Description: <input type="text" name="camp_description">
			Camp Start Date:<input  type="date" name="start_date">
			Camp End Date: <input  type="date" name="end_date"><br>
			Grade Range (ex. 2nd to 3rd): <input type="text" name="grade_range"><br>
			Cost: <input type="text" name="cost">
			Horse Option cost (Put 0 if there is no horse option for this camp):<input type="text" name="horse_opt"><br>
			Waiting List Size:<input type="text" name="waiting_list_size"><br>
			Number of Boy Campers allowed to Register:<input type="text" name="boy_registration_size"><br>
			Number of Girl Campers allowed to Register:<input type="text" name="girl_registration_size"><br>
			Overall number of campers:<input type="text" name="overall_size">
			<br>^ Note the number of boy campers and girl campers can be the same size as the number of overall allowed for this camp.  
			This will allow the boy/girl registrations to be flexible.  However if you only want 8 boys to register for this camp then put 8 in the 
			boy campers.  If the overall camp size is 32, and you only want to allow 8 boys, then you will need to put 24 in the number of allowed girls.
			<br><br>
			<button onclick="addNewCamp()" class="tablink" >Add New Camp</button>
		</div> 
    </div>
	<?php modalSetup() ?>
	<div id="snackbar"></div>
	<div id="error"></div>
	<script src="../wp-content/plugins/SRBC/admin/camps_management.js"></script>
    <?php
}

function srbc_camp_reports()
{
	// check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
	?>
	<style>
		@media print
		{		
			#adminmenuwrap,#dontprint,#footer-thankyou,#footer-upgrade
			{
				visibility:hidden;
			}
			
		}
		table, th, td
		{
			border: 1px solid black;
			border-collapse: collapse;
			background:white;
		}
		th
		{
			background-color:#5cb85c;
			font-size:large;
		}
		td,th
		{
			padding:5px;
		}
	</style>
	<div id="dontprint">
	<h1>Reports</h1>
	<button style="float:right;" onclick="window.print()">Print Report</button>
	Program Area:
		<select class="inputs" id="area">
			<option value="">All</option>
			<option value="Lakeside">Lakeside</option>
			<option value="Wagon Train">Wagon Train</option>
			<option value="Wilderness">Wilderness</option>
			<option value="Workcrew">Workcrew</option>
			<option value="Sports">Sports Camp</option>
			<option value="Fall Retreat">Fall Retreat</option>
			<option value="Winter Camp">Winter Camp</option>
		</select>
		Buslist <select class="inputs" id="buslist">
			<option value="all">All</option>
			<option value="none">No busride</option>
			<option value="to">To Camp</option>
			<option value="from">To Anchorage</option>
			<option value="both">Both Ways</option>
		</select><br>
		Scholarships <input id="scholarship" type="checkbox">
		Discounts <input id="discount" type="checkbox">
		Start Date: <input id="start_date" type="date">
		End Date: <input id="end_date" type="date">
		Camper didn't check in <input id="not_checked_in" type="checkbox">
		Hasn't paid in full <input id="not_payed" type="checkbox">
		<button onclick="generateReport();">Generate Report</button>		
	</div>
	<br><br>
	<div id="results"></div>
	<script src="../wp-content/plugins/SRBC/admin/reports.js"></script>
<?php
}
?>