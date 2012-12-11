<?php
/**
 * FOCUS Upgrade.
 * This file contains the FOCUS upgrade class.
 * @package upgrade
 */
	

/**
 * FOCUS Upgrade.
 * This class contains upgrade routines that can be inherited by specific version upgrade classes.
 * @package upgrade
 */
class upgrade {
	var $version = "default";
	var $on_error = "fail";

	function already_applied(){
		return NULL;
	}

	function check(){
		global $dbup;

		echo "Running check for ".$this->version."...\n";

		if($this->already_applied() === true){
			return true;
		}

		if(!isset($this->new_tables) && !isset($this->new_fields) && ($this->already_applied()===NULL)){
			echo "Check for the previous application of this upgrade is not implemented.\n";
			return false;
		}
			
		if(isset($this->new_tables)){
			$found_any = FALSE;
			foreach($this->new_tables as $table){
				if($table_info = @$dbup->db->tableinfo($table)){
					echo "Found ".$table."\n";
					$found_any = TRUE;
				}
				else{
					echo "Could not find ".$table."\n";
					if($found_any){
						trigger_error("This upgrade appears to have been partially applied or removed.",E_USER_ERROR);
					}
					return false;
				}
			}
		}
		if(isset($this->new_fields)){
			foreach($this->new_fields as $table=>$field){
				$field_found = false;
				if($table_info = @$dbup->db->tableinfo($table)){
					foreach($table_info as $field_info){
						if($field_info['name'] == $field){
							$field_found = true;
							continue;
						}
					}
					if($field_found){
						echo "Found ".$table.".".$field."\n";
					}
					else{
						echo "Could not find ".$field."\n";
					}
				}
				else{
					echo "Could not find ".$table."\n";
					return false;
				}
			}
		}

		return false;
	}

	function run(){
		global $dbup;
		
		$dbup->autoCommit(FALSE);
		$rv = TRUE;
		if(isset($this->sql)){
			$rv = $this->run_sql($this->sql);
		}
		/* COMMIT/ROLLBACK */
		if($rv === FALSE) {
			echo "\nRolling back changes.\n";
			$dbup->db->rollback();
			if(isset($this->sql_undo)){
				echo "Some tables were not dropped in case they are still in use.\n If you know what you are doing, you may wish to execute this sql:\n";
				foreach($this->sql_undo as $line){
					echo $line.";\n";
				}
			}
			if($this->on_error == "proceed"){
				echo "This upgrade is not critical or has already been applied.  Proceeding to the next upgrade.";
				return true;
			}
			return false;
		} else {
			echo "\n Committing changes. \n";
			$dbup->db->commit();
			return true;
		}
	}

	function run_sql($sql){

		global $dbup;
		foreach($sql as $statement){
			echo "\n------------------Start SQL----------------------------\n";
			echo $statement;
			$return = $dbup->db_query($statement);
			if($return){
				echo "\n------------------End SQL - Success----------------\n";
			} else {
				echo "\n------------------End SQL - FAILED-----------------\n";
				if($this->on_error == "proceed"){
					echo "This upgrade is not critical or has already been applied.  Proceeding to the next upgrade.";
				}
				else{
					return false;
				}
			}
		}
		return true;

	}

	function run_focus_data_maintenance_plsql() {
		global $dbup;
		echo 'Refreshing Focus_Data_Maintenance PL/SQL Package:' . "\n";

		$ac = $dbup->db->autoCommit; // stash the current value
		$dbup->db->autoCommit(TRUE);
		
		global $argv;
		$path=trim(dirname($argv[0]),"actions");
		$fnames = array (
			$path.'db/Focus_Data_Maintenance_decl.plsql', // the pkg declaration
			$path.'db/Focus_Data_Maintenance_body.plsql'  // the pkg body
		);
		
		$s = TRUE;
		while(($s !== FALSE) && ($fname = array_shift($fnames))) {
			$msg = '    ' . $fname . ':';
			$s = file_get_contents($fname);
			if($s !== FALSE) {
				$s = $dbup->db_query($s);
				if($s !== FALSE) {
					$msg .= ' Success' . "\n";
				} else {
					$msg .= ' FAILED executing database query' . "\n";
				}
			} else {
				$msg .= ' FAILED to open/read file' . "\n";
			}

			echo $msg;
		}
		
		$dbup->db->autoCommit = $ac; // restore autoCommit value
		return $s;
	}

	function run_focus_network_totals_plsql() {
		global $dbup;
		echo 'Refreshing Net PL/SQL Package:' . "\n";
		$ac = $dbup->db->autoCommit; // stash the current value
		$dbup->db->autoCommit(TRUE);
		
		global $argv;
		$path=trim(dirname($argv[0]),"actions");
		$fnames = array (
			$path.'db/Focus_Network_Totals_Spec.plsql', // the pkg declaration
			$path.'db/Focus_Network_Totals_Body.plsql'  // the pkg body
		);
		
		$s = TRUE;
		while(($s !== FALSE) && ($fname = array_shift($fnames))) {
			$msg = '    ' . $fname . ':';
			$s = file_get_contents($fname);
			if($s !== FALSE) {
				$s = $dbup->db_query($s);
				if($s !== FALSE) {
					$msg .= ' Success' . "\n";
				} else {
					$msg .= ' FAILED executing database query' . "\n";
				}
			} else {
				$msg .= ' FAILED to open/read file' . "\n";
			}

			echo $msg;
		}
		
		$dbup->db->autoCommit = $ac; // restore autoCommit value
		return $s;
	}

	function run_focus_jobs_pkg_plsql() {
		global $dbup;
		echo 'Refreshing JobsPkg PL/SQL Package:' . "\n";

		$ac = $dbup->db->autoCommit; // stash the current value
		$dbup->db->autoCommit(TRUE);
		
		global $argv;
		$path=trim(dirname($argv[0]),"actions");
		$fnames = array (
			$path.'db/JobsPkgSpec.plsql', // the pkg declaration
			$path.'db/JobsPkgBody.plsql'  // the pkg body
		);
		
		$s = TRUE;
		while(($s !== FALSE) && ($fname = array_shift($fnames))) {
			$msg = '    ' . $fname . ':';
			$s = file_get_contents($fname);
			if($s !== FALSE) {
				$s = $dbup->db_query($s);
				if($s !== FALSE) {
					$msg .= ' Success' . "\n";
				} else {
					$msg .= ' FAILED executing database query' . "\n";
				}
			} else {
				$msg .= ' FAILED to open/read file' . "\n";
			}

			echo $msg;
		}
		
		$dbup->db->autoCommit = $ac; // restore autoCommit value
		return $s;
	}


	function run_focus_parser_utils_plsql() {
		global $dbup;
		echo 'Refreshing Focus_Parser_Utils PL/SQL Package:' . "\n";

		$ac = $dbup->db->autoCommit; // stash the current value
		$dbup->db->autoCommit(TRUE);
		
		global $argv;
		$path=trim(dirname($argv[0]),"actions");
		$fnames = array (
			$path.'db/Focus_Parser_Utils_Spec.plsql', // the pkg declaration
			$path.'db/Focus_Parser_Utils_Body.plsql'  // the pkg body
		);
		
		$s = TRUE;
		while(($s !== FALSE) && ($fname = array_shift($fnames))) {
			$msg = '    ' . $fname . ':';
			$s = file_get_contents($fname);
			if($s !== FALSE) {
				$s = $dbup->db_query($s);
				if($s !== FALSE) {
					$msg .= ' Success' . "\n";
				} else {
					$msg .= ' FAILED executing database query' . "\n";
				}
			} else {
				$msg .= ' FAILED to open/read file' . "\n";
			}

			echo $msg;
		}
		
		$dbup->db->autoCommit = $ac; // restore autoCommit value
		return $s;
	}
}
