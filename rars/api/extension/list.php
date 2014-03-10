<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

class ExtensionList extends RazorAPI
{
    private $types = array("theme");

    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    public function get($type)
    {
        if (empty($type) || !in_array($type, $this->types)) $this->response(null, null, 400);

        // first scan the folders for manifests
        $manifests = RazorFileTools::find_file_contents(RAZOR_BASE_PATH."extension", "manifest.json", "json", "end");

        // split into types, so we can filter a little
        $extensions = array();
        foreach ($manifests as $mf)
        {
            if (!isset($extensions[$mf->type])) $extensions[$mf->type] = array();

            $extensions[$mf->type][] = $mf;
        }

        $response = ($type == "all" ? $extensions : $extensions[$type]);
        
        $this->response(array("extensions" => ($type == "all" ? $extensions : $extensions[$type])), "json");
    }
}

/* EOF */