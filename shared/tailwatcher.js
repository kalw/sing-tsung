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
 * Create textareas that watch logs grow.
 * To use this, just create some textareas and buttons with special names, and
 * you can have a textarea with start and stop buttons, where you can click
 * "start" and have it watch a given log file, and append to the textarea
 * whenever the log grows.
 * You can stop it with a stop button.

 * Make sure the directories exist.
 * 1. Make sure there's js_request/tail.php, off of the directory of the php
 *    script that's in the url of the page using this file.
 * 2. Make sure there's /logs, off of web-root, and that apache has write
 *    access to it.
 *
 * Then, you can make a file like this:
 *
 * <code>
 * <html>
 * <head>
 * 
 * <script type="text/javascript" src="portability.js"></script>
 * <script type="text/javascript" src="XHConn.js"></script>
 * <script type="text/javascript" src="tailwatcher.js"></script>
 * 
 * </head>
 * 
 * <body>
 * 
 * <form>
 * <p>
 * <input type="button" name="tail-f:start" value="Start" id="cookie:/logfile.txt" />
 * <input type="button" name="tail-f:stop" value="Stop" id="cookie:" />
 * </p>
 * 
 * <p>
 * <textarea cols="80" rows="25" id="cookie"></textarea>
 * </p>
 * 
 * </form>
 * </body>
 * </html>
 * </code>
 *
 * @author Ryan Hughes <rjhughes@umich.edu>
 */

/**
 * After how many msec to re-check the log?
 */
var TAILWATCHER_MSEC = 1000;

/**
 * Let a textarea serve as a viewer for a log.  Watch as it grows!
 * There should be an 
 * <input type="button" name="tail-f:start" id=something_special />.
 * What should the id be?  It should be:
 * <id_of_textarea>:<log_file_path_relative_to_logroot>
 *
 * So if you have <textarea id="cookie" />, then you can have:
 * <input type="button" name="tail-f:start" id="cookie:/path/to/logfile.txt" />
 *
 * Clicking that start button will automatically start spitting out the logs as
 * they come, onto that textarea.
 *
 * If you have an
 * <input type="button" name="tail-f:stop" id="cookie" />
 * then clicking that button will stop the logging.
 */
function tailwatcher_init(){
  var candidate_inputs = document.body.getElementsByTagName("input");
  for (var i=0; i<candidate_inputs.length; i++) {
    // If it's one of our start buttons, make it act like one!
    if (candidate_inputs[i].type == 'button' &&
        candidate_inputs[i].name == 'tail-f:start')
    {
      AttachEvent(candidate_inputs[i], "click", tailwatcher_click_start);
    } // if it's the start button

    // If it's one of our stop buttons, make it act like one!
    if (candidate_inputs[i].type == 'button' &&
        candidate_inputs[i].name == 'tail-f:stop')
    {
      candidate_inputs[i].disabled = true;
      AttachEvent(candidate_inputs[i], "click", tailwatcher_click_stop, false);
    } // if it's the start button
  } // for all the candidate inputs
} // function tailwatcher_init
AttachEvent(window, "load", tailwatcher_init, false);


/**
 * UI handler:  Clicked the stop button.
 * We expect the button to have .id=<id_of_textarea>:
 * That is, the id of the textarea we want to stop, followed by a colon.
 */
function tailwatcher_click_stop(event){
  target = get_target(event, window.event);
  if (target.disabled == true) { return; }

  var re = /(.*):/;
  if (!re.test(target.id)) { return; }
  var matches = re.exec(target.id);
  tailwatcher_stop(matches[1]);

  // Now, enable all the start buttons for this text field, and disable all the
  // stop buttons.
  var candidate_inputs = document.getElementsByTagName("input");
  for (var i=0; i<candidate_inputs.length; i++) {
    // If it's a start button, enable it.
    if (candidate_inputs[i].type == 'button' &&
        candidate_inputs[i].name == 'tail-f:start' &&
        candidate_inputs[i].id.indexOf(matches[1]+":")===0)
    {
      candidate_inputs[i].disabled = false;
    } // if it's a start button

    // If it's a stop button, disable it.
    if (candidate_inputs[i].type == 'button' &&
        candidate_inputs[i].name == 'tail-f:stop' &&
        candidate_inputs[i].id == matches[1]+":")
    {
      candidate_inputs[i].disabled = true;
    } // if it's a stop button
  } // for all the input candidates
} // function tailwatcher_click_stop


/**
 * Go ahead and stop watching on the target there.
 * The target should be the textarea, and should have .tailwatcher_interval.
 * We should be able to clear that interval and that will stop watching for us.
 *
 * @param {String} target_id The id of the textarea we want to stop.
 */
function tailwatcher_stop(target_id){
  var target = document.getElementById(target_id);
  if (!target) { return; }

  window.clearInterval(target.tailwatcher_interval);
} // function tailwatcher_stop

/**
 * Respond to clicking the start button.
 * This is a UI response function that calls the tailwatcher function.
 * It parses the .name field of the button that was clicked.
 * We will be expecting its target to have:
 * .name="<id_of_textarea>:<path_to_logfile>"
 *
 * The id_of_textarea is the textarea where the logfile is to be displayed.
 * The path to the logfile is relative to the log directory root, defined in
 * tail.php.
 *
 * @param {EventDescriptor} event
 */
function tailwatcher_click_start(event){
  var target = get_target(event, window.event);

  if (target.disabled == true) { return; }

  re = /([^:]*):(.*)/;
  if (!re.test(target.id)) { return; }

  var matches = re.exec(target.id);
  tailwatcher_start(matches[1], matches[2]);

  // Now, enable all the stop buttons for this text field, and disable all the
  // start buttons.
  var candidate_inputs = document.getElementsByTagName("input");
  for (var i=0; i<candidate_inputs.length; i++) {
    // If it's a start button, disable it.
    if (candidate_inputs[i].type == 'button' &&
        candidate_inputs[i].name == 'tail-f:start' &&
        candidate_inputs[i].id.indexOf(matches[1]+":")===0)
    {
      candidate_inputs[i].disabled = true;
    } // if it's a start button

    // If it's a stop button, enable it.
    if (candidate_inputs[i].type == 'button' &&
        candidate_inputs[i].name == 'tail-f:stop' &&
        candidate_inputs[i].id == matches[1]+":")
    {
      candidate_inputs[i].disabled = false;
    } // if it's a stop button
  } // for all the input candidates
} // function tailwatcher_click_start


/**
 * Start watching a log.
 * Makes GET requests every TAILWATCHER_MSEC msecs.  Those GET requests will be
 * asking for the tail of the logfile.  We will put what we get at the end of
 * the textarea.
 * It will clear the textarea, unless it has a property called start_text.  In
 * that case, the textarea is filled with the start_text.
 *
 * @param {String} textarea_id The id of the textarea that will receive text.
 * @param {String} logfile_path The logfile path, relative to the log-root,
 *                              defined in tail.php.
 */
function tailwatcher_start(textarea_id, logfile_path){
  var target_textarea = document.getElementById(textarea_id);
  if (!target_textarea) { return; }

  var request_url = new String(document.location).replace(/\?.*/, '');
  if (request_url[request_url.length-1] != "/") {
    var path_parts = request_url.split("/");
    path_parts.pop();
    request_url = path_parts.join("/");
  } // if we have to break it up
  request_url += "/js_request/tail.php";


  // Save it for later, so that it will know how to make further requests
  target_textarea.tailwatcher_request_url = request_url;
  target_textarea.tailwatcher_offset_lines = 0;
  target_textarea.value = "";
  if (target_textarea.start_text) {
    target_textarea.value += target_textarea.start_text;
  } // if we've got some start text

  target_textarea.tailwatcher_request_vars = "file="+logfile_path;

  var goFn = function () {
    tailwatcher_refresh_log(target_textarea);
  } // var goFn

  target_textarea.tailwatcher_interval = window.setInterval(goFn, 
                                                            TAILWATCHER_MSEC);
} // function tailwatcher_start


/**
 * Update the textarea from the log.
 * The textarea should have these properties set:
 * element.tailwatcher_request_url: The url to make requests for log info to.
 *   We'll add to the end of it.
 *
 * element.tailwatcher_request_vars: The variables to use in the request.  I
 *   will add "&offset=n" to the end of it.
 *
 * element.tailwatcher_offset_lines: How many lines we've already seen, and can
 *   therefore start seeing the file after that.
 *
 * element.tailwatcher_interval: The interval id.  You can use this variable to
 *   clear the interval.
 *
 * @param {DOM Element} element The textarea that we're updating.
 */
function tailwatcher_refresh_log(target){
  var xhconn = new XHConn();
  if (!xhconn) { return; }

  var add_to_log = function (xmlhttp){
    target.value += xmlhttp.responseText;
    // Scroll to the bottom
    target.scrollTop = target.scrollHeight;
    if (xmlhttp.responseText) {
      var lines = xmlhttp.responseText.split('\n');
      target.tailwatcher_offset_lines += lines.length-1;
    } // if there's some new text
  } // var add_to_log

  var url = 
    target.tailwatcher_request_url + "?" +
    target.tailwatcher_request_vars+"&offset="+target.tailwatcher_offset_lines;

  xhconn.connect(
    target.tailwatcher_request_url, 
    "GET",
    target.tailwatcher_request_vars+"&offset="+target.tailwatcher_offset_lines, 
    add_to_log);
} // function tailwatcher_refresh_log
