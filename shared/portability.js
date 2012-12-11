/*****************************************************************************
 * File: portability.js
 * Author: Various, noted below.  Compiled by Ryan Hughes (<rjhuges@umich.edu>)
 * Licence: GPL for the Ryan Hughes stuff.  Otherwise, noted below.
 * ---------------------------------------------------------------------------
 * What it does:
 *  This is my repository of js funtions that make for a cross-platform js
 *  experience.  It is mostly me cobbling together other people's work into one
 *  easy-to-include file.
 *****************************************************************************/

//*** This code is copyright 2003 by Gavin Kistner, gavin@refinery.com
//*** It is covered under the license viewable at http://phrogz.net/JS/_ReuseLicense.txt
//*** Reuse or modification is free provided you abide by the terms of that license.
//*** (Including the first two lines above in your source code satisfies the conditions.)


//***Cross browser attach event function. For 'evt' pass a string value with the leading "on" omitted
//***e.g. AttachEvent(window,'load',MyFunctionNameWithoutParenthesis,false);


// I added the stuff about the my_event_tester, because old safari, 1.0, works
// on the addEventListener model, but won't fire onclick events with checkbox
// and radio.  It will work if you use onclick, though.
// I am Ryan Hughes <rjhughes@umich.edu>

var MY_EVENT_TESTER_NOTSO = false;
function my_event_tester()
{
    MY_EVENT_TESTER_NOTSO = false;
} // function my_event_tester
    

function AttachEvent(obj,evt,fnc,useCapture){
    if (!useCapture) useCapture=false;

    MyAttachEvent(obj,evt,fnc);
    if( evt == "click" && (obj.type=='checkbox'||obj.type=='radio') ) {
	if (obj.addEventListener){
	    var e = document.createElement('input');
	    e.style.visibility='hidden';   e.style.width=0;   e.style.height=0;
	    e.type='checkbox';
	    document.body.appendChild(e);
	    e.addEventListener("click",my_event_tester,useCapture);
	    MY_EVENT_TESTER_NOTSO = true;
	    e.click();
	    if( MY_EVENT_TESTER_NOTSO ) {
		// MyAttachEvent(obj,evt,fnc);
		obj['on'+evt]=function(){ MyFireEvent(obj,evt) };
		return true;
	    } else {
		e.removeEventListener("click", my_event_tester, useCapture);
		document.body.removeChild(e);
	    }
	}
    } // if it's a click event -- this needs special handling in old safari

    if (obj.addEventListener){
	obj.addEventListener(evt,fnc,useCapture);
	return true;
    } else if (obj.attachEvent) {
	return obj.attachEvent("on"+evt,fnc);
    } else{
	// MyAttachEvent(obj,evt,fnc);
	obj['on'+evt]=function(){ MyFireEvent(obj,evt) };
    }
} 

//The following are for browsers like NS4 or IE5Mac which don't support either
//attachEvent or addEventListener
function MyAttachEvent(obj,evt,fnc){
    if (!obj.myEvents) obj.myEvents={};
    if (!obj.myEvents[evt]) obj.myEvents[evt]=[];
    var evts = obj.myEvents[evt];
    evts[evts.length]=fnc;
}
function MyFireEvent(obj,evt){
    if (!obj || !obj.myEvents || !obj.myEvents[evt]) return;
    var evts = obj.myEvents[evt];
    var passevt = [];
    passevt['target'] = obj;
    for (var i=0,len=evts.length;i<len;i++) evts[i](passevt);
}



// +----------------------------------------------------------------+
// | Array functions that are missing in IE 5.0                     |
// | Author: Cezary Tomczak [www.gosu.pl]                           |
// | Free for any use as long as all copyright messages are intact. |
// +----------------------------------------------------------------+
// [ I found it here http://gosu.pl/download/ie5.js -- Ryan ]

// Removes the last element from an array and returns that element.
if (!Array.prototype.pop) {
    Array.prototype.pop = function() {
        var last;
        if (this.length) {
            last = this[this.length - 1];
            this.length -= 1;
        }
        return last;
    };
}

// Adds one or more elements to the end of an array and returns the new length of the array.
if (!Array.prototype.push) {
    Array.prototype.push = function() {
        for (var i = 0; i < arguments.length; ++i) {
            this[this.length] = arguments[i];
        }
        return this.length;
    };
}

// Removes the first element from an array and returns that element.
if (!Array.prototype.shift) {
    Array.prototype.shift = function() {
        var first;
        if (this.length) {
            first = this[0];
            for (var i = 0; i < this.length - 1; ++i) {
                this[i] = this[i + 1];
            }
            this.length -= 1;
        }
        return first;
    };
}

// Adds one or more elements to the front of an array and returns the new length of the array.
if (!Array.prototype.unshift) {
    Array.prototype.unshift = function() {
        if (arguments.length) {
            var i, len = arguments.length;
            for (i = this.length + len - 1; i >= len; --i) {
                this[i] = this[i - len];
            }
            for (i = 0; i < len; ++i) {
                this[i] = arguments[i];
            }
        }
        return this.length;
    };
}

// Adds and/or removes elements from an array.
if (!Array.prototype.splice) {
    Array.prototype.splice = function(index, howMany) {
        var elements = [], removed = [], i;
        for (i = 2; i < arguments.length; ++i) {
            elements.push(arguments[i]);
        }
        for (i = index; (i < index + howMany) && (i < this.length); ++i) {
            removed.push(this[i]);
        }
        for (i = index + howMany; i < this.length; ++i) {
            this[i - howMany] = this[i];
        }
        this.length -= removed.length;
        for (i = this.length + elements.length - 1; i >= index + elements.length; --i) {
            this[i] = this[i - elements.length];
        }
        for (i = 0; i < elements.length; ++i) {
            this[index + i] = elements[i];
        }
        return removed;
    };
}




// This stuff was written by me, Ryan Hughes.  It is subject to the terms of
// the GNU General Public Licence.  http://www.gnu.org/licenses/gpl.txt
function say(txt)
{
    var e = document.getElementById('jscon');
    if( !e ) return;
    e.value += txt+"\n";
} // function say


function clear_con()
{
    var e = document.getElementById('jscon');
    if( !e ) return;
    e.value = "";
} // function clear_con


function spitout(obj)
{
    var con = document.getElementById('jscon');
    if( !con ) return;
    // con.value = "";
    con.value += "(\n";
    for( i in obj ) {
	con.value += i+": "+obj[i]+"\n";
    } // for obj's properties
    con.value += ")\n";
} // function spitout


function make_console()
{
    var bdy = document.getElementsByTagName('body')[0];

    var con_hr = document.createElement('hr');
    bdy.appendChild(con_hr);

    var conform = document.createElement('form');
    bdy.appendChild(conform);
	var condiv = document.createElement('div');
	conform.appendChild(condiv);
	    condiv.innerHTML += '<input type="reset" value="Clear" onclick="clear_con();" />';

	    var con_br = document.createElement('br');
	    condiv.appendChild(con_br);

	    var con_ta = document.createElement('textarea');
	    con_ta.rows = 24;
	    con_ta.cols = 80;
	    con_ta.id = 'jscon';
	    condiv.appendChild(con_ta);
	// div
    // form
} // function make_console


function get_computed_height(obj)
{
    var objHeight;
    var testobj;
    var gotit = false;

    // for Mozilla
    if( document.defaultView && document.defaultView.getComputedStyle ) {
	testobj = document.defaultView.getComputedStyle(obj, "");
	if( testobj ) {
	    objHeight = testobj.getPropertyValue("height");
	    if( objHeight ) { return objHeight; }
	} // if we got a height
    } // if we can get a height this way

    // for IE4+
    if( obj.offsetHeight ) {
	objHeight = obj.offsetHeight;
	return objHeight;
    }

    // for NN4+
    if( obj.clip.height ) {
	objHeight = obj.clip.height;
	return objHeight;
    }
} // function get_computed_height



//*** This code is copyright 2002-2003 by Gavin Kistner and Refinery; www.refinery.com
//*** It is covered under the license viewable at http://phrogz.net/JS/_ReuseLicense.txt
//*** Reuse or modification is free provided you abide by the terms of that license.
//*** (Including the first two lines above in your source code satisfies the conditions.)

//***Adds a new class to an object, preserving existing classes
function AddClass(obj,cName){ KillClass(obj,cName); return obj && (obj.className+=(obj.className.length>0?' ':'')+cName); }

//***Removes a particular class from an object, preserving other existing classes.
function KillClass(obj,cName){ return obj && (obj.className=obj.className.replace(new RegExp("^"+cName+"\\b\\s*|\\s*\\b"+cName+"\\b",'g'),'')); }

//***Returns true if the object has the class assigned, false otherwise.
function HasClass(obj,cName){ return (!obj || !obj.className)?false:(new RegExp("\\b"+cName+"\\b")).test(obj.className) }



function alertthedata()
{
    var allofem = document.all ? document.all : document.getElementsByTagName('*');
    for( var i=0; i<allofem.length; i++ ) {
	var elt = allofem[i];
	if( elt.className.indexOf('=') > -1 ) {
	    var classes = elt.className.split(/\s+/);
	    for(var j=0; j<classes.length; j++) {
		if( classes[j].indexOf('=') > -1 ) {
		    key_val = classes[j].split('=');
		    key = key_val[0];
		    val = unescape(key_val[1]);
		    elt[key] = val;
		} // if it's got the magic tag
	    } // for all the classes mentioned
	} // if it's got the tag
	
    } // for all of em
} // function alertthedata
// AttachEvent(window, "load", alertthedata);

// IE and Firefox differ in how to get the "target" of an event handler.
// Event handlers should be like this:
// function u_clicked_me(evt) { alert(evt.target); }
// and you should see the reference to the button you clicked.
// IE thinks it should be like this:
// function u_clicked_me() { alert(window.event.srcElement); }
// Also, old Safari has some bugs in it, so neither method works well.
// SCREW THAT!
// Do this: 
// function u_clicked_me(evt) { var target=get_target(evt, window.event);
//				alert(target); }
function get_target(evt, wdoevt)
{
    evt = (evt)? evt : wdoevt;
    target = (evt.srcElement)? evt.srcElement : evt.target;
    nnn9999xxx = false;
    wuz_id = target.id;
    if( !target.selectedIndex ) { evt.target.id = 'nnn9999xxx'; } 
    else { target.id = 'nnn9999xxx'; } // a Safari hack
    if( !nnn9999xxx ) { nnn9999xxx = target; }

    retval = nnn9999xxx;
    nnn9999xxx.id = wuz_id;

    return retval;
} // function get_target



/**
 * Move cursor position in a textfield.
 * This comes from:
 * http://parentnode.org/javascript/working-with-the-cursor-position/
 */
function setCaretTo(obj, pos) { 
    if(obj.createTextRange) { 
        /* Create a TextRange, set the internal pointer to
           a specified position and show the cursor at this
           position
        */ 
        var range = obj.createTextRange(); 
        range.move("character", pos); 
        range.select(); 
    } else if(obj.selectionStart) { 
        /* Gecko is a little bit shorter on that. Simply
           focus the element and set the selection to a
           specified position
        */ 
        obj.focus(); 
        obj.setSelectionRange(pos, pos); 
    } 
} 




/**
 * Write a message to a textarea called "dbg", if it exists.
 */
function dbg(msg) 
{
  var ta = document.getElementById("dbg");
  if (!ta) { return; }
  ta.value += msg+"\n";
  ta.scrollTop = ta.scrollHeight;
} // function dgb
