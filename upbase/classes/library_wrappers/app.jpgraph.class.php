<?php

  //require_once("upbase/libs/jpgraph-2/src/jpgraph.php");
	//@todo detect php4 or 5 and route to jpg 1 or 2
	//ini_set('include_path',ini_get('include_path').':upbase/libs/jpgraph-2/src');
  global $app;
  $phpversion = phpversion();
  if($phpversion[0]==4){
  	ini_set('include_path',ini_get('include_path').':upbase/libs/jpgraph-1/src');
	$app->dbug('Jpgraph series','1 (PHP 4 detected)');
  }
  else{
  	ini_set('include_path',ini_get('include_path').':upbase/libs/jpgraph-2/src');
	$app->dbug('Jpgraph series','2 (PHP 5 detected)');
  }
  require_once("jpgraph.php");

?>
