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

class FileImage extends RazorAPI
{
	private $root_path = null;
	private $root_url = null;
	private $image_types = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
	private $image_ext = array("jpg", "jpeg", "gif", "png");

	function __construct()
	{
		// REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
		parent::__construct();

		// imagepath and relative url (important when shifting domains)
		$this->root_path = RAZOR_BASE_PATH.'storage/files/images';
		$this->root_url = str_replace("http://{$_SERVER["SERVER_NAME"]}".($_SERVER["SERVER_PORT"] == "80" ? "" : ":{$_SERVER["SERVER_PORT"]}"), "", RAZOR_BASE_URL).'storage/files/images';
	}

	public function get()
	{
		if ((int) $this->check_access() < 6) $this->response(null, null, 401);
		
		// check if folders exist
		if (!is_dir($this->root_path)) $this->response(null, null, 401);

		// grab folder here, load in the files for a particular folder
		$files = RazorFileTools::read_dir_contents($this->root_path, $type = 'files');

		// remove anything not an image file ext
		foreach ($files as $key => $file)
		{
			$path_parts = explode('.', $file);
			if (!in_array(end($path_parts), $this->image_ext) || !in_array(exif_imagetype("{$this->root_path}/{$file}"), $this->image_types))
			{
				unset($files[$key]);
				continue;
			}

			$files[$key] = array("url" => "{$this->root_url}/{$file}", "name" => $file);
		}
		sort($files);

		// json encode
		$this->response(array("imageList" => array_values($files)), "json");
	}

	// add or update content
	public function post()
	{
		if ((int) $this->check_access() < 6) $this->response(null, null, 401);

		// check if folders exist
		if (!is_dir(RAZOR_BASE_PATH."storage/files")) mkdir(RAZOR_BASE_PATH."storage/files");
		if (!is_dir(RAZOR_BASE_PATH."storage/files/images")) mkdir(RAZOR_BASE_PATH."storage/files/images");

		$files = array();
		foreach ($_FILES as $file)
		{
			// check type and ext, return 406 if file invalid
			$file_ext = explode(".", strtolower($file["name"]));
			if (!in_array(end($file_ext), $this->image_ext) || !in_array(exif_imagetype($file["tmp_name"]), $this->image_types)) $this->response(null, null, 406);

			// next check for errors
			if (!isset($file['error']) || is_array($file['error'])) throw new Exception('Invalid file upload parameters');
			
			switch ($file['error']) {
				case UPLOAD_ERR_OK:
				break;
				case UPLOAD_ERR_NO_FILE:
					throw new Exception('No file sent.');
				break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new Exception('Exceeded filesize limit.');
				break;
				default:
					throw new Exception('Unknown errors.');
				break;
			}

			// check size, return 406 if file invalid
			if ($file['size'] > 50000000) $this->response(null, null, 406);

			// finally clean data (as we want to use the name, i know this is not very secure but really, we need to keep them readable and I have checked EVERYTHING else and cleaned the name)
			$name = preg_replace("/[^a-z0-9\\040\\.\\-\\_\\\\]/", "", strtolower($file["name"]));
			$files[] = array(
				"name"	  => $name,
				"tmp_name"  => $file["tmp_name"],
				"url"	   => "{$this->root_url}/{$name}",
			);
		}

		// if no errors, all files fine, so add them
		foreach ($files as $key => $file)
		{
			move_uploaded_file($file["tmp_name"], "{$this->root_path}/{$file["name"]}");
			unset($files[$key]["tmp_name"]);
		}

		// json encode
		$this->response(array("files" => $files), "json");
	}
}

/* EOF */