<?php
require_once('upbase/classes/screen_base.class.php');

class upbase_demo extends screen_base{
	// The navigation propery contains the information used to display the 
	// navigational menu for this demonstration.  This menu system is just
	// one example of how a developer could handle navigation in Upbase.
	var $navigation = array(
			'Welcome'=>'welcome',
			'Simple Table'=>'simple_table_demo',
			'Email'=>'email_demo',
			'Excel'=>'excel_demo',
			'PDF'=>'pdf_demo',
			'Graph'=>'graph_demo',
			'Debugging/Timing'=>'debugging_demo',
			'AJAX'=>'ajax_demo',
			'Database Abstraction'=>'database_demo',
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
			$links[]='<a href="index.php?screen='.$screen.'">'.$title.'</a> |';	
		}
		$app->smarty->assign('navigation',$links);
	}
}

?>
