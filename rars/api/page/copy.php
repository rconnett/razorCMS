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
 
class PageCopy extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	// copy page
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

		// copy the page
		$row = array(
			"name" => $data["name"], 
			"title" => $data["title"], 
			"link" => $data["link"], 
			"keywords" => $data["keywords"], 
			"description" => $data["description"], 
			"access_level" => (int) $data["access_level"], 
			"theme" => $data["theme"], 
			"json_settings" => $data["json_settings"], 
			"active" => false
		);

		$new_page = $db->add_rows($row);
		$db->disconnect(); 		
		if ($new_page["count"] != 1) $this->response(null, null, 400);

		// next lets get all the page content for page we are copying
		$db->connect("page_content");
		$search = array("column" => "page_id", "value" => $data["id"]);
		$page_content = $db->get_rows($search);

		// now copy if any found
		if ($page_content["count"] > 0)
		{
			$new_rows = array();
			foreach ($page_content["result"] as $row)
			{
				$new_row = array();
				foreach ($row as $key => $col)
				{
					if ($key == "id") continue;
					else if ($key == "page_id") $new_row[$key] = $new_page["result"][0]["id"];
					else $new_row[$key] = $col;
				}
				$new_rows[] = $new_row;
			}
			$db->add_rows($new_rows);
		} 
		$db->disconnect(); 

		// return the basic page details
		$this->response($new_page["result"][0], "json");
	}
}

/* EOF */