<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class ContentEditor extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    public function get($page_id)
    {
        // go through all changes and update all
        $db = new RazorDB();
        $db->connect("page_content");

        // set options
        $options = array(
            "join" => array("table" => "content", "join_to" => "content_id"),
            "order" => array("column" => "position", "direction" => "asc")
        );

        $search = array("column" => "page_id", "value" => (int) $page_id);

        $results = $db->get_rows($search, $options)["result"];
        $db->disconnect(); 

        // split into content and locations
        $content = array();
        $locations = array();
        foreach ($results as $row)
        {
            $content[$row["content_id"]] = array(
                "content_id" => $row["content_id"],
                "name" => $row["content_id.name"],
                "content" => $row["content_id.content"]
            );

            $locations[$row["location"]][$row["column"]][] = array("id" => $row["id"], "content_id" => $row["content_id"]);
        }
        
        // return the basic user details
        $this->response(array("content" => $content, "locations" => $locations), "json");
    }

    // add or update content
    public function post($data)
    {
        // login check - if fail, return no data to stop error flagging to user
        if (!$this->check_access()) $this->response(null, null, 401);
        if (!isset($data["content"])) $this->response(null, null, 400);

        // update content
        $db = new RazorDB();
        $db->connect("content");

        // update or add content
        $new_content_map = array();
        foreach ($data["content"] as $content)
        {
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
                $new_content_map[$content["content_id"]] = $db->add_rows($row)["result"][0]["id"];   
            }
        }

        $db->disconnect(); 

        // update or add locations

        $db = new RazorDB();
        $db->connect("page_content");

        // 1. first take snapshot of current
        $search = array("column" => "page_id", "value" => (int) $data["page_id"]);
        $current_page_content = $db->get_rows($search)["result"];

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
                        $db->edit_rows($search, array("location" => $location, "column" => (int) $column, "position" => $pos + 1));
                        $page_content_map[] = $block["id"];
                    }
                    else
                    {
                        // add new
                        $row = array(
                            "page_id" => (int) $data["page_id"],
                            "content_id" => (isset($new_content_map[$block["content_id"]]) ? $new_content_map[$block["content_id"]] : $block["content_id"]),
                            "location" => $location,
                            "column" => (int) $column,
                            "position" => $pos + 1
                        );

                        $page_content_map[] = $db->add_rows($row)["result"][0];  
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