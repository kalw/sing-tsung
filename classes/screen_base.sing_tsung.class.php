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
require_once('upbase/classes/screen_base.class.php');

class sing_tsung_screen_base extends screen_base{
	// The navigation propery contains the information used to display the 
	// navigational menu for this demonstration.  This menu system is just
	// one example of how a developer could handle navigation in Upbase.
	var $navigation = array(
			'Record a Session'=>'record',
			'Manage Profiles' => 'manage',
			'Run a Profile' => 'run'			

	);

	// Spreadsheet header is a multi-dimensional array with a structure used by many 
	// components in Upbase.  Here the header is used to provide information that will
	// be used to reformat demo_data into a nice, human readable format as it passes
	// through an Upbase convenience function.
	var $demo_data_header = array(
		array('name'=>'People'),
		array('name'=>'Dates','type'=>'date'),
	);

	// Demo data is another multi-dimensional array.  It holds multiple rows of records
	// described by demo_data_header.  Data that has been formatted and described this way
	// is ready to be handled by Upbase's many convenience functions.
	var $demo_data = array(
		array('People'=>'Jim','Dates'=>'1234567890'),
		array('People'=>'Dave','Dates'=>'1234567890'),
		array('People'=>'Jango','Dates'=>'1234567890'),
	);

	// This demonstration uses a relativly simple navigation system.  assign_navigation()
	// is a member of the screen_base class, a part of Upbase, which is called when
	// building a page.  Here it is overridden with a method which turns the above
	// navigation array into an array of links.  That array is then assign to smarty
	// and will be displayed by the smarty template written for navigation (navigation.tpl).
	function assign_navigation(){
		global $app;
		$links = array();
		foreach($this->navigation as $title=>$screen){
      if ($screen == $this->screen_name) {
        $links[]='<a href="index.php?screen='.$screen.'" class="navhere">'.$title.'</a> |';	
      } else {
        $links[]='<a href="index.php?screen='.$screen.'">'.$title.'</a> |';	
      } // if it's this screen or not
		}
		$app->smarty->assign('navigation',$links);
	} // function assign_navigation

  function run(){
    global $app;
    $this->get = $_GET;
    $this->post = $_POST;
    $app->smarty->assign('screen_name', $this->screen_name);
    parent::run();
  } // function run

}

?>