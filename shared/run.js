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
 * Change the message that is written on the screen.
 */
function change_message(new_msg){
  var old_div = document.getElementById("message_div");
  var msg_div = document.createElement("div");
  msg_div.id = "message_div";
  msg_div.appendChild(document.createTextNode(new_msg));

  old_div.parentNode.insertBefore(msg_div, old_div);
  old_div.parentNode.removeChild(old_div);
} // function change_message


/**
 * Change the "Status" message -- the output of the tsung status command.
 */
function change_status_message(new_msg){
  var old_div = document.getElementById("tsung_status_pre");
  var msg_div = document.createElement("pre");
  msg_div.id = "tsung_status_pre";
  msg_div.appendChild(document.createTextNode(new_msg));

  old_div.parentNode.insertBefore(msg_div, old_div);
  old_div.parentNode.removeChild(old_div);
} // function status_message


/**
 * Set up watching tsung to say when it's done.  Show feedback.
 */
function start_tsung_player(){
  // If this is set, it's already running.  Don't start a new one, just because
  // they clicked on it a bunch of times.
  if (typeof document.tsung_interval != "undefined" &&
      document.tsung_interval)
  { return; }
  // We should set the interval to something, in case they click again before
  // we get the ajax request fired off.
  document.tsung_interval = 1;

  change_message("Tsung player starting...");
} // function start_tsung_player


/**
 * The tsung player has been started.  Start poking it.
 * We expect the reply to be "OK" or "NOT OK".
 */
function tsung_player_started(msg){
  if (msg) {
    change_message("Could not start Tsung player. "+msg);
    document.tsung_interval = 0;
    return;
  } // if error
  document.tsung_interval = window.setInterval(poke_tsung, 1500);
  change_message("Tsung player started...");

  document.tsung_status_poker = window.setInterval(status_poke, 10000);
  change_status_message("");
} // function tsung_player_started


/**
 * Poke tsung to see what the status is.
 */
function status_poke(){
  window.clearInterval(document.tsung_interval);
  document.tsung_interval = 0;

  init_xmlhttp();
  xmlhttp.open("POST", "index.php?screen="+SCREEN_NAME+"&presubmit=true",true);
  xmlhttp.onreadystatechange=changed;
  xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xmlhttp.send("whattodo=status");
} // function status_poke


/**
 * We have a return value from tsung status.  Show it.
 */
function process_tsung_status(msg){
  // If we were supposed to have stopped, don't bother anybody.
  if (!document.tsung_status_poker) { return; }

  document.tsung_interval = window.setInterval(poke_tsung, 1500);

  var txt = unescape(msg);
  change_status_message(txt);
} // function process_tsung_status


/**
 * Tell the user that they've stopped tsung.
 * Quit poking long enough for the user to see the message, then start poking
 * again to see if it was really stopped.
 */
function stop_tsung_player(){
  window.clearInterval(document.tsung_interval);
  document.tsung_interval = 0;
  change_message("Stop command sent.");
  document.tsung_interval = window.setInterval(poke_tsung, 1500);
} // function stop_tsung_player


/**
 * See if it was already running.
 */
function was_already_running(){
  init_xmlhttp();
  xmlhttp.open("POST", "index.php?screen="+SCREEN_NAME+"&presubmit=true",true);
  xmlhttp.onreadystatechange=changed;
  xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xmlhttp.send("whattodo=poke&initial=true");
} // function was_already_runnig
AttachEvent(window, "load", was_already_running, false);

/**
 * We poked it just once to see if it was already running.
 */
function alreadyrunning_poked(msg){
  if (msg != "not finished") { return; }
  document.tsung_interval = window.setInterval(poke_tsung, 1500);
  document.tsung_status_poker = window.setInterval(status_poke, 10000);
  change_message("Tsung player was already running...");
} // function alreadyrunning_poked


/**
 * See it tsung is still running.
 */
function poke_tsung(){
  init_xmlhttp();
  xmlhttp.open("POST", "index.php?screen="+SCREEN_NAME+"&presubmit=true",true);
  xmlhttp.onreadystatechange=changed;
  xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xmlhttp.send("whattodo=poke");
} // function poke_tsung


/**
 * The tsung player has been poked.  Is it there?
 * We expect one of these results: * "finished"; * "not finished".
 */
function tsung_poked(msg){
  // If we aren't needed, walk away with our dignity.
  // (They may have unset tsung_interval to tell us to stop poking while they
  // display, for example, the stop message).
  if (typeof document.tsung_interval == "undefined" 
      || !document.tsung_interval)
  { return; }

  if (msg == "not finished") {
    if (typeof document.tsung_elipses == "undefined")
    {document.tsung_elipses=-1;}

    document.tsung_elipses = (document.tsung_elipses + 1) % 3;
    var elipses = "";
    for(var a=0; a<document.tsung_elipses+1; a++) { elipses += ". "; }
    change_message("Tsung player running"+elipses);
  } else if (msg == "finished") {
    tsung_player_stopped();
  } 
} // function tsung_poked


/**
 * Once tsung is stopped, we can notify the user and stop poking.
 */
function tsung_player_stopped(xmlhttp){
  // We're clear to start again.
  window.clearInterval(document.tsung_interval);
  document.tsung_interval = 0;

  window.clearInterval(document.tsung_status_poker);
  document.tsung_status_poker = 0;

  change_message("Tsung player stopped.");
  change_status_message("");
} // function tsung_player_stopped


function presubmit_me(button){
  var hdn = document.getElementById('whattodo');
  hdn.value = button.value;
  presubmit(button.form);
} // function presubmit_me

//Kang 1-18-2007
function tsung_status_generated(){
   change_message("Tsung report generated!");
   window.open(REPORT_DIR+'/graph.html','mywindow','menubar=1, toolbar=1, width=600,height=400');
}

