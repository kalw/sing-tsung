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

/**
 * Return the lines from a tsung log file.
 * This will spit out the end of a tsung log file.  It edits out all the stuff
 * relating to the ajax-y calls that keep the log-viewer up-to-date.
 * In Sing-Tsung, the recording screen will ask once a second for updates to
 * the log file.  Since these go out over the web proxy, they will be recorded
 * in the sing-tsung log file.  We have to replace them with blank lines, so as
 * not to screw up the line count.
 *
 * TODO Is it going to be a problem to have all the <thinktime> tags in a row?
 * Do they need to be consolidated?  I hope not!  We're leaving them for now!
 */
ini_set('include_path', "..:".ini_get('include_path'));
require_once('../config.php');

/**
 * The directory where we will look for logs.
 * If you could look for logs any ol' place, that would be a security hole.
 * Therefore, you can only look for them under this directory.
 */
// $LOGFILE_ROOT = $_SERVER['DOCUMENT_ROOT']."/logs";
// $LOGFILE_ROOT = "/var/www/localhost/htdocs/logs/.tsung";
$LOGFILE_ROOT = $TSUNG_CONFIG['config_file_dir'];

$filename = $LOGFILE_ROOT.$_REQUEST['file'];

$lines = @file($filename);

// First pass:  Edit out all the stuff relating to the ajax calls that keep the
// log-viewer up-to-date.
for ($i=0+$_REQUEST['offset']; $i<count($lines); $i++) {
  if (preg_match('/js_request\/tail\.php/', $lines[$i])) {
    $lines[$i] = "\n";
  } // if it's a bad one
} // for all the lines

$txt = "";
for($i=0+$_REQUEST['offset']; $i<count($lines); $i++) {
  $txt .= $lines[$i];
} // for all the lines

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: text/plain");
echo $txt;

?>
