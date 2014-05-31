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
 
class ListRepository extends RazorAPI
{
	private $repo_url = "http://archive.razorcms.co.uk/";
	private $ext_list = "extension.list.json";
	private $cat_list = "category.list.json";
	private $han_list = "handle.list.json";

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($id)
	{
		if ((int) $this->check_access() < 9) $this->response(null, null, 401);

		$list_url = "";
		switch($id)
		{
			case "extension":
				$list = $this->ext_list;
			break;
			case "category":
				$list = $this->cat_list;
			break;
			case "handle":
				$list = $this->han_list;
			break;
			default:
				$this->response(null, null, 400);
			break;
		}

		$headers = @get_headers($this->repo_url.$list);
	  
		if(strpos($headers[0], "404") === false) 
		{
			$ctx = stream_context_create(array( 
				'http' => array( 
					'timeout' => 60
					) 
				) 
			);			 

			$repo_file = @file_get_contents($this->repo_url.$list, false, $ctx);

			if (!empty($repo_file))
			{
				$repo = json_decode($repo_file);
				$this->response(array("list" => $repo), "json");
			}
		}

		// send back unnavailable
		$this->response(null, null, 404);
	}
}

/* EOF */