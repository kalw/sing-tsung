<?php
	
class screen_base {
	var $access_granted = TRUE;
	var $css = "upbase/upbase.css";

	/**
	 * The navigation propery contains the information used to display the 
	 * navigational menu for the application.  This menu system is just
	 * one example of how a developer could handle navigation in Upbase.
	 *
	 * One example of how to extend it would be to override
	 * screen_base::assign_navigation to read this $navigation variable and
	 * set a smarty variable.  Maybe you'll put an array of screen-names in
	 * the $navigation var.
	 * @var mixed $navigation
	 */
	var $navigation = "";

	var $template = "";
	var $session_name = "upbase_application";

	function screen_base($get, $post){
		$this->get = $get;
		$this->post = $post;
		session_name($this->session_name);
		session_start();
	}

	/**
	 * Do something useful with the $navigation variable.
	 * assign_navigation() is a member of the screen_base class, a part of
	 * Upbase, which is called when building a page.  
	 * It could be overridden
	 * with a method which turns the above navigation array into an array
	 * of links.  That array is then assign to smarty and will be displayed
	 * by the smarty template written for navigation (navigation.tpl).
	 */
	function assign_navigation(){
		return TRUE;
	}



    function paginate_pre(){
        global $app;

        $app->focusdb->force_pagination($this->page_size,$this->page);

        if($this->page_size){
            $this->lower_limit = $this->page_size * ($this->page - 1);
        }
    }

    function paginate_post(){
        global $app;

        $num_pages = ceil($app->focusdb->last_numrows/$this->page_size);
        $this->pages = array();

        for($i=1; $i <= $num_pages; $i++){
            $this->pages[$i]=$i;
        }
        if($num_pages <= 1){
            $this->pagination=FALSE;
        }

        $app->smarty->assign('page', $this->page);
        $app->smarty->assign('page_size', $this->page_size);
        $app->smarty->assign('pages', $this->pages);
    }


    function consider_get_post(){
        global $app;

        if(isset($this->get['nav'])){
            $this->navigation = $this->get['nav'];
        }
        if(isset($this->get['page']) && is_numeric($this->get['page'])){
            $this->page = $this->get['page'];
        }
        if(isset($this->get['presubmit'])){
            // presubmit will call die to prevent further code from executing
            $this->presubmit();
        }
        elseif(isset($this->post['save'])){
            $app->start_time('Saving');
            $this->save();
            $app->end_time();
        }
        elseif(isset($this->post['del'])){
            $this->delete();
        }
        return true;
    }

	function apply_business_rules($rawdata){
		return FALSE;
	}

	function presubmit(){
		global $app;
		$app->ajax = true;
		list($status, $new_values) = $this->apply_business_rules($this->post);
		if(isset($new_values['javascript_end'])){
			$javascript_end = $new_values['javascript_end'];
			unset($new_values['javascript_end']);
		}
		foreach($new_values as $table=>$fields){
			foreach($fields as $name=>$properties){
				if(!is_array($properties)){
					$this->presubmit_r($fields, $table);
				}
				else{
					foreach($properties as $property=>$value){
						if(is_array($value) && $property !== 'style'){
							$this->presubmit_r($value, $table."[".$name."][".$property."]");
						}
						else{
							echo "e=document.getElementById('".$table."[".$name."]');if(!e){e=document.getElementsByName('".$table."[".$name."]');e=e[0];}\n";

							if($property == 'style'){
								foreach($value as $k=>$v){
								echo "e.style.".$k."='".str_replace("''","\'",$v)."';\n";
								}
							}
							else{
								echo "e.".$property."='".str_replace("\n","\\n",str_replace("''","\'",$value))."';\n";
								if($property == 'onclick'){
									echo "e.onclick = new Function(\"state\", e.onclick);";
								}
							}
						echo"e=null;\n";
						}
					}
				}
			}
		}
		if(isset($javascript_end)){
			echo $javascript_end;
		}
		die();
	}

	function presubmit_r($properties, $prefix){
		foreach($properties as $property=>$value){
			if(is_array($value) && $property !== 'style'){
				$this->presubmit_r($value, $prefix."[".$property."]");
			}
			else{
						echo "e=document.getElementById('".$prefix."');if(!e){e=document.getElementsByName('".$prefix."');e=e[0];}\n";
						if($property == 'style'){
							foreach($value as $k=>$v){
							echo "e.style.".$k."='".str_replace("''","\'",$v)."';\n";
							}
						}
						else{
							echo "try{e.".$property."='".str_replace("''","\'",$value)."';}\ncatch(e){document.write('$prefix: '+e);}\n";
							if($property == 'onclick'){
								echo "e.onclick = new Function(\"state\", e.onclick);";
							}
						}
						echo"e=null;\n";
			}
		}
	}

    function convert_checkboxes_to_booleans(&$array, $table, $checkboxes){
        foreach($checkboxes as $key){
            if(isset($array[$table][$key])){
                if($array[$table][$key]=='on'){
                    $array[$table][$key]=1;
                }
            }
            else{
                $array[$table][$key]=0;
            }
        }
    }
    /**
     * DOMdecode
     * Changed an array formatted to match html form properties to an array
     * formatted to match a database query
     * @param mixed $data
     * @access public
     * @todo recursive domdecode
     * @return void
     */
    function DOMdecode($data){
        $new_data = array();
        foreach($data as $field=>$properties){
            if(isset($properties['value'])){
                $new_data[$field]=$properties['value'];
                if(isset($properties['checked']) && $properties['checked'] == 'on'){
                    $new_data[$field]=1;
                }
                if(isset($properties['checked']) && $properties['checked'] == ''){
                    $new_data[$field]=0;
                }
            }
            elseif(isset($properties['innerHTML'])){
                if($properties['innerHTML'] != ''){
                    $new_data[$field]=$properties['innerHTML'];
                }
            }
        elseif(is_array($properties)){
                $new_data[$field] = $this->DOMdecode($properties);
            }
        }
        return $new_data;
    }

	/**
	 * DOMencode 
	 * 
	 *  Push all the values into a new level of the multidimensional array to create parallels with the html input form DOM model
	 * @param mixed $rawdata 
	 * @access public
	 * @return void
	 */
	function DOMencode($rawdata){
		$data = array();
		foreach ($rawdata as $t=>$fields){
			foreach($fields as $name=>$value){
				if(is_array($value)){
					$data[$t][$name] = $this->DOMencode_r($value);
				}
				else{
					$data[$t][$name]['value'] = $value;
				}
			}
		}
		return $data;
	}

    function DOMencode_r($rawdata){
        $data = array();
        foreach ($rawdata as $k=>$v){
            if(is_array($v)){
                $data[$k] = $this->DOMencode_r($v);
            }
            else{
                $data[$k]['value'] = $v;
            }
     }
        return $data;

    }



    function get_excel($headers, $array, $filename, $title="Spreadsheet"){
        global $app;
        // Creating a workbook
        $workbook = $app->Spreadsheet_Excel_Writer_Init();

        $filename .= ".xls";
        $filename = str_replace(" ","_",$filename);
        // sending HTTP headers
        $workbook->send($filename);

        // Creating a worksheet
        $worksheet =& $workbook->addWorksheet($title);

        // Add the column header
        $i=0;
        foreach($headers as $header){
            if($header['type'] != 'meta'){
                $worksheet->write(0, $i, $header['name']);
                $i++;
            }
        }

        for($j=0; $j < count($array); $j++){
            $i=0;
            foreach($headers as $header){
               if($header['type'] != 'meta'){
                    if(!is_array($value)){
                        $val = $array[$j][$header['name']];
                        if($header['type'] == 'date' && !empty($val)){
                            $val = date("m/d/Y", $val);
                        }
                        if($header['type'] == 'time' && !empty($val)){
                            $val = date("h:i:s A", $val);
                        }
                        if($header['type'] == 'checkbox'){
                            if($val){
                                $val = "Yes";
                            }else{
                                $val = "No";
                            }
                        }
                        $worksheet->write($j+1, $i, $val);
                        $i++;
                    }

                }
            }
        }
        // Let's send the file
        $workbook->close();
        die();
    }


  function check_permissions(){
    $this->access_granted = TRUE;
  }

	function run(){
		global $app;

		$app->start_time('Bulding navigation links');
		  $this->assign_navigation();
		$app->end_time();

		$app->start_time('Checking permissions');
		  $this->check_permissions();
		$app->end_time();

		if($this->access_granted){
			$app->start_time('Considering get and post');
			$this->consider_get_post();
			$app->end_time();

			$app->smarty->assign('get',$this->get);
			$app->smarty->assign('mid',$this->template);
			$app->smarty->assign('css',$this->css);
			$app->dbug('Template',$this->template);
		}
		$app->display("main.tpl");
	}
	

}

?>
