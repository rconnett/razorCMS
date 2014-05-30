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
 
class SettingData extends RazorAPI
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
        if ((int) $this->check_access() < 9) $this->response(null, null, 401);
        if (empty($data)) $this->response(null, null, 400);

        $db = new RazorDB();
        $db->connect("setting");

        if (isset($data["name"]))
        {
            $search = array("column" => "name", "value" => "name");
            $db->edit_rows($search, array("value" => $data["name"]));
        }
    
        if (isset($data["google_analytics_code"]))
        {
            $search = array("column" => "name", "value" => "google_analytics_code");
            $db->edit_rows($search, array("value" => $data["google_analytics_code"]));
        }
    
        if (isset($data["allow_registration"]))
        {
            $search = array("column" => "name", "value" => "allow_registration");
            $db->edit_rows($search, array("value" => (string) $data["allow_registration"]));
        }
    
        if (isset($data["manual_activation"]))
        {
            $search = array("column" => "name", "value" => "manual_activation");
            $db->edit_rows($search, array("value" => (string) $data["manual_activation"]));
        }
    
        if (isset($data["registration_email"]))
        {
            $search = array("column" => "name", "value" => "registration_email");
            $db->edit_rows($search, array("value" => (string) $data["registration_email"]));
        }
    
        if (isset($data["activation_email"]))
        {
            $search = array("column" => "name", "value" => "activation_email");
            $db->edit_rows($search, array("value" => (string) $data["activation_email"]));
        }
    
        if (isset($data["activate_user_email"]))
        {
            $search = array("column" => "name", "value" => "activate_user_email");
            $db->edit_rows($search, array("value" => (string) $data["activate_user_email"]));
        }

        $db->disconnect(); 
        $this->response("success", "json");
    }
}

/* EOF */