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
	
}
?>
