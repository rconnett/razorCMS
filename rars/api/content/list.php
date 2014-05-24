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
 
class ContentList extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($id)
	{
		$db = new RazorDB();

		$db->connect("content");

		// set options
		$options = array(
			"order" => array("column" => "id", "direction" => "desc")
		);

		$search = array("column" => "id", "value" => null, "not" => true);

		$content = $db->get_rows($search, $options);
		$content = $content["result"];
		$db->disconnect(); 

		// now get all page content so we can show what pages are using this content
		$db->connect("page_content");

		$options = array(
			"join" => array("table" => "page", "join_to" => "page_id")
		);

		$search = array("column" => "id", "value" => null, "not" => true);

		$page_content = $db->get_rows($search, $options);
		$page_content = $page_content["result"];
		$db->disconnect(); 

		foreach ($content as $key => $row)
		{
			foreach ($page_content as $pc)
			{
				if ($row["id"] == $pc["content_id"])
				{
				   if (!isset($content[$key]["used_on_pages"])) $content[$key]["used_on_pages"] = array();
				   $content[$key]["used_on_pages"][$pc["page_id"]] = array("id" => $pc["page_id"], "name" => $pc["page_id.name"], "link" => $pc["page_id.link"]);  
				}
			}
		}

		
		// return the basic user details
		$this->response(array("content" => $content), "json");
	}
}

/* EOF */