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
 
class ContentEditor extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($page_id)
	{
		$db = new RazorDB();
		$db->connect("page_content");

		// set options
		$options = array(
			"order" => array("column" => "position", "direction" => "asc")
		);

		$search = array("column" => "page_id", "value" => (int) $page_id);

		$page_contents = $db->get_rows($search, $options);
		$page_contents = $page_contents["result"];
		$db->disconnect(); 

		// split into content and locations
		$db->connect("content");
		$content = array();
		$locations = array();
		foreach ($page_contents as $row)
		{
			if (!empty($row["content_id"]))
			{
				$options = array("limit" => 1);
				$search = array("column" => "id", "value" => (int) $row["content_id"]);
				$found_content = $db->get_rows($search, $options);
				$found_content = $found_content["result"][0];

				$content[$found_content["id"]] = array(
					"content_id" => $found_content["id"],
					"name" => $found_content["name"],
					"content" => $found_content["content"]
				);
			}

			$location_data = array(
				"id" => $row["id"], 
				"content_id" => $row["content_id"], 
				"extension" => $row["extension"],
				"settings" => json_decode($row["json_settings"])
			);

			if (!empty($row["extension"]))
			{
				$manifest = RazorFileTools::read_file_contents(RAZOR_BASE_PATH."extension/{$row['extension']}", "json");
				if (isset($manifest->content_settings) && !empty($manifest->content_settings))
				{
					// create object
					if (!is_object($location_data["settings"])) $location_data["settings"] = new stdClass();

					// copy settings
					$location_data["extension_content_settings"] = $manifest->content_settings;
					
					// if no settings present, add defaults from manifest
					foreach ($manifest->content_settings as $cs)
					{
						if (!isset($location_data["settings"]->{$cs->name})) $location_data["settings"]->{$cs->name} = $cs->value;
					}
				}
			}

			$locations[$row["location"]][$row["column"]][] = $location_data;
		}		
		$db->disconnect(); 

		// return the basic user details
		$this->response(array("content" => $content, "locations" => $locations), "json");
	}

	// add or update content
	public function post($data)
	{
		if ((int) $this->check_access() < 6) $this->response(null, null, 401);
		if (!isset($data["content"])) $this->response(null, null, 400);

		// update content
		$db = new RazorDB();
		$db->connect("content");

		// update or add content
		$new_content_map = array();
		foreach ($data["content"] as $key => $content)
		{	
			// if content name empty, try to resolve this to something
			if (empty($content["name"]))
			{
				$name = strip_tags(str_replace("><", "> <", $content["content"]));
				$cap = (strlen($name) > 30 ? 30 : strlen($name));
				$content["name"] = substr($name, 0, $cap)."...";
			}

			if (!isset($content["content_id"]) || !isset($content["content"]) || empty($content["content"]))
			{
				unset($data["content"][$key]);
				continue;
			}

			if (stripos($content["content_id"], "new-") === false)
			{
				// update
				$search = array("column" => "id", "value" => $content["content_id"]);
				$db->edit_rows($search, array("content" => $content["content"], "name" => $content["name"]));
			}
			else
			{
				// add new content and map the ID to the new id for locations table
				$row = array("content" => $content["content"], "name" => $content["name"]);
				$result = $db->add_rows($row);
				$new_content_map[$content["content_id"]] = $result["result"][0]["id"];   
			}
		}

		$db->disconnect(); 

		// update or add locations

		$db = new RazorDB();
		$db->connect("page_content");

		// 1. first take snapshot of current
		$search = array("column" => "page_id", "value" => (int) $data["page_id"]);
		$current_page_content = $db->get_rows($search);
		$current_page_content = $current_page_content["result"];

		// 2. iterate through updating or adding, make a note of all id's
		$page_content_map = array();
		foreach ($data["locations"] as $location => $columns)
		{
			foreach ($columns as $column => $blocks)
			{
				foreach ($blocks as $pos => $block)
				{
					if ($block["id"] != "new")
					{
						// update
						$search = array("column" => "id", "value" => $block["id"]);
						$row = array("location" => $location, "column" => (int) $column, "position" => $pos + 1, "json_settings" => json_encode($block["settings"]));

						if (isset($block["extension"])) $row["extension"] = $block["extension"];
						
						$db->edit_rows($search, $row);
						$page_content_map[] = $block["id"];
					}
					else
					{
						// add new, if new, add, if new but already present add, else add as ext
						$new_content_id = (isset($block["content_id"], $new_content_map[$block["content_id"]]) ? $new_content_map[$block["content_id"]] : (isset($block["content_id"]) && is_numeric($block["content_id"]) ? $block["content_id"] : null));

						if (!empty($new_content_id) || isset($block["extension"]))
						{
							$row = array(
								"page_id" => (int) $data["page_id"],
								"content_id" => $new_content_id,
								"location" => $location,
								"column" => (int) $column,
								"position" => $pos + 1
							);

							if (isset($block["extension"]))
							{
								$row["extension"] = $block["extension"];
								$row["json_settings"] = (isset($block["settings"]) ? json_encode($block["settings"]) : null);
							}

							$result = $db->add_rows($row);
							$page_content_map[] = $result["result"][0];
						}
					}
				}
			}
		}

		// 3. run through id's affected against snapshot, if any missing, remove them.
		foreach ($current_page_content as $row)
		{
			if (!in_array($row["id"], $page_content_map)) $db->delete_rows(array("column" => "id", "value" => (int) $row["id"]));
		}

		$db->disconnect(); 

		// return the basic user details
		$this->response("success", "json");
	}
}

/* EOF */