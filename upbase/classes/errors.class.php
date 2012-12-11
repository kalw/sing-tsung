<?php

function custom_error_handler($errno, $errstr, $errfile, $errline){
  errors::error_handler($errno, $errstr, $errfile, $errline);
}


class errors {

	function error_handler($errno, $errstr, $errfile, $errline)
	{
	  global $app;
	  if(!is_object($app) ||  !is_writable($app->smarty->compile_dir)){
		switch ($errno) {
		  case E_USER_ERROR:
			  echo "<b>Sorry.  You have encountered a serious error:</b><br/>$errstr<br />Please contact your system administrator<br/>\n".date("m/d/Y H:i:s");
		   //echo "  Fatal error in line $errline of file $errfile";
		   exit(1);
		   break;
		  case E_USER_WARNING:
		   echo "<b>Warning:</b> $errstr<br/><br/>";
		   break;
		  case E_USER_NOTICE:
			  echo "<b>Notice:</b> $errstr\n<br><br/>";
		   break;
		  case E_WARNING:
		   echo "<b>Warning: $errstr</b>:<br>\n$errfile ($errline)<br><br>";
		   break;
		  case E_NOTICE:
			  //echo "Notice: $errstr: $errfile ($errline)<br/>";
		   break;
		  default:
			  //suppressing a php4 feature depricated in php5
			  if(substr($errstr,'var: Depricated.') === FALSE ){
				echo "<b>Unknown error type:</b> [$errno] $errstr:\n $errfile ($errline)<br/><br/>\n";
			  }
		   break;
		}

		}elseif($app->ajax){
	   switch ($errno) {
		  case E_USER_ERROR:
		   echo "alert('Sorry.  You have encountered a serious error: $errstr. Please contact your system administrator".date("m/d/Y H:i:s").");";
		   exit(1);
		   break;
		  case E_USER_WARNING:
			   echo "alert('Warning: $errstr');";
			   break;
		  case E_USER_NOTICE:
			   echo "alert('$errstr');";
		   break;
		  case E_NOTICE:
			   echo "alert('$errstr');";
		   break;
		  default:
			  //suppressing a php4 feature depricated in php5
			  if(substr($errstr,'var: Depricated.') === FALSE ){
			   echo "alert('Unknown error type: $errstr');";
			  }
		   break;
		  }
	  }else{
	   switch ($errno) {
		  case E_USER_ERROR:
		   echo "<b>Sorry.  You have encountered a serious error:</b><br/>$errstr<br />Please contact your system administrator<br/>\n".date("m/d/Y H:i:s");
		   /*
		   echo "<b>Sorry.  You have encountered a fatal error.</b> [$errno] $errstr<br />\n";
		   echo "  Fatal error in line $errline of file $errfile";
		   echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
		   echo "Aborting...<br />\n";
		   */
		   exit(1);
		   break;
		  case E_USER_WARNING:
			  $app->sys_errors[]="Warning: $errstr";
			   //$app->smarty->assign('sys_errors',$app->sys_errors);
			   /*
			   $app->smarty->assign('mid','blank.tpl');
			   $app->display("main.tpl");
			   exit(1);
			   */
			   break;
		  case E_USER_NOTICE:
		   $app->sys_errors[]="Notice: $errstr";
		   //$app->smarty->assign('sys_errors',$app->sys_errors);
		   break;
		  case E_NOTICE:
		   $app->sys_errors[]="Notice: $errstr ($errfile [$errline])";
		   if(isset($app->smarty_has_run)){
			   //echo "Notice: $errstr ($errfile [$errline])<br/>";
		   }
		   break;
		  default:
			  //suppressing a php4 feature depricated in php5
			  if(substr($errstr,'var: Depricated.') === FALSE ){
		   		$app->sys_errors[]="Unknown error type [$errno]: $errstr ($errfile:$errline)";
			  }
		   break;
		  }
    	}
	}
}
?>
