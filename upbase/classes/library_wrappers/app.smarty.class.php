<?php
/**
 * API abstraction layer for Smarty.
 * @package Upbase_Library_Wrappers
 */

require_once("upbase/libs/Smarty/Smarty.class.php");

/**
 * Smarty library bindings.
 * One of these should be instantiated in your UpbaseApp object.  It provides
 * library abstraction, should the smarty API change.
 *
 * While I'm talkin' at ya, why don't I explain some smarty variables that
 * templates may expect.
 *
 * upbase_debugging - from the config file.  A boolean:  Is debugging on?
 * upbase_debugging_data - array of debugging messages.
 * upbase_timing - array of timing info, for debugging.
 *
 */
class AppSmarty extends Smarty {
    function AppSmarty() {
		global $upbase_freeze_template_cache, $upbase_templates_dirs, $upbase_smarty_cache_directory;
        $this->template_dir =  '';
//        $this->compile_dir  =  'smarty/templates_c';
		if(isset($upbase_smarty_cache_directory) && !empty($upbase_smarty_cache_directory)){
			$this->compile_dir  =  $upbase_smarty_cache_directory;
		}
		else{
			$this->compile_dir  =  $upbase_templates_dirs[0].'_c';
		}
        $this->config_dir   =  'smarty/configs';
        $this->cache_dir    =  'smarty/cache';
		$this->use_sub_dirs =  TRUE;
    }

	function assign_array($values){
		foreach($values as $key=>$value){
			$this->assign($key, $value);
		}
	}

    function _smarty_include($params) {
		global $upbase_templates_dirs;
		$template = $params['smarty_include_tpl_file'];
		foreach($upbase_templates_dirs as $dir){
			if (file_exists($dir.'/'.$template) ){
				$params['smarty_include_tpl_file'] = $dir.'/'.$template;
        			return parent::_smarty_include($params);
			}
		}
		$params['smarty_include_tpl_file'] = 'upbase/default_templates/missing_template.tpl';	
        return parent::_smarty_include($params);
    }


} // AppSmarty
?>
