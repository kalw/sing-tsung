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
<!-- style -->
<link type="text/css" href="shared/sing-tsung.css" rel="stylesheet">

<center>
<div>
<table class=control_menu width=100% cellspacing=0>
<tr><th colspan=2>Session Recording and Creation</th></tr>
<tr><td>
	<input type='button' name="RECORD[START]" id="RECORD[START]" value="Start Recording">
</td><td>
	<input type='button' name="RECORD[STOP]" id="RECORD[STOP]" value="Stop Recording">
</td></tr>
</table>
<span name="DISPLAY_ONLY[RECORD][STATUS]" id="DISPLAY_ONLY[RECORD][STATUS]" class="status_text">
</span>
<hr>
<form method=POST>
<table width=100% class=control_menu cellpadding=0 cellspacing=0>
 <tr>
  <td align=right>
	Session Name:
  </td>
  <td>
	<input type=text name="RECORD[NAME]">
  </td>
 </tr>
 <tr>
  <td align=right valign=top>
  	Session Body:
  </td>
  <td>
	<textarea name="RECORD[SESSION_BODY]" id="RECORD[SESSION_BODY]" rows=20 cols=60>
		Dynamic ajax here.
	</textarea>
  </td>
 </tr>
 <tr>
  <td colspan=2 align=right>
	<input type='Submit' value='Save'>
  </td>
 </tr>
</table>
</form>
</div>
</center>
