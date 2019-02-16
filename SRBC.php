<?php
/*
Plugin Name: SRBC
Description: Solid Rock Bible Camp Plugin for Registration and Displaying Users
Version: 0.5
*/
//Require
require("admin/shortcodes.php");
require("admin/menus.php");
require("functions.php");

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
		camp_description TEXT NOT NULL,
		start_date DATE,
		end_date DATE,
		cost SMALLINT NOT NULL,
		horse_opt TINYINT NOT NULL,
		waiting_list_size SMALLINT NOT NULL,
		boy_registration_size SMALLINT NOT NULL,
		girl_registration_size SMALLINT NOT NULL,
		overall_size SMALLINT NOT NULL,
		grade_range TINYTEXT NOT NULL,
		PRIMARY KEY (camp_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	//Create a registration database that keeps track of individual registrations.
	//Waitlist is 0 if they are not on the waitlist.  It will be an increasing number as each registration gets added to the
	//Database that is on the waitlist for that camp.
    $sql = "CREATE TABLE IF NOT EXISTS srbc_registration (
		registration_id INT AUTO_INCREMENT,
		camp_id INT NOT NULL,
		camper_id INT NOT NULL,
		counselor TINYTEXT,
		cabin TINYTEXT,
		horse_opt TINYINT NOT NULL,
		busride TINYTEXT NOT NULL,
		discount SMALLINT,
		scholarship_amt SMALLINT ,
		scholarship_type TINYTEXT,
		payed_check SMALLINT,
		payed_cash SMALLINT,
		payed_card SMALLINT,
		amount_due SMALLINT NOT NULL,
		waitlist TINYTEXT NOT NULL,		
		checked_in TINYINT NOT NULL,
		PRIMARY KEY (registration_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	//Database for encrypted credit card storage
	$sql = "CREATE TABLE IF NOT EXISTS srbc_cc (
		cc_id INT AUTO_INCREMENT,
		data TEXT NOT NULL,
		amount SMALLINT NOT NULL,
		camper_name TINYTEXT NOT NULL,
		camp TINYTEXT NOT NULL,
		payment_date TINYTEXT NOT NULL,
		PRIMARY KEY (cc_id)
		)  ENGINE=INNODB;";

	dbDelta( $sql );
	
	
	//Database for encrypted cc
	$sql = "CREATE TABLE IF NOT EXISTS srbc_payments (
		payment_id INT AUTO_INCREMENT,
		camp_id INT NOT NULL,
		camper_id INT NOT NULL,
		payment_type TINYTEXT NOT NULL,
		payment_amt SMALLINT NOT NULL,
		payment_date TINYTEXT,
		note TINYTEXT,
		PRIMARY KEY (payment_id)
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
		plugin_dir_url(__FILE__) . 'images\SRBC-logo.jpg',
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

//Shortcodes
add_shortcode( 'srbc_registration', 'srbc_registration' );
add_shortcode( 'srbc_registration_complete', 'srbc_registration_complete' );
add_shortcode( 'srbc_camps', 'srbc_camps' );
add_shortcode( 'srbc_application', 'srbc_application' );
add_shortcode( 'srbc_application_complete', 'srbc_application_complete' );
add_shortcode( 'srbc_camp_search', 'srbc_camp_search' );
add_shortcode( 'srbc_contact_form_email', 'srbc_contact_form_email' );
add_shortcode( 'srbc_volunteer_contact_form_email', 'srbc_volunteer_contact_form_email' );
