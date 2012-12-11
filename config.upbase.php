<?php
/*
 * Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
 *   206 S. Fifth Avenue Suite 150
 *   Ann Arbor, MI 48104
 *   http://www.linuxbox.com
 * Written by Ryan Hughes (ryan@linuxbox.com)
 *  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version
 * 2 of the License, or (at your option) any later version.
 */

  // Debugging
	$upbase_debugging = TRUE;

  // Default Screen
	$upbase_default_screen = 'welcome';

  // Core upbase classes (can be replaced with custom/inheriting classes)
	$upbase_app_class = 'classes/app.class.php';

  // Directory search order for screens and templates
	$upbase_screens_dirs[0] = 'screens';
	$upbase_screens_dirs[1] = 'upbase/default_screens';

	$upbase_templates_dirs[0] = 'smarty/templates';
	$upbase_templates_dirs[1] = 'upbase/default_templates';
	
  // Logging directives
	$upbase_log_file_directory="/tmp";

  // File permission requirements
	$upbase_web_writable_dirs[]='smarty/templates_c';
	$upbase_web_writable_dirs[]='smarty/cache';
	$upbase_web_writable_dirs[]=$upbase_log_file_directory;

  // Smarty configuration options
	$upbase_freeze_template_cache = FALSE;
	$upbase_smarty_freeze_template_cache = FALSE;
	//$upbase_smarty_cache_directory = '/tmp'; // Defaults to $upbase_templates_dirs[0].'_c'
?>
