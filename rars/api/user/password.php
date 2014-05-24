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
 
class UserPassword extends RazorAPI
{
	private $resource = null;

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	// fetch logged in user details if logged in, always chuck same error back regardless
	public function post($data)
	{
		// check present, token ok, password and password confirm ok
		if (!isset($data["token"], $data["passwords"]["password"], $data["passwords"]["repeat_password"])) $this->response("Bad data", null, 400);
		if (empty($data["token"]) || strlen($data["token"]) < 20) $this->response("Bad data", null, 400);
		if (empty($data["passwords"]["password"]) || empty($data["passwords"]["repeat_password"]) || $data["passwords"]["password"] !== $data["passwords"]["repeat_password"]) $this->response("Bad data", null, 400);

		$token_data = explode("_", $data["token"]);
		if (count($token_data) != 2 || empty($token_data[0]) || empty($token_data[1])) $this->response("Bad data", null, 400);

		/* data present and pre check good, lets do a user search and check */

		// try find user
		$db = new RazorDB();
		$db->connect("user");
		$search = array("column" => "id", "value" => (int) $token_data[1]);
		$user = $db->get_rows($search);
		$db->disconnect(); 

		// no valid user found
		if ($user["count"] != 1) $this->response("Bad data", null, 400);

		$user = $user["result"][0];

		// check token
		if (empty($user["reminder_token"]) || $token_data[0] != $user["reminder_token"] || $user["reminder_time"] + 3600 < time()) $this->response("Bad data", null, 400);

		/* user ok, token ok, lets change password */

		$password = RazorAPI::create_hash($data["passwords"]["password"]);

		// set new reminder
		$db->connect("user");
		$search = array("column" => "id", "value" => $user["id"]);
		$row = array(
			"password" => $password,
			"reminder_token" => ""
		);
		$db->edit_rows($search, $row);
		$db->disconnect(); 

		$this->response("success", "json");
	}
}