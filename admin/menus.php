<?php 
function staff_application_menu()
{
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
	<h1>Staff Applications</h1>
	<p>Note if all the data is false, then please double check the password or the file you imported.  You will need to reload the page to make changes.</p>
	Password :  <input type="password" id="pwd"> Decryption Progress <progress value="0" id="progress"></progress>
	<div ondrop="drop(event)" ondragover="allowDrop(event)" style="background:lightblue;height:50px;width:400px;float:right;">Drop key file here</div>
	<table id="cc_table" style="width:100%;" >
		<tr>
			<th>Name</th>
			<th>SSN</th>
			<th>Delete</th>
		</tr>
		
	<?php
	
	global $wpdb;
	$apps = $wpdb->get_results("SELECT * FROM srbc_staff_app ORDER BY staff_app_id ASC");
	foreach ($apps as $app)
	{
		/*TODO: I would like to store this more efficiently but not right this moment
		$camper = $wpdb->get_row($wpdb->prepare("SELECT *
							FROM ((srbc_registration 
							INNER JOIN srbc_camps ON srbc_registration.camp_id=srbc_camps.camp_id)
							INNER JOIN srbc_campers ON srbc_registration.camper_id=srbc_campers.camper_id)
							WHERE ",$ccs->registration_id);*/
		echo "<tr><td>" . $app->Firstname . " " . $app->Middlename . " " . $app->Lastname;
		echo '</td><td class="ssn">' . $app->ssn;
		echo '</td><td><button onclick="' . "if(confirm('Are you sure you want to delete?')){postAjax(" . "{'deleteid':" . $app->staff_app_id . ", 'wp_nonce' : '" . wp_create_nonce( 'delete_ssn_'.$app->staff_app_id) . '\'})}">Delete</button>';
		echo "</td></tr>";
	}
	echo "</table> ";
	?>
	
	<script src="../wp-content/plugins/SRBC/JSEncrypt/jsencrypt.min.js"></script>
	<script src="../wp-content/plugins/SRBC/Jsrsasign/jsrsasign-all-min.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/sortTable.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/staff_application.js"></script>
	
	<?php
}
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

function srbc_settings()
{
	// check user capabilities
    if (!current_user_can('manage_options') || in_array( 'program', (array) wp_get_current_user()->roles)) {
         exit("Thus I refute thee.... P.H.");
    }
	global $wpdb;
	//Archive old databases by moving them into new database with the suffix of the year they were archived
	if(isset($_POST["rename_database"]))
	{
		//Rename databases and recreate new ones
		$wpdb->query("RENAME TABLE srbc_registration TO srbc_registration" . date("Y") .  ",
		srbc_camps TO srbc_camps" . date("Y") . ", srbc_payments to srbc_payments" . date("Y") . ", srbc_registration_inactive TO srbc_registration_inactive".
		date("Y") . ";");
		srbc_install();
		goto end;
	}
	
	//Update the summer camps disabled option
	if(isset($_POST['srbc_summer_camps_disable']))
		update_option("srbc_summer_camps_disable",$_POST["srbc_summer_camps_disable"]);
	else
		update_option("srbc_summer_camps_disable","");
	//Update globals
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
	end:
	?>
	<h1>Settings</h1>
	<form method="post">
	Disable Summer Camps <input type="checkbox" name="srbc_summer_camps_disable" value="true" <?php echo (get_option("srbc_summer_camps_disable") == "true") ? "checked" : ""; ?>>
	<h1>Database Management</h1>
	Please choose which year you would like to pull data from: 
	<input type="year"  pattern="[2][0-9][0-9][0-9]" placeholder="ex 2019" title="Use a full year format like 2019" name="srbc_database_year" value="<?php echo get_option("srbc_database_year");?>">
	  	<br>
		Clear the field and hit save to revert to the current database.
		<br>
		<br>
		<input type="submit" value="Save">
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
		echo '</td><td><button onclick="' . "if(confirm('Are you sure you want to delete?')){postAjax(" . "{'deleteid':" . $cc->cc_id . ", 'wp_nonce':'" . wp_create_nonce( 'delete_cc'.$cc->cc_id ) . '\' })}">Delete</button>';
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



function srbc_overview_page()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
	<style type="text/css">

/* essential terminal styles */

.term {
	font-family: courier,fixed,swiss,sans-serif;
	font-size: 12px;
	color: #0393fc;
	background: none;
}
.termReverse {
	color: #111111;
	background: #33d011;
}
</style>
	<script language="JavaScript" type="text/javascript" src="../wp-content/plugins/SRBC/termlib/termlib.js"></script>
	<script language="JavaScript" type="text/javascript" src="../wp-content/plugins/SRBC/termlib/termlib_parser.js"></script>
	<script language="JavaScript" type="text/javascript" src="../wp-content/plugins/SRBC/termlib/peterHawkeConsole.js"></script>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <h2 id="typeity"></h2>
    </div>
	<p style="font-size: 3px;font-family:monospace;line-height:1.4;white-space:pre">
odddolc:;'..          .........................................,lddlccc:;clooc'...............................................................:dkOkkkkOOOO0Oxc'.........................................
ddxdllcc:'..  ......................',;cccccllcll:::cc:;::clc;;cdxdoooolcodxxl,.....'''',,,'',,,''',,;;;;;;;,',,,',,,;;:::;;,',,'',,;;;,,'',;cokOkxxxxxkkOO00kl,.................'',,,'',,,,;;,,,,,,,,,'
xxxdllllc,..  ............'''''''''',;:ldxxkkxxkkxddxxoodddxdooxxxdodddolodxxo;....'',,,;,,;;;;,'...',;;,,,;;;;cc:::clloolcc:;:c:clooddolcccoxOOkxxddoodxkOO00Od:'''..',,,,'',,,;:ccccccclcodolccloooolc
xxxdllllc,...............''',,,'''',,,:ldxkkxxkkkkxxxxddkkxxxxxkkkxdxxxooodxxo;...'',,,;,;;;;;,'..........',;;;:;,''',,,;:clc:lolldxkOkxxdodxO0OkxdooooodxkOOKK0xc;'''',,,,,,;;;;clllooddoodxdllloxxkkxo
xxkxolllc,........'......''',,,,,,,,,,:oxxkkkkkkkkkkkkdxkkkxxxkkOOxdxxxoodxkko;''''',,;;,,,,''''',,,;;;;;;;;,,,,'''......':olcoolodxkOkxdddkO0OkkxolooodddxkO0KK0kl;,,,,;;,,,;;;:cloodddxdddxxooodxkkkxo
xxkxdoolc,.......''......'',,,,,,,',,;:oddxkkkkkkkkkOkxdxxxxxxkOOOkxkkkxdxkkko;'''''',,''........''''''.......     ...........;lodxxxkkdodk00OkkxolllooddodxOOO0KKOo:,,;;;;,,;:ccllloooooooodxdooxkOOOkd
xkkxdoolc,......'''....'''',,,,,;;,;:loxddkkkkOO00OOOOxxkkkkkO0000OOOkkxxxxkxo;'................'''''''......              .   ..:dxkkkddk00OkkdooolllooddodkOOO0KKOo:;;;;;;;:::clllcloooddodxoodxkkO0Ox
xxxdooooc,.....'''''...'''',,;;cl;''';okdldkOOOO000000OO0000O00000OOOOkkxxxxxl;'.    ..,'.    ..'',;;;;;,'...                     .,ldddkOOkxxdooooolooooddddxkOO0KKOdllllccloddoooccooc:c:cxOocodxkO00k
xxxdooodl,.....',,,''''''',;cccod;....cko:okOOOO0000000O000000000OkkkOOkxxxxdl;'.   ....    ...''',;;;,;,,'...                       ..,coooddoooooloodddoooodxOOOKXK0OOOkxdxk0Oxdoccol,...,dOo:lxkOOOOx
xxxdddddl;'...',,,,,''''',,:ooodo,....lxo:oOOOOO000O0OOOOOOO000000OkOOOkkkkxdl;..  ..       ..',;;,,,,,'....                            .':looddooooodxxxdodddxkO00KKXKOOOkkkO00Oxdlcol,...,dOd:okOOO0Ox
xxxdddddl;'...',,,,,,'''',,:ooloo,...'okocdOOOOO00OOO0OO00KK0000000O00OOkkkko;...          .....'''....                               .....;cooddddooddxxxxxxdxxkO00KXK0OOOOOOKK0Okocll,...;x0dcokOOOOOk
kxxxxdddl;'...',,,,,,,,,',;loooxo,...;dOdcdOOO0000OOO0000KKKK000K00000OOkkkd:.             ..............                                  .;cloddddooddddxxxdxxxk000KXK0kkkxdk000kolol;..':kKxclxkOkOOk
kxxxxxxxl;'...',,,,,,,,,,,;ldxxd:'...;lxkxxkOO00000OOOOOO00000OO00000OOkOOkkl.           .....''',''.......                                ':clooooooddddddxxdddxxO000KXKOkkdccdkkkdddc,'',:dOkoldkkkkkk
kxxkkxxxl;'..',,;;;,,,,,,,:lxkd;....',;cxOOkO000000000OOO00000OO000000OOOOOd,.    ..  .....''....'',,,,,....                               .:oooddoooodddooddddddxkk000KXK0kdc::ldxkxc'.'',;:ldxdoxkOOOO
kxxxxxxxl;'''',;;;;,,,,,,,;lkx;......',;lk0kxk00000000000KKKKK0000OO000O0Od'     .. ......',::;;,,'''',;:::;'........ ..                    ;lodxddddooooooodooodxkkk0KKKXX0ko:;:cdxc....''',;cdkddk0000
kkxxxxxxl;'''',;;;;;,,,,,;;lkd,.....''',:xOdlxO00000000000KKK000K000000O0k;    ...   .....',cldddolc:::;,',;:;,,''',,'....                  .:oododdddoooloodododxkkkO0KKXXX0xl:;;cc,....''..':oxolxO000
kxxddxxxl,'.',,,;;;;;;;;;;;oOd,.....'''':xOdlx0KKKKKK00000KKK00KK0000000Ol.         ......',,,:coxdlcclol:,'....',,,,,'''.....               .codddooooollooodddddxxxkO0KKKXX0dl:;;;'...'''''';okdlx0KKK
xxddxxkxc,''',,,;;;;;;;,,,;oOd,....'''',ck0dlk0KKKKKKK0KKKKKKK0KK0000000l.         ...,:;,;;,'',:oxO00kdlcloooc:;,,,;,,''.......              'odddoooolloooooooddddxxkO0K0KXX0xl::;'...''''',:dkdlxKXXK
kxxddxkxc,''',,,;;;;;;;,,,:dOd,...''',,,lO0dlk0KKKKKKKKKKKKKK0000000KKKk,          ..',:oddxxdollollooxkOOxooddddolc:;,''........             .:oddddolooooooodddddddxkO00KKKXX0xoc;'..''''',,:dkdld0KKK
Okkxdxkxc,''',,,;;;;;;;,,,:dOd,..''',,,;lO0olk00KKKKK000000000000KKK0KKx'          ..,:ldkO00KXXXXXKOkdoddxxxxddddddol:,'.........             ,oddoooooooooooddddddxkkkkO0KKKXK0xl:,..'',,,,;cdkdldOOO0
kkkxxxkxc,''',,;,;;;;;;;;;:dko,..''',,,;oO0dlk0KKKKKK00000000000KKK0000l.         ..'cdkO0XXXXXNNNNNNXXKK000OOkkkkkxoc;,,,'........            'odoolloooodoooodddddxkkxxkO000KXX0xl;'.''',,,;cdxoldkOOO
kkxxxkkxc,''',,;;;;;;;;;;;cdkl'...'',,,;oOOookKKKKKKKKK000KK0000000Okxx;         ..:dkOKXNNNNNNNWNNNNNXKKKXXKK0OOkxddoolc:;'.......            'loollloooodddoooddddxkkxdxkkO0OKXX0xl;'.'',,,,:oxocdOOO0
kkxxxkkdc,'',,,;;;;;;;:;;;cdkl'..''',,,;oOOooOKKKKKKKKKKKKKKKKK00000Okl.        ..,d0K0kxxkkkOO00KXNNXKXKKXXK0Oxxxxxddddol:,'......            'loolloooooodddodddddxxxxdxkxxkOO0KK0xl;'',,,,,:oxocdO0O0
kkxxxxkdc,'',,,;;;;;::::;;:dkl'..''',,,;o0Ood0KKKXXXKKKKKKKKKK0KKK00KK0l.       .,oOOdolcc:;,;:cloxkOOk00OKKOxolllc:;;,,''''.......           .:ooooooodddoodddddxxdxxkxxxxxxxkOO0KK0xl:,'',,';ldocdOOO0
kkxxxkkxc,,',,,;;;;::::::;:xkl'..''',,,;o0Ood0KKKXXXKKKKKKKKKKKKKKKKKK0l.     ..:k0Oxxdlc:,'....',;,,;cddodoc;'.....................         .:ooooooooodddddddddxxxxxkxxdxxxdxk000KK0xo:,,,,',:llcdOOkO
kxxxxkkdc,,,,,;;;;::::::::cxkc'..'''',,;o0Ood0KKKKKKKKKKKKKKK0KKKKKKKXO;..   ..:kKKOdc,.............'';lo;'....... .................         .:dooooooooodddddoddddxxxkxxdxxxdxkO000KX0koc;,'',;c::okkkO
xdddxkkd:,,,,,;;;;::::::::cxkc'..''',,,:d0OooO000KKKKKKKKK00000KKXKKKKx,,;....,xKXK0koc:cl;.  .....'':x00o,....           ......'''.         .looooooooooodddddddddddxxxxdxxxxxxkO000KK0kdc;,',,;;;lxkk0
ddddxkkd:,,,,,;;;;;:::::;;cxkc'.''',,,,:dOklok000KKKK0000000000KXKKK0KOoc;''..l0XXNNNXXOkdl:,,,;:ccoddkkdc,.....  ..     .......'''..        ,oooooooooooooooooooddddxxxddxxxxxxxxOOO0KXKOdl:,',;;;cdxk0
xdddxkkd:,,,,,;;;;;;::;;;;lxxc'.'''',,,:dOxcoO000KKKK0000000000KKKKKKKKK0c'..,kKXXNNWNNXK0kxddk0KOkkkdokd,. .....................'''.     ...:ooooollloooooloolcldddxxxxxdxxxxxxxdxkO00KXKOxl:,,;;;:ldk0
xxkkkkkd:,,,,,;;;;;;:;;;;;lxxc'..''',,,:dOkcoO00KKKKKK0KKKKKKK0KKKKKKKKK0o,..;OKXXXNNNNNXXXXXXNNNX00K0KXKl.. .....................''.     ..'cddddollooooooooo::oddddxxxxxxxxxxxxdxxkO00KXKOxlc;;;;;coxO
kkkkkkkd:,,,,,;;;;;:::;;;:lkx:'..''',,,:x0xcoO000KKKKKKKKKKKK00KKK0KKKKK0dc,.,kKKKXXXNNXXXXXNNWNNNNXXXXNKo'..  .':cllc:;,''........'.    ....cooolc::lloooool;;lolodddxxxddddddxxxkxxkOO0KXX0xoc::;;:cld
kkkOOOOd:,,,,,;;;;;:::;;;:lkx:..''',,,,:x0xcoO000KKK000000000000KK000KKK0kd:..o00KKKKKKKKXXNNNNNNXXXXXXNKd;.... ..,:coooc;,'.........    ...'loc::c;;lloolc:,'cdc;lddddddxxddooodxkkkxxO00KXX0xolc;::::l
OOO0O0Od:,,,,;;;;;;;;;;;;:okx:..'',,,,,:xOxcdO00000000000000000KKKK00KKK0xdl;.:O00000KKKKKKXXXNXKKXXXXNNKd;''.....';:clolc;'.........    ...,c:cl:;;;,,,:;,'',;oc';ddddddxddollc:lkxxxdxO00KXX0kdl::c:::
OOOO00Od:,,,;;;;;;;;:::;;:okx:...'',,,;:xOxcdO000KKK000K000000KKKKK0KKKK0xdxd;,x0OOOOOOOO0KKXXXXKkodxO0Odc'.......,;,,:cc:,.........     ...':oxkx;'''''''...',;:..lddddddddodo:;okkxxdxk0000XXKkdoccc::
000000koc;',;;;;;;;;:::;;:okx;...'',,,,:dOxcoO000000000K000000KKXKKKKKKXKOxkkc,d0OOOOkkkO0KXNNNNOc,,,:l:'..  .....';,..'''..........   .....oxxKNXo.';'.......,,..;dxdddddolc:;;lxkkxxdxxk0000KXKOxolc::
0000O0Oo:c,',;;;;;;;::;;;:oOx;..'',,,,,cxOxcok00000OO0000000OO0KKKKKKKKKKKOxdc,oOOOOOO000KXNNNNNX00OOxc'...........','.. ...........  .....lOOXWW0c..,,...  ...'.,oxdddoc;,,,;codxxxxxxxdxxO000KXKkdolc:
00OO00Oo;::,',;;;;;;:;;;;:oOd;..'',,,,,cx0xcoOO0000O00000000O00KKKKKKKKKKKKko;'ckOOOO0KXXXNNNNK0Okkkxdl:,...........''..  .........   ....:OO0KKO:...',..  .   ...:lc:,,;;cldddddxxxxxddddxxO00KXXKOxol:
0OOO00Od:,;:,',;;,,;:;;:::okd;.''',,,,;cxOdcoOOO00000000000000KXXXKKKKKKKXX0l'.;dkkkOO0KXKOOxollodooolcclc,.     .................   ....'dkOko:,.  .,,.    .. ......,codxxxxxxxxxxxxxddxxxxxk000KXX0xol
0OOO000xc;,,:,.',;;;;;;;:coxkdoolloddoddxkdldkOOO0000000000000KXXXXXXXKKKKKKo'.'lxxxxdkOkl;;:cdxkkxxxkkkkxl;..      ..............   .. .:oll;'...  .;'      ....''..'ldxxxdddxxxxxxxxxxxdxxxxO000KXX0kd
OkOO000Oo;,',;,.';::::;:lddxO00OkkO00O0OxooxO0000000000000000KKXXXXXXKKKK00Ol. .:lloolll:,,;:oxdoc::;;:;,''......      ...........      .;'...... ..;,.     .........;oddxddddxxxxxxxdddddddxxxk000KXX0k
kkkOOOOOd:,,'',,..,::;;:lddoooolcclllllc::lxO000000000000000KKKKKKKKKKK00kxl;.  .;:ccc:;,,,:oxxxkOOkkxdollc;,......     .........      .'.........;;,.    ..'.. ...'cdddddddddxxxxxdddddddxxxxxxxk00KXXK
kkkkOOOko:;,'.'',..';:::lddool::;,,;;;;;;:lxO000000OO0000000KKXKKKK00OOkxoooc.  .';;;;;,'';dKXXKK0Oxxdddddoc;'......       .....      ..........'cc.     .','.. ...cxdodddddddddddddddddddxkkxxdddkO0KKX
OOOOOOOko:;;,'.',;...;::ldxkOOkxdooodxddddxOOOO000OOOOO00000KKKK00OOOO000OO0k;   .''.',;;;cx00O0Oko:;,'',,''........         .       .'... .. .':;.  .. ..''......,dddddddodddddddddddddddxkkxxddddxO00K
00OOOOOko:;;,'...,,'..,:oxkOOOOkkxxxkOOkxxkO00000000OO000000KKKK00OOOO00O0O00o.    ....,,;:lxOkkxdl;,'..............                .,;'......';;.  .........''...:dxddxddddddddddddddddddxxxxxxddddxk00
0000000Oo:;;;,'...','..,cokO0OOOkkxxkOOOkkOO0000000000000000KKKK0000OOOOO000Ox;.     ...',,,:loooll:;,'...........                .,,,;;,....,;,.  ........',,'...;loddddddddddddddddxddddxxxdddddddddxO
00K0000Odc:;,;'....',,..'lkOOOOkkkxxkOkxxkOOOOOOO0000O000OOO0KKKK00OOOOOO0000Oo,...    ......',;;;,'''...........                .:lc:::::;,;;,.  ..........''',;;:coddxdddddddddddddddddddxxdddddddoodx
00000000OOxc,,,'....','...oO000OOOkxkkdl:coxkO000000000000000KKKK0OOkkOOOOOOOko;';:,.   ................. .......               .,loc;;;cc::;,..  .......',;:loddddddddddddddddddddddddddddxxxdddddddddd
OOOO00000KOl;,,'.....','...cO0000OkkOOoc:'..,cx00000000000000KKKK00OOOOOOOOOOko;,cddl,...................    .               ...;lool;;:cc;;,'..  . ..,:loddddddddoooooddddddddddddddddddddddddddddddddd
OOOO00000KOl;,,'.......,'...;k0000OkO0xc:l;...'ckO00000000KKKKKKKK0OOOOOOOOOOkkdlldxdoc;...                               .....,;cooc;;cc:;,,'......':lllodddoodddddddddddddddoddddddddddddddxxxdddodddd
0000000K00Ol;,,,''......,,...'d0000OO0o,,:ll,...,dO0000000KKKKXXKKOOOOOOOOOOkkxxoodkkkkdc;'.                             .....':oolc::c:;,,,,.......''',,;::clodddddddoddoooooooddddddxddddxxxxxxddooooo
000000KK000d:,,,,'.......',....lOK0O00d;'',cl:'...lOKKK000KK0OO00000O00OOOkkkdooccokOOOOkdl;'..                        .......';ll::::c:,'''.....''''.,:ccc:;;:loollooooooooddoodddddddddxxxxkkxdddddooo
kkO00KK0000k:,,,,''.......,;.. .:k0O00ko:,'',:c,...;o0KK00K0d:;cclddkOOkxoccl:,,,;oOOO00Okxol:,......           ..............'',::;;,cl;'.....'.....,,'.',,;;,,,;::ccclloloddoodddddddxxxxxxxxxdddddooo
kkkO0000OO0kc,,,,'''.......',.. .,d0kl:;:c;'..;:,....,lOK000xc;''',;ccc:::;;:c:::cdO0000OOkkkxl;'..............................''',,',:;;,',,',,,''''..     ....',,,;;::coooooooddddddddxxxxxxxxddddoooo
OOOOO000OOOxc,,,''''.........'..  ':;';;,;:'...,;,.....,okkxc:oc'....'',;;;;;:::clxO0OOkdollc:,..........                 ......',;:clolclllllc;;c::;'............    ...';:loodddddddddddddddddddddoooo
0OOkkOO000Ox:'',,,''..........'..    .,c:,''.';;,,'. ...':lccodd:.......',,'..,,,;:c:;,'...                              ......;looloddolc::c:;'',,''..'''.........     ..',;clddooddddddddddddooddddddd
0OOkxkkOO0Od;'''',''..............    .;cc;,..';:,''...;:loccxxo:'''...............                                       ..';:ldxdodddl:;::;','... .......'''........  ...',:looooddddddxxxxxdddddddddo
dxxxdddxdoc:;,'.''''........ ...'..    .;ccl:...,;,',,;ldoddoxkl;'''...'....                                             .',',cddoodxoc:;:;'',;'.   .   ...........',;;;;;,..':ldddddxdxxxxxxxxddxxxddoo
;cllccccc:;::c:,'................''.    .;;::;...''.,codxddkxxko:;'...........                                            ...;dxdxdoc;;:::;'.',.....      ..........':lodxo:;:;cooooodxxxxxxxxxddddxdddo
::ccllcllcccc::c:,.......... ... .'.     .'',c:..,;:lodxkxdxkxdol;'............                                          ..;;:llllc:::;;::;;;;,....          ......',coddolclolcclooodxxxxxxxxxddddddddl
lclooolcccclllccll:,.......... ..  ...    .'':c,..;oddxddkxdkkolc;'.............                                       ...';,,;;,;c;;;;;,'',''...             .....':cclllllllllc::loodxxdddddddoodddddl
oooooloolllllodddoclc,............ ....    .'';:'.'cddddodxdoool::;.............                                      .',';:;;,';;'';:;'.','....                    .,;cccccccccc:,;cloddddddoooodxxxddc
dddollloollloddxdolll::,........... .',.    ..,;;'.'codxxxdol:ll:,,.............                                     .;;;;::;'.''..,:,'.........                      ..,;:cccclc;'.':oddddddddoodkkkkdl
oddollodooooodxxxddolllll;'...........''.    .';:;';clodddxdlcod:,'..............                                    ,ccc,,;,'..........  ......                     ..''',,,,;;;'...',::cloddoodxkOOko:
ooooooooodoooodxxxolodollcc;'......... .;.    .,::'.,:llloxdlcloc;. .............                                   'lllc;;:;,,'..   .       .                      ....''''...........''',:oooodddxkxl,
kxxxxxxdddddddxddddoddddollll:,'.........'.    .,:'.;'..',:ooc::,.  ..............                                 .:oc:;,,;'.....                               .....''.......';;,,'....''....'coooodo:
ccllooodxxdddxkxxxdddddxxdoodl:;,......  .'.    .;,....,:,,clc:;,.  ..............                                .;ccc:;..'                                   ....''......',,'...              ;ooodddl
 .......',,,;:c:::coodxxxxdodlcc:,,'.......,.    .'..';:c:ccllllc,   .............                              .,coc::,'.            ....                     ..'......''''..   ..............',;,:oddl
              .....'',:::;,;looooooc;,......'.    .'';cc:;clccc::'   ..............                            ,:lolc;,.           ..  .                      .... ...''..   .......'....',''.......:lol
                    .. ......',;ccccll:,.....,.   .'';c:;,:;::,,,.   ..'','........                           'ccc:::,..      ....''.                            ..''...   ..'''''...',,,...   ...'...:c
..                       .   ........',,'''.','.   .',::;;,;oc''.    .'',,,''......                          .:l,''',.       ...'''.                           ......   ....,,,'.',;:;...........    .,c
,....                                .....''''..   .'',:c;,,,''.     .',;;;,,'.....                         .,cc.'...       .''''..                          .....   ..,,''.....';:,.......          .,c
xo:'.                              .......','.     .',''::,,;,'.    .',,;;;,,,'....          ............   .,....        ......                     .... ......    .,,,,;;;,.,cc,. .'..             .:c
codo:;..                   .................     ..',,,..,;,..     .,;;;;;,,,,,'....     ....'',,,,,,,'......'..        ....                 .......''.. .,'..     .,,',,,;,,cl;. .''.               'c:
...',:::'.          ........''...........     .'..,;,,'...,,.......,;;;;;;,,,,,,'... .......'',,,,'''',,'''....         .     ....          ......',.. ..,'.       .,,,..,,;c:. .',.                .,c:
,,'....,;,.  .............'''''.........      .'..,,,'''....','.,,,;;;,;;,,,;;;,,','...''....'''''''''',','........         ..',,,..       ......''.   .,.        .;,,'',;:c,  .;'                  .;lc
olccc,............'''''..''''''......        .''.';,'',,'...''.,;:::;,,;;,,;;,;,,;;'.''''''...'...''''',,,''',,'....  .........',;,..   .......',.  ....       . .,:;;;;:c:. .',.                   .,lc
xxooo:...'',''''''''''''''.'''.....          .;;,,;,',;. .,,...;::c:;,,,;;;;;;;,,;,...........'...'''',,,,;;,','. ..  .',''''..',;;,,. .'....''.. ....       ... .',;:::c;. .,'.                 .....:c
Okddo:...,,,,'''',,,,'''''........           '::;,'',;.   .'...'cllcc::;;;;;;;;,;;'...........''',,,,;;;;'..........   .,,.'''..',;;,'. ....''.  .''.           ..,:c:cc'  .;;.      .. .         .. .'c
Okxdo:'.',,,,,,,,,,,''''........            .;::,',;;.   ..'''..'coollc::::::;;;;,'...........',,,;:::;,.   ..'....     .;,''''..',:;;,. ..,,.  ...        ......,;::c:.  .;;.   .  .  ..  .      .. ..:
OOxxd:'',;,,,,,',''''..''.....             .,:;,,,:;.   .',,,''..':ooccc::clc:;:;'.'...'..',,,,;::;'..     ..''.     ...,,;;,',,'',;cc;,. .,.  ...         .'',;;;:;;'. ..,:.   ..... .....       .....,
0Okkd:,,;;,,,,'''''''.......              .;:,,,;:,.  ..',,;,,'''.':oollllccc:::;...'.''',,;::;,,..     ........    .''','';:,';,,;;:cc;;,.  ..'.              ....    ...;,   .. ..... ... ..   .......
0OOOxc,;;;,,,'''''''......               .,;,',::.  ...',,;,,,'';,..;ldoolclc:cl;..',,;;:::;,...     ..''..  ...'...',,'''.':c,,::;;:cc:,;,. ...           ......  . ....,;.   ................   ......
0OOOd:,,,,,,'''''''.....                .','';;;. ..',,,,,,,,,..';,..;lodollccll,',;::cll:'...    .,;,'.     ':c:..,:::,'''.'cc,;c:;,;;;,','.            ..'..........''','    .......... ..............
OOOOo;,,''''.....''...                  .,'',;'....',,,,,,,,,,.,;,,;'',:dxdooxd:,;:cooc;..     ..',..     ..,:cc:..:c::;,,'''':c;,:;,,''.'.',.        ...'''..''.... ...''.   ..........................
OOOxc,,''''....''...                   ....',...''',,,,,,,;;;',;;;;,;'.':dkdx0d::cc;'..      .....      .,;::c:::,,cc::;,,'''.';c;,,;,.......''.    .  .''',''''...  ..'..    ......,...................
00kl,'''''...''..               ..    .;;..,,'.',,,,,,,,,;:::;::,;;,,;'..;okO0xc:,..      .'...      ..,::;;:c::;;:::;,,,,,''''.,:,'','...'',,,'.......... ...''........     ......',......''... .......
0Oo;'''''''''...               ..     .;:;...,,',,;;;;;,,;:ccccc::::;,;,'.'o0x;..      ..''.      .,:cc::;;::::::::;,,,,,,,,,'...,;,.....,,,'....'.........                  ........',..........  .....
0x:,'''''''.....              ...     .,;:'.';,.,::;,,,;:ccccclllllcc;,;,'.';.      .''...      .,:odolc:;;;;;:;;;;,'.'''''''..',,'...,,,'.....'..  .........                .''......'.....',.... ..''.
k:,'''''...',,.            .....      .;;'.':l:',;,',,,,::::cccccllcc:;,:;'..    .',,..       .,lollllcc:;,,,;;,,,,'''''''....';,.  .,,.........     .....  ..             ....'...'........''..    ....
c,''''...',;,'           ......      .',,.,::lc,,,'',''',,,,;:::;:::;::;;::,.  .',,.         .,::c:::::;;;,,,,,,,,,'''''';;'..''.  ..........                               .'..'..',;'..','''.. ..  ...
,','...',,'..         .........      ','..;::cc;,;,,'..''''',,,;;,,,,;;;;,:l:,,'.           ..,,'''',,,,;;,,,,,,''''''''','.','.   .......                          ..    ...'...,;;,,',,,;,''....    ..
,''''.....           ..........     .:,'..,:;cl:,,,,,''''''',,,,''',,,,,,,,::..             .'''''''''''','''''''...'''''...,'.  ...'.                              ..  . .'....'::,,;;::;,''''''...   .
..,:;.........................     .;:,...,::cc:'.',,,'''''''''''',;;;;;,;;,..             ..'''''''''..''..'''.......',...'..  .....                                  ........;c:,:lc;;;,'...'',''..   
...',,;:::;;,,,'''...........      ,c;'...',;;::,,;;''''''',,,,,,;;;;;;;;;;;;,.           .,,.''''''''.....''''....'..''.....  ..''.                                   ......':ol;;clc,''''..'',''....  
doxkOOOOOkkdc,'',,''.....         .:;,,. ..,,',:;''''''''',,,,,;;;;,;;;;;;,;:;;,.       .,:;;,,'',',,''''''''''.............   ....                                    ...':okOkolol:,,,,,,;,,,'''......
0kxxdoolc::;,.....           .    ';',,. .',,',;;,''''',;;;;;;:::;;:::::c:::::::;.    ..;::;;;;;;;;;;,,,,,''''''...........    ...                                     ..'cx0KK0Okdoc;:c:;,,;,'''.......
,'...............          ..    .''';'..,'.;;,:c;,,,',,,;;;;::::::cccccclc:::::::'...,:ccccccc::::::;;;,,,'''......... .'.    .                                       ...,lk00000Okxdolc:,''''.........
...........'',,,'....    ....    ...,;......;c;:l:,;:,,;;;;::ccc::cccc:;;:c::::::::;,:cclloolc:;,,,,,'.....''''.........,'                                             .....;ok000OOkkxdlc;'..''''''''''
..........',,;,,'..'........        .'..  ..':;,;'';:,,,,,,;:ccllllcc:;,;:ccllllllllclcc::;'''............''''''','','.,,. ..',.                                        .....':xOOOkkkxdolc:,',;;;;,,,,,
..........'',,,,,...........        ...  ...,;,,;,.,,''',,'',;:cllllccc::::loolcc:;,,,''',,;;;;;;,''''''''''''''''',,.',. ..',,.                                        ......':okkkkkxxdlcc:::::::;,,,,
.....'''......'''..........          .   ..'.',,::',;,,,,,'.'',;::::cclccccclc:;,,,'',::cllllcc:;,,,''',,,'',,'''''''.',. .'',.      ..                                  ......'cdkOOkkxxolcclolc:;;,,,,
.',;;,,,,''...............           .   .''.';;:c,',,,;;,,,,',;;;;,,;;;;:::ccccoooc:lllllll:;;;,,;,''',,''''''''''...,'  .,,..   ....                                   .......;okOOkkxxdoollodollcccc:
...',;;;;,,,,''...........          ...  .....;:cc,,;;;;;,,,,,,;;;;,,,,,,,,,,;:cllcc:::::;;::;;;,;;,'',,,'',''''''...';. .',,.     ....                                  .......;okOOOkxxdddoooddddoddol
......',;;;;;,,,'''.''...           .........,:::;',:,,;;,,,;;;,,;,,,,,,,,,,,,;;:;,,,,;:::::::;,;;;,'',,,,''''',,....,;. .','.    ....                                   .......;okOOkkxxddddoodddooddol
	</p>
	<div id="termDiv"></div>
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
	this.termOpen();
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
	<link rel="stylesheet" type="text/css" href="../wp-content/plugins/SRBC/admin/camper_management.css">
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
			Camp: <input type="text" name="name">
			Start Date:<input  type="date" name="start_date">
			End Date: <input  type="date" name="end_date"><br>
			Dropoff Time:<input  type="text" name="dropoff_time" value="5:00pm">
			Pickup Time: <input  type="text" name="pickup_time" value="10:00am"><br>
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
			<button onclick="addNewCamp()" class="tablink" >Create New Camp</button>
		</div> 
    </div>
	<?php modalSetup() ?>
	<div id="snackbar"></div>
	<div id="error"></div>
	<script src="../wp-content/plugins/SRBC/admin/camps_management.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/camper_modal.js"></script>
	<script src="../wp-content/plugins/SRBC/admin/sortTable.js"></script>
    <?php
}

//Helper function for srbc_camps_management
//Lists all the camps for a certain area in a table with appropriate delete buttons and information
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
										
		echo '<tr onclick="openCampModal(' . $camp->camp_id . ')"><td>' . $camp->name;
		echo "</td><td>" . $camp->start_date . "</td>";
		echo "<td>" . $male_registered . "</td>";
		echo "<td>" . $female_registered . "</td>";
		echo "<td>" . ($male_registered + $female_registered) . "/" . $camp->overall_size . "</td>"; 
		echo "<td>" . $waitlistsize ."/" . $camp->waiting_list_size . "</td>";
		if ($area == "Lakeside")
			echo "<td>" . $horseWaitlist ."/" . $camp->horse_waiting_list_size . "</td>";
		echo '<td><button class="big_button" style="padding:2px;" onclick="deleteCamp(event,' . $camp->camp_id . ',\'' . wp_create_nonce('delete-camp_'.$camp->camp_id) . '\');">Delete</button></td></tr>';
	}
	echo "</table> ";
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
	Note: Unless a report has a waitlist column all reports exclude waitlisted campers... Hopefully 😬<br>
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
		</select><button onclick="generateReport('area_report')">Area Report</button><br>
		
<br>
		<h2 style="display:inline">General Reports</h2> <div class="tooltip">?
			<span class="tooltiptext">These general reports can also be narrowed to a specific camp or program area, except for camp numbers</span>
		</div> 
		<br>
		<button onclick="generateReport('scholarships')">Scholarships </button>
		<button onclick="generateReport('discounts')">Discounts </button>
		<button onclick="generateReport('emails')">Emails </button>
		<button onclick="generateReport('camp_numbers')">Camp Numbers </button>
		<button onclick="generateReport('all_camp_totals')">All Camp Totals</button>
		<button onclick="generateReport('not_checked_in')">Camper not checked in</button>
		<button onclick="generateReport('balance_due')">Balance Due</button>
		<button onclick="generateReport('overpaid')">Overpaid</button>
		<button onclick="generateReport('refunds')">Refund Report</button>
		<button onclick="generateReport('balance_due_emails')">Balance Due Emails</button>
		<button onclick="generateReport('balance_due_addresses')">Balance Due Addresses</button>
		<button onclick="generateReport('inactive_registrations')">Inactive Registrations</button>
		<button onclick="generateReport('no_health_form')">No Heatlh Form</button>
		<button onclick="generateReport('kids_mailingList')">Kids Mailing List for Merge</button><br>
		<hr>
		<h2 style="display:inline;">Date specific reports</h2> <div class="tooltip">?
			<span class="tooltiptext">Please choose the same date twice if you are doing a report for all camps starting on that day.
		For buslists pick the starting date(Earlier starting date first)for two camps as as there will be some campers going both ways</span>
		</div> 
		Camp Start Date <input id="start_date" type="date">
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
		<button onclick="generateReport('healthForms')">Health Forms</button>


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