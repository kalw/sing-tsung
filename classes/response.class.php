<?php


/**
 *
 * Class Response : Sitebuilder
 *
 * This class contains a response to the frontend application
 * that contains any error code info or return values.  It also
 * contains the original request object.
 *
 * @author		Brook Davis <brook@linuxbox.com>
 * @copyright		(c) 2006 by Brook Davis, The Linux Box Corp.
 * @version		2.0
 * @package		undercarriage
 */
class Response{

	/**
	 * The error code, if any
	 * @var		integer
	 * @access	public
	 */
	var $error_code;

	/**
	 * A specific error messaage, if any
	 * @var		string
	 * @access	public
	 */
	var $message;

	/**
	 * The request that initiated this response
	 * @var		object
	 * @access	public
	 */
	var $original_request;





}


?>
