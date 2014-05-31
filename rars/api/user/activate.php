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

class UserActivate extends RazorAPI
{
	private $resource = null;

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($id)
	{
		if (strlen($id) < 20) $this->response("Activation key not set",  400);

		$db = new RazorDB();
		$db->connect("user");
		$search = array("column" => "activate_token", "value" => $id);
		$user = $db->get_rows($search);
		if ($user["count"] != 1) $this->response(null, null, 409);

		// now we know token is ok, lets activate user

		// set new reminder
		$search = array("column" => "id", "value" => $user["result"][0]["id"]);
		$row = array(
			"activate_token" => null,
			"active" => true
		);
		$db->edit_rows($search, $row);
		$db->disconnect(); 

		// if all ok, redirect to login page and set activate message off
		$redirect = RAZOR_BASE_URL."login#/user-activated";
		header("Location: {$redirect}");
		exit();		
	}
}