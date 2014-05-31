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
 
class MenuEditor extends RazorAPI
{
	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();
	}

	public function get($page_id)
	{
		$db = new RazorDB();

		// get menu data too
		$db->connect("menu_item");

		$options = array(
			"join" => array(
				array("table" => "menu", "join_to" => "menu_id"),
				array("table" => "page", "join_to" => "page_id")
			),
			"order" => array("column" => "position", "direction" => "asc")
		);
		$search = array("column" => "id", "not" => true, "value" => null);
		$menu_items = $db->get_rows($search, $options);
		$menu_items = $menu_items["result"];
		
		$db->disconnect();  

		$menus = array();
		foreach ($menu_items as $mi)
		{
			if (!isset($menus[$mi["menu_id.name"]]))
			{
				$menus[$mi["menu_id.name"]] = array(
					"id" => $mi["menu_id.id"], 
					"name" => $mi["menu_id.name"],
					"menu_items" => array()
				);
			}

			if ($mi["level"] == 1)
			{
				$menus[$mi["menu_id.name"]]["menu_items"][] = array(
					"id" => $mi["id"],
					"position" => $mi["position"],
					"page_id" => $mi["page_id"],
					"page_name" => $mi["page_id.name"],
					"page_link" => $mi["page_id.link"],
					"page_active" => $mi["page_id.active"],
					"level" => $mi["level"]
				);				
			}

			if ($mi["level"] == 2)
			{
				$parent = count($menus[$mi["menu_id.name"]]["menu_items"]) - 1;
				
				if (!isset($menus[$mi["menu_id.name"]]["menu_items"][$parent]["sub_menu"]))
				{
					$menus[$mi["menu_id.name"]]["menu_items"][$parent]["sub_menu"] = array();
				}

				$menus[$mi["menu_id.name"]]["menu_items"][$parent]["sub_menu"][] = array(
					"id" => $mi["id"],
					"position" => $mi["position"],
					"page_id" => $mi["page_id"],
					"page_name" => $mi["page_id.name"],
					"page_link" => $mi["page_id.link"],
					"page_active" => $mi["page_id.active"],
					"level" => $mi["level"]
				);   
			}
		}

		// if menu items missing, build a clean array to allow people to add new
		$db->connect("menu");

		$search = array("column" => "id", "not" => true, "value" => null);
		$menus_clean = $db->get_rows($search);
		$menus_clean = $menus_clean["result"];
		
		$db->disconnect();  

		foreach ($menus_clean as $mc) 
		{
			if (isset($menus[$mc["name"]])) continue;

			$menus[$mc["name"]] = array(
				"id" => $mc["id"], 
				"name" => $mc["name"],
				"menu_items" => array()
			);
		}

		// return the basic user details
		$this->response(array("menus" => $menus), "json");
	}

	// add or update content
	public function post($data)
	{
		// login check - if fail, return no data to stop error flagging to user
		if ((int) $this->check_access() < 8) $this->response(null, null, 401);
	
		// menu item
		$db = new RazorDB();
		$db->connect("menu_item");

		// 1. grab all menus in position order
		$options = array(
			"order" => array("column" => "position", "direction" => "asc")
		);
		$search = array("column" => "id", "not" => true, "value" => null);
		$all_menu_items = $db->get_rows($search, $options);
		$all_menu_items = $all_menu_items["result"];

		// 2. make flat arrays
		$new_menus_flat = array();
		foreach ($data as $menu)
		{
			// set up menu item arrays
			if (!isset($new_menus_flat[$menu["id"]])) $new_menus_flat[$menu["id"]] = array();
			
			foreach ($menu["menu_items"] as $mi)
			{
				if (isset($mi["id"])) $new_menus_flat[$menu["id"]][] = $mi["id"];

				if (isset($mi["sub_menu"]) & !empty($mi["sub_menu"]))
				{
					foreach ($mi["sub_menu"] as $sub_menu_item)
					{
						if (isset($sub_menu_item["id"])) $new_menus_flat[$menu["id"]][] = $sub_menu_item["id"];
					}
				}
			}
		}

		$current_menus_flat = array();
		foreach ($all_menu_items as $ami)
		{
			// set up menu item arrays
			if (!isset($current_menus_flat[$ami["menu_id"]])) $current_menus_flat[$ami["menu_id"]] = array();
			$current_menus_flat[$ami["menu_id"]][] = $ami["id"];

			// at same time remove any items missing		  
			if (!in_array($ami["id"], $new_menus_flat[$ami["menu_id"]])) $db->delete_rows(array("column" => "id", "value" => (int) $ami["id"]));
		}

		// 3. update all of sent menu data, by looping through the new $data
		foreach ($data as $new_menu)
		{
			$pos = 1;
			// each menu
			foreach ($new_menu["menu_items"] as $nmi)
			{
				if (isset($nmi["id"]) && in_array($nmi["id"], $current_menus_flat[$new_menu["id"]]))
				{
					// update menu item
					$search = array("column" => "id", "value" => $nmi["id"]);
					$db->edit_rows($search, array("position" => $pos));
				}
				else
				{
					// add new item
					$row = array(
						"menu_id" => (int) $new_menu["id"],
						"position" => $pos,
						"level" => 1,
						"page_id" => $nmi["page_id"],
						"link_id" => 0
					);

					$db->add_rows($row);  
				}

				$pos++;

				// now check for sub menu
				if (isset($nmi["sub_menu"]) && !empty($nmi["sub_menu"]))
				{
					foreach ($nmi["sub_menu"] as $nsmi)
					{
						if (isset($nsmi["id"]) && in_array($nsmi["id"], $current_menus_flat[$new_menu["id"]]))
						{
							// update menu item
							$search = array("column" => "id", "value" => $nsmi["id"]);
							$db->edit_rows($search, array("position" => $pos));
						}
						else
						{
							// add new item
							$row = array(
								"menu_id" => (int) $new_menu["id"],
								"position" => $pos,
								"level" => 2,
								"page_id" => $nsmi["page_id"],
								"link_id" => 0
							);

							$db->add_rows($row);  
						}

						$pos++;
					}
				}
			}
		}

		$db->disconnect();  

		$this->response("success", "json");
	}
}

/* EOF */