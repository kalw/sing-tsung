<?php
/*****************************************************************************


AUTHORS

Mike Gatny <mike@linuxbox.com>
Ziba Scott <ziba@linuxbox.com>


COPYRIGHT

Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
206 S. Fifth Avenue Suite 150
Ann Arbor, MI 48104
http://www.linuxbox.com


*****************************************************************************/

/** @file app.class.php */

require_once("upbase/classes/app.class.php");

class App extends UpbaseApp{
	var $tail_log = FALSE;
	
	/**
	 * Application constructor
	 * 
	 * @param array $config the app's config hash
	 */
	function App() {
		return parent::UpbaseApp();
	}

	function jpgraph_init(){
		return parent::jpgraph_init();
	}

	function peardb_init($dsn, $options = FALSE){
		return parent::peardb_init($dsn, $options);
	}

  	function phpmailer_init(){
		return parent::phpmailer_init();
    }

	function dbug($name, $value){
		return parent::dbug($name,$value);
	}

	function display($template){
		return parent::display($template);
	}
	
	function check_required_permissions(){
		return parent::check_required_permissions();
	}

	function log($file, $message){
		return parent::log($file,$message);
	}

	// Sanitizing methods
	/**
	 * Check string for sql injection attacks.  Every value that originates in GET and is put into a sql statement
	 * must be passed through this function for security.
	 * @param string $string A string to be sanitized.
	 */
	function clean_sql($string){
		return parent::clean_sql($string);
	}
	
	/**
	 * Cleans an array.
	 * This function validates and cleans the values passed in ian array.
	 * It ensures that malicious actions cannot be performed.
	 * @return array Clean values
	 */
	function clean_array($array){
		return parent::clean_array($array);
	}


} // App

?>
