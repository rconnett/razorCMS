<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class SiteData extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    // add or update content
    public function post($data)
    {
        // login check - if fail, return no data to stop error flagging to user
        if ((int) $this->check_access() < 10) $this->response(null, null, 401);
        if (empty($data)) $this->response(null, null, 400);

        $db = new RazorDB();
        $db->connect("site");

        // check link unique
        $search = array("column" => "id", "value" => 1);

        $row = array();

        if (isset($data["name"])) $row["name"] = $data["name"];
        if (isset($data["home_page"])) $row["home_page"] = (int) $data["home_page"];

        $db->edit_rows($search, $row);

        $db->disconnect(); 

        // activate home page if not
        if (isset($data["home_page"]))
        {
            $db->connect("page");
            $search = array("column" => "id", "value" => (int) $data["home_page"]);
            $row = array("active" => true);
            $db->edit_rows($search, $row);
            $db->disconnect();         
        }

        $this->response("success", "json");
    }
}

/* EOF */