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
 
class ContentData extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	// add or update content
	public function delete($id)
	{
		// login check - if fail, return no data to stop error flagging to user
		if ((int) $this->check_access() < 8) $this->response(null, null, 401);
		if (!is_numeric($id)) $this->response(null, null, 400);

		$db = new RazorDB();
		
		// delete page
		$db->connect("content");
		$db->delete_rows(array("column" => "id", "value" => (int) $id));
		$db->disconnect(); 

		// remove any page_content items
		$db->connect("page_content");
		$db->delete_rows(array("column" => "content_id", "value" => (int) $id));
		$db->disconnect(); 

		// return the basic user details
		$this->response("success", "json");
	}
}

/* EOF */