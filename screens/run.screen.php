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
require_once("config.php");
require_once('classes/screen_base.sing_tsung.class.php');
require_once('classes/profile.class.php');


class screen extends sing_tsung_screen_base{
	var $template = 'run.tpl';		
	var $screen_name = 'run';

  function stop_tsung(){
    global $TSUNG_CONFIG;
    $cmd = "export HOME={$TSUNG_CONFIG['log_dir']}; /opt/tsung/bin/tsung stop 2>&1 > /dev/null";
    $msg = `$cmd`;
    return array(true, array('javascript_end'=>"tsung_player_stopped();",));
  } // function stop_tsung

  function start_tsung(){
    $msg = "";
    if (($socket = socket_create(AF_UNIX, SOCK_STREAM, 0)) < 0) {
      $msg = "Socket failed: ".socket_strerror($socket)."\n";
    } // if socket failed

    if (($result = socket_connect($socket, "/tmp/tsung_start_stop")) < 0) {
      $msg = "Connect failed: ".socket_strerror($socket)."\n";
    } // if socket connect failed

    socket_write($socket, "STRT", 5);
    $response = socket_read($socket, 3);
    socket_close($socket);

    if (!$msg && $response == "OK\n") {
      return array(true, array('javascript_end'=>
          "tsung_player_started('');",));
    } else {
      $msg = str_replace("\\", "\\\\", $msg);
      $msg = str_replace("'", "\\'", $msg);
      return array(true, array('javascript_end'=>
          "tsung_player_started('Couldn\'t connect: {$msg}');",));
    } // if we were okay or not
  } // function start_tsung

  function generate_status_report(){
    global $TSUNG_CONFIG;

    $cmd = "ls -r ".$TSUNG_CONFIG['log_dir']."/.tsung/log/";

    $msg = `$cmd`;
    $lines = explode("\n", $msg);

    $found=0;
    $folder = "";
    foreach ($lines as $line) {
        if(preg_match("/^[0-9]{8}-[0-9]{2}:[0-9]{2}$/", $line)) {
            $found=1;
            $folder = $line;
            break;
        }
    } // foreach line of the ls
    if(!$found){
        echo "Fail to get folder name that matches the pattern!";
        exit;
    }
    
    
    if (! copy(
        $TSUNG_CONFIG['log_dir']."/.tsung/log/".trim($folder,"\n\r")
            ."/tsung.log", 
        $TSUNG_CONFIG['report_dir']."/tsung.log")) {
      echo "Failed!";
    }

    $cmd=$TSUNG_CONFIG['report_pl'];
    $msg = `$cmd`;
    
    return array(true, array('javascript_end'=>"tsung_status_generated();",));
  } // function generate_status_report


  function apply_business_rules($rawdata){
    global $app;
    global $TSUNG_CONFIG;

    if ($app->ajax) {
  switch ($rawdata['whattodo']) {
   case 'Run Selected Profile':
        ini_set("include_path", 
                dirname(__FILE__)."/..:".ini_get("include_path"));
        require_once(dirname(__FILE__)."/../classes/profile.class.php");

        $profile_id = $_REQUEST['profile_id'];

        $prof = new TsungProf();
        $prof->load($profile_id);
        $prof->export("tsung.xml");

        $this->stop_tsung();
        return $this->start_tsung();
        break;

   case 'Stop All Profiles':
        $profile_id = $_REQUEST['profile_id'];

        return $this->stop_tsung();
        break;


   case 'Generate Stats Report':
      //make sure tsung stopped
      return $this->generate_status_report();
      break;

   case 'status':
      global $TSUNG_CONFIG;

      /* run tsung stop at least once, if any pids exist */
      $cmd = "./tsung_status --prefix {$TSUNG_CONFIG['install_dir']} 2>&1";
      $msg = `$cmd`;
      $lines = explode("\n", $msg);
      array_shift($lines);
      $msg = "Tsung Status:\n".implode("\n", $lines);
      $msg = urlencode($msg);
      $msg = str_replace("+", " ", $msg);

      return array(true, array('javascript_end'=>"process_tsung_status('{$msg}');"));
      break;

   case 'poke':
        $lines = `ps axwww | grep tsung | grep erlang | grep -v stop | grep -v grep | grep -v recorder`;

        if ($lines) {
          $msg = "not finished";
        } else {
          $msg = "finished";
        }
        if (!isset($rawdata['initial']) || $rawdata['initial'] != 'true') {
          return array(true, 
              array("javascript_end"=>"tsung_poked('{$msg}');"));
        } else {
          return array(true, 
              array("javascript_end"=>"alreadyrunning_poked('{$msg}');"));
        }
        break;
      } // switch whattodo

      // Please don't use this.  Please return something inside the switch.
      return array(true, array());
    } // if ajax
  } // function apply_business_rules

  function consider_get_post(){
    global $db, $app, $TSUNG_CONFIG;

    $app->smarty->assign('report_dir', $TSUNG_CONFIG['report_dir_web']);

    parent::consider_get_post();

    // Get the list of profile names, to make the select dropdown
    $profiles = TsungProf::get_profile_names();

    $app->smarty->assign('profiles', $profiles);
  } // function consider_get_post
} // class screen
?>
