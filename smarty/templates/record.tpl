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
<link rel="stylesheet" type="text/css" href="shared/content.css">
<link rel="stylesheet" type="text/css" href="shared/record.css">

<script type="text/javascript" src="shared/record.js"></script>
<script type="text/javascript">
var SCREEN_NAME = '{$screen_name}';
</script>

{if $messages}
<div class="messages" id="message_div">
Messages:
<ul>
{foreach from=$messages item="message"}
<li>{$message}</li>
{/foreach}
</ul>
</div>
{else}
<div id="message_div"></div>
{/if}

<div>
<form method="POST" action="index.php">
  <input type="hidden" name="screen" value="{$screen_name}" />
  <input type="hidden" name="whattodo" id="whattodo" value="" />

<span name="DISPLAY_ONLY[ACTION][STATUS]" id="DISPLAY_ONLY[ACTION][STATUS]" class="status_text">
</span>


<table id="copout_table">
<tr valign="top">
<td>

<table id="control_menu" class="control_menu" width="100%" cellspacing="0">
<tr><th colspan=2>Session Management</th></tr>
<tr>
  <td>
   <select name="session[id]" id="session[id]">
   <option value="">(New session)</option>
   {foreach from=$all_sessions item="this_session"}
      <option value="{$this_session.id}" {if $this_session.id==$last_data.session.id}selected{/if}>{$this_session.name}</option>
   {/foreach}
   </select>
  </td>
  <td>
    <input type="submit" name="ACTION[EDIT]" value="Edit" 
      onclick="return confirm_abandon_changes();" />
    <input type="submit" id="deletebutton" name="ACTION[DELETE]" value="Delete" 
      onclick="return confirm_delete_session();">
  </td>
</tr>
<tr>
<td colspan="2" id="createbuttongoeshere">
<a href="?screen=record">Create a new session</a>
</td>
</tr>
</table>


</td>

<td>
<table id="control_menu" class="control_menu" width="100%" cellspacing="0">
<tr><th colspan=2>Session Recording and Creation</th></tr>
<tr id="buttonbar"><td colspan="2">
  <div id="startbuttons_col">
{if ! $last_data.edit}
	<input type="button" name="ACTION[START]" id="ACTION[START]" value="Start Recording" onclick="start_tsung(this);">
{else}
	<input type="button" name="ACTION[START]" id="ACTION[START]" value="Clear and Restart Recording" onclick="start_tsung(this);">
	<input type="button" name="ACTION[START]" id="ACTION[CONTINUE]" value="Continue Recording">
{/if}
  </div>

  <div id="stopbutton_col">
	<input type="button" name="ACTION[STOP]" id="ACTION[STOP]" value="Stop Recording" onclick="stop_hasbeen_clicked(this);">
  </div>
</td></tr>

 <tr>
  <td align=right>
	Session Name:
  </td>
  <td>
	<input type=text name="session[name]" value="{$last_data.session.name}" />
  </td>
 </tr>
 <tr>
  <td align=right valign=top>
  	Session Body:
  </td>
  <td id="record_console">
  <!-- <p><textarea id="dbg" rows="6" cols="60"></textarea></p> -->
	<textarea name="session[data]" id="session[data]" rows=20 cols=60>{if $last_data.session}{$last_data.session.data|escape}{else}        Click "Start Recording" to record a session.{/if}</textarea>
  </td>
 </tr>
 <tr>
  <td colspan=2 align=right>
	<input type='Submit' name="ACTION[SAVE]" value='Save'>
  </td>
 </tr>
</table>

</td>
</tr>
</table>

</form>

</div>
