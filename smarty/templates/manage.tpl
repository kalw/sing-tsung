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
<link rel="stylesheet" type="text/css" href="shared/manage.css">

<script type="text/javascript" src="shared/manage.js"></script>

<form method="post" action="">

<input type="hidden" name="profile[id]" value="{$profile.id}" />

<table>
<tr valign="top">
<td>
	<table>
	<tr>
	<td colspan=2 align=right>
	</td>
	</tr>

	<tr>
	<td>
	</td>
	<td>
    <table class="control_menu" cellpadding="0" cellspacing="0">
    <tr bgcolor="white"><th colspan="2">
    Edit an existing profile:
    </th></tr>
    <tr><td>
    <select name="profile_id" id="profile_id">
    <option value="">(New profile)</option>
		{foreach from=$profiles item="prof"}
      {if $chosen_profile && $chosen_profile == $prof.id}
    <option value="{$prof.id}" selected>{$prof.name}</option>
      {else}
    <option value="{$prof.id}">{$prof.name}</option>
      {/if}
    {/foreach}
    </select>
    </td>
    <td id="editbuttongoeshere">
      <input type="submit" name="action" value="Edit" onclick="return confirm_edit();" />
    </td></tr>
    <tr><td id="createbuttongoeshere" colspan="2">
    <a href="?screen=manage" onclick="return confirm_edit();">Create a new profile</a>
    </td></tr>
    </table>
	</td>
	</tr>
	</table>
</td>
<td>
	<table class=control_menu cellpadding=0 cellspacing=0>
	<tr>
	<th bgcolor=white colspan=2>
		General Information
	</th>
	</tr>
	<tr>
	 <td align='right'>
	 	<span class="accelerator">N</span>ame
	 </td>
	 <td>
	 	<input type=text accesskey="n" name="profile[name]" value="{$profile.prof_name}">
	 </td>
	</tr>
	<tr><th bgcolor=white colspan=2>Load</th></tr>
  <tr><td align="right">Ma<span class="accelerator">x</span> Users Per Instance</td>
    <td><input type="text" name="profile[maxusers]" accesskey="x" value="{if $profile.maxusers}{$profile.maxusers}{else}{$defaults.maxusers}{/if}" /></td>
	<tr><td align='right'><span class="accelerator">A</span>rrival Phase Duration in Minutes</td>
      <td><input type=text name="profile[load_arrival_phase_minutes]" accesskey="a" value="{if $profile.load_arrival_phase_minutes!==null}{$profile.load_arrival_phase_minutes}{else}{$defaults.arrivalphase}{/if}" /></td></tr>
	<tr><td align='right'><span class="accelerator">I</span>nterarrival Duration in Seconds</td>
      <td><input type=text name="profile[load_interarrival_duration_sec]" title="Set to >=1 to generate traffic.  Set between 0 and 1 to generate heavy load." accesskey="i" value="{if $profile.load_interarrival_duration_sec!==null}{$profile.load_interarrival_duration_sec|string_format:"%f"}{else}{$defaults.interarrival|string_format:"%f"}{/if}" /></td></tr>
	<tr><th bgcolor=white colspan=2>Sessions</th></tr>
	<tr id="addone_row"><td>
    <select id="session_to_add">
    <!-- RYAN WUZ HERE -->
    {foreach from=$all_sessions item="session"}
    <option value="{$session.id}">{$session.name}</option>
    {/foreach}
    </select>
  </td><td><input type=button value=Add onclick="add_session()"></td></tr>
	<tr id="sessions_row"><td>Session Name</td><td>Remove</td></tr>
  {foreach from=$profile.sessions item="session"}
	<tr><td><a href="index.php?screen=record&ACTION[EDIT]=Edit&session[id]={$session.id}">{$session.name}</a></td><td><input type=checkbox name="session_remove[]" value="{$session.id}"></td></tr>
  {foreachelse}
  <tr id="nobodyhere"><td colspan="2">(There are no sessions associated with this profile.)</td></tr>
  {/foreach}
  <tr><td colspan="2" id="savebuttonbar">
    <input type="hidden" name="save" value="save" />
    {if $profile.id}
      <input type="submit" id="deletebutton" name="action" value="Delete Profile" accesskey="d" onclick="return confirm('Are you sure you want to delete?')">
    {/if}
    <input type="submit" name="action" value="Save" accesskey="s">
  </td></tr>
	</table>
</td>
</tr>
</table>

</form>

