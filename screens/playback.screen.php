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
require_once('classes/profile.class.php');

class screen extends sing_tsung_screen_base{
	var $template = 'notemplate.tpl';		
	var $screen_name = 'playback';

  function consider_get_post(){
    global $db;

    // What we should actually do here is construct the xml config file, so
    // that run() can run tsung and it will have the right config file.

  } // function consider_get_post


  /**
   * Just run and print what we want to.
   * The output of this is supposed to be just the pid or 0 if fail.  No reason
   * to have a whole template just for that.  This is an ajax-only screen.
   * The only reason I'm firing up the whole environment is so that we can get
   * the data out of the TsungProf class, and write the .xml config file for
   * tsung.  (those things are done in consider_get_post).
   */
  function run(){
    global $TSUNG_CONFIG;

    // This will eventually be a php_reactor-based solution.
    // This is a kludge until I can get that figured out.
    $path = $_SERVER['DOCUMENT_ROOT']."/".$_SERVER['SCRIPT_NAME'];
    $path = dirname($path)."/start_playback.php";
    system("/usr/bin/php {$path}");
  } // function run
} // class screen
?>
