<?php
/**
 * Simple PEAR DB wrapper class.
 * This file contains the {@link mcpear} class. It extends the standard
 * PHP PEAR DB library. For more information, see {@link http://pear.php.net}
 * @todo remove application dependency on seeing get user info rather than a triggered error
 * @package database
 */


/** 
 * A PEAR DB based class for interacting with a SQL database. 
 * Generic PEAR DB function wrappers used for various DB operations. PEAR is
 * a very handy and easy to use library, but having some wrappers makes using
 * it even easier and faster. This also allows for central error checking and
 * handy utility functions. For more information about PEAR, please see
 * {@link http://pear.php.net}.
 */
require_once('upbase/libs/DB.php');
class AppDB extends DB{
  /**
   * Constructor.
   * Establishes database connection.
   * @param string $dsn Data Source Name
  function mcpeardb ($dsn = FALSE) {
    $this->dsn = $dsn;
    $this->db = $this->db_connect();
	$this->pagination = FALSE;
	$this->pagination_lock = FALSE;

    if ($this->db) {
      // Set default fetch mode to associative
      $this->setFetchMode(DB_FETCHMODE_ASSOC);
    }
  }
   */
  
  /**
   * Initializes a PEAR database connection.
   * Textbook PEAR database connection function. 
   * @return mixed PEAR DB Database connection
  function db_connect () {
    if(!$this->dsn){
      return error("Your DB class constructor must set \$this->dsn");
    }

    /* Include the PEAR DB library. It should be available with every PHP
       installation, unless it was specifically excluded. On package-based
       Linux distributions, check to see if the PHP PEAR package is installed.
       For more information, see http://pear.php.net */
  /**
    require_once('libs/DB.php');
	$options = array(
           'debug' => 2,
    );

      
    // Initialize the PEAR connection
    $db = DB::connect($this->dsn, $options);

    // Check to make sure we didn't encounter an error
    return $this->verify($db);
  }
   */
                                                                                                   
  /**
   * General database query method.
   * Textbook PEAR database query. We use getuserinfo() for error
   * information.
   * @param string $sql SQL query
   * @return mixed Query result resource or FALSE on error
   */
  function db_query ($sql){
	if($this->pagination){
		$this->pagination = FALSE;
		$countquery = 'SELECT COUNT(*) FROM ('.$sql.')';

		$res = $this->db_query($countquery);
		if(!$res){
			return false;
		}

		$numrows = $res->fetchRow(DB_FETCHMODE_ORDERED);
		$numrows = $numrows[0];
		$this->last_numrows = $numrows;

		if($numrows === "0"){
			return 0;
		}

		$lowerlimit = ($this->page - 1) * $this->page_size;
		if($res = $this->limitquery($sql, $lowerlimit, $this->page_size)){
			return $this->verify($res);
		}
	  }
	else{
		$q =& $this->query($sql);
		return $this->verify($q);
	}
  }

  /**
   * General database query method.
   * Textbook PEAR database query. We use getuserinfo() for error
   * information.
   * @param string $sql SQL query
   * @return mixed and associative array or FALSE on error
   */
  function db_query_assoc ($sql){
		$rv = FALSE;
	 	if($res = $this->db_query($sql)) {
            while($row = $res->fetchRow()) {
                $rv[] = $row;
            }
        } else {
			$rv=FALSE;
        }
			return $rv;	
  }

  /**
   * Retrieves a row of data.
   * Returns a row of data for the given SQL query.
   * @param string $sql SQL query
   * @return mixed Row of data or FALSE on error
   */
  function get_row ($sql) {
    $row =& $this->getRow($sql);
    return $this->verify($row);
  }

  /**
   * Retrieves a list of data.
   * Returns a list of data for the given SQL query.
   * @param string $sql SQL query
   * @return mixed List of results or FALSE on error
   */
  function get_list ($sql) {
    $results =& $this->getAll($sql);
    return $this->verify($results);
  }
 
  /**
   * Retrieves a column of data.
   * Retrieves a column of data composed of the first row of the results for
   * the given SQL query.
   * @param string $sql SQL query
   * @return mixed Column of results or FALSE on error
   */
  function get_column ($sql) {
    $results =& $this->getCol($sql);
    return $this->verify($results);
  } 

  /**
   * Retrieves a single value.
   * Retrieves the value of the first result for the given SQL query.
   * @param string $sql SQL query
   * @return mixed Value of the first result or FALSE on error
   */
  function get_one ($sql) {
    $results =& $this->getOne($sql);
    return $this->verify($results);
  }
  
  /**
   * Retrieves an array of field names.
   * Retrieves an array of field names in a given table.
   * @param string $table SQL query
   * @return mixed An array of field names for a given table or FALSE on error
   */
  function get_field_names ($table) {
    $results = $this->tableInfo($table);
    $tableinfo = $this->verify($results);
    if($tableinfo){
      foreach($tableinfo as $t){
        $field_names[] = $t['name'];
      }
      return $field_names;
    }
    return FALSE;
  }
 
  /**
   * Returns table row count.
   * Returns the total number of rows in a given table.
   * @param string $table Table name
   * @return mixed Number of rows in the table or FALSE on error
   */
  function get_total ($table) {
    $sql = "SELECT COUNT(*)
            AS total
            FROM $table"; 
    $total =& $this->getOne($sql);
    return $this->verify($total);
  }
  
  /**
   * Returns table contents.
   * Returns an array of associative arrays for the given SQL query.
   * @param string $table Table name
   * @return mixed Associative array of results or FALSE on error
   */
  function get_table ($table) {
    $sql = "SELECT *
            FROM $table"; 
    return $this->get_list($sql);
  }

  /**
   * Returns next insert id.
   * Returns the next insert id for the sequence $name. Depending on the
   * database you're using, native sequences may or may not exist. PEAR tries
   * to handle things equally by using native sequences in databases which
   * support them, or creating sequence tables in those who don't. By default,
   * PEAR will add the string "_seq" to any sequence name. Keep that in mind
   * when asking it for the next sequence number.
   * @param string $name The name of the sequence
   * @return integer Next id sequence number
   */
  function next_id ($name) {
    $id =& $this->nextId($name);
    return $this->verify($id);
  }

  /**
   * Adds a table row.
   * Adds a row to the specific $table using the keys of the $values
   * array as field names and the values as values for those fields.
   * @param array $values Associative array of keys and values
   * @param string $table Table name
   * @return mixed Query result resource or FALSE on error
   */
  function add ($values, $table) {
    if (is_array($values)) {
      // Unset any values which should be NULL
      foreach($values as $key=>$value){
        if($value == ""){
          unset($values[$key]);
        }
      }

      // Auto-execute the INSERT query
      $q = $this->autoExecute($table, $values, DB_AUTOQUERY_INSERT);
      return $this->verify($q);
    } else {
      return error("Add expects an array of data");
    }
  }
 
  /**
   * Updates table rows.
   * Updates table rows for the specific $table using the keys of the $values
   * array as field names and the values as values for those fields.
   * @param array $values Associative array of keys and values
   * @param string $table Table name
   * @param string $where_key A key into the $values array used for the WHERE
   * clause
   * @return mixed Query result resource or FALSE on error
   */
  function update ($values, $table, $where_key) {
    // Create the WHERE clause
	if(empty($where_key)){
		return false;
	}
    $update_key = "$where_key = '{$values[$where_key]}'";
      
    // Set Null any values that say NULL
      foreach($values as $key=>$value){
        if($value == "NULL"){
          $values[$key]=NULL;
        }
      }
    
    // Auto-execute the UPDATE query
    $q = $this->autoExecute($table, $values, DB_AUTOQUERY_UPDATE, $update_key);
    return $this->verify($q);
  }

  /**
   * Deletes table rows.
   * Deletes table rows from a specific $table using the value of the $values
   * array's $where_key key as field name and value as value for the WHERE
   * clause.
   * @param array $values Associative array of keys and values
   * @param string $table Table name
   * @param string $where_key A key into the $values array used for the WHERE
   * clause
   * @return mixed Query result resource or FALSE on error
   */
  function delete ($values, $table, $where_key) {
    $sql = "DELETE FROM $table
            WHERE $where_key = '{$values[$where_key]}'";
    return $this->db_query($sql);
  }

  /**
   * Returns the result or prints an error.
   * This function is meant to be used in the 'return' statement of all mcpear
   * functions. It checks to see if there is a valid result or a DB error. If
   * the result is valid, it is returned. If there is a DB error, it is printed
   * and FALSE is returned.
   * @param mixed $result DB result
   * @return mixed Query result or FALSE on error
   */
  function &verify (&$result) {
    if (DB::isError($result)){
      //trigger_error('A database query has failed',E_USER_WARNING);
      return error($result->getUserInfo());
      trigger_error($result->getUserInfo(),E_USER_WARNING);
	  return FALSE;
    } else {
      return $result;
    }
  }
  
  /**
   * Enable/disable automatic commits
   *
   * @param $onoff true/false whether to autocommit
   * @return bool The old value of autocommit
   */
  function autoCommit($onoff = false) {
    $rv = $this->autoCommit; 
	$this->autoCommit = (bool) $onoff; 
    return $rv;
  }


  /**
   * Enable the limiting of queries to pages when later using db_query
   *
   * @param  int $page_size the desired size of a page 
   * @param int $page the desired page
   */
	function force_pagination($page_size=0, $page=1){
		$this->page_size = $page_size;
		$this->page = $page;
		$this->pagination = TRUE;
	}

}
?>
