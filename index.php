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

// permission defines
// 6 to 10 - access to admin dash
define("SUPER_ADMIN", 10); // only one account with this and it cannot be removed
define("ADMIN", 9); // pretty much the same as super admin but can be removed
define("MANAGER", 8); // add, edt, remove content only
define("EDITOR", 7); // add, edit content only
define("CONTRIBUTER", 6); // add content only
// 1 to 5 - no access to admin dash, user levels only
define("USER", 1); // base level, can onlyalter profile and user areas of public site that are protected to level 1

// includes
include_once(RAZOR_BASE_PATH.'library/php/razor/razor_file_tools.php');
include_once(RAZOR_BASE_PATH.'library/php/razor/razor_error_handler.php');
include_once(RAZOR_BASE_PATH.'library/php/razor/razor_site.php');
include_once(RAZOR_BASE_PATH."library/php/razor/razor_db.php");

// includes
include_once(RAZOR_BASE_PATH.'library/php/razor/razor_file_tools.php');
include_once(RAZOR_BASE_PATH.'library/php/razor/razor_error_handler.php');
include_once(RAZOR_BASE_PATH.'library/php/razor/razor_site.php');
include_once(RAZOR_BASE_PATH."library/php/razor/razor_db.php");

// Load error handler
$error = new RazorErrorHandler();
set_error_handler(array($error, 'handle_error'));
set_exception_handler(array($error, 'handle_error'));

// check any required folders exist
if (!is_dir(RAZOR_BASE_PATH."extension")) mkdir(RAZOR_BASE_PATH."extension");

// continue with public load
$site = new RazorSite();
$site->load();
$site->render();
 
/* PHP END */