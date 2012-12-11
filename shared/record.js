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
function dbg(msg){
  var dbgout = document.getElementById('dbg');
  if (!dbgout) return;
  dbgout.value += msg+"\n";
  dbgout.scrollTop = dbg.scrollHeight;
} // function dbg

document.dbg = dbg;


/**
 * Change the message that is written on the screen.
 */
function change_message(new_msg){
  var old_div = document.getElementById("message_div");
  var msg_div = document.createElement("div");
  msg_div.id = "message_div";
  old_div.parentNode.insertBefore(msg_div, old_div);
  old_div.parentNode.removeChild(old_div);
  msg_div.appendChild(document.createTextNode("Messages:"));
  var ul = document.createElement("ul");
  msg_div.appendChild(ul);
  var li = document.createElement("li");
  ul.appendChild(li);
  li.appendChild(document.createTextNode(new_msg));
} // function change_message


/**
 * See if tsung was already running.  Inform the user.
 */
function was_recorder_already_running(){
  init_xmlhttp();
  xmlhttp.open("POST", "index.php?screen="+SCREEN_NAME+"&presubmit=true",true);
  xmlhttp.onreadystatechange=changed;
  xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xmlhttp.send("whattodo=poke&initial=true");
} // function was_recorder_already_running
AttachEvent(window, "load", was_recorder_already_running, false);


/**
 * Enable the continue button, if it exists, on startup.
 * When the page loads, it may or may not print out a continue button.  Let's
 * make sure it's got an event attached to it if it does.
 */
function enable_continue_button(){
  var continuebutton = document.getElementById("ACTION[CONTINUE]");
  if (continuebutton) {
    AttachEvent(continuebutton, "click", continue_tsung, false);
  } // if the continue button exists
} // function enable_continue-button
AttachEvent(window, "load", enable_continue_button, false);

/**
 * We have checked the recorder.  If it's running, display a message.
 * @param {Object} xmlhttp Contains .responseText, returned from server.
 */
function recorder_checked(msg){
  if (msg != "running") { 
    // The stop button can be disabled.
    var stop_button = document.getElementById("ACTION[STOP]");
    stop_button.disabled = true;
    return; 
  } // if it's not running

  var url = new String(document.location);
  var matches = new RegExp("https?:\/\/([^\/]*)").exec(url);
  change_message("The tsung recorder has already been started, somewhere.  "+
    "You are seeing its recording, already in progress.  "+
    "To add to it, set your browser's proxy settings to: "+matches[1]+
    ", port 8090, for all protocols.  "+
    "Also, set your browser not to use the proxy when looking at "+
    "sites beginning with "+matches[1]+".");

  start_tailwatching();
} // function recorder_checked

/**
 * Start the Tsung recorder, make the textarea watch the log grow.
 * Also, manage the buttons, so stop is enabled and start is disabled.
 * @param {Object} start_button The button that you clicked.
 */
function start_tsung(button){
  if (button.value == "Clear and Restart Recording") {
    if (!confirm("Do you really want to clear your previous recording "+
                  "and start over?")) 
    {
      return;
    } // if they didn't confirm
    else {
      var textarea = document.getElementById('session[data]');
      textarea.value = "";
    } // else they confirmed clear and restart
  } // if it's restart


  presubmit_me(button);
} // function start_tsung


/**
 * Called once tsung has been started.  Sets up the log watcher.
 * We expect start_tsung.php to have returned:
 * 1,<logfile-path>
 * if it succeeded, and
 * 0,<error-message>
 * if not.
 */
function tsung_started(errorText){
  var textarea = document.getElementById('session[data]');

  if (errorText) {
    textarea.value = "Error: "+errorText;
    return;
  } // if it didn't work

  // Put a message that says how to do it.
  var url = new String(document.location);
  var matches = new RegExp("https?:\/\/([^\/]*)").exec(url);
  change_message(
    "The tsung recorder has been started.  "+
    "Set your web browser's proxy settings to: "+matches[1]+", port 8090, "+
    "for all protocols.  Also, set it to not use the proxy when connecting "+
    "to sites beginning with "+matches[1]+" Then, when you look at websites, "+
    "this page will record what you do.");

  start_tailwatching();
} // function tsung_started


/**
 * Start the tailwatcher and set the buttons up.
 */
function start_tailwatching(){
  // Okay, we're good now.  Let's set up the log watcher.
  tailwatcher_start('session[data]', "current_log.xml");

  // We're all good.  Set the buttons and wait it out.
  var stop_button = document.getElementById('ACTION[STOP]');
  stop_button.disabled = false;

  var start_button = document.getElementById('ACTION[START]');
  start_button.disabled = true;

  var continue_button = document.getElementById('ACTION[CONTINUE]');
  if (continue_button) { continue_button.disabled = true; }

  if (!document.tsung_poker) {
    document.tsung_poker = window.setInterval(poke_tsung, 2000);
    dbg("Starting poker.  ("+document.tsung_poker+")");
  }
} // function start_tailwatching


/**
 * See if the recorder is still running.  Flip out if not.
 */
function poke_tsung(){
  init_xmlhttp();
  xmlhttp.open("POST", "index.php?screen="+SCREEN_NAME+"&presubmit=true",true);
  xmlhttp.onreadystatechange=changed;
  xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xmlhttp.send("whattodo=poke");
} // function poke_tsung

/**
 * What did they say?  Still running?  If not, flip out.
 */
function tsung_stopped_by_someone_else(){
  if (ACTUALLY_I_STOPPED_IT) { return; }
  change_message(
    "Tsung has been stopped by somebody else.  "+
    "The proxy is no longer listening to you.  "+
    "You may return your browser's proxy settings to normal.  "+
    "You may save this recording session.");
  tsung_stopped();
} // function tsung_poked


/**
 * Stop the Tsung recorder, and fix buttons:  Stop disabled, start enabled.
 */
function stop_tsung(){
  you_stopped_tsung();
} // function stop_tsung
  

/**
 * The tsung recorder has stopped.  Update the interface.
 */
function you_stopped_tsung(){
  // Put a message that says how to switch back.
  change_message(
                    "The tsung recorder has been stopped.  "+
                    "The proxy is no longer listening to you.  "+
                    "You may now click 'Save' to save your record.  "+
                    "Don't forget to switch your browser back to normal, "+
                    "non-proxy mode in order to view websites besides this "+
                    "one.");
  tsung_stopped();
} // function you_stopped_tsung


/**
 * No matter how tsung got stopped, we should update the interface.
 */
function tsung_stopped(){
  tailwatcher_stop("session[data]");
  var stop_button = document.getElementById('ACTION[STOP]');
  stop_button.disabled = true;
  var start_button = document.getElementById('ACTION[START]');
  start_button.value = "Clear and Restart Recording";
  start_button.disabled = false;

  var continue_button = document.getElementById('ACTION[CONTINUE]');
  if (!continue_button) {
    continue_button = document.createElement('input');
    continue_button.type = 'button';
    continue_button.id = 'ACTION[CONTINUE]';
    continue_button.className = "continuebutton";
    continue_button.value = "Continue Recording";
    start_button.parentNode.insertBefore(continue_button, start_button.nextSibling);
    AttachEvent(continue_button, "click", continue_tsung, false);
  } // if there's no continue button
  else {
    continue_button.disabled = false;
    AttachEvent(continue_button, "click", continue_tsung, false);
  } // else there was already a continue button

  // Stop seeing whether somebody else has stopped tsung - we don't need to
  // know.
  window.clearInterval(document.tsung_poker);
  document.tsung_poker = 0;
} // function stop_tsung


/**
 * Continue recording, appending to the end of the text field.
 * @param {Object} event The event object.  We can use it to get the target.
 */
function continue_tsung(event){
  var button = get_target(event, window.event);
  var textarea = document.getElementById('session[data]');
  var txt = textarea.value.replace(/<\/session>\s*$/, '');
  textarea.value = txt;
  textarea.start_text = txt;
  // This is a hack to make the tailwatcher skip the new <session> tag that
  // re-starting the recorder will write.
  textarea.tailwatcher_offset_lines=1;

  start_tsung(button);
} // function continue_tsung


/**
 * The user clicked to edit.  Should we let them?
 * @return False to block form submit, or true to abandon changes.
 */
function confirm_abandon_changes(){
	var textarea = document.getElementById("session[data]");
  if (textarea && textarea.value &&
    !/^\s*Click "Start Recording" to record a session.\s*$/.test(textarea.value)) 
  {
    var select_box = document.getElementById("session[id]");
    return confirm(
      "Really abandon changes and edit the session "+
      select_box.options[select_box.selectedIndex].firstChild.nodeValue+
      " instead?");
  } // if they had something in there

  return true;
} // function confirm_abandon_changes


function stop_hasbeen_clicked(button){
  window.clearInterval(document.tsung_poker); 
  document.tsung_poker = 0; 
  presubmit_me(button);
} // function stop_hasbeen_clicked


function presubmit_me(button){
  var hdn = document.getElementById('whattodo');
  hdn.value = button.value;
  presubmit(button.form);
} // function presubmit_me


/**
 * They said to delete a session.  Make sure they know what they're doing.
 * @return bool False if they said no, don't delete.
 */
function confirm_delete_session() {
  var session_to_delete = "";
  var selector = document.getElementById('session[id]');
  var session = selector.options[selector.selectedIndex];

  session_to_delete = session.innerHTML;
  return confirm(
      "Are you sure you want to delete the session '"+session_to_delete+"'\n  "
      +"Note:  You are asking to delete the session whose name appears on the "
      +"'Session Management' screen, which may be different from the session "
      +"on the main part of the screen.");
} // function confirm_delete_session
