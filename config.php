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

/* TODO For some reason, this include path does not satisfy Pear DB.  It tries
 * to include 'DB/common.php', but won't look for it in the include path, even
 * though it totally could find it there. */
/* Therefore, you have to install the Pear DB class onto the main system in
 * order to make it work.  This is bad behavior. */
ini_set('include_path', ini_get('include_path').":".dirname($_SERVER['SCRIPT_NAME'])."/upbase/libs");

require_once ('connections_config.php');


// This is the directory that tsung uses to write its configuration files for
// each session.  It also stores the log of that session.
// Make sure that this directory is readable and writeable by the apache user.
// For example, you might create this directory and then do:
// chmod g+rw daemon logs
// (if you apache runs with 'daemon' as its group)
$TSUNG_CONFIG['log_dir'] = "/var/www/localhost/htdocs/logs";

// This is the directory that sing-tsung is installed at.  config.php should be
// in this directory.
$TSUNG_CONFIG['install_dir'] = "/var/www/localhost/htdocs/ryan-new";

// This directory should be accessible on the web.
$TSUNG_CONFIG['report_dir'] = "{$TSUNG_CONFIG['log_dir']}/tsung_stat_report/";
$TSUNG_CONFIG['report_dir_web'] = "/logs/tsung_stat_report/";

$TSUNG_CONFIG['config_file_dir'] = $TSUNG_CONFIG['log_dir']."/.tsung/";
$TSUNG_CONFIG['prog_prefix'] = "/opt/tsung";
$TSUNG_CONFIG['prog_bin'] = "{$TSUNG_CONFIG['prog_prefix']}/bin/tsung";
$TSUNG_CONFIG['log_list_tmpfile']="/tmp/tsung_logs.txt";
$TSUNG_CONFIG['report_pl'] = "{$TSUNG_CONFIG['prog_prefix']}/lib/tsung/bin/tsung_stats_new.pl";


function sprint_r($thing){
  ob_start();
  print_r($thing);
  $txt = ob_get_contents();
  ob_end_clean();
  return $txt;
} // function sprint_r
?>
