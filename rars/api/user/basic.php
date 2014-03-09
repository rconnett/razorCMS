<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class UserBasic extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    // fetch logged in user details if logged in
    public function get($id)
    {
        // login check - if fail, return no data to stop error flagging to user
        if (!$this->check_access() || $id !== "current") $this->response(null, null, 204);

        // convert last logged for front end
        $user = $this->user;
        $user["last_logged_in"] = date("D jS M Y", $user["last_logged_in"]);

        // return the basic user details
        $this->response(array("user" => $user), "json");
    }
}

/* EOF */