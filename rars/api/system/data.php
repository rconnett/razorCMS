<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class SystemData extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    public function get($id)
    {
        if ((int) $this->check_access() < 6) $this->response(null, null, 401);
        if (empty($id)) $this->response(null, null, 400);

        // get menu data too
        $db = new RazorDB();
        $db->connect("system");

        $search = array("column" => "id", "value" => 1);
        $system = $db->get_rows($search);
        $system = $system["result"][0];
        
        $db->disconnect(); 

        $this->response(array("system" => $system), "json");
    }
}

/* EOF */