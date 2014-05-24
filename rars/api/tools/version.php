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
 
class ToolsVersion extends RazorAPI
{
	private $check_url = "http://www.razorcms.co.uk/rars/live/version/";

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($id)
	{
		if ($id != "current") $this->response(null, null, 400); 

		$host = (isset($_SERVER["SERVER_NAME"]) ? urlencode($_SERVER["SERVER_NAME"]) : (isset($_SERVER["HTTP_HOST"]) ? urlencode($_SERVER["HTTP_HOST"]) : "current"));

		$headers = @get_headers($this->check_url);
		
		if(strpos($headers[0], "404") === false) 
		{
			$ctx = stream_context_create(array( 
				'http' => array( 
					'timeout' => 60
					) 
				) 
			);			 

			$version_file = @file_get_contents($this->check_url.$host, false, $ctx);
			if (!empty($version_file))
			{
				$version = json_decode($version_file);
				$this->response($version, "json");
			}
			else
			{
				// send back unnavailable
				$this->response(null, null, 404);
			}
		}

		// send back unnavailable
		$this->response(null, null, 404);
	}
}

/* EOF */