<?php
/**
 * Class Test-Campers
 *
 * @package SRBC
 */

/**
 * Sample test case.
 */
echo getcwd();
require_once __DIR__ . '/../requires/Camper.php';
require_once __DIR__  . '/../SRBC.php';
class CamperTest extends WP_UnitTestCase {

	private	$camper =  array(
		'camper_id' => "1",
		'camper_first_name' => "Peter", 
		'camper_last_name' => "Hawke",
		'parent_first_name' => "Malcolm",
		'parent_last_name' => "Hawke",
		'email' => "armystorms@gmail.com",
		'birthday' => "2001-01-01",
		'address' => "36251 Solid Rock Road #1",
		'city' => "Soldotna",
		'state' => "AK",
		'zipcode' => "99669",
		'phone' => "7777777",
		'phone2' => "7777777",
		'grade' => "7",
		'gender' => "male"	
		);
	/**
	 * Call install plugin.
	 */
	public function install()
	{
		srbc_install();
	}
	/**
	 * Tests creating a camper
	 */
	public function test_camper_creation() 
	{
		//Create databases
		$this->install();
		$camper_id = Camper::createCamper($this->camper);
		
		global $wpdb;
		$camperdb =  $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_campers WHERE camper_id=%d",	$camper_id));
		
		//Trim up camperDB
		unset($camperdb->age);
		unset($camperdb->notes);
		$this->assertEquals($this->camper,(array)$camperdb);
	}

	/**
	 * Test that the software actually catches when a camper resigns up and udpates information
	 */
	public function test_camper_duplicates() 
	{
		global $wpdb;
		$camper =  array(
		'camper_id' => "1",
		'camper_first_name' => "Peter", 
		'camper_last_name' => "Hawke",
		'parent_first_name' => "Malcolm",
		'parent_last_name' => "Hawke",
		'email' => "armystorms@gmail.com",
		'birthday' => "2001-01-01",
		'address' => "Somewhere",
		'city' => "Soldotna",
		'state' => "AK",
		'zipcode' => "99669",
		'phone' => "7777777",
		'phone2' => "7777777",
		'grade' => "12",
		'gender' => "male"	
		);
		
		//$camper_id = Camper::createCamper($this->camper);
		
		$camper_id = Camper::createCamper($camper);
		
		$camperdb = $wpdb->get_results("SELECT * FROM srbc_campers WHERE camper_first_name='Peter' AND camper_last_name='Hawke' AND birthday='2001-01-01'");
		//Make sure there isn't 2 results
		$this->assertTrue(count($camperdb) == 1);
		//Check that we updated the address
		$this->assertTrue($camper['address'] == $camperdb[0]->address);
	}
}
