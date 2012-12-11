<?xml version="1.0"?>
<!DOCTYPE tsung SYSTEM "/opt/tsung/share/tsung/tsung-1.0.dtd">
<!--
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
 -->
<tsung loglevel="notice" version="1.0">
 <!-- Client side setup --> 
<clients>
 <!-- <client host="localhost" use_controller_vm="false" maxusers="{$maxusers}" />  -->
 <client host="localhost" use_controller_vm="false" maxusers="{$maxusers}" />  
</clients>

<!-- Server side setup -->
<servers>
 <server host="blah" port="80" type="tcp"></server>
</servers>

 <!-- to start os monitoring (cpu, network, memory). Use an erlang agent on the remote machine or SNMP. erlang is the default -->

<load>
<!-- several arrival phases can be set: for each phase, you can set the mean inter-arrival time between new clients and the phase duration -->

<!-- Experimentation by Matt suggests that having more than one arrivalphase causes tsung to generate higher network throughput by spawning more than one netwok socket.  Here we have 2, the minimal number required to cause this to happen -->

<arrivalphase phase="1" duration="{$duration}" unit="minute">
 <users interarrival="{$interarrival|string_format:"%f"}" unit="second"></users>
</arrivalphase>

<arrivalphase phase="2" duration="{$duration}" unit="minute">
 <users interarrival="{$interarrival|string_format:"%f"}" unit="second"></users>
</arrivalphase>

</load>

<options>
 <option type="ts_http" name="user_agent">
 <user_agent probability="80">Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.8) Gecko/20050513 Galeon/1.3.21</user_agent>
 <user_agent probability="20">Mozilla/5.0 (Windows; U; Windows NT 5.2; fr-FR; rv:1.7.8) Gecko/20050511 Firefox/1.0.4</user_agent>
 </option>
</options>

<sessions>
{$sessions}
</sessions>
</tsung>
