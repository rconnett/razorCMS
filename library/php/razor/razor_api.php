<?php if (!defined("RARS_BASE_PATH") && !defined("RAZOR_BASE_PATH")) die("No direct script access to this content");

/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
// RazorAPI class
class RazorAPI
{
	private $backtrace = null;
	public $user = null;

	function __construct()
	{
		// placeholder in case we need it
	}

	public static function clean_data($data)
	{
		if (is_object($data) || is_array($data))
		{
			$data_array = array();
			foreach ($data as $key => $value)
			{
				$clean_key = preg_replace("/[|`<>?;'\"]/", '', (string) $key);
				$data_array[$clean_key] = RazorAPI::clean_data($value);
			}
			return $data_array;
		}
		elseif (is_string($data))
		{
			// we do not have to do much checking here, the db class protects itself against harmfull chars
			if (defined("RARS_CLEAN_DATA_ALLOWED_TAGS")) return strip_tags($data, RARS_CLEAN_DATA_ALLOWED_TAGS);
			else return $data;			
		}
		elseif (is_bool($data) || is_int($data) || is_float($data)) return $data;
		else return null;
	}

	// clean output data of slashes
	public static function clean_output($data)
	{
		if (is_object($data) || is_array($data))
		{
			$data_array = array();
			foreach ($data as $key => $value)
			{
				$clean_key = preg_replace("/[|`<>?;'\"]/", '', (string) $key);
				$data_array[$clean_key] = RazorAPI::clean_output($value);
			}
			return $data_array;
		}
		elseif (is_string($data)) return stripcslashes($data);
		elseif (is_bool($data) || is_int($data) || is_float($data)) return $data;
		else return null;
	}

	// function to obscure passwords //
	public static function create_hash($inText, $saltHash=NULL, $mode='sha1'){
		// check if hash function available, else fallback to sha1 //
		$hashOK = false;
		if(function_exists('hash')) {
		$hashOK = true;
		}
		// hash the text //
		if($hashOK) {
			$textHash = hash($mode, $inText);
		} else {
			$textHash = sha1($inText);
		}
		// set where salt will appear in hash //
		$saltStart = strlen($inText);
		// if no salt given create random one //
		if($saltHash == NULL) {
			if($hashOK) {
				$saltHash = hash($mode, uniqid(rand(), true));
			} else {
				$saltHash = sha1(uniqid(rand(), true));
			}
		}
		// add salt into text hash at pass length position and hash it //
		if($saltStart > 0 && $saltStart < strlen($saltHash)) {
			$textHashStart = substr($textHash,0,$saltStart);
			$textHashEnd = substr($textHash,$saltStart,strlen($saltHash));
			if($hashOK) {
				$outHash = hash($mode, $textHashEnd.$saltHash.$textHashStart);
			} else {
				$outHash = sha1($textHashEnd.$saltHash.$textHashStart);
			}
		} elseif($saltStart > (strlen($saltHash)-1)) {
			if($hashOK) {
				$outHash = hash($mode, $textHash.$saltHash);
			} else {
				$outHash = sha1($textHash.$saltHash);
			}
		} else {
			if($hashOK) {
				$outHash = hash($mode, $saltHash.$textHash);
			} else {
				$outHash = sha1($saltHash.$textHash);
			}
		}
		// put salt at front of hash //
		$output = $saltHash.$outHash;
		return $output;
	}
	// end ///////////////////////////

	public function login($data)
	{
		// check if email set
		if (!isset($data["username"])) throw new Exception("No Login username");
		if (!isset($data["password"])) throw new Exception("No Login password");

		$ip_address = preg_replace("/[^0-9.]/", '', substr($_SERVER["REMOTE_ADDR"], 0, 50));
		$user_agent = preg_replace("/[^0-9a-zA-Z.:;-_]/", '', substr($_SERVER["HTTP_USER_AGENT"], 0, 250));

		// check ban list if active before doing anything else
		if (RARS_ACCESS_BAN_ATTEMPS > 0)
		{
			// find banned rows
			$db = new RazorDB();
			$db->connect("banned");
			$search = array(
				array("column" => "ip_address", "value" => $ip_address), 
				array("column" => "user_agent", "value" => $user_agent, "and" => true)
			);
			$count = $db->get_rows($search);
			$count = $count["count"];
			$db->disconnect(); 

			if ($count > 0) return RazorAPI::response(array("message" => "Login failed: ip banned", "login_error_code" => 104), "json");  
		}

		/* carry on with login */

		// find user
		$db = new RazorDB();
		$db->connect("user");
		$search = array("column" => "email_address", "value" => $data["username"]);
		$options = array("amount" => 1);
		$res = $db->get_rows($search, $options);
		$db->disconnect(); 

		// check user found
		if ($res["count"] != 1) return RazorAPI::response(array("message" => "Login failed: username or password missmatch", "login_error_code" => 101), "json");

		// grab user details
		$user = $res["result"][0];

		// check if user is locked out here
		if (!empty($user["lock_until"]) && $user["lock_until"] > time())
		{
			return RazorAPI::response(array("message" => "Login failed: user locked out please try later", "login_error_code" => 102, "time_left" => $user["lock_until"] - time()), "json");
		}

		// check active user
		if (!$user["active"]) return RazorAPI::response(array("message" => "Login failed: user not active", "login_error_code" => 103), "json");
		
		// now check if password ok (we need password first to get salt from it before we can check it), if not then send response		
		if (RazorAPI::create_hash($data["password"],substr($user["password"],0,(strlen($user["password"])/2)),'sha1') !== $user["password"])
		{
			// update failed attempts and lockout
			$db = new RazorDB();
			$db->connect("user");
			$search = array("column" => "id", "value" => $user["id"]);
			$changes = array("failed_attempts" => $user["failed_attempts"] + 1);
			if ($user["failed_attempts"] > 0 && $user["failed_attempts"] % RARS_ACCESS_ATTEMPTS == 0) $changes["lock_until"] = time() + RARS_ACCESS_LOCKOUT;
			$db->edit_rows($search, $changes);
			$db->disconnect();

			// add to banned list if banned active and too many attempts
			if (RARS_ACCESS_BAN_ATTEMPS > 0 && $user["failed_attempts"] + 1 >= RARS_ACCESS_BAN_ATTEMPS)
			{
				$db = new RazorDB();
				$db->connect("banned");
				$row = array("ip_address" => $ip_address, "user_agent" => $user_agent);
				$db->add_rows($row);   
				$db->disconnect(); 
			}

			return RazorAPI::response(array("message" => "Login failed: username or password missmatch", "login_error_code" => 101), "json");
		}

		/* we are now authenticated, respond and send token back */

		// need to create a token and last logged stamp and save it in the db
		$last_logged = time();
		$pass_hash = $user["password"];
		$token = sha1($last_logged.$user_agent.$ip_address.$pass_hash)."_".$user["id"];

		// store last logged and reset lockout/attempts
		$db = new RazorDB();
		$db->connect("user");
		$search = array("column" => "id", "value" => $user["id"]);
		$changes = array(
			"last_logged_in"	=> $last_logged,
			"last_accessed"	 => $last_logged,
			"failed_attempts"   => 0,
			"lock_until"		=> null,
			"ip_address"		=> $ip_address
		);
		$db->edit_rows($search, $changes);
		$db->disconnect();

		// collect user data
		$user = array(
			"id"				=> $user["id"],
			"name"			  => $user["name"],
			"email_address"	 => $user["email_address"],
			"last_logged_in"	=> $user["last_logged_in"],
			"access_level"	  => $user["access_level"]
		);

		// setup response
		return RazorAPI::response(array("token" => $token, "user" => $user), "json");
	}

	public function check_access($access_timeout = RARS_ACCESS_TIMEOUT)
	{
		// retrieve token from incoming request
		$token = (isset($_SERVER["HTTP_AUTHORIZATION"]) ? $_SERVER["HTTP_AUTHORIZATION"] : (isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]) ? $_SERVER["REDIRECT_HTTP_AUTHORIZATION"] : (isset($_COOKIE["token"]) ? $_COOKIE["token"] : null )));
		if (empty($token)) return false;

		// extract token and id
		$token_data = explode("_", $token);
		if (count($token_data) != 2) return false;
		$token = preg_replace("/[^a-zA-Z0-9]/", '', $token_data[0]);
		$id = (int) $token_data[1];

		// find user
		$db = new RazorDB();
		$db->connect("user");
		$search = array("column" => "id", "value" => $id);
		$options = array("amount" => 1);
		$res = $db->get_rows($search, $options);
		$db->disconnect(); 

		// no user found or no access in XXX seconds
		if ($res["count"] != 1) return false;	 
		$user = $res["result"][0];
		if ($user["last_accessed"] < time() - $access_timeout) return false;

		/* all ok, so go verify user */

		// need to create a token and last logged stamp
		$last_logged = $user["last_logged_in"];
		$user_agent = preg_replace("/[^0-9a-zA-Z.:;-_]/", '', substr($_SERVER["HTTP_USER_AGENT"], 0, 250));
		$ip_address = preg_replace("/[^0-9.]/", '', substr($_SERVER["REMOTE_ADDR"], 0, 50));
		$pass_hash = $user["password"];
		$gen_token = sha1($last_logged.$user_agent.$ip_address.$pass_hash);

		if ($gen_token !== $token) return false;

		// set user and return
		$this->user = array(
			"id"				=> $user["id"],
			"name"			  => $user["name"],
			"email_address"	 => $user["email_address"],
			"last_logged_in"	=> $user["last_logged_in"],
			"access_level"	  => $user["access_level"]
		);

		// update access time to keep connection alive, only do this once an hour to keep writes to db down for user table
		// connection will stay live for a day anyway so we do not need to be this heavy on the last access time writes
		if ($user["last_accessed"] > time() - 3600) return $this->user["access_level"];

		$db = new RazorDB();
		$db->connect("user");
		$search = array("column" => "id", "value" => $this->user["id"]);
		$changes = array("last_accessed" => time());
		$db->edit_rows($search, $changes);
		$db->disconnect();

		return $this->user["access_level"];
	}

	public function email($from, $to, $subject, $message)
	{
		$headers = "From: {$from}\r\nReply-To: {$from}\r\nMIME-Version: 1.0" . "\r\nContent-type:text/html;charset=UTF-8";
		
		// do base check for production server and mail only if not localhost
		if (strpos(RAZOR_BASE_URL, "http://localhost") !== 0) mail($to, $subject, $message, $headers);
	}

	public static function response($data, $type = null, $code = null)
	{
		switch ($code)
		{
			// 2XX Success
			case 201:
				header("HTTP/1.0 201 Created");
			break;
			case 202:
				header("HTTP/1.0 202 Accepted");
			break;
			case 204:
				header("HTTP/1.0 204 No Content");
			break;
			case 205:
				header("HTTP/1.0 205 Reset Content");
			break;
			case 206:
				header("HTTP/1.0 206 Partial Content");
			break;

			// 4XX Client Error
			case 400:
				$data = array("error" => "HTTP/1.0 400 Bad Request", "response" => $data);
				header($data["error"]);
			break;
			case 401:
				$data = array("error" => "HTTP/1.0 401 Unauthorized", "response" => $data);
				header($data["error"]);
			break;
			case 402:
				$data = array("error" => "HTTP/1.0 402 Payment Required", "response" => $data);
				header($data["error"]);
			break;
			case 403:
				$data = array("error" => "HTTP/1.0 403 Forbidden", "response" => $data);
				header($data["error"]);
			break;
			case 404:
				$data = array("error" => "HTTP/1.0 404 Not Found", "response" => $data);
				header($data["error"]);
			break;
			case 405:
				$data = array("error" => "HTTP/1.0 405 Method Not Allowed", "response" => $data);
				header($data["error"]);
			break;
			case 406:
				$data = array("error" => "HTTP/1.0 406 Not Acceptable", "response" => $data);
				header($data["error"]);
			break;
			case 407:
				$data = array("error" => "HTTP/1.0 407 Proxy Authentication Required", "response" => $data);
				header($data["error"]);
			break;
			case 408:
				$data = array("error" => "HTTP/1.0 408 Request Timeout", "response" => $data);
				header($data["error"]);
			break;
			case 409:
				$data = array("error" => "HTTP/1.0 409 Conflict", "response" => $data);
				header($data["error"]);
			break;

			//5XX Server Error
			case 500:
				$data = array("error" => "HTTP/1.0 500 Internal Server Error", "response" => $data);
				header($data["error"]);
			break;
			case 501:
				$data = array("error" => "HTTP/1.0 501 Not Implemented", "response" => $data);
				header($data["error"]);
			break;
		}

		if ($type == null || !method_exists("RazorAPI", $type)) RazorAPI::raw($data);
		else RazorAPI::$type($data);
	}

	private static function raw($data)
	{
		$data = RazorAPI::clean_output($data);
		
		header("Cache-Control: no-cache, no-store, must-revalidate");
		echo (isset($data["error"]) ? $data["error"].(empty($data["response"]) ? "" : " with response: ".$data["response"]) : var_export($data, true));
		exit();
	}

	private static function json($data)
	{
		$data = RazorAPI::clean_output($data);
		
		header("Content-type: application/json");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		echo json_encode($data);
		exit();
	}

	private static function xml($data)
	{
		$data = RazorAPI::clean_output($data);

		// build sitemap index
		$output = '<?xml version="1.0" encoding="UTF-8"?>';
		$output.= $data;

		header('Content-Type: application/xml; charset=utf-8');
		header("Cache-Control: no-cache, no-store, must-revalidate");
		echo $output;
		exit();
	}
}
/* EOF */