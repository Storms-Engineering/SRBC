<?php
class Camp
{
	public static function deleteCamp($campId)
	{
		global $wpdb;
		$wpdb->delete( $GLOBALS['srbc_camps'], array( 'camp_id' => $campId) );
		echo "Camp Deleted and Data Saved Sucessfully";
	}
}
?>