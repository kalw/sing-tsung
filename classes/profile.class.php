<?php
/*
 * Copyright (C) 2005 The Linux Box Corp.  All rights reserved.
 *   206 S. Fifth Avenue Suite 150
 *   Ann Arbor, MI 48104
 *   http://www.linuxbox.com
 * v0.1 by KZHAO 11/9/2006
 *  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version
 * 2 of the License, or (at your option) any later version.
 */
/* configuration.class.php: 		*/
/* the configuration for tsung		*/

require_once("connections_config.php");
require_once("config.php");
require_once("config.upbase.php");
require_once("upbase/classes/library_wrappers/app.smarty.class.php");
//obal $db;


class TsungProf {
   //Variables
   var $filename=null;
   var $prof_id = NULL;
   var $prof_name = NULL;
   var $client = NULL;
   var $use_controller_vm = NULL;
   var $server_host = NULL;
   var $server_port = NULL;
   var $server_type = NULL;
   var $monitor_host = NULL;
   var $monitor_type = NULL;
   var $load_arrival_phase_minutes = NULL;
   var $load_interarrival_duration_sec = NULL;

   /**
    * An array of session ids.
    * TODO SHOULD be array of (id, name), so that we can display the names in
    * the interface.
    * @var array $sessions
    */
   var $sessions = array();
      
    function TsungProf(){
	//$this->filename=$file;
    }
    /******************************************************/
    //Parse XML configuration files and load configurations
  function load($profile_id){
      global $db;

      //if(!$db) echo "Null db!!";
      //$db->select("Select * from session");
      $rows = $db->get_profile($profile_id, $message);
      if ($rows === false) {
         trigger_error("Error: {$message}");
      } // if there was a db error
      if (count($rows)==0) {
         trigger_error("No profile found.");
       } 
      else if (count($rows)>1) {
          trigger_error("Too many profiles returned.");
      } // if there was an abberrant number of rows
	
     $row = $rows[0];
	 
  	 $this->prof_id	    =$row['id'];
	   $this->prof_name   =$row['name'];
	   $this->client	    =$row['client'];
     $this->use_controller_vm=$row['use_controller_vm'];
     $this->maxusers    =$row['maxusers'];
     $this->server_host =$row['server_host'];
     $this->server_port =$row['server_port'];
   	 //$this->server_type =$row['server_type'];
     $this->load_arrival_phase_minutes=$row['load_arrival_phase_minutes'];
  	 $this->load_interarrival_duration_sec=$row['load_interarrival_duration_sec'];

  	 //$this->sessions = explode('|', $row['sessions']);	 
  	 $this->sessions=$db->get_profile_session_info($profile_id);
     if ($this->sessions === false) {
        $this->sessions = array();
        echo "Profile session query failed!";
     }
    
   } // function load


   /**
    * Return an associative array of all the things we'll want to display.
    */
   function get_smarty_vars() {
     $ret_val = array(
       "id" => $this->prof_id,
       "prof_name" => $this->prof_name,
       "client" => $this->client,
       "use_controller_vm" => $this->use_controller_vm,
       "maxusers" => $this->maxusers,
       "server_host" => $this->server_host,
       "server_port" => $this->server_port,
       "load_arrival_phase_minutes" => $this->load_arrival_phase_minutes,
       "load_interarrival_duration_sec"=>$this->load_interarrival_duration_sec,
       "sessions" => $this->sessions
     );

     return $ret_val;
   } // function get_smarty_vars


    /***************************/
    // Save configurations of this profile back to databse
    function store(){
      	global $db;
	      return $db->save_profile($this);
    } // function save


    /****************************/
    // Save this configuration as another profile
  function saveAs($newname){
    	global $db;//$TSUNG_CONFIG;
	    /*$link = mysql_connect($TSUNG_CONFIG['dbhost'], $TSUNG_CONFIG['dbuser'], $TSUNG_CONFIG['dbpass']);
    	if (!$link) {
		    die('Could not connect to MySQL: '. mysql_error());
	    }
	    $db_selected = mysql_select_db($TSUNG_CONFIG['dbname'], $link);
	    if (!$db_selected) {
		    die ('Can\'t use '.$TSUNG_CONFIG['dbname'].': ' . mysql_error());
	    }
	    $session_ids="";
  	  foreach ($this->sessions as $i => $sid) {
	  	$session_ids.=$sid."|";
    	}
	    $session_ids=substr($session_ids,0,strlen($session_ids)-1);						
	*/
	  
    $query ="INSERT INTO profile(name, client, use_controller_vm, server_host, server_port, load_arrival_phase_minutes, load_interarrival_duration_sec) ";
  	$query.="VALUES('$newname','".$this->client."',".$this->use_controller_vm.", '".$this->server_host."', ".$this->server_port.", ".$this->load_arrival_phase_minutes.", ".$this->load_interarrival_duration_sec.")";
    //	mysql_query($query) or die("Fail to create new profile: ".mysql_error());
    if(!$db->insert($query,null))
        echo "Fail to save as new profile!";
  }
    
 /****************/
 //Used for testing
 function show(){
	//ho "Filename:".$this->filename."<br>";
	//echo  $conf_name;
  	echo  "Profile: ".$this->prof_name."<br>";
  	echo  "Client host: ".$this->client."<br>";
  	echo  "ControlVM: ".$this->use_controller_vm."<br>";
  	echo  "Server host: ".$this->server_host."<br>";
  	echo  "Server port: ".$this->server_port."<br>";
  	//echo  "Server type: ".$this->server_type."<br>";
      	//echo  "Monitor host: ".$this->monitor_host."<br>";
  	//echo  "Monitor type: ".$this->monitor_type."<br>";
  	echo  "Phase duration: ".$this->load_arrival_phase_minutes."<br>";
  	echo  "Interval: ".$this->load_interarrival_duration_sec."<br>";
  	echo  "Sessions: ";
  	print_r ($this->sessions);
 }
    /********************************/
    //Write profile to an XML file
 function export($file=null){
    global $TSUNG_CONFIG, $db;

	  if($file!=null)
  		$conf_file=$file;
  	else
  		$conf_file=$this->prof_name.".xml";
    	
  	$conf_file=$TSUNG_CONFIG['config_file_dir'].$conf_file;
  	
  	if (is_writable($conf_file) || 
         (!file_exists($conf_file) && is_writable(dirname($conf_file)))){
  		if(!$fh = fopen($conf_file, 'w')){
  			echo "Error in opening destination file $filename!\n";
  			return FALSE;
		}


      $query="SELECT S.data FROM profile_sessions PS, session S WHERE PS.profile_id=? AND PS.session_id=S.id";
      $result=$db->select($query, array($this->prof_id));
      
      if(!$result)
          die('Error, fail to query sessions for this profile!');

      $sessions = "";
      $num_results = count($result);
  		foreach ($result as $sess) {
  			if($sess){
           $probability = 100/$num_results;
           $sess['data'] = str_replace(
              "probability='100'", 
              "probability='{$probability}'",
              $sess['data']);
	  			 $sessions.=$sess['data'];
  				 $sessions.="\n\n";
  			 } // if this session is non-blank
  		} // foreach recorded session
  				

      $tsungmaker = new Smarty();
      $tsungmaker->template_dir = dirname(__FILE__)."/../smarty/templates";
      $tsungmaker->compile_dir = dirname(__FILE__)."/../smarty/templates_c";
      $tsungmaker->assign('sessions', $sessions);
      $tsungmaker->assign('duration', $this->load_arrival_phase_minutes);
      $tsungmaker->assign(
          'interarrival',
          $this->load_interarrival_duration_sec);
      $tsungmaker->assign('maxusers', $this->maxusers);
      ob_start();
      $tsungmaker->display('tsung.xml.tpl');
      $xmlConf = ob_get_contents();
      ob_end_clean();

      /********************/
  		//Write back to file
  		if (fwrite($fh, $xmlConf ) === FALSE){ 
  	       		echo "Error in writing to file: $conf_file!\n";
  	       		return FALSE;
  		}
  		fclose($fh);
  		return TRUE;
  	}
  	
    else {
  		echo "The file $conf_file is not writable";
  		return FALSE;
    }
 }//end of export function

    /*******************************/
    // Add a session to configuration
    function addSession($session_id){
    	//global $TSUNG_CONFIG;
   	//s->sessions[]=$session_id; 
	global $db; //global $TSUNG_CONFIG;
       //$this->sessions[]=$session_id;
        $query="INSERT INTO profile_sessions(profile_id,session_id) VALUES (?, ?)";
	return $db->insert($query, array($this->prof_id, $session_id));
    }
    /*************************************/
    // Remove a session from session array
    function removeSession($session_id){
   	//nset($this->sessions[$session_idx]);
	global $db;
	//unset($this->sessions[$session_idx]);
	$query="DELETE FROM profile_sessions where profile_id=? AND session_id=?";
	return $db->delete($query, array($this->prof_id, $session_id));	       
    }
    

    /**
     * Get a list of (id, name) for all profiles.
     * This is suitable for making a select dropdown, where all you need is the
     * names and ids of the sessions.
     * @return mixed array [(id, name), (id, name), ...], or false if fail.
     */
    static function get_profile_names(){
      global $db;
      $query = "SELECT id, name FROM profile ORDER BY name, id";
      $rows = $db->select($query, array(), $message);
      if ($rows === false) {
        trigger_error("Error: {$message}");
        return false;
      } // if no rows

      return $rows;
    } // function get_profile_list
    

}//end of class
?>
