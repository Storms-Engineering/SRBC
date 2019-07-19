<?php 
//This class is related to all queries for searching for campers and generating tables.
class CamperSearch
{
	//Search for either a camper name or last name or a combined query of first name last name
	public static function searchParentAndCamper($query)
	{
		global $wpdb;
		$name = explode(" ",$query);
		$campers = null;
		if (count($name) == 2)
		{
			$fname = $name[0];
			$campers = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM srbc_campers WHERE (camper_first_name 
				LIKE %s AND camper_last_name LIKE %s )OR (parent_first_name LIKE %s AND parent_last_name LIKE %s)
				ORDER BY camper_id ASC", 
				$name[0]."%",$name[1]."%",$name[0]."%",$name[1]."%"));
		}
		else
		{
			$name = $name[0];
			$campers = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM srbc_campers WHERE camper_first_name 
				LIKE %s OR camper_last_name LIKE %s OR parent_first_name LIKE %s OR parent_last_name LIKE %s
				ORDER BY camper_id ASC", 
				$name."%",$name."%",$name."%",$name."%"));
		}
		return $campers;
	}
	
	//Gets campers using a general query that searches through quite a bit of data
	public static function getCampers()
	{
		throw new Exception("Method is not yet implemented");
	}
	
	public static function getCampersByCampID($camp_id)
	{
		global $wpdb;
		$campers = $wpdb->get_results(
			$wpdb->prepare( "SELECT *
							FROM ((" . $GLOBALS['srbc_registration'] . " 
							INNER JOIN " . $GLOBALS['srbc_camps'] . " ON " . $GLOBALS['srbc_registration'] . '.camp_id= ' . $GLOBALS['srbc_camps'] . '.camp_id)
							INNER JOIN srbc_campers ON ' . $GLOBALS['srbc_registration'] . ".camper_id=srbc_campers.camper_id)
							WHERE " . $GLOBALS['srbc_camps'] . ".camp_id=%d",$camp_id));	
		return $campers;
	}
	
}
?>
