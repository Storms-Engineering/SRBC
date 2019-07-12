<?php 
function srbc_program_access()
{
	// check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
	?>
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/popup.css">
	<style>
	table
	{
		float:left;
		margin:7px;
	}
	table, th, td
	{
		border: 1px solid black;
		border-collapse: collapse;
	}
	th
	{
		font-size:large;
	}
	td,th
	{
		padding:5px;
	}
	.big_button {
    background-color: #6699ff;
    color: white;
    cursor: pointer;
    padding: 10px;
    border: none;
    text-align: left;
    outline: none;
    font-size: 15px;
	margin:2px;
	}
	
	.big_button:hover
	{
		background-color: #8caef3;
	}
	/*Toast Notification*/
	 /* The snackbar - position it at the bottom and in the middle of the screen */
#snackbar {
    visibility: hidden; /* Hidden by default. Visible on click */
    min-width: 250px; /* Set a default minimum width */
    margin-left: -125px; /* Divide value of min-width by 2 */
    background-color: #333; /* Black background color */
    color: #fff; /* White text color */
    text-align: center; /* Centered text */
    border-radius: 2px; /* Rounded borders */
    padding: 16px; /* Padding */
    position: fixed; /* Sit on top of the screen */
    z-index: 1; /* Add a z-index if needed */
    left: 50%; /* Center the snackbar */
    bottom: 30px; /* 30px from the bottom */
}

/* Show the snackbar when clicking on a button (class added with JavaScript) */
#snackbar.show {
    visibility: visible; /* Show the snackbar */
    /* Add animation: Take 0.5 seconds to fade in and out the snackbar.
   However, delay the fade out process for 2.5 seconds */
   -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
   animation: fadein 0.5s, fadeout 0.5s 2.5s;
}
	</style>
    <div class="wrap">
        <h1>Program Access</h1>
		<br>
		Select Camp:
		<?php
		global $wpdb;
				$camps = $wpdb->get_results("SELECT * FROM ".$GLOBALS['srbc_camps'] ." ORDER BY start_date ASC");
				echo '<select id="camp" name="camp" onchange="showLodging()"><option value="none">none</option>';
				foreach ($camps as $camp){
					echo '<option value='.$camp->camp_id .'>'.$camp->area . ' ' . $camp->name .'</option>';
				}
				echo '</select>';
		?>
		Please select your area
		<select class="inputs" id="area" onchange="showLodging()">
			<option value="Lakeside">Lakeside</option>
			<option value="Wagon Train">Wagon Train</option>
			<option value="Wilderness">Wilderness</option>
			<!-- These might possibly be added later
			<option value="Workcrew">Workcrew</option>
			<option value="Sports">Sports Camp</option>
			<option value="Fall Retreat">Fall Retreat</option>
			<option value="Winter Camp">Winter Camp</option>-->
		</select><br>
		
		
		<div id="results">
		</div>
		<div id="popup_background"><div id="popup">
		Search <input id="search" style="width:250px;" list="suggestions" type="search"> 
		<button id="search_button" class="big_button" style="padding:5px;" onclick="search();">Search</button>
		<div id="results_campers"></div>
		<button id="popup_button" class="big_button" style="padding:5px;" >Ok</button>
		</div></div>
    </div>
	<div id="snackbar"></div>
	<script src="../wp-content/plugins/SRBC/admin/popup.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/program_access.js"></script>
	
	<?php
}

function srbc_database()
{
	// check user capabilities
    if (!current_user_can('manage_options') || in_array( 'program', (array) wp_get_current_user()->roles)) {
         exit("Thus I refute thee.... P.H.");
    }
	global $wpdb;
	if(isset($_POST["srbc_database_year"]))
	{
		update_option("srbc_database_year",$_POST["srbc_database_year"]);
		//Test query - kinda lets the user know that this database doesn't exist
		$wpdb->query("SELECT camp_id FROM srbc_camps" . get_option("srbc_database_year"));
		$GLOBALS["srbc_camps"] = "srbc_camps" . get_option("srbc_database_year");
		$GLOBALS['srbc_payments'] = "srbc_payments" . get_option("srbc_database_year");
		$GLOBALS['srbc_registration'] = "srbc_registration" . get_option("srbc_database_year");
		$GLOBALS['srbc_registration_inactive'] = "srbc_registration_inactive" . get_option("srbc_database_year");
	}
	if(isset($_POST["rename_database"]))
	{
		
		//Rename databases and recreate new ones
		$wpdb->query("RENAME TABLE srbc_registration TO srbc_registration" . date("Y") .  ",
		srbc_camps TO srbc_camps" . date("Y") . ", srbc_payments to srbc_payments" . date("Y") . ", srbc_registration_inactive TO srbc_registration_inactive".
		date("Y") . ";");
		srbc_install();
	}
	?>
	<h1>Database Management</h1>
	<form method="post">
	Please choose which year you would like to pull data from: 
	<input type="year"  pattern="[2][0-9][0-9][0-9]" placeholder="2019" title="Use a full year format like 2019" name="srbc_database_year" value="<?php echo get_option("srbc_database_year");?>">
	  <input type="submit" value="Save">
	  <br>
	  	Clear the field and hit save to revert to the current database.
	</form>
	<br><br>
	<form method="post" onsubmit="return confirm('Are you sure you want to archive all data?');">
	  <input type="submit" name="rename_database" value="Archive All Current Data">
	  <h2 style="color:red">Please Note that this will make all registrations and camps archived.  
	  If you wish to access this data please enter the year that you archived the data.
	  </h2>
	  <p>
	  When you archive data, it will use the current year to label the database.
		So if you archive the database in 2019, and are trying to access it in 2020, just enter 2019 for the year.
		If you archive the database in Nov of 2019, it will move all the camps, registrations, and payments to the 2019 database.
		If you try to archive it again in lets say dec 2019, it will probably delete the old database or throw an error.
		But after you archive it in Nov 2019, this database is 'New' and wiped clean.  Camper addresses and information will be retained through all
		database archiving.
	  </p>
	</form>
	<?php
	
}
function srbc_credit_cards(){
	// check user capabilities
    if (!current_user_can('manage_options') || in_array( 'program', (array) wp_get_current_user()->roles)) {
         exit("Thus I refute thee.... P.H.");
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
	/*@media print
	{		
		td:nth-child(6) {
		   display: none;
		}
		th:nth-child(6) {
		   display: none;
		}
			
	}*/
	</style>
	<h1>Credit Cards pending processing:</h1>
	Password :  <input type="password" id="pwd"> Decryption Progress <progress value="0" id="progress"></progress>
	<div ondrop="drop(event)" ondragover="allowDrop(event)" style="background:lightblue;height:50px;width:400px;float:right;">Drop key file here</div>
	<table id="cc_table" style="width:100%;" >
		<tr>
			<th>Date</th>
			<th>Data</th>
			<th>Amount</th>
			<th>For Camper</th>
			<th>Camp</th>
			<th>Comments</th>
			<th>Delete</th>
		</tr>
	<?php
	global $wpdb;
	$ccs = $wpdb->get_results("SELECT * FROM srbc_cc ORDER BY cc_id ASC");
	foreach ($ccs as $cc)
	{
		/*TODO: I would like to store this more efficiently but not right this moment
		$camper = $wpdb->get_row($wpdb->prepare("SELECT *
							FROM ((srbc_registration 
							INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
							INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
							WHERE ",$ccs->registration_id);*/
		echo "<tr><td>" . $cc->payment_date;
		echo '</td><td>' . $cc->data;
		echo "</td><td>$" . $cc->amount;
		echo "</td><td>" . $cc->camper_name;
		echo "</td><td>" . $cc->camp;
		echo "</td><td>" . $cc->comments;
		echo '</td><td><button onclick="' . "if(confirm('Are you sure you want to delete?')){postAjax(" . "{'deleteid':" . $cc->cc_id . '})}">Delete</button>';
		echo "</td></tr>";
	}
	echo "</table> ";
	?>
	<script src="../wp-content/plugins/SRBC/JSEncrypt/jsencrypt.min.js"></script>
	<script src="../wp-content/plugins/SRBC/Jsrsasign/jsrsasign-all-min.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/sortTable.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/credit_card.js"></script>
	<?php
}

//Shows camper management page
function srbc_camper_management()
{
    // check user capabilities
	//TODO Make a security function that can be called here?
   if (!current_user_can('manage_options') || in_array( 'program', (array) wp_get_current_user()->roles)) {
         exit("Thus I refute thee.... P.H.");
    }
    ?>
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/camper_management.css">
    <div class="wrap">
        <h1>Camper Management</h1>
		<br>
		Search <input id="search" style="width:250px;" list="suggestions" type="search"> 
		<button id="search_button" class="big_button" style="padding:5px;" onclick="search();">Search</button>
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
		$camps = $wpdb->get_results("SELECT area,name FROM ". $GLOBALS['srbc_camps'], ARRAY_N);
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
	<script src="../wp-content/plugins/SRBC/admin/camper_modal.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/sortTable.js"></script>
    <?php
}

function listCamps($area)
{
	echo '<table style="width:100%;">
		<tr>
			<th>Camp</th>
			<th>Start Date</th>
			<th>Boys Registered</th>
			<th>Girls Registered</th>
			<th>Total Registered</th>
			<th>Waitlist</th>';
	if ($area == "Lakeside")
		echo '<th>Horse Waitlist</th>';
	echo '<th>Delete</th>
		</tr>';
	global $wpdb;
	if ($area != "Lakeside")
		$camps = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_camps'] . " WHERE area='%s' ORDER BY start_date",$area));
	else
		$camps = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $GLOBALS['srbc_camps'] . " WHERE area='%s' OR area='Sports' ORDER BY start_date",$area));
	foreach ($camps as $camp)
	{
		$waitlistsize = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										WHERE camp_id=%s AND NOT waitlist=0",$camp->camp_id)); 
		$male_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='male'",$camp->camp_id)); 
		$female_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										LEFT JOIN srbc_campers ON " . $GLOBALS['srbc_registration'] . ".camper_id = srbc_campers.camper_id
										WHERE camp_id=%s AND waitlist=0 AND srbc_campers.gender='female'",$camp->camp_id)); 
		$horseWaitlist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(camp_id)
										FROM " . $GLOBALS['srbc_registration'] . "
										WHERE camp_id=%s AND NOT horse_waitlist=0",$camp->camp_id)); 
										
		echo '<tr onclick="openModal(' . $camp->camp_id . ')"><td>' . $camp->name;
		echo "</td><td>" . $camp->start_date . "</td>";
		echo "<td>" . $male_registered . "</td>";
		echo "<td>" . $female_registered . "</td>";
		echo "<td>" . ($male_registered + $female_registered) . "/" . $camp->overall_size . "</td>"; 
		echo "<td>" . $waitlistsize ."/" . $camp->waiting_list_size . "</td>";
		if ($area == "Lakeside")
			echo "<td>" . $horseWaitlist ."/" . $camp->horse_waiting_list_size . "</td>";
		echo '<td><button class="big_button" style="padding:2px;" onclick="deleteCamp(event,' . $camp->camp_id . ');">Delete</button></td></tr>';
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
        <h2 id="typeity"></h2>
    </div>
	<script>
	<?php 
	echo "name = '" . wp_get_current_user()->display_name . "';";
	?>
	var i = 0;
	var txt = "Hello " + name + ".... what does it look like? The shape in the glass?" +
	"\rIn this hour of victory, we taste only defeat. I ask, why? We are Forerunners, guardians of all that exists." +
	"The roots of the galaxy have grown deep under our careful tending. Where there is life, the wisdom of our countless " +
	"generations has saturated the soil. Our strength is a luminous sun, towards which all intelligence blossoms... And the " + 
	"impervious shelter beneath which it has prospered. I stand before you, accused of the sin of ensuring Forerunner ascendancy. "+ 
	"Of attempting to save us from this fate where we are forced to... recede. Humanity stands as the greatest threat in the galaxy."+
	" Refusing to eradicate them is a fool's gambit. We squander eons in the darkness, while they seize our triumphs for their own."+
	"The Mantle of responsibility for all things belongs to Forerunners alone. Think of my acts as you will. But do not doubt the reality:" +
	" the Reclamation... has already begun. And we are hopeless to stop it."; /* The text */
	var speed = 80; /* The speed/duration of the effect in milliseconds */

	function typeWriter() {
	  if (i < txt.length) {
		document.getElementById("typeity").innerHTML += txt.charAt(i);
		i++;
		setTimeout(typeWriter, speed);
	  }
	}
	typeWriter();
	</script>
    <?php
}
function srbc_camps_management()
{
	// check user capabilities
    if (!current_user_can('manage_options') || in_array( 'program', (array) wp_get_current_user()->roles)) {
         exit("Thus I refute thee.... P.H.");
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
			echo "<h2>Fall Retreat</h2><br>";
			listCamps("Fall Retreat");
			echo "<h2>Winter Camp</h2><br>";
			listCamps("Winter Camp");
		?>

		</div>
		
		<div id="New" class="tabcontent">
			Area: <select class="inputs" name="area">
					<option value="Lakeside">Lakeside</option>
					<option value="Wagon Train">Wagon Train</option>
					<option value="Wilderness">Wilderness</option>
					<option value="Workcrew">Workcrew</option>
					<option value="Sports">Sports Camp</option>
					<option value="Fall Retreat">Fall Retreat</option>
					<option value="Winter Camp">Winter Camp</option>
					</select>
			Camp: <input type="text" name="name">Start Date:<input  type="date" name="start_date">
			End Date: <input  type="date" name="end_date"><br>
			Description: <br><textarea class="description" rows="2" cols="30"></textarea>
			<br>
			Grade Range (ex. 2nd to 3rd): <input type="text" name="grade_range"><br>
			Cost(Must be whole number): $<input type="text" name="cost"><br>
			Horse Cost(Only for Wagon Train camps - this is hidden to parents): $<input type="text" name="horse_cost"><br>
			Horse Option cost (Put 0 if there is no horse option for this camp): $<input type="text" name="horse_opt_cost"><br>
			Horse List Size: <input type="text" name="horse_list_size">
			Horse Waiting List Size: <input type="text" name="horse_waiting_list_size"><br>
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
	//TODO I might make reports more flexible by adding columns that the user can pick from
	//and the type of data that they would want to sort by.  I think this should be fine for now, but might remkae in the future.
	// check user capabilities
    if (!current_user_can('manage_options') || in_array( 'program', (array) wp_get_current_user()->roles)) {
         exit("Thus I refute thee.... P.H.");
    }
	?>
	<style>
		@media print
		{		
			#adminmenuwrap,#dontprint,#footer-thankyou,#footer-upgrade
			{
				display:none;
			}
			
		}
		button {
			background-color: #11835a;
			color: white;
			cursor: pointer;
			padding: 8px;
			border: none;
			text-align: left;
			outline: none;
			font-size: 15px;
			margin:2px;
		}
		button:hover
		{
			background-color: #21b17d;
		}
	</style>
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/camper_management.css">
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/tooltip.css">
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
		</select><br>
<br>
		<h2 style="display:inline">General Reports</h2> <div class="tooltip">?
			<span class="tooltiptext">These general reports can also be narrowed to a specific camp or program area, except for camp numbers</span>
		</div> 
		<br>
		<button onclick="generateReport('scholarship')">Scholarships </button>
		<button onclick="generateReport('discount')">Discounts </button>
		<button onclick="generateReport('emails')">Emails </button>
		<button onclick="generateReport('camp_numbers')">Camp Numbers </button>
		<button onclick="generateReport('not_checked_in')">Camper didn't check in </button>
		<button onclick="generateReport('not_payed')">Balance Due</button>
		<button onclick="generateReport('inactive_registrations')">Inactive Registrations</button><br>
		<hr>
		<h2 style="display:inline;">Date specific reports</h2> <div class="tooltip">?
			<span class="tooltiptext">Please choose the same date twice if you are doing a report for all camps starting on that day.
		For buslists pick the starting date(Earlier starting date first)for two camps as as there will be some campers going both ways</span>
		</div> 
		Camp Dates between <input id="start_date" type="date"> & <input id="end_date" type="date">
		<br>
		<button onclick="generateReport('buslist')">Buslist </button><select class="inputs" id="buslist_type">
			<option value="to">To Camp</option>
			<option value="from">To Anchorage</option>
		</select>

		<button onclick="generateReport('backup_registration')">Backup Registrations </button><br>
		<button onclick="generateReport('signout_sheets')">Signout Sheets</button>
		<button onclick="generateReport('registration_day')">Registration Day Report</button>
		<button onclick="generateReport('transactions')">Registration Day Transactions</button>
		<button onclick="generateReport('mailing_list')">Mailing List</button>


		<hr>
		<h2 style="display:inline;">Camp Specific Reports</h2>
		<?php 
				global $wpdb;
				$camps = $wpdb->get_results("SELECT * FROM ".$GLOBALS['srbc_camps'] ." ORDER BY area ASC");
				echo '<select id="camp" name="camp"><option value="none">none</option>';
				foreach ($camps as $camp){
					echo '<option value='.$camp->camp_id .'>'.$camp->area . ' ' . $camp->name .'</option>';
				}
				echo '</select>';
		?><br>
		<button onclick="generateReport('camp_report')">Camp Report </button>		
		<button onclick="generateReport('camper_report')">Camper Report </button>		
		<button onclick="generateReport('snackshop')">Snackshop</button>
		<button onclick="generateReport('horsemanship')">LS Horsemanship </button>
		<button onclick="generateReport('program_camper_sheets')">Program Camper Sheets </button>
		<button onclick="generateReport('packing_list_sent')">Packing List Sent</button>
		
	</div>
	<br><br>
	<div id="results"></div>
	<?php modalSetup(); ?>
	<div id="snackbar"></div>
	<script src="../wp-content/plugins/SRBC/admin/sortTable.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/reports.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/camper_modal.js"></script>

<?php
}
?>