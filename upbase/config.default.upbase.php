<?php
  /**
   * Turn on or off debugging.
   * @global boolean $upbase_debugging
   */
	$upbase_debugging = TRUE;

  /**
   * Core upbase class definition files.
   * This is the path to a file that defines a class called App.  The defaults
   * are hard-coded.  If you want to override this, then uncomment it and
   * add your own class file.
   *
   * @global string $upbase_app_class
   */
	//$upbase_app_class = 'upbase/classes/app.class.php';

  /** Upbase error class.
   * @global string $upbase_errors_class
   */
	//$upbase_errors_class = 'upbase/classes/errors.class.php';

  // Directory search order for screens and templates
	//$upbase_screens_dirs[0] = 'screens';
	$upbase_screens_dirs[1] = 'upbase/default_screens';
	//$upbase_templates_dirs[0] = 'smarty/templates';
	$upbase_templates_dirs[1] = 'upbase/default_templates';
	
  // Logging directives
	$upbase_log_file_directory="/tmp";

  // File permission requirements
	//$upbase_web_writable_dirs[]='smarty/templates_c';
	//$upbase_web_writable_dirs[]='smarty/cache';
	$upbase_web_writable_dirs[]=$upbase_log_file_directory;

  // Smarty configuration options
	$upbase_freeze_template_cache = FALSE;
	$upbase_smarty_freeze_template_cache = FALSE;
	$upbase_smarty_cache_directory = '/tmp'; // Defaults to $upbase_templates_dirs[0].'_c'
?>
