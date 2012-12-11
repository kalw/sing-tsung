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
function add_session(){
  var selbox = document.getElementById("session_to_add");
  var sessions_row = document.getElementById("sessions_row");
  var nobodyrow = document.getElementById("nobodyhere");
  if (nobodyrow) { nobodyrow.parentNode.removeChild(nobodyrow); }

  // Now, make a new row for me.
  var myrow = document.createElement("tr");

  var td = document.createElement("td");
  myrow.appendChild(td);

  var input = document.createElement("input");
  td.appendChild(input);
  input.type = "hidden";
  input.name = "session_add[]";
  input.value = selbox.options[selbox.selectedIndex].value;
  
  // All the children of the option, taken together, comprise the name.
  var txts_to_add = selbox.options[selbox.selectedIndex].childNodes;
  for (var i=0; i<txts_to_add.length; i++) {
    var newtxt = txts_to_add[i].cloneNode(true);
    var link = document.createElement("a");
    link.href = "index.php?screen=record&ACTION[EDIT]=Edit&session[id]="
                + input.value;
    link.appendChild(newtxt);
    td.appendChild(link);
  } // for all the children of the option

  var td = document.createElement("td");
  myrow.appendChild(td);

  var checkbox = document.createElement("input");
  td.appendChild(checkbox);
  checkbox.type = "checkbox";
  checkbox.name = "session_notreally[]";
  checkbox.value = selbox.options[selbox.selectedIndex].value;

  var insertBeforeMe = document.getElementById('savebuttonbar');
  insertBeforeMe = insertBeforeMe.parentNode;
  sessions_row.parentNode.insertBefore(myrow, insertBeforeMe);
} // function add_session


/**
 * Sets up all form elements to mark a global variable when they're altered.
 */
function init_detect_when_needs_saving(){
  document.form_needs_saving = false;

  var mark_needs_saving = function(){
    document.form_needs_saving=true;
  }

  var allinputs = document.getElementsByTagName("input");
  for (var i=0; i<allinputs.length; i++) {
    var elt=allinputs[i];
    if (elt.type=="submit") { continue; }
    AttachEvent(elt, "focus", mark_needs_saving, false);
  } // for all inputs
} // function detect_when_needs_saving
AttachEvent(window, "load", init_detect_when_needs_saving, false);


/**
 * If they've changed things on the form, ask if they really want to save.
 */
function confirm_edit() {
  if (document.form_needs_saving) {
    var profile_sel = document.getElementById("profile_id");
    var sel_option = profile_sel.options[profile_sel.selectedIndex];
    var profile_name = sel_option.firstChild.nodeValue;
    return confirm(
        "Are you sure you want to abandon your changes and edit the profile '"+
        profile_name+"' instead?");
  } // if form_needs_saving
    
  return true;
} // function confirm_edit

