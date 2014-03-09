<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class UserData extends RazorAPI
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
        if (!$this->check_access()) $this->response(null, null, 401);
        if (empty($data)) $this->response(null, null, 400);

        $db = new RazorDB();
        $db->connect("user");

        // check link unique
        $search = array("column" => "id", "value" => $this->user["id"]);

        $row = array(
            "name" => $data["name"], 
            "email_address" => $data["email_address"]
        );

        if (isset($data["new_password"])) $row["password"] = $this->create_hash($data["new_password"]);

        $db->edit_rows($search, $row);

        $db->disconnect(); 

        // return the basic user details
        if (isset($data["new_password"])) $this->response(array("reload" => true), "json");

        $this->response("success", "json");
    }
}

/* EOF */