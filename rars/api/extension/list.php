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
 
class ExtensionList extends RazorAPI
{
	private $types = array("theme", "system", "all");

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($type)
	{
		if ((int) $this->check_access() < 9) $this->response(null, null, 401);
		if (empty($type) || !in_array($type, $this->types)) $this->response(null, null, 400);

		// first scan the folders for manifests
		$manifests = RazorFileTools::find_file_contents(RAZOR_BASE_PATH."extension", "manifest.json", "json", "end");

		// split into types, so we can filter a little
		$extensions = array();

		$db = new RazorDB();
		$db->connect("extension");

		foreach ($manifests as $mf)
		{			
			// grab settings if any
			if (isset($mf->settings))
			{
				$options = array("amount" => 1);
				$search = array(array("column" => "extension", "value" => $mf->extension),array("column" => "type", "value" => $mf->type),array("column" => "handle", "value" => $mf->handle));
				$extension = $db->get_rows($search, $options);
				if ($extension["count"] == 1)
				{
					$db_settings = json_decode($extension["result"][0]["json_settings"]);

					foreach ($mf->settings as $key => $setting) 
					{
						if (isset($db_settings->{$setting->name})) $mf->settings[$key]->value = $db_settings->{$setting->name};
					}
				}
			} 

			// sort list
			if ($mf->type == $type)
			{
				if ($mf->type == "theme")
				{
					// group manifest layouts for themes
					if (!isset($extensions[$mf->type.$mf->handle.$mf->extension]))
					{
						$extensions[$mf->type.$mf->handle.$mf->extension] = array(
							"layouts" => array(),
							"type" => $mf->type,
							"handle" => $mf->handle,
							"description" => $mf->description,
							"name" => $mf->name
						);
					}
					
					$extensions[$mf->type.$mf->handle.$mf->extension]["layouts"][] = $mf;
				}
				else $extensions[] = $mf;
			}
			else if ($type == "system" && $mf->type != "theme") $extensions[] = $mf;
			else if ($type == "all")
			{
				$mf->type = ucfirst($mf->type);

				if ($mf->type == "Theme")
				{
					// group manifest layouts for themes
					if (!isset($extensions[$mf->type.$mf->handle.$mf->extension]))
					{
						$extensions[$mf->type.$mf->handle.$mf->extension] = array(
							"layouts" => array(),
							"type" => $mf->type,
							"handle" => $mf->handle,
							"extension" => $mf->extension,
							"description" => $mf->description,
							"name" => $mf->name
						);
					}
					
					$extensions[$mf->type.$mf->handle.$mf->extension]["layouts"][] = $mf;
				}
				else $extensions[] = $mf;
			}
		}

		// ensure we have array return and not object
		$extensions = array_values($extensions);

		$db->disconnect(); 

		$this->response(array("extensions" => $extensions), "json");
	}
}

/* EOF */