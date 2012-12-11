<?php

/**
 * Extension of FocusDB.
 * This class extends the {@link FocusDB} class. It contains all the application upgrade
 * specific database logic while {@link FocusDB} implements wider application specific logic.
 * @package upgrade
 */
class FocusDB_Upgrade extends FocusDB {

	function check_focus_version(){
		// Suppresed notices because tableinfo can trigger a pear notice
		$sql = "SELECT * FROM FOCUS_VERSION ORDER BY VERSION DESC";
	    $table_sql =
		'CREATE TABLE "FOCUS_VERSION" (
				"VERSION" VARCHAR2(60) NOT NULL,
				"TIMESTAMP" NUMBER(10) NOT NULL,
				CONSTRAINT "FOCUS_VERSION_PRIMARY" PRIMARY KEY("VERSION")
				)';

		if(@$this->db->tableinfo('FOCUS_VERSION')){
			if($version_table = $this->get_list($sql)){
				return $version_table[0]['VERSION'];
			}
			else{
				echo "No version found.  Assuming pre-2.1 release.\n";
				return "1.0.5";
			}
		}
		else{
			echo "No version table found.  Assuming pre-2.1 release. Creating table.\n";
			if(!$this->db_query($table_sql)){
			   trigger_error("Error creating version table");
			   return FALSE;
			};
			return "1.0.5";
		}
	}

	function get_old_survey_info($field){
		$sql = 'SELECT '.$field.', PROJECT_SLOT_ID FROM PROJECT_SLOT WHERE '.$field.' is not NULL order by '.$field.' DESC';
		$rslt = $this->get_list($sql);
		return $rslt;
	}

	function get_old_pulse_survey_start_info(){
		$sql = 'SELECT PULSE_SURVEY_START_DATE, PROJECT_SLOT_ID FROM PROJECT_SLOT WHERE PULSE_SURVEY_START_DATE is not NULL order by PULSE_SURVEY_START_DATE DESC';
		$rslt = $this->get_list($sql);
		return $rslt;
	}

	function get_old_cio_survey_date_temp_info(){

		$sql = 'SELECT CIO_SURVEY_DATE_TEMP, PROJECT_SLOT_ID FROM PROJECT_SLOT WHERE CIO_SURVEY_DATE_TEMP is not NULL order by CIO_SURVEY_DATE_TEMP DESC';
		$rslt = $this->get_list($sql);
		return $rslt;

	}

	function convert_history_slot_number_to_project_slot_id(){
		echo "Converting history log PROJECT_SLOT entries.\n";

		$get_pairs_sql = "
			SELECT PROJECT_SLOT_ID, SLOT_NUMBER FROM PROJECT_SLOT WHERE SLOT_NUMBER IN 
			(SELECT HISTORY.UNIQUE_FIELD FROM HISTORY WHERE TABLE_NAME = 'PROJECT_SLOT')";
		$update_history_sql = 
			'UPDATE HISTORY SET UNIQUE_FIELD = ? WHERE  UNIQUE_FIELD = ? AND TABLE_NAME=\'PROJECT_SLOT\'';

		$this->stmt_update_history = $this->db->prepare($update_history_sql);
		
		$pairs = $this->db_query_assoc($get_pairs_sql);
			
		if(is_array($pairs)){
			echo "Found ".count($pairs)." bad history entries.\n Correcting:\n";
			foreach($pairs as $pair){
				echo $pair['SLOT_NUMBER']." to ". $pair['PROJECT_SLOT_ID']."\n";
				$res = $this->db->execute($this->stmt_update_history, array($pair['PROJECT_SLOT_ID'], $pair['SLOT_NUMBER'])); 
				if(!$res){
					echo "Error updating history.\n";
					return false;
				}
			}
			echo "done.\n";
		}
		else{
			echo "No bad history entries found.\n";
			return false;
		}
		}
	
}
