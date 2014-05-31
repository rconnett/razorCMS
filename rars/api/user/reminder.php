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
 
class UserReminder extends RazorAPI
{
	private $resource = null;

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	// request password reset
	public function post($data)
	{
		// no email
		if (empty($data["email"])) $this->response("User not found", "json", 404);

		// try find user
		$db = new RazorDB();
		$db->connect("user");
		$options = array("amount" => 1);
		$search = array("column" => "email_address", "value" => $data["email"]);
		$user = $db->get_rows($search);
		$db->disconnect(); 

		// check for match
		if ($user["count"] != 1) $this->response("User not found", "json", 404);

		// check attempts
		$user = $user["result"][0];
		if ($user["reminder_time"] > time() - 600) $this->response("Only one password request allowed per hour", "json", 401);

		/* Match found, attempts good, carry on */

		// now we will store token and send it via email
		$user_agent = $_SERVER["HTTP_USER_AGENT"];
		$ip_address = $_SERVER["REMOTE_ADDR"];
		$pass_hash = $user["password"];
		$reminder_time = time();
		$reminder_token = sha1($reminder_time.$user_agent.$ip_address.$pass_hash);

		// set new reminder
		$db->connect("user");
		$search = array("column" => "id", "value" => $user["id"]);
		$row = array(
			"reminder_token" => $reminder_token,
			"reminder_time" => $reminder_time
		);
		$db->edit_rows($search, $row);
		$db->disconnect(); 

		// email user pasword reset email
		$server_email = str_replace("www.", "", $_SERVER["SERVER_NAME"]);
		$reminder_link = RAZOR_BASE_URL."login#/password-reset/{$reminder_token}_{$user["id"]}";
		$message = <<<EOT
<html>
<head>
	<title>razorCMS - Password Reset</title>
</head>
<body>
	<h1>Reset your razorCMS Account Password</h1>
	<p>This email address has requested a password reset for the account on razorCMS ({$_SERVER["SERVER_NAME"]}). If this was not you that requested this, please ignore this email and the password reset will expire in 1 hour.</p>
	<p>If you did request this, then you can reset your password using the link below.</p>
	<a href="{$reminder_link}">$reminder_link</a>
</body>
</html>
EOT;
		$this->email("no-reply@{$server_email}", $user["email_address"], "razorCMS Account Password Reset", $message);

		$this->response("success", "json");
	}
}