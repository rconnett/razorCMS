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
 
class SettingData extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	// add or update content
	public function post($data)
	{
		// login check - if fail, return no data to stop error flagging to user
		if ((int) $this->check_access() < 9) $this->response(null, null, 401);
		if (empty($data)) $this->response(null, null, 400);

		$db = new RazorDB();
		$db->connect("setting");

		if (isset($data["name"]))
		{
			$search = array("column" => "name", "value" => "name");
			$res = $db->edit_rows($search, array("value" => $data["name"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "name", "value" => (string) $data["name"], "type" => "string"));
		}
	
		if (isset($data["google_analytics_code"]))
		{
			$search = array("column" => "name", "value" => "google_analytics_code");
			$res = $db->edit_rows($search, array("value" => $data["google_analytics_code"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "google_analytics_code", "value" => (string) $data["google_analytics_code"], "type" => "string"));
		}
	
		if (isset($data["forgot_password_email"]))
		{
			$search = array("column" => "name", "value" => "forgot_password_email");
			$res = $db->edit_rows($search, array("value" => (string) $data["forgot_password_email"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "forgot_password_email", "value" => (string) $data["forgot_password_email"], "type" => "string"));
		}
	
		if (isset($data["allow_registration"]))
		{
			$search = array("column" => "name", "value" => "allow_registration");
			$res = $db->edit_rows($search, array("value" => (string) $data["allow_registration"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "allow_registration", "value" => (string) $data["allow_registration"], "type" => "bool"));
		}
	
		if (isset($data["manual_activation"]))
		{
			$search = array("column" => "name", "value" => "manual_activation");
			$res = $db->edit_rows($search, array("value" => (string) $data["manual_activation"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "manual_activation", "value" => (string) $data["manual_activation"], "type" => "bool"));
		}
	
		if (isset($data["registration_email"]))
		{
			$search = array("column" => "name", "value" => "registration_email");
			$res = $db->edit_rows($search, array("value" => (string) $data["registration_email"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "registration_email", "value" => (string) $data["registration_email"], "type" => "string"));
		}
	
		if (isset($data["activation_email"]))
		{
			$search = array("column" => "name", "value" => "activation_email");
			$res = $db->edit_rows($search, array("value" => (string) $data["activation_email"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "activation_email", "value" => (string) $data["activation_email"], "type" => "string"));
		}
	
		if (isset($data["activate_user_email"]))
		{
			$search = array("column" => "name", "value" => "activate_user_email");
			$res = $db->edit_rows($search, array("value" => (string) $data["activate_user_email"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "activate_user_email", "value" => (string) $data["activate_user_email"], "type" => "string"));
		}
	
		if (isset($data["cookie_message"]))
		{
			$search = array("column" => "name", "value" => "cookie_message");
			$res = $db->edit_rows($search, array("value" => (string) $data["cookie_message"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "cookie_message", "value" => (string) $data["cookie_message"], "type" => "string"));
		}
	
		if (isset($data["cookie_message_button"]))
		{
			$search = array("column" => "name", "value" => "cookie_message_button");
			$res = $db->edit_rows($search, array("value" => (string) $data["cookie_message_button"]));
			if ($res["count"] == 0)	$db->add_rows(array("name" => "cookie_message_button", "value" => (string) $data["cookie_message_button"], "type" => "string"));
		}

		$db->disconnect(); 
		$this->response("success", "json");
	}
}

/* EOF */