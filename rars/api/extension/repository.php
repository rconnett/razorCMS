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
 
class ExtensionRepository extends RazorAPI
{
    private $repo_url = "http://archive.razorcms.co.uk/";
    private $ext_list = "extension.list.json";

    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    public function get($id)
    {
        if ((int) $this->check_access() < 10) $this->response(null, null, 401);
        if (empty($id)) $this->response(null, null, 400);

        $headers = @get_headers($this->repo_url.$this->ext_list);
      
        if(strpos($headers[0], "404") === false) 
        {
            $ctx = stream_context_create(array( 
                'http' => array( 
                    'timeout' => 60
                    ) 
                ) 
            );             

            $repo_file = @file_get_contents($this->repo_url.$this->ext_list, false, $ctx);

            if (!empty($repo_file))
            {
                $repo = json_decode($repo_file);
                $this->response(array("repository" => $repo), "json");
            }
        }

        // send back unnavailable
        $this->response(null, null, 404);
    }

    public function post($data)
    {
        if ((int) $this->check_access() < 10) $this->response(null, null, 401);
        if (empty($data) || !isset($data["category"]) || !isset($data["handle"]) || !isset($data["name"]) || !isset($data["manifests"])) $this->response(null, null, 400);
        if (!is_array($data["manifests"]) || count($data["manifests"]) < 1) $this->response(null, null, 400);

        //"category": "communication", "handle": "razorcms", "name": "contact-form", "displayName": "Contact Form", "manifests": ["contact-form"], "images": []
        
        // fetch cleaned data
        $category = preg_replace('/[^a-zA-Z0-9-_]/', '', $data["category"]);
        $handle = preg_replace('/[^a-zA-Z0-9-_]/', '', $data["handle"]);
        $name = preg_replace('/[^a-zA-Z0-9-_]/', '', $data["name"]);
        $manifest = preg_replace('/[^a-zA-Z0-9-_]/', '', $data["manifests"][0]); // grab first only

        // fetch details
        $man_url = $this->repo_url."extension/{$category}/{$handle}/{$name}/{$manifest}.manifest.json";
        $headers = @get_headers($man_url);

        if(strpos($headers[0], "404") === false) 
        {
            $ctx = stream_context_create(array( 
                'http' => array( 
                    'timeout' => 60
                    ) 
                ) 
            );             

            $details_file = @file_get_contents($man_url, false, $ctx);

            if (!empty($details_file))
            {
                $details = json_decode($details_file);
                $this->response(array("details" => $details), "json");
            }
        }

        // send back not found if no details
        $this->response(null, null, 404);
    }
}

/* EOF */