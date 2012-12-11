<?php
/**
 * Initializes upbase to handle requests.  Included by index.php.
 *
 * $Id: $
 *
 * COPYRIGHT
 *
 * Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
 * 206 S. Fifth Avenue Suite 150
 * Ann Arbor, MI 48104
 * http://www.linuxbox.com
 *
 * @author Ziba Scott <ziba@linuxbox.com>
 * @copyright Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
 * @package upbase
 */

// set to the user defined error handler
require_once('upbase/classes/errors.class.php');
$old_error_handler = set_error_handler("custom_error_handler");

// Check for the presence of a configuration file
if(!is_file('config.upbase.php')){
	require_once('upbase/config.default.upbase.php');
	trigger_error('config.upbase.php not found, using upbase/config.default.upbase.php');
}
else{
	require_once('config.upbase.php');
}

/**
 * Stores information on the state of the application.
 * @global UpbaseApp $app
 */
// I set it now, to please the documentor.  It's actually set later.
$app = false;

// Check for the presence of the requested app class file 
if(!isset($upbase_app_class)){
	$upbase_app_class = 'upbase/classes/app.class.php';
	require_once($upbase_app_class);
	$app = new UpbaseApp();
}
elseif(!is_file($upbase_app_class) || empty($upbase_app_class)){
	trigger_error('Your chosen upbase_app_class was not found: "'.$upbase_app_class.'", using upbase/classes/app.class.php.  The upbase_app_class variable should contain a file name.  Please check your upbase configuration file');
	require('upbase/classes/app.class.php');
	$app = new UpbaseApp();
}
else{
	require_once($upbase_app_class);
	if(!class_exists('App')){
    trigger_error('Your app class file, '.$upbase_app_class.', does not contain a class named app or App.', E_USER_ERROR);
  }
	$app = new App();
}

$app->dbug('Application class file',$upbase_app_class);

class screen_handler{

	function make_screen_object($get, $post){
		global $upbase_screens_dirs, $app;
		$screen_found = FALSE;
		// Check for a screen request
		if(!isset($get['screen']) && !isset($post['screen'])){
			global $upbase_default_screen;
			if(!empty($upbase_default_screen)){
				$screen_name = $upbase_default_screen;
			}
			else{
				$screen_name = 'no_requested_screen';
			}
		}
		elseif(isset($get['screen'])){
			$screen_name = $get['screen'];
		}
		else{
			$screen_name = $post['screen'];
		}

		// Check for the existence of a corresponding php file
		foreach($upbase_screens_dirs as $dir){
			if(!is_file($dir.'/'.$screen_name.'.screen.php')){
				$app->dbug('Screen not found in '.$dir,$screen_name.'.screen.php');
			}
			else{
				require_once($dir.'/'.$screen_name.'.screen.php');
				if(!class_exists('screen')){
					trigger_error("Malformed screen file: ".$screen_name);
					$screen_name = 'malformed_screen';
					require_once($dir.'/'.$screen_name.'.screen.php');
				}
				else{
					$screen_found = TRUE;
					$screen = new screen($get, $post);
					break;
				}
			}
		}

		if(!$screen_found){
		  foreach($upbase_screens_dirs as $dir){
			  if(is_file($dir.'/no_screen_exists.screen.php')){
				require_once($dir.'/no_screen_exists.screen.php');
 			  $screen = new screen($get, $post);
        break;
        }
      }
		}
		
		$app->smarty->assign('screen_name',$screen_name);
		return $screen;
	} // function make_screen_object

	function screen_handler(){

		// Create database and application (smarty) handles to be used later
		//$db = new FocusDB($dsn);
		global $app;

		$post = $app->clean_array($_POST);
		$get = $app->clean_array($_GET);

		$screen = $this->make_screen_object($get, $post);
		$screen->run();
	} // function screen_handler
} // class screen_handler

	new screen_handler();
?>
