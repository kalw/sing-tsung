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
require_once('classes/screen_base.sing_tsung.class.php');

class screen extends sing_tsung_screen_base{
	var $template = 'welcome.tpl';		
	var $screen_name = 'welcome';

	function populate_interface(){

		global $app, $dsn;
		if(!$app->peardb_init($dsn)){
			
		}
	}

}
?>
