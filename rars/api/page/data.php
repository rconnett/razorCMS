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
 
class PageData extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	// add page
	public function post($data)
	{
		// login check - if fail, return no data to stop error flagging to user
		if ((int) $this->check_access() < 6) $this->response(null, null, 401);
		if (empty($data)) $this->response(null, null, 400);

		$db = new RazorDB();
		$db->connect("page");

		// check link unique
		$options = array("amount" => 1);
		$search = array("column" => "link", "value" => (isset($data["link"]) ? $data["link"] : ""));
		$count = $db->get_rows($search, $options);
		if ($count["count"] > 0) $this->response(array("error" => "duplicate link found", "code" => 101), 'json', 409);

		$row = array(
			"name" => $data["name"], 
			"title" => $data["title"], 
			"link" => $data["link"], 
			"keywords" => $data["keywords"], 
			"description" => $data["description"], 
			"access_level" => (int) $data["access_level"], 
			"active" => false
		);

		$result = $db->add_rows($row);
		$result = $result["result"][0];   

		$db->disconnect(); 

		// return the basic user details
		$this->response($result, "json");
	}

	// add or update content
	public function delete($id)
	{
		// login check - if fail, return no data to stop error flagging to user
		if ((int) $this->check_access() < 8) $this->response(null, null, 401);
		if (!is_numeric($id)) $this->response(null, null, 400);

		$db = new RazorDB();
		
		// delete page
		$db->connect("page");
		$db->delete_rows(array("column" => "id", "value" => (int) $id));
		$db->disconnect(); 

		// remove any page_content items
		$db->connect("page_content");
		$db->delete_rows(array("column" => "page_id", "value" => (int) $id));
		$db->disconnect(); 

		// remove any menu_items
		$db->connect("menu_item");
		$db->delete_rows(array("column" => "page_id", "value" => (int) $id));
		$db->disconnect(); 

		// return the basic user details
		$this->response("success", "json");
	}
}

/* EOF */