{*
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
 *}
<link rel="stylesheet" type="text/css" href="shared/content.css">
<link rel="stylesheet" type="text/css" href="shared/run.css">

<script type="text/javascript">
var SCREEN_NAME = '{$screen_name}';
var REPORT_DIR  = '{$report_dir}';
</script>

<script type="text/javascript" src="shared/run.js"></script>


<form method="post">
<table class="control_menu" id="runmenu" cellspacing="0">
<tr><th colspan="2">Select a Profile to Run</th></tr>
<tr><td>
<select id="profile_id" name="profile_id">
{foreach from=$profiles item="profile"}
<option value="{$profile.id}">{$profile.name}</option>
{/foreach}
</select>
</td>
<input type="hidden" name="screen" value="run" />
<input type="hidden" name="whattodo" id="whattodo" />

<td><input type="button" value="Run Selected Profile" 
    onclick="start_tsung_player(); presubmit_me(this); " /></td>
</tr>

<tr>
  <td>
  </td>
  <td><input type="button" value="Stop All Profiles" onclick="stop_tsung_player(); presubmit_me(this);"/>
  </td>
</tr>

<tr>
  <td>
  </td>
  <td id="status_button"><input type="button" value="Generate Stats Report" onclick="presubmit_me(this);"/>
  (from the latest log file)</td>
</tr>
</table>

<div class="messages" id="message_div"></div>

<pre id="tsung_status_pre"></pre>

</form>
