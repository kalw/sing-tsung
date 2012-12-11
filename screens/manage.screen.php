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
	var $template = 'manage.tpl';		
	var $screen_name = 'manage';

  var $defaults = array(
      'maxusers' => 1024,
      'arrivalphase' => 10,
      'interarrival' => 5,
    );

  /**
   * Sanitized variables to save, when calling the save function.
   * We should be able to literally put the keys and values into a sql
   * statement.
   * @var array $data_to_save
   */
  var $data_to_save = array();
  
  function consider_get_post(){
    global $app, $db;	

    $app->smarty->assign("defaults", $this->defaults);

    $urlbase = $_SERVER['SCRIPT_NAME']."?screen={$this->screen_name}";
    $app->smarty->assign("urlbase", $urlbase);

    // Figure out which profile we're supposed to be looking at.
    $chosen_profile = false;
    $delete_profile = false;
    if (isset($this->get['profile_id'])) {
      $chosen_profile = $this->get['profile_id'];
      $app->smarty->assign("chosen_profile", $this->get['profile_id']);
    } else if (isset($this->post['profile_id'])) {
      $chosen_profile = $this->post['profile_id'];
      $app->smarty->assign("chosen_profile", $this->post['profile_id']);
    } // if they've asked for a particular 


    //Delete a profile---Kang 11202006
    if(isset($this->post['action']) && $this->post['action']=="Delete Profile"){
        $delete_profile=true;
        $query="DELETE FROM profile WHERE id= ?";
        if(!$db->delete($query,array( $chosen_profile))){
            echo "Fail to delete profile record!";
            return;
        }
        $query="DELETE FROM profile_sessions WHERE profile_id= ?";
        if(!$db->delete($query,array( $chosen_profile))){
            echo "Fail to delete session record for the profile!";
            return;
        }

        $this->forceRedirect(
            "{$_SERVER['SCRIPT_NAME']}?screen=manage",
            "Profile deleted...");
    }
    elseif (isset($this->post['action']) && $this->post['action'] == 'Save') {
      $this->save();
    } // if we're supposed to save

    else if (isset($this->post['action']) && $this->post['action'] == 'Edit') {
      if (isset($this->post['profile_id']) && !$this->post['profile_id']) {
        $this->forceRedirect(
            "{$_SERVER['SCRIPT_NAME']}?screen=manage",
            "Switching...");
      } // if the profile_id isn't set
      else {
        $this->forceRedirect(
            "{$_SERVER['SCRIPT_NAME']}?screen=manage&profile_id=".
                $this->post['profile_id'],
            "Switching...");
      } // if it's a real one they're switching to
    } // if we're supposed to edit
      

    // Populate the interface
    $message = "";
    $data=$db->select("SELECT id, name FROM profile", array(), $message);
    if($data!==false){
      $app->smarty->assign('profiles', $data);
    } else {
      trigger_error("Get all profiles: {$message}");
    } // if we got data

    $query = "SELECT id, name
              FROM   session
              ORDER BY name, id";
    $all_sessions = $db->select($query, $message);
    if ($all_sessions!==false) {
      $app->smarty->assign('all_sessions', $all_sessions);
    } else {
      trigger_error("Get all sessions: {$message}");
    } // if we got sessions

    if ($chosen_profile && !$delete_profile) { 
      $prof=new TsungProf();
      $prof->load($chosen_profile);
      $profile_smarty_vars = $prof->get_smarty_vars();
      $app->smarty->assign("profile", $profile_smarty_vars);
    } // if they're looking at a particular profile
    elseif($delete_profile){
      $this->forceRedirect($_SERVER['SCRIPT_NAME']."?screen=manage","Profile deleted, redirecting...");
     // header("Location: ".$_SERVER['SCRIPT_NAME']."?screen=manage");
    } // elseif delete
  } // function consider_get_post

  /**
   * Called if we're definately writing to the database.
   */
  function save(){
    global $db, $app;

    // Twist it around.
    $data_to_save = $this->post;

    if (!isset($data_to_save['profile']['use_controller_vm'])) {
      $data_to_save['profile']['use_controller_vm'] = 0;
    } // if the use_controller_vm isn't set

    $params = array();
    if ($data_to_save['profile']['id']) {
      $query = "UPDATE profile SET ";
      foreach ($data_to_save['profile'] as $key => $val) {
        if ($key == "id") { continue; }
        $query .= "{$key}=?, ";
        $params[] = $val;
      } // foreach keyval
      $query = rtrim($query, ", ");
      $query .= " WHERE id=?";
      $params[] = $data_to_save['profile']['id'];

      
      $db->update($query, $params);
    } // if update
    else {
      $query = "INSERT INTO profile (";
      $values = "VALUES (";
      foreach ($data_to_save['profile'] as $key => $val) {
        if ($key=="id") { continue; }
        $query .= "{$key}, ";
        $values .= "?, ";
        $params[] = $val;
      } // foreach keyval

      $query = rtrim($query, ", ").") " .
               rtrim($values, ", ").")";

      $inserted_profile_id = $db->insert($query, $params);
    } // else insert


    // If you clicked add, and then remove, without hitting save, then it'll
    // say add, notreally.
    $session_add = isset($data_to_save['session_add'])?
                          $data_to_save['session_add'] :
                          array();

    $session_notreally = isset($data_to_save['session_notreally'])?
                          $data_to_save['session_notreally'] :
                          array();

    $session_remove = isset($data_to_save['session_remove'])?
                          $data_to_save['session_remove'] :
                          array();

    foreach ($session_notreally as $notthis) {
      $key = array_search($notthis, $session_add);
      unset($session_add[$key]);
    } // foreach notthis

    // Add the new ones
    $chosen_id = $data_to_save['profile']['id']?
            $data_to_save['profile']['id'] :
            $inserted_profile_id;

    foreach ($session_add as $newsession) {
      $query = "INSERT INTO profile_sessions (profile_id, session_id) ".
               "VALUES (?, ?)";
      $params = array($chosen_id, $newsession);

      $db->insert($query, $params);
    } // foreach new session

    // Delete the old ones
    $params = array($data_to_save['profile']['id']);
    if ($session_remove) {
      $query = "DELETE FROM profile_sessions
                WHERE profile_id=?
                  AND session_id IN (";
      foreach ($session_remove as $rmsession) {
        $query .= "?, ";
        $params[] = $rmsession;
      } // foreach session to remove
      //echo $query."!!!!<br>";
      $query= rtrim($query,", ").")";
      
      //echo $query."!!!!";
      $res = $db->st_delete($query, $params, $message);
      if ($res===false) {
        trigger_error("Error: {$message} QUERY==={$query}");
      } // if it didn't work.
    } // if there's anything to remove

    if ($chosen_id) {
      $this->forceRedirect(
          "{$_SERVER['SCRIPT_NAME']}?screen=manage&profile_id={$chosen_id}",
          "Saving...");
    } // if the id is set
  } // function save

  function populate_interface(){
    global $app;
          
    if($app->db){
    }
  } // function populate_interface
  
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
  }
} // class TsungProf
?>
