<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
class UserList extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($id)
	{
		if ((int) $this->check_access() < 10) $this->response(null, null, 401);

		$db = new RazorDB();

		$db->connect("user");

		// set options
		$options = array(
			"filter" => array(
				"id", 
				"name", 
				"email_address", 
				"access_level", 
				"active", 
				"ip_address", 
				"last_logged_in"
			)
		);

		$search = array("column" => "id", "value" => null, "not" => true);

		$user = $db->get_rows($search, $options);
		$db->disconnect(); 
		
		// return the basic user details
		$this->response(array("users" => $user["result"]), "json");
	}
}

/* EOF */