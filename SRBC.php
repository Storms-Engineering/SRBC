<?php
/*
Plugin Name: SRBC
Description: Solid Rock Bible Camp Plugin for Registration and Displaying Users
Version: 0.8.09
*/
//Require
require_once "admin/shortcodes.php";
require_once "admin/menus.php";
require_once "functions.php";

//Initial install code, setting up database 
function srbc_install() {

	//Create a table for campers
    $sql = "CREATE TABLE IF NOT EXISTS srbc_campers (
		camper_id INT AUTO_INCREMENT,
		camper_first_name TINYTEXT NOT NULL,
		camper_last_name TINYTEXT NOT NULL,
		parent_first_name TINYTEXT NOT NULL,
		parent_last_name TINYTEXT NOT NULL,
		email TEXT NOT NULL,
		birthday DATE,
		address TEXT NOT NULL,
		city TINYTEXT NOT NULL,
		state TINYTEXT NOT NULL,
		zipcode MEDIUMINT NOT NULL,
		phone TINYTEXT NOT NULL,
		phone2 TINYTEXT,
		grade TINYTEXT NOT NULL,
		gender TINYTEXT NOT NULL,
		age TINYINT NOT NULL,
		notes TEXT,
		PRIMARY KEY (camper_id)
		)  ENGINE=INNODB;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	//Create a table for Camps
    $sql = "CREATE TABLE IF NOT EXISTS srbc_camps (
		camp_id INT AUTO_INCREMENT,
		area TINYTEXT NOT NULL,
		name TINYTEXT NOT NULL,
		description TEXT NOT NULL,
		start_date DATE,
		end_date DATE,
		dropoff_time TINYTEXT NOT NULL,
		pickup_time TINYTEXT NOT NULL,
		cost SMALLINT NOT NULL,
		horse_cost SMALLINT,
		horse_opt_cost TINYINT NOT NULL,
		horse_list_size TINYINT NOT NULL,
		horse_waiting_list_size TINYINT NOT NULL,
		waiting_list_size SMALLINT NOT NULL,
		boy_registration_size SMALLINT NOT NULL,
		girl_registration_size SMALLINT NOT NULL,
		overall_size SMALLINT NOT NULL,
		grade_range TINYTEXT NOT NULL,
		closed_to_registrations TINYINT,
		PRIMARY KEY (camp_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	//Create a registration database that keeps track of individual registrations.
	//Waitlist is 0 if they are not on the waitlist and a 1 if they are on the waitlist
	//Horse_waiting list is the same as above ^
    $sql = "CREATE TABLE IF NOT EXISTS srbc_registration (
		registration_id INT AUTO_INCREMENT,
		camp_id INT NOT NULL,
		camper_id INT NOT NULL,
		date TINYTEXT NOT NULL,
		counselor TINYTEXT,
		assistant_counselor TINYTEXT,
		lodging TINYTEXT,
		horse_opt TINYINT NOT NULL,
		busride TINYTEXT NOT NULL,
		discount_type TINYTEXT,
		discount FLOAT(6,2),
		scholarship_amt FLOAT(6,2) ,
		scholarship_type TINYTEXT,
		waitlist TINYINT NOT NULL,	
		horse_waitlist TINYINT NOT NULL,
		checked_in TINYINT NOT NULL,
		health_form TINYINT NOT NULL,
		packing_list_sent TINYINT NOT NULL,
		registration_notes TEXT,
		PRIMARY KEY (registration_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	//Inactive registrations table
	$sql = "CREATE TABLE IF NOT EXISTS srbc_registration_inactive (
		registration_id INT AUTO_INCREMENT,
		camp_id INT NOT NULL,
		camper_id INT NOT NULL,
		date TINYTEXT NOT NULL,
		counselor TINYTEXT,
		assistant_counselor TINYTEXT,
		lodging TINYTEXT,
		horse_opt TINYINT NOT NULL,
		busride TINYTEXT NOT NULL,
		discount_type TINYTEXT,
		discount FLOAT(6,2),
		scholarship_amt FLOAT(6,2) ,
		scholarship_type TINYTEXT,
		waitlist TINYINT NOT NULL,	
		horse_waitlist TINYINT NOT NULL,
		checked_in TINYINT NOT NULL,
		health_form TINYINT NOT NULL,
		packing_list_sent TINYINT NOT NULL,
		registration_notes TEXT,
		PRIMARY KEY (registration_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	//Database for encrypted credit card storage
	//TODO I should consolidate these into one tracked id (registration_id) - ugh I wasn't expecting to use this so much
	$sql = "CREATE TABLE IF NOT EXISTS srbc_cc (
		cc_id INT AUTO_INCREMENT,
		data TEXT NOT NULL,
		amount FLOAT(6,2) NOT NULL,
		camper_name TINYTEXT NOT NULL,
		camp TINYTEXT NOT NULL,
		payment_date TINYTEXT NOT NULL,
		comments TINYTEXT,
		PRIMARY KEY (cc_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	
	//Database keeping track of payments
	$sql = "CREATE TABLE IF NOT EXISTS srbc_payments (
		payment_id INT AUTO_INCREMENT,
		registration_id INT NOT NULL,
		payment_type TINYTEXT NOT NULL,
		payment_amt FLOAT(6,2) NOT NULL,
		payment_date TINYTEXT,
		note TINYTEXT,
		fee_type TINYTEXT,
		registration_day TINYINT,
		entered_by TINYTEXT,
		PRIMARY KEY (payment_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	//Create application database for staff applications
	$sql = "CREATE TABLE IF NOT EXISTS srbc_staff_app (
		staff_app_id INT AUTO_INCREMENT,
		Firstname TINYTEXT,
		Middlename TINYTEXT,
		Lastname TINYTEXT,
		ssn TEXT NOT NULL,
		PRIMARY KEY (staff_app_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );

	//Create health form database
	$sql = "CREATE TABLE IF NOT EXISTS srbc_health_form (
		health_form_id INT AUTO_INCREMENT,
		camper_id INT,
		IV TEXT,
		aesKey TEXT,
		data TEXT,
		PRIMARY KEY (health_form_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'srbc_install' );

//Debugging stuff
/*register_activation_hook( __FILE__, 'my_activation_func' );
function my_activation_func() {
    file_put_contents( __DIR__ . '/my_loggg.txt', ob_get_contents() );
}*/
//Remove any things we have setup
function pluginprefix_deactivation() {
	
}
register_deactivation_hook( __FILE__, 'pluginprefix_deactivation' );
/*----------------------------------------------------------------------
MENU HOOKS
*/
//Master page
function srbc_overview()
{
    add_menu_page(
        'SRBC',
        'SRBC',
        'manage_options',
        'srbc_overview',
		'srbc_overview_page',
		plugin_dir_url(__FILE__) . 'images\SRBC-logo.png',
        50
    );
}
add_action('admin_menu', 'srbc_overview');
//Submenus

function srbc_campers()
{
    add_submenu_page(
        'srbc_overview',
        'Camper Management',
        'Camper Management',
        'manage_options',
        'camper_management',
        'srbc_camper_management'
    );
}
add_action('admin_menu', 'srbc_campers');
//Submenu Camps
function camps_list()
{
   add_submenu_page(
        'srbc_overview',
        'Camps Management',
        'Camps Management',
        'manage_options',
        'camp_management',
		'srbc_camps_management'
    );
}
add_action('admin_menu', 'camps_list');

function camps_reports()
{
   add_submenu_page(
        'srbc_overview',
        'Reports',
        'Reports',
        'manage_options',
        'camp_reports',
		'srbc_camp_reports'
    );
}
add_action('admin_menu', 'camps_reports');

function credit_cards()
{
   add_submenu_page(
        'srbc_overview',
        'Credit Cards',
        'Credit Cards',
        'manage_options',
        'credit_cards',
		'srbc_credit_cards'
    );
}
add_action('admin_menu', 'credit_cards');

function program_menu()
{
   add_submenu_page(
        'srbc_overview',
        'Camper Lodging',
        'Camper Lodging',
        'manage_options',
        'srbc_program_access',
		'srbc_program_access'
    );
}
add_action('admin_menu', 'program_menu');

function staff_app_setup()
{
   add_submenu_page(
        'srbc_overview',
        'Staff Applications',
        'Staff Applications',
        'manage_options',
        'staff_application_menu',
		'staff_application_menu'
    );
}
add_action('admin_menu', 'staff_app_setup');

function settings()
{
   add_submenu_page(
        'srbc_overview',
        'Settings',
        'Settings',
        'manage_options',
        'srbc_settings',
		'srbc_settings'
    );
}
add_action('admin_menu', 'settings');
//END MENUS

//Settings
function register_my_setting() {
    $args = array(
            'type' => 'string', 
            'default' => NULL,
            );
	register_setting( 'srbc_options_group', 'srbc_database_year', $args ); 
	register_setting( 'srbc_options_group', 'srbc_summer_camps_disable', $args ); 
} 
add_action( 'admin_init', 'register_my_setting');

//Notice for if they are currently in a different database year
function database_notice(){
	if (get_option("srbc_database_year") != "")
	{
         echo '<div class="notice notice-warning is-dismissible">
             <p>Notice you are currently using the '. get_option("srbc_database_year") .' database.</p>
         </div>';
	}
}
add_action('admin_notices', 'database_notice');

//Shortcodes
add_shortcode( 'srbc_registration', 'srbc_registration' );
add_shortcode( 'srbc_registration_complete', 'srbc_registration_complete' );
add_shortcode( 'srbc_camps', 'srbc_camps' );
add_shortcode( 'srbc_application', 'srbc_application' );
add_shortcode( 'srbc_application_complete', 'srbc_application_complete' );
add_shortcode( 'srbc_camp_search', 'srbc_camp_search' );
add_shortcode( 'srbc_contact_form_email', 'srbc_contact_form_email' );
add_shortcode( 'srbc_volunteer_contact_form_email', 'srbc_volunteer_contact_form_email' );
add_shortcode( 'srbc_health_form_generate', 'srbc_health_form_generate' );


