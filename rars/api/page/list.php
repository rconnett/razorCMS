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
 
class PageList extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($id)
	{
		$db = new RazorDB();
		$db->connect("page");

		$search = array("column" => "id", "value" => null, "not" => true);

		$pages = $db->get_rows($search);
		$pages = $pages["result"];
		$db->disconnect(); 
		
		// return the basic user details
		$this->response(array("pages" => $pages), "json");
	}
}

/* EOF */