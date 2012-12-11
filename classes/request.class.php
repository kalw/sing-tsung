<?php


/**
 *
 * Class Request : Sitebuilder
 *
 * This class will contain data describing a system action
 * that should be taken by a privileged process.  It includesd
 * the name of the function and the arguments, if any, to pass it.
 *
 * @author		Brook Davis <brook@linuxbox.com>
 * @copyright		(c) 2006 Brook Davis, The Linux Box Corp.
 * @version		2.0
 * @package		undercarriage
 */

class Request{


	/**
	 * The id of the Request
	 * @var		integer
	 * @access	public
	 */
	var $id;

	/**
	 * The type of request
	 * @var		string
	 * @access	public
	 */
	var $request_type;

	/**
	 * Arguments to be passed to the function
	 * @var		array
	 * @access	public
	 */
	var $request_data;


	/**
	*
	* function Request()
	*
	* Constructor for class Request
	*
	* @return	void
	* @access	public
	* @param	$type	The request type
	* @param	$data	an array of data for the request
	*/
	function Request($type, $data){
		$this->request_type=$type;
		$this->request_data=$data;
	}
}


?>
