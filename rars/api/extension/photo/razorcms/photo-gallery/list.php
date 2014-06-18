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
 
class ExtensionPhotoRazorcmsPhotogalleryList extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($type)
	{
		if ((int) $this->check_access() < 9) $this->response(null, null, 401);
		if (empty($type) || !in_array($type, $this->types)) $this->response(null, null, 400);

		$this->response("success", "json");
	}
}

/* EOF */