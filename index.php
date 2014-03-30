<?php

/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
    // set session
    session_start();
    session_regenerate_id();
	
    // sidewide constants
    define("RAZOR_BASE_PATH", str_replace(array("index.php"), "", $_SERVER["SCRIPT_FILENAME"]));
    $port = ($_SERVER["SERVER_PORT"] == "80" ? "" : ":{$_SERVER["SERVER_PORT"]}");
    define("RAZOR_BASE_URL", "http://".$_SERVER["SERVER_NAME"].$port.str_replace(array("index.php"), "", $_SERVER["SCRIPT_NAME"]));
    define("RAZOR_USERS_IP", $_SERVER["REMOTE_ADDR"]);
    define("RAZOR_USERS_UAGENT", $_SERVER["HTTP_USER_AGENT"]);

    // includes
	include_once(RAZOR_BASE_PATH.'library/php/razor/razor_file_tools.php');
	include_once(RAZOR_BASE_PATH.'library/php/razor/razor_error_handler.php');
    include_once(RAZOR_BASE_PATH.'library/php/razor/razor_site.php');
    include_once(RAZOR_BASE_PATH."library/php/razor/razor_db.php");

	// Load error handler
	$error = new RazorErrorHandler();
	set_error_handler(array($error, 'handle_error'));
	set_exception_handler(array($error, 'handle_error'));

    // continue with public load
    $site = new RazorSite();
    $site->load();
    $site->render();
 
/* PHP END */