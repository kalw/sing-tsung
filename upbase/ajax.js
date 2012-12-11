var xmlhttp=false;

function init_xmlhttp(){
if(xmlhttp){
xmlhttp.onreadystatechange = function () {};
xmlhttp.abort();
xmlhttp=null;
}
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlhttp = false;
  }
 }
@end @*/
if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttp = new XMLHttpRequest();
	} catch (e) {
		xmlhttp=false;
	}
}
if (!xmlhttp && window.createRequest) {
	try {
		xmlhttp = window.createRequest();
	} catch (e) {
		xmlhttp=false;
	}
}
}
function remote_strtotime(element, timestring){
		init_xmlhttp();
		if(timestring ==''){
			return false;
		}
		xmlhttp.open("GET", "index.php?screen=ajax&strtotime="+timestring,false);
		xmlhttp.onreadystatechange=
			function() {
			  if (xmlhttp.readyState==4) {
				element.value = xmlhttp.responseText;
			  }
		 	}
		xmlhttp.send(null);
		element.value = xmlhttp.responseText;
		xmlhttp.abort();
		xmlhttp = false;
}

function presubmit_synchronous_breaksie(myform){
		
		var els = myform.elements;
		var screen = '';
		var postData = '';
			for (var i=0;i<els.length;i++)
			{
				var name = escape(els[i].name);
				var value = escape(els[i].value);
				var type = escape(els[i].type);
				if(name == 'screen'){
					screen = value;
				}
				if(type !="checkbox" || els[i].checked){
					postData += name + '=' + value + '&';
				}
			}
		init_xmlhttp();

		xmlhttp.open("POST", "index.php?screen="+screen+"&presubmit=true",false);
		xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		xmlhttp.onreadystatechange=changed_dummy;
		xmlhttp.send(postData);

		if(xmlhttp.readyState != 4){
		}
		else{
			e=document.getElementById('DEBUG[ajax_output]');
			if(e){
			e.innerHTML='<pre>'+xmlhttp.responseText+'</pre>';
					e=null;
			}
			eval(xmlhttp.responseText);

		}

}

function changed_dummy(){
//	alert('changed: '+xmlhttp.readyState);
//	alert('status: '+ xmlhttp.status);
}

function presubmit_synchronous(myform){
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
	presubmit_synchronous_breaksie(myform);
   return true;
  }
 }
	//synchronous calls periodically invoke the full 5 minute timeout period in ie
	presubmit(myform);
}
function presubmit(myform){
		var els = myform.elements;
		var screen = '';
		var postData = '';
			for (var i=0;i<els.length;i++)
			{
				var name = escape(els[i].name);
				var value = escape(els[i].value);
				var type = escape(els[i].type);
				if(name == 'screen'){
					screen = value;
				}
				if(type !="checkbox" || els[i].checked){
					postData += name + '=' + value + '&';
				}
			}
		init_xmlhttp();
		xmlhttp.open("POST", "index.php?screen="+screen+"&presubmit=true",true);
		xmlhttp.onreadystatechange=changed;
		xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		xmlhttp.send(postData);
//		xmlhttp.abort();
//		xmlhttp = false;
}

function changed(){
			  if (xmlhttp.readyState==4) {
				e=document.getElementById('DEBUG[ajax_output]');
				if(e){
					e.innerHTML='<pre>'+xmlhttp.responseText+'</pre>';
					e=null;
				}
				eval(xmlhttp.responseText);
			  }
}

