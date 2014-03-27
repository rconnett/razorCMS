<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class ExtensionData extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    public function post($ext)
    {
        if ((int) $this->check_access() < 10) $this->response(null, null, 401);
        if (empty($ext)) $this->response(null, null, 400);

        $settings = array();
        foreach ($ext["settings"] as $set) $settings[$set["name"]] = $set["value"];

        $db = new RazorDB();
        $db->connect("extension");
        $options = array("amount" => 1);
        $search = array(
            array("column" => "extension", "value" => $ext["extension"]),
            array("column" => "type", "value" => $ext["type"]),
            array("column" => "handle", "value" => $ext["handle"])
        );
        $extension = $db->get_rows($search, $options);

        if ($extension["count"] == 1) $db->edit_rows($search, array("json_settings" => json_encode($settings)));
        else 
        {
            // add new
            $row = array(
                "extension" => $ext["extension"],
                "type" => $ext["type"],
                "handle" => $ext["handle"],
                "json_settings" => json_encode($settings),
                "user_id" => $this->user["id"],
                "access_level" => 0
            );
            $db->add_rows($row);
        }

        $db->disconnect(); 

        $this->response("success", "json");
    }
}

/* EOF */