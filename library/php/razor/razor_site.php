<?php if (!defined("RAZOR_BASE_PATH")) die("No direct script access to this content");

/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
class RazorSite
{
	private $link = null;
	private $all_menus = null;
	private $site = null;
	private $page = null;
	private $menu = null;
	private $content = null;
	private $login = false;
	private $logged_in = false;

	function __construct()
	{
		// generate path from get
		$this->link = (isset($_GET["path"]) ? $_GET["path"] : null);
	}

	public function load()
	{
		// check for admin flag
		if ($this->link == "login")
		{
			$this->link = null;
			$this->login = true;
		}

		// check for logged in
		if (isset($_COOKIE["token"]))
		{
			include(RAZOR_BASE_PATH."library/php/razor/razor_api.php");
			$api = new RazorAPI();
			$this->logged_in = $api->check_access(86400);
		}

		// load data
		$this->get_all_menus();
		$this->get_site_data();
		$this->get_page_data();
		$this->get_menu_data();
		$this->get_content_data();
	}

	public function render()
	{
		// is 404 ?
		if (empty($this->page) || (!isset($_COOKIE["token"]) && !$this->page["active"]))
		{ 
			header("HTTP/1.0 404 Not Found");
			include_once(RAZOR_BASE_PATH."theme/view/404.php");
			return;
		}

		// is 401 ?
		if ($this->logged_in < $this->page["access_level"])
		{
			header("HTTP/1.0 401 Unauthorized");
			include_once(RAZOR_BASE_PATH."theme/view/401.php");
			return;
		}

		// if default not chosen, load manifest
		if (!empty($this->page["theme"]) && is_file(RAZOR_BASE_PATH."extension/theme/{$this->page["theme"]}"))
		{
			$manifest = RazorFileTools::read_file_contents(RAZOR_BASE_PATH."extension/theme/{$this->page["theme"]}", "json");
			$view_path = RAZOR_BASE_PATH."extension/theme/{$manifest->handle}/{$manifest->extension}/view/{$manifest->layout}.php";

			if (is_file($view_path)) include_once($view_path);
		}
		else include_once(RAZOR_BASE_PATH."theme/view/default.php");
	}

	public function content($loc, $col)
	{
		// create extension dependancy list
		$ext_dep_list = array();
		
		// admin angluar loading for editor, return
		if (isset($_GET["edit"]) && ($this->logged_in > 6 || ($this->logged_in > 5 && !$this->page["active"])))
		{
//<div text-angular name="{$loc}{$col}{{block.content_id}}" ng-if="!block.extension" ta-disabled="!editingThis('{$loc}{$col}' + block.content_id)" class="content-edit" ng-model="content[block.content_id].content" ng-click="startBlockEdit('{$loc}{$col}',  block.content_id)" ></div>

			echo <<<OUTPUT
<div class="content-column" ng-if="changed" ng-class="{'edit': toggle}">
	<div class="content-block" ng-class="{'active': editingThis('{$loc}{$col}' + block.content_id)}" ng-repeat="block in locations.{$loc}.{$col}">

		<div class="input-group block-controls" ng-if="!block.extension">
			<span class="input-group-btn">
				<button class="btn btn-default" ng-click="locations.{$loc}.{$col}.splice(\$index - 1, 0, locations.{$loc}.{$col}.splice(\$index, 1)[0])" ng-show="toggle"><i class="fa fa-arrow-up"></i></button>
				<button class="btn btn-default" ng-click="locations.{$loc}.{$col}.splice(\$index + 1, 0, locations.{$loc}.{$col}.splice(\$index, 1)[0])" ng-show="toggle"><i class="fa fa-arrow-down"></i></button>
			</span>
			<input type="text" class="form-control" placeholder="Add Content Name" ng-show="toggle" ng-model="content[block.content_id].name"/>
			<span class="input-group-btn">
				<button class="btn btn-warning" ng-show="toggle" ng-click="removeContent('{$loc}', '{$col}', \$index)"><i class="fa fa-times"></i></button>
			</span>
		</div>

		<div id="{$loc}{$col}{{block.content_id}}" ng-if="!block.extension" class="content-edit" ng-click="startBlockEdit('{$loc}{$col}',  block.content_id)" ng-bind-html="content[block.content_id].content | html"></div>

		<div class="content-settings" ng-if="block.extension">
			<div class="extension-controls">
				<span class="btn-group pull-left">
					<button class="btn btn-default" ng-click="locations.{$loc}.{$col}.splice(\$index - 1, 0, locations.{$loc}.{$col}.splice(\$index, 1)[0])" ng-show="toggle"><i class="fa fa-arrow-up"></i></button>
					<button class="btn btn-default" ng-click="locations.{$loc}.{$col}.splice(\$index + 1, 0, locations.{$loc}.{$col}.splice(\$index, 1)[0])" ng-show="toggle"><i class="fa fa-arrow-down"></i></button>
				</span>
				<h3 class="extension-title pull-left"><i class="fa fa-puzzle-piece"></i> Extension</h3>
				<button class="btn btn-warning pull-right" ng-show="toggle" ng-click="removeContent('{$loc}', '{$col}', \$index)"><i class="fa fa-times"></i></button>
			</div>
			<form class="form-horizontal" role="form" name="form" novalidate>
				<div class="form-group">
					<label class="col-sm-3 control-label">Type</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" value="{{block.extension.split('/')[0]}}" disabled>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Handle</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" value="{{block.extension.split('/')[1]}}" disabled>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Extension</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" value="{{block.extension.split('/')[2]}}" disabled>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[0]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[0].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[0].placeholder}}" name="input0" ng-model="block.settings[block.extension_content_settings[0].name]" ng-pattern="{{block.extension_content_settings[0].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input0.\$dirty && form.input0.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input0.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[1]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[1].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[1].placeholder}}" name="input1" ng-model="block.settings[block.extension_content_settings[1].name]" ng-pattern="{{block.extension_content_settings[1].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input1.\$dirty && form.input1.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input1.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[2]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[2].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[2].placeholder}}" name="input2" ng-model="block.settings[block.extension_content_settings[2].name]" ng-pattern="{{block.extension_content_settings[2].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input2.\$dirty && form.input2.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input2.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[3]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[3].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[3].placeholder}}" name="input3" ng-model="block.settings[block.extension_content_settings[3].name]" ng-pattern="{{block.extension_content_settings[3].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input3.\$dirty && form.input3.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input3.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[4]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[4].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[4].placeholder}}" name="input4" ng-model="block.settings[block.extension_content_settings[4].name]" ng-pattern="{{block.extension_content_settings[4].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input4.\$dirty && form.input4.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input4.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[5]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[5].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[5].placeholder}}" name="input5" ng-model="block.settings[block.extension_content_settings[5].name]" ng-pattern="{{block.extension_content_settings[5].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input5.\$dirty && form.input5.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input5.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[6]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[6].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[6].placeholder}}" name="input6" ng-model="block.settings[block.extension_content_settings[6].name]" ng-pattern="{{block.extension_content_settings[6].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input6.\$dirty && form.input6.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input6.\$error.pattern">Invalid</span>
					</div>
				</div>
				<div class="form-group" ng-if="block.extension_content_settings[7]">
					<label class="col-sm-3 control-label">{{block.extension_content_settings[7].label}}</label>
					<div class="col-sm-7">
						<input type="text" class="form-control" placeholder="{{block.extension_content_settings[7].placeholder}}" name="input7" ng-model="block.settings[block.extension_content_settings[7].name]" ng-pattern="{{block.extension_content_settings[7].regex}}" >
					</div>
					<div class="col-sm-2 error-block" ng-show="form.input7.\$dirty && form.input7.\$invalid">
						<span class="alert alert-danger alert-form" ng-show="form.input7.\$error.pattern">Invalid</span>
					</div>
				</div>
			</form>	  
		</div>
	</div>
	<button class="btn btn-default" ng-show="toggle" ng-click="addNewBlock('{$loc}', '{$col}')"><i class="fa fa-plus"></i></button>
	<button class="btn btn-default" ng-show="toggle" ng-click="findBlock('{$loc}', '{$col}')"><i class="fa fa-search"></i></button>
	<button class="btn btn-default" ng-show="toggle" ng-click="findExtension('{$loc}', '{$col}')"><i class="fa fa-puzzle-piece"></i></button>
</div>
OUTPUT;
			return;
		}
	   
		$db = new RazorDB();

		// if not editor and not empty, output content for public
		foreach ($this->content as $c_data)
		{
			if ($c_data["location"] == $loc && $c_data["column"] == $col)
			{
				if (!empty($c_data["content_id"]))
				{
					// load content	
					echo '<div ng-if="!changed" content-id="'.$c_data["content_id"].'">';

					$db->connect("content");
					$search = array("column" => "id", "value" => $c_data["content_id"]);
					$content = $db->get_rows($search);
					$content = $content["result"][0];
					$db->disconnect(); 

					echo str_replace("\\n", "", $content["content"]);

					echo '</div>';
				}
				elseif (!empty($c_data["extension"]))
				{
					// load extension
					$manifest = RazorFileTools::read_file_contents(RAZOR_BASE_PATH."extension/{$c_data['extension']}", "json");
					$view_path = RAZOR_BASE_PATH."extension/{$manifest->type}/{$manifest->handle}/{$manifest->extension}/view/{$manifest->view}.php";
					
					echo '<div ng-if="!changed">';
					include($view_path);
					echo '</div>';
				}
			}
		}
	}

	public function menu($loc)
	{
		// first, check if menu present, if not create it
		if ($this->add_new_menu($loc)) $this->get_menu_data();;

		// admin angluar loading for editor, return
		if (isset($_GET["edit"]) && $this->logged_in > 6)
		{
			echo <<<OUTPUT
<li ng-if="changed" ng-repeat="mi in menus.{$loc}.menu_items" ng-class="{'click-and-sort': toggle, 'active': linkIsActive(mi.page_id), 'dropdown': mi.sub_menu || toggle, 'selected': \$parent.clickAndSort['{$loc}'].selected, 'place-holder': \$parent.clickAndSort['{$loc}'].picked != \$index && \$parent.clickAndSort['{$loc}'].selected}">
	<a ng-href="{{(!toggle ? getMenuLink(mi.page_link) : '#')}}" ng-click="clickAndSortClick('{$loc}', \$index, menus.{$loc}.menu_items)">
		<button class="btn btn-xs btn-default" ng-if="toggle" ng-click="menus.{$loc}.menu_items.splice(\$index, 1)"><i class="fa fa-times"></i></button>
		<i class="fa fa-eye-slash" ng-hide="mi.page_active"></i>
		{{mi.page_name}}
		<i class="fa fa-caret-down" ng-if="mi.sub_menu"></i>
	</a>
	<ul class="dropdown-menu">
		<li ng-repeat="mis in mi.sub_menu" ng-class="{'click-and-sort-sub': toggle, 'active': linkIsActive(mis.page_id), 'selected': \$parent.clickAndSort['{$loc}Sub'].selected, 'place-holder': \$parent.clickAndSort['{$loc}Sub'].picked != \$index && \$parent.clickAndSort['{$loc}Sub'].selected}">
			<a ng-href="{{(!toggle ? getMenuLink(mis.page_link) : '#')}}" ng-click="clickAndSortClick('{$loc}Sub', \$index, mi.sub_menu)">
				<button class="btn btn-xs btn-default" ng-if="toggle" ng-click="mi.sub_menu.splice(\$index, 1)"><i class="fa fa-times"></i></button>
				<i class="fa fa-eye-slash" ng-hide="mis.page_active"></i> 
				{{mis.page_name}}
			</a>
		</li>

		<li ng-if="toggle" class="text-center"><a style="cursor: pointer;" class="add-new-menu" ng-click="findMenuItem('{$loc}', \$index)"><i class="fa fa-th-list"></i></a></li>
	</ul>
</li>

<li ng-show="toggle" class="add-new-menu"><a style="cursor: pointer;" ng-click="findMenuItem('{$loc}')"><i class="fa fa-th-list"></i></a></li>
OUTPUT;
		}

		// empty, return
		if (!isset($this->menu[$loc])) return;

		// else carry on with nromal php loading
		foreach ($this->menu[$loc] as $m_item)
		{
			if (!empty($m_item["page_id"]) && $m_item["page_id.active"])
			{
				// sort any submenu items
				if (!isset($m_item["sub_menu"]))
				{
					echo '<li '.($this->logged_in < 7 ? '' : 'ng-if="!changed"').' '.($m_item["page_id"] == $this->page["id"] ? ' class="active"' : '').'>';
					echo '<a href="'.RAZOR_BASE_URL.$m_item["page_id.link"].'">'.$m_item["page_id.name"].'</a>';
				}
				else
				{
					echo '<li ng-if="!changed" class="dropdown'.($m_item["page_id"] == $this->page["id"] ? ' active' : '').'">';
					echo '<a class="dropdown-toggle" href="'.RAZOR_BASE_URL.$m_item["page_id.link"].'">'.$m_item["page_id.name"].' <i class="fa fa-caret-down"></i></a>';
					echo '<ul class="dropdown-menu">';
					foreach ($m_item["sub_menu"] as $sm_item)
					{
						if (!empty($sm_item["page_id"]) && $sm_item["page_id.active"])
						{
							echo '<li'.($sm_item["page_id"] == $this->page["id"] ? ' class="active"' : '').'>';
							echo '<a href="'.RAZOR_BASE_URL.$sm_item["page_id.link"].'">'.$sm_item["page_id.name"].'</a>';
							echo '</li>';   
						}
					}
					echo "</ul>";
				}

				echo '</li>';   
			}
		}
	}

	public function data_main()
	{
		// public or preview
		if (isset($_GET["preview"]) || (!$this->login && !isset($_COOKIE["token"]))) 
		{
			echo 'data-main="base-module"';
			return;
		}

		// logged in
		if (!isset($_GET["edit"]) || $this->logged_in < 6)
		{
			echo 'data-main="admin-access-module"';
			return;
		}

		// admin editable
		echo 'data-main="admin-edit-module"';
	}

	public function body()
	{
		// public or preview
		if (isset($_GET["preview"]) || (!$this->login && !isset($_COOKIE["token"])))
		{
			// start by opening body
			echo "<body>";

			// if public viewable only, allow google tracking code to be used
			if (!isset($_GET["preview"]) && !empty($this->site["google_analytics_code"]))
			{
				echo <<<OUTPUT
	<!-- google tracking script -->
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '{$this->site["google_analytics_code"]}', '{$_SERVER["SERVER_NAME"]}');
			ga('send', 'pageview');
		</script>
	<!-- google tracking script -->
OUTPUT;
			}

			// add in IE8 and below header
			echo <<<OUTPUT
	<!--[if lt IE 9]>
		<div class="ie8">
			<p class="message">
				<i class="fa fa-exclamation-triangle"></i> You are using an outdated version of Internet Explorer that is not supported, 
				please update your browser or consider using an alternative, modern browser, such as 
				<a href="http://www.google.com/chrome">Google Chome</a>.
			</p>
		<div>
	<![endif]-->
OUTPUT;

			// if public viewable only, allow google tracking code to be used
			if (!empty($this->site["cookie_message"]) && !empty($this->site["cookie_message_button"]))
			{
				echo <<<OUTPUT
	<!-- cookie message -->
	<div id="razor-cookie" class="cookie-message" ng-controller="cookieMessage">
		<div class="alert alert-info alert-dismissable ng-cloak" ng-if="!hideMessage">
			<p class="text-center">
				{$this->site["cookie_message"]}
				<button class="btn btn-default" ng-click="agree()">{$this->site["cookie_message_button"]}</button>
			</p>
		</div>
	</div>
	<!-- cookie message -->
OUTPUT;
			}

			return;
		}

		// logged in
		if (!isset($_GET["edit"]) || $this->logged_in < 6)
		{
			include(RAZOR_BASE_PATH."theme/partial/admin-access.php");
			return true;
		}

		include(RAZOR_BASE_PATH."theme/partial/admin-edit.php");
		return true;
	}

	private function get_site_data()
	{
		$db = new RazorDB();
		$db->connect("setting");
		$res = $db->get_rows(array("column" => "id", "value" => null, "not" => true));
		$db->disconnect(); 

		foreach ($res["result"] as $result)
		{
			switch ($result["type"])
			{
				case "bool":
					$this->site[$result["name"]] = (bool) $result["value"];
				break;
				case "int":
					$this->site[$result["name"]] = (int) $result["value"];
				break;
				default:
					$this->site[$result["name"]] = (string) $result["value"];
				break;
			}
		}
	}

	private function get_page_data()
	{
		$db = new RazorDB();
		$db->connect("page");
		$search = (empty($this->link) ? array("column" => "id", "value" => $this->site["home_page"]) : array("column" => "link", "value" => $this->link));
		$res = $db->get_rows($search);
		$db->disconnect(); 

		$this->page = ($res["count"] == 1 ? $res["result"][0] : null);
	}

	private function get_menu_data()
	{
		// if no page found, end here
		if (empty($this->page)) return;

		// collate all menus (to cut down on duplicate searches)
		$this->menu = array();

		$db = new RazorDB();
		$db->connect("menu_item");

		// set options
		$options = array(
			"join" => array(array("table" => "page", "join_to" => "page_id"), array("table" => "menu", "join_to" => "menu_id")),
			"order" => array("column" => "position", "direction" => "asc")
		);

		// get all menu_links
		$menus = $db->get_rows(array("column" => "id", "not" => true, "value" => null), $options);
		$menus = $menus["result"];

		// sort them into name
		foreach ($menus as $menu)
		{
			if (!isset($this->menu[$menu["menu_id.name"]])) $this->menu[$menu["menu_id.name"]] = array();

			if ($menu["level"] == 1) $this->menu[$menu["menu_id.name"]][] = $menu;

			if ($menu["level"] == 2)
			{
				$parent = count($this->menu[$menu["menu_id.name"]]) - 1;

				if (!isset($this->menu[$menu["menu_id.name"]][$parent]["sub_menu"])) $this->menu[$menu["menu_id.name"]][$parent]["sub_menu"] = array();

				$this->menu[$menu["menu_id.name"]][$parent]["sub_menu"][] = $menu;
			}
		}
		
		$db->disconnect();
	}

	private function add_new_menu($loc)
	{
		// check if menu exists in db, if yes return false to carry on
		if (in_array($loc, $this->all_menus)) return false;

		// create new menu
		$db = new RazorDB();
		$db->connect("menu");
		$db->add_rows(array("name" => $loc));
		$db->disconnect(); 

		return true;
	}

	private function get_content_data()
	{
		// if no page found, end here
		if (empty($this->page)) return;

		// grab all content
		$db = new RazorDB();
		$db->connect("page_content");

		// set options
		$options = array(
			"order" => array("column" => "position", "direction" => "asc")
		);

		$search = array("column" => "page_id", "value" => $this->page["id"]);

		$content = $db->get_rows($search, $options);
		$this->content = $content["result"];
		$db->disconnect(); 
	}

	private function get_all_menus()
	{
		$db = new RazorDB();
		$db->connect("menu");
		$search = array("column" => "id", "not" => true, "value" => null);
		$menus = $db->get_rows($search);
		$all_menus = array();
		foreach ($menus["result"] as $menu) $all_menus[] = $menu["name"];
		$this->all_menus = $all_menus;
	}
}
/* EOF */