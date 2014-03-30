<?php if (!defined("RARS_BASE_PATH")) die("No direct script access to this content");

/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith - adapted from nicedit script, details below
 * @site ulsmith.net
 * @created Feb 2014
 */
 
/* NicEdit - Micro Inline WYSIWYG
 * Copyright 2007-2009 Brian Kirchoff
 *
 * NicEdit is distributed under the terms of the MIT license
 * For more information visit http://nicedit.com/
 * Do not remove this copyright message
 *
 * nicUpload Reciever Script PHP Edition
 * @description: Save images uploaded for a users computer to a directory, and
 * return the URL of the image to the client for use in nicEdit
 * @author: Brian Kirchoff <briankircho@gmail.com>
 * @sponsored by: DotConcepts (http://www.dotconcepts.net)
 * @version: 0.9.0
 */

/* 
* @author: Christoph Pahre
* @version: 0.1
* @description: different modification, so that this php file is working with the newest nicEdit.js (needs also modification - @see) 
* @see http://stackoverflow.com/questions/11677128/nicupload-says-invalid-upload-id-cant-make-it-works
*/

class FileImage extends RazorAPI
{
    function __construct()
    {
        // REQUIRED IN EXTENDED CLASS TO LOAD DEFAULTS
        parent::__construct();
    }

    // add or update content
    public function post()
    {
        if ((int) $this->check_access() < 10) $this->response(null, null, 401);


        define('NICUPLOAD_PATH', RAZOR_BASE_PATH.'storage/files/images'); // Set the path (relative or absolute) to
                                              // the directory to save image files

        define('NICUPLOAD_URI', RAZOR_BASE_URL.'storage/files/images');   // Set the URL (relative or absolute) to
                                              // the directory defined above

        if (!is_dir(RAZOR_BASE_PATH.'storage/files')) mkdir(RAZOR_BASE_PATH.'storage/files');
        if (!is_dir(RAZOR_BASE_PATH.'storage/files/images')) mkdir(RAZOR_BASE_PATH.'storage/files/images');

        $nicupload_allowed_extensions = array('jpg','jpeg','png','gif','bmp');

        if(!function_exists('json_encode')) {
            die('{"error" : "Image upload host does not have the required dependicies (json_encode/decode)"}');
        }

        if($_SERVER['REQUEST_METHOD']=='POST') { // Upload is complete

            $file = $_FILES['image'];
            $image = $file['tmp_name'];
            $id = $file['name'];

            $max_upload_size = $this->ini_max_upload_size();
            if(!$file) {
                $this->nicupload_error('Must be less than '.$this->bytes_to_readable($max_upload_size));
            }

            $ext = strtolower(substr(strrchr($file['name'], '.'), 1));
            @$size = getimagesize($image);
            if(!$size || !in_array($ext, $nicupload_allowed_extensions)) {
                $this->nicupload_error('Invalid image file, must be a valid image less than '.$this->bytes_to_readable($max_upload_size));
            }

            $filename = $id;
            $path = NICUPLOAD_PATH.'/'.$filename;

            if(!move_uploaded_file($image, $path)) {
                $this->nicupload_error('Server error, failed to move file');
            }

            $status = array();
            $status['done'] = 1;
            $status['width'] = $size[0];
            $rp = realpath($path);
            $status['url'] =  NICUPLOAD_URI ."/".$id;


            $this->nicupload_output($status, false);
            exit;
        } 
    }

    private function nicupload_error($msg) {
        echo $this->nicupload_output(array('error' => $msg)); 
    }

    private function nicupload_output($status, $showLoadingMsg = false) {
        $script = json_encode($status);
        $script = str_replace("\\/", '/', $script);
        echo $script;

        exit;
    }

    private function ini_max_upload_size() {
        $post_size = ini_get('post_max_size');
        $upload_size = ini_get('upload_max_filesize');
        if(!$post_size) $post_size = '8M';
        if(!$upload_size) $upload_size = '2M';

        return min( $this->ini_bytes_from_string($post_size), $this->ini_bytes_from_string($upload_size) );
    }

    private function ini_bytes_from_string($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    private function bytes_to_readable( $bytes ) {
        if ($bytes<=0)
            return '0 Byte';

        $convention=1000; //[1000->10^x|1024->2^x]
        $s=array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
        $e=floor(log($bytes,$convention));
        return round($bytes/pow($convention,$e),2).' '.$s[$e];
    }
}

/* EOF */