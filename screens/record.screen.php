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
	var $template = 'record.tpl';		
	var $screen_name = 'record';

  /**
   * The data that you posted, so we can put it in the fields.
   * @var array $last_data
   */
  var $last_data;

  /**
   * Non-error messages.  They'll be displayed.
   * @var array $messages
   */
  var $messages = array();

  /**
   * Mark this as true: at the end of run(), we will stop the tsung recorder.
   * @var boolean $also_stop_tsung
   */
  var $also_stop_tsung = false;


  /**
   * Translates data into no-magic-quotes mode.
   * The magic quotes will be added by the DB object in preparing the query.
   * Therefore, we should present the DB object things which are not
   * magic-quotesed.
   * We will recurse, to make sure we've got everything.
   * @param array $data Query data to translate.
   * @return array Translated data, in no-magic-quotes mode.
   */
  function handle_magic_quotes($data){
    $ret_val = array();

    if (ini_get('magic_quotes_gpc')) {
      foreach ($data as $key => $value) {
        if (is_array($value)) {
          $ret_val[$key] = $this->handle_magic_quotes($value);
        } else {
          $ret_val[$key] = stripslashes($value);
        } // if whether we need to recurse or not
      } // foreach data point
    } // if we have magic quotes on
    else {
      $ret_val = $data;
    } // there weren't magic quotes to begin with!

    return $ret_val;
  } // function handle_magic_quotes


  function stop_recorder(){
    global $TSUNG_CONFIG;

    /* run tsung stop at least once, if any pids exist */
    $cmd = "./stop_tsung_recorder --prefix {$TSUNG_CONFIG['install_dir']} 2>&1 > /dev/null";
    $msg = `$cmd`;

    return array(true, array('javascript_end'=>'stop_tsung();'));
  } // function stop_recorder


  function start_recorder(){
    global $TSUNG_CONFIG;
    $TIMEOUT_SECS = 5;

    // Let's not build up a bunch of trash files.
    exec("rm {$TSUNG_CONFIG['log_dir']}/.tsung/tsung_recorder*.xml");

    $t1 = time();
    $handle = popen("./start_tsung_recorder --prefix {$TSUNG_CONFIG['install_dir']} 2>&1", "r");
    $console = "";

    $t2 = time();
    $console.=fread($handle, 8192); 

    while ($t2-$t1<$TIMEOUT_SECS 
        && !preg_match('/^"Record file: (.*)".$/m', $console, $matches) 
        && !preg_match('/Can\'t start/', $console, $matches))
    {
      $t2 = time();
      $console.=fread($handle, 8192);
    } // for listening for input.  

    /*
    * If we've got some matches, that means it matched.
    */
    ob_start();
    print_r($matches);
    $str = ob_get_contents();
    ob_end_clean();
    // return array(true, array('javascript_end'=>$str));
    if ($matches && count($matches)>=2 && $matches[1]) {
      $file = $matches[1];
      preg_match(
          '/Starting Tsung recorder on port ([0-9]*)/', 
          $console, 
          $matches2);
      exec("rm -rf {$TSUNG_CONFIG['config_file_dir']}/current_log.xml");
      exec("ln -s {$file} {$TSUNG_CONFIG['config_file_dir']}/current_log.xml");

      $ret_val = array(true, array());
      $ret_arr =& $ret_val[1];
      $ret_arr['javascript_end'] = 
          "var button=document.getElementById('ACTION[START]');
          tsung_started();";
      // echo "1,{$file}";
      return $ret_val;
    } // if we've got a file
    else {
      $console = str_replace("'", "\\'", $console);
      $console = str_replace("\r", "\\r", $console);
      $console = str_replace("\n", "\\n", $console);
      $ret_val = array(false, array());
      $ret_arr =& $ret_val[1];
      $ret_arr['javascript_end'] = 
          "var button=document.getElementById('ACTION[START]');
          tsung_started('{$console}');";
      return $ret_val;
    } // else we don't got a file
  } // function start_recorder

  function poke($rawdata){
    $lines = `ps axwww | grep tsung | grep recorder | grep -v stop | grep -v grep`;

    if (!isset($rawdata['initial']) || $rawdata['initial'] != 'true') {
      if ($lines) {
        return array(true, array());
      } else {
        return array(true, 
               array('javascript_end'=>'tsung_stopped_by_someone_else();'));
      }
    } else {
      if ($lines) {
        return array(true, 
               array('javascript_end'=>'recorder_checked("running");'));
      } else {
        return array(true, 
               array('javascript_end'=>'recorder_checked("not running");'));
      }
    } // if whether initial or not
  } // function poke


  /**
   * Called to start the recorder.
   */
  function apply_business_rules($rawdata){
    global $app;
    global $TSUNG_CONFIG;

    if ($app->ajax) {
      switch($rawdata['whattodo']) {
      case 'Stop Recording':
        return $this->stop_recorder();
        break;

      case 'Start Recording':
      case 'Clear and Restart Recording':
      case 'Continue Recording':
        return $this->start_recorder();
        break;

      case 'poke':
        return $this->poke($rawdata);
        break;
      } // switch whattodo
    } // if it's ajax-ly
  } // function apply_business_rules


  /**
   * Respond to posts and dispatch responses.  Save or record.
   * If post['ACTION']['SAVE'] is set, then it will save into the database.
   * If post['ACTION']['START'] is set, then it will start tsung using
   * phpreactor.
   * @global App Used to access the smarty.
   * @global DB The DB object.
   */
	function consider_get_post(){
    global $app, $db;

    if (isset($this->post['whattodo'])
        && $this->post['whattodo'] == 'Create a new session') 
    {
      $this->post = array();
    } // if create new session

    parent::consider_get_post();

    $this->post = $this->handle_magic_quotes($this->post);
    if ($this->post) {
      $this->last_data = $this->post;
    } else {
      $this->last_data = $this->get;
    } // if it's post or get

    if (isset($this->post['ACTION']['SAVE'])) {
      $doit = true;
      $this->last_data['edit'] = 1;

      // A couple of quick checks.
      if (!$this->post['session']['name']) {
        trigger_error("Please enter a session name");
        $doit = false;
      } // if they didn't put a name in

      $id_in_question = null;
      if (isset($this->post['session']['id'])) {
        $id_in_question = $this->post['session']['id'];
      } // if there's an id.

      // Trip an error if they're trying to step on a different session.
      $id_from_name = $db->session_id_by_name($this->post['session']['name']);
      if (($id_in_question===null && $id_from_name!==null)
          || ($id_in_question!==null && $id_from_name!== null 
              && $id_from_name!=$id_in_question))
      {
        trigger_error("A session by that name already exists.");
        $doit = false;
      } // if there's a session by that name


      // Are we adding or editing?

      if ($id_in_question) {
        // EDITING, not adding.
        $message="";
        if ($doit) {
          $res = $db->update_session(
                          $id_in_question,
                          $this->post['session']['name'], 
                          $this->post['session']['data'],
                          $message);
          if($res === false) {
            trigger_error($message);
          } // if error message
          else {
            $this->also_stop_tsung = true;
          } // else there's no error and we're successful
        } // if doit
      } // if we're supposed to update
      else {
        // ADDING, not editing.
        if ($doit) {
          $res = $db->add_session($this->post['session']['name'], 
                          $this->post['session']['data'],
                          $message);

          if($res === false) {
            trigger_error($message);
          } // if error message
          else {
            // If there was a cross-platform way of doing this, I'd love it.
            $this->last_data['session']['id'] = mysql_insert_id();
            $this->also_stop_tsung = true;
          } // else there's no error and we're successful
        } // if doit
      } // else we're adding, not editing

      if ($this->also_stop_tsung) {
        $this->messages[] = 
                  "Session \"{$this->post['session']['name']}\" saved.  ".
                  "The proxy is no longer listening to you.  ".
                  "You may now switch your browser back to normal, ".
                  "non-proxy mode, if you have not done so already.";
      } // if we are supposed to stop
    } // if they've opted to save

    
    // The default is array(), so that's what'll be there if we didn't set any
    // messages.
    $app->smarty->assign('messages', $this->messages);
    
    //Display all the sessions
    $query = "SELECT id, name
              FROM   session
              ORDER BY name, id";
    $all_sessions = $db->select($query, null,$message);
    if ($all_sessions!==false) {
        $app->smarty->assign('all_sessions', $all_sessions);
    }
    else {
        trigger_error("Get all sessions: {$message}");
    } // if we got sessions
        
    //Handle the deletion of sessions
    $session_delete=false;
    if (isset($this->post['ACTION']['DELETE']) && $this->post['ACTION']['DELETE']=="Delete" && isset($this->post['session']['id'])){
       $query="DELETE FROM session  WHERE id= ?";
       if(!$db->delete($query,array($this->post['session']['id']))){
          echo "Fail to delete this session record!";
          return;
        }
       $query="DELETE FROM profile_sessions WHERE session_id= ?";
       if(!$db->delete($query,array($this->post['session']['id']))){
           echo "Fail to delete the session record in the profile_session table!";
           return;
       }
       $session_delete=true;
    }

    if (isset($this->post['ACTION']['EDIT']) 
        && $this->post['ACTION']['EDIT'] == "Edit" 
        && isset($this->post['session']['id']))
    {
      if ($this->post['session']['id']) {
        $this->forceRedirect($_SERVER['SCRIPT_NAME']."?screen=record&ACTION[EDIT]=Edit&session[id]={$this->post['session']['id']}","Loading session...");
      } else {
        $this->forceRedirect($_SERVER['SCRIPT_NAME']."?screen=record","Creating a new session...");
      } // if they set a session id or wanted to create a new one
    } // if they posted the edit request, make it into a post

    if (isset($this->get['ACTION']['EDIT']) 
        && $this->get['ACTION']['EDIT'] == "Edit" 
        && isset($this->get['session']['id'])
        && $this->get['session']['id'])
    {
      $this->last_data['edit'] = 1;

      $query = "SELECT id, name, data
                FROM session
                WHERE id=?";
      $rows = $db->select($query, 
                          array($this->get['session']['id']),
                          $message);
      if (!$rows) {
        echo "Error: {$message}<br />\n";
      } // if it broke

      $this->last_data['session'] = $rows[0];
    } // if we should edit

    if ($app->sys_errors) {
      $app->smarty->assign('last_data', $this->last_data);
    } // if there were errors
    if($session_delete)
        $this->forceRedirect($_SERVER['SCRIPT_NAME']."?screen=record","Session deleted, redirecting...");

    // Always do this.  It's like printing.
    $app->smarty->assign('last_data', $this->last_data);
	} // function consider_get_post

  /**
   * We override this so we can stop tsung if we need to.
   */
  function run(){
    parent::run();
  } // function run

  function forceRedirect($url,$message,$die=true) {
       if (!headers_sent()) {
            ob_end_clean();
            header("Location: " . $url);
       }
       printf("<HTML>");
       printf("<META http-equiv=\"Refresh\" content=\"0;url=%s\">", $url);
       printf("<BODY onload=\"try {self.location.href='%s' } catch(e) {}\"><a href=\"%s\">".$message." </a></BODY>", $url, $url);
       printf("</HTML>");
       if ($die)
           die();
  } //end of funciton forceRedirect

} // class screen
?>
