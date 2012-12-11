<?php
/*
 * Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
 *   206 S. Fifth Avenue Suite 150
 *   Ann Arbor, MI 48104
 *   http://www.linuxbox.com
 * Written by Ryan Hughes (ryan@linuxbox.com)
 * and  KZHAO 10/31/2006
 *  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version
 * 2 of the License, or (at your option) any later version.
 */

/**
 * Interface between Sing-Tsung and the DB.
 * This is a "Model" in the MVC paradigm.  It knows how to work with the
 * database.
 * I'll have to explain the data model to you sometime.
 */
require_once("classes/my.mcpeardb.class.php");
class SingTsungDB extends mcpeardb{

  /**
   * Insert data into the database, with all the checking and substitution.
   * Give the INSERT query with ? wherever you want data substituted from the
   * $data array.
   * Example:
   * <code>
   * $query = "INSERT INTO cool (name, coolness) 
   *                 VALUES (?, ?)";
   * $data = array("Ryan", "100%");
   * if (! $dbobj->insert($query, $data, $error)) { echo "Error: $error"; }
   * </code>
   *
   * It returns the result object that's returned by the pear DB class, 
   * or false if the query failed.
   *
   * TODO It should return the insert_id of the newly-inserted row.  That's why
   * it's a separate function from update().
   * I didn't do that because I don't think PEAR DB has a way to get the insert
   * id.  I'm not sure about that, but it hasn't come up yet.
   * UPDATE:  Yes, it has.  But I did it with the mysql_insert_id function, so
   * it's not portable.
   *
   * @param string $query The INSERT query, with ? for data substitution.
   * @param array $data The an array of data to be substituted.
   * @param string $message Lets you get the error message.  It's a ref.
   * @return mixed Pear-DB MySQL result object, or false.
   */
  function insert($query, $data, &$message=null){
    $res = $this->db->query($query, $data);
    if (DB::isError($res)) {
      $message = $res->getMessage();
      return false;
    } // if db error

    // Whoops, this isn't portable!
    $id = mysql_insert_id();
    return $id;
  } // function insert


  /**
   * Update data in the database, with all the checking and substitution.
   * Give the UPDATE query with ? wherever you want data substituted from the
   * $data array.
   * Example:
   * <code>
   * $query = "UPDATE cool SET name=?, coolness=?";
   * $data = array("Ryan", "100%");
   * if (! $dbobj->update($query, $data, $error)) { echo "Error: $error"; }
   * </code>
   *
   * It returns the result object that's returned by the pear DB class, 
   * or false if the query failed.
   *
   * @param string $query The UPDATE query, with ? for data substitution.
   * @param array $data The an array of data to be substituted.
   * @param string $message Lets you get the error message.  It's a ref.
   * @return mixed Pear-DB MySQL result object, or false.
   */
  function update($query, $data, &$message=null){
    $res = $this->db->query($query, $data);
    if (DB::isError($res)) {
      $message = $res->getMessage();
      return false;
    } // if db error

    return $res;
  } // function update
  
  //Similar to update
  /**
   * It's called st_delete because delete was a reserved word.
   * "st" means "sing-tsung".
   */
  function st_delete($query, $data, &$message=null){
     $res = $this->db->query($query, $data);
     if (DB::isError($res)) {
        $message = $res->getMessage();
        return false;
     } // if db error
     return $res;
   } // function st_delete


  /**
   * Safely perform the query.  Return the rows.
   * Substitutes the data into the query and ships it off to the server.
   * Returns the rows, or false if there was an error.  If there was an error,
   * $message will be filled with the message string from the server.  That's
   * why it's passed by reference.
   *
   * @param string $query The query.
   * @param array $data Data to be substituted into array.
   * @param string $message String reference.  Error messages are placed here.
   * @return mixed Array with rows, or false if failed.
   */
  function select($query, $data=array(), &$message=null){
    global $db;

    // Okay, if you forget to put data in there, and you just do message, then
    // you get an error which is very non-intuitive:  You lose the fetchmode.
    // That took me forever to figure out.  Therefore, I will support
    // select($query, $message) as well.
    // But the second parameter is often passed in as array(), so we'll get
    // "can't be passed by reference" errors.  Therefore, if they use the
    // alternative form, we'll fill $message with a stock string.
    if (is_null($message) && !is_array($data)) {
      $data = array();
      $message = "Got the wrong args to ".get_class($this).
                 "->select.  Arg 2 should be an array of data, ".
                 "and arg 3 should be a string, passed by reference.";
    } // if they did it wrong

    $rows = $this->db->getAll($query, $data, DB_FETCHMODE_ASSOC);

    if (DB::isError($rows)) {
      $message = $rows->getMessage();
      return false;
    } // if db error

    return $rows;
  } // function select


  /**
   * Delete.  Return error messages by reference.
   * Return pear db result object, or false, if it worked or not.
   * One day, it should return the number of rows deleted.
   */
  function delete($query, $data=array(), &$message){
    $result = $this->db->query($query, $data);

    if (DB::isError($result)) {
      $message = $result->getMessage();
      return false;
    } // if db error

    return $result;
  } // function delete


  /**
   * Return the id of the session with the given name, or null.
   * @param string $name The name of the session to check.
   * @return mixed The session id, or null.
   */
  function session_id_by_name($name) {
    $query = "SELECT id 
              FROM session 
              WHERE name=?";
    $rows = $this->select($query, array($name), $error_message);
    if ($rows === false) {
      trigger_error("Error: {$error_message} QUERY==={$query}");
    } // if there was an error

    if (count($rows) > 0) {
      return $rows[0]['id'];
    } // if there's already one 

    return null;
  } // function session_id_by_name

  /**
   * Adds a new recording session to the database.
   * @param string $name The name of the session, for later use.
   * @param string $data The session xml data.
   * @param string $message Ref that lets you get error messages.
   * @return mixed Pear-DB result object or false.
   */
  function add_session($name, $data, &$message=null){
    $data = rtrim($data);
    $data = preg_replace('/<\/session>$/', '', $data);
    $data .= "</session>";

    $query = "INSERT INTO session (name, data) VALUES (?, ?)";
    return $this->insert($query, array($name, $data), $message);
  } // function add_session


  /**
   * Edits an existing session in the database.
   * @param string $id The id of the session to update.
   * @param string $name The name of the session, for later use.
   * @param string $data The session xml data.
   * @param string $message Ref that lets you get error messages.
   * @return mixed Pear-DB result object or false.
   */
  function update_session($id, $name, $data, &$message=null){
    $data = rtrim($data);
    $data = preg_replace('/<\/session>$/', '', $data);
    $data .= "</session>";

    $query = "UPDATE session SET name=?, data=? WHERE id=?";
    return $this->update($query, array($name, $data, $id), $message);
  } // function update_session


  /**
   * Get the info for a particular profile.
   * @param string $profile_id The id of the profile you want.
   * @param string $message Reference, to get DB messages back.
   * @return mixed Array of results, or false.
   */
  function get_profile($profile_id, &$message=null){
    $query="SELECT * from profile WHERE id=?";
    return $this->select($query, array($profile_id), $message);
  } // function get_profile


  /**
   * Take a profile object, save its data in the db.
   * @param TsungProf $profile_obj The profile object.
   * @param string $message Reference, to get DB messages back.
   * @return mixed PEAR DB result object, or false if failure.
   */
  function save_profile($profile_obj, &$message=null){
    // Update regular information of the profile only 
    $query="UPDATE profile SET name=?, client=?, use_controller_vm=?, server_host=?, server_port=?, load_arrival_phase_minutes=?, load_interarrival_duration_sec=? WHERE id=?";

    $data = array(
      $profile_obj->prof_name,
      $profile_obj->client,
      $profile_obj->use_controller_vm,
      $profile_obj->server_host,
      $profile_obj->server_port,
      $profile_obj->load_arrival_phase_minutes,
      $profile_obj->load_interarrival_duration_sec,
      //$session_ids,
      $profile_obj->prof_id
    );

    if(!$this->update($query, $data, $message))
        return FALSE;
  } // function save_profile
  
  /**
  * Get the sessiojs for a particular profile.
  * @param string $profile_id The id of the profile you want.
  * @param string $message Reference, to get DB messages back.
  * @return mixed Array of results, or false.
  */
  function get_profile_session_info($profile_id){
      $query="SELECT S.id, S.name 
              FROM profile_sessions P, session S
              WHERE P.profile_id=?
                AND S.id=P.session_id";
      return $this->select($query, array($profile_id), $message);
  }

} // class SingTsungDB

?>
