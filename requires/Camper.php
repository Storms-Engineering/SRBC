<?php
class Camper
{
	//Check if this camper already exists in our database
	//Returns null if that camper doesn't exist and returns the camper if they do exist
	public static function doesCamperExist($camper_first_name,$camper_last_name,$birthday)
	{
		global $wpdb;
		$camper = $wpdb->get_row($wpdb->prepare("SELECT * FROM srbc_campers WHERE camper_first_name=%s AND camper_last_name=%s AND birthday=%s",
		$camper_first_name,$camper_last_name,$birthday));
		return $camper;
	}
	
	public static function createCamper($info)
	{
		global $wpdb;
		$camper = Camper::doesCamperExist($info["camper_first_name"],$info["camper_last_name"],$info["birthday"]);
		
		//Calculate the campers age
		$d1 = new DateTime(date("Y/m/d"));
		$d2 = new DateTime($info["birthday"]);
		$diff = $d2->diff($d1);
		$age = $diff->y;
		
		$camper_id = 0;
		if ($camper!=NULL)
		{
			//Camper already exists so use their ID
			$camper_id = $camper->camper_id;
		}
		//Replace existing camper information or create a new one if camper doesn't exist
		$wpdb->replace( 
		'srbc_campers', 
		array( 
			'camper_id' =>$camper_id,
			'camper_first_name' => $info["camper_first_name"], 
			'camper_last_name' => $info["camper_last_name"],
			'birthday' => $info["birthday"],
			'age' => $age,
			'gender' => $info["gender"],
			'grade' => $info["grade"],
			'parent_first_name' => $info["parent_first_name"],
			'parent_last_name' => $info["parent_last_name"],
			'email' => $info["email"],
			'phone' => $info["phone"],
			'phone2' => $info["phone2"],
			'address' => $info["address"],
			'city' => $info["city"],
			'state' => $info["state"],
			'zipcode' => $info["zipcode"]
		), 
		array( 
			'%d',
			'%s', 
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d'		
			
		) 
		);
		$camper_id = $wpdb->insert_id;
		
		return $camper_id;
	}
}
?>