/** XHConn - Simple XMLHTTP Interface - brad@xkr.us - 2005-01-24             **
 ** Code licensed under Creative Commons Attribution-ShareAlike License      **
 ** http://creativecommons.org/licenses/by-sa/2.0/                           **/
function XHConn()
{
  var xmlhttp;

  if (typeof ActiveXObject != "undefined" && ActiveXObject) {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } else if (typeof ActiveXObject != "undefined" && ActiveXObject) {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); 
  } else if (typeof XMLHttpRequest != "undefined" && XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  }

  if (!xmlhttp) return null;
    

  this.connect = function(sURL, sMethod, sVars, fnDone)
  {
    if (!xmlhttp) return false;
    sMethod = sMethod.toUpperCase();

    try {
      if (sMethod == "GET")
      {
        xmlhttp.open(sMethod, sURL+"?"+sVars, true);
        sVars = "";
      }
      else
      {
        xmlhttp.open(sMethod, sURL, true);
        xmlhttp.setRequestHeader("Method", "POST "+sURL+" HTTP/1.1");
        xmlhttp.setRequestHeader("Content-Type",
          "application/x-www-form-urlencoded");
      }
      xmlhttp.onreadystatechange = function(){ if (xmlhttp.readyState == 4) {
        fnDone(xmlhttp); }};
      xmlhttp.send(sVars);
    }
    catch(z) { return false; }
    return true;
  };
  return this;
}
