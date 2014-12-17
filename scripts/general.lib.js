/********************************/
/****** GENERAL JS LIBRARY ******/
/****** © 2012 JACOB BRUCE ******/
/**** FOR PERSONAL USE ONLY! ****/
/********************************/

var lowerChars = 'abcdefghijklmnopqrstuvwxyz';
var capsChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
var symbolChars = '~!@#$%^&*()_+{}|:<>?-=[]\;';
var filesadded = ''; // list of files dynamically added
var RFC2822_optimized = new RegExp("[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?", "i");

Object.prototype.isInteger = function () {
  return (this.toString().search(/^-?[0-9]+$/) == 0);
}

Object.prototype.isUnsignedInteger = function () {
  return (this.toString().search(/^[0-9]+$/) == 0);
}

Object.prototype.roundNumber = function (dec) {
	if (this.isNaN) {
		return 0;
	} else {
		var result = String(Math.round(this*Math.pow(10,dec))/Math.pow(10,dec));
		if (result.indexOf('.') < 0) {result += '.';}
		while ((result.length - result.indexOf('.')) <= dec) {result += '0';}
		return result;
	}
}

String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

String.prototype.isEmpty = function () {
	var tCopy = this.trim();
	if (null == tCopy || tCopy == '') {
		return true;
	} else {
		return false;
	}
}

String.prototype.containsChars = function (needles) {
   needles = String(needles);
   var result = false;
   for (var i = 0; i < this.length; i++) {
  	if (needles.indexOf(this.charAt(i)) != -1) {
		result = true;
	}
  }
  return result;
}

String.prototype.isValidEmail = function () {
    var result = RFC2822_optimized.exec(this.trim());
	if (result == false || null == result) {
		return false;
	} else {
		return true;
	}
}

Document.prototype.getElementsByClass = function(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null ) node = document;
	if ( tag == null ) tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function loadFile(filename, filetype){
  if (filetype == "js") { //if filename is a external JavaScript file
    var fileref=document.createElement('script');
    fileref.setAttribute("type","text/javascript");
    fileref.setAttribute("src", filename);
  } else {
    if (filetype == "css"){ //if filename is an external CSS file
      var fileref=document.createElement("link");
      fileref.setAttribute("rel", "stylesheet");
      fileref.setAttribute("type", "text/css");
      fileref.setAttribute("href", filename);
	}
  }
  if (typeof fileref!="undefined") {
    document.getElementsByTagName("head")[0].appendChild(fileref);
  }
}

function loadFileOnce(filename, filetype) {
  if (filesadded.indexOf("["+filename+"]") == -1) {
    loadFile(filename, filetype);
    filesadded+="["+filename+"]"; //List of files added in the form "[filename1],[filename2],etc"
  } else {
   return false;
  }
}

function addEventOnload(myFunction) {
  if (window.addEventListener) {
    window.addEventListener('load', myFunction, false);
  } else {
	if (window.attachEvent) {
      window.attachEvent('onload', myFunction);
	} else {
	  if (window.onload) {
	    window.onload = myFunction;
	  }
	}
  }
}

function setCookie(name, value, days) {
  var expDate = new Date();
  expDate.setDate(expDate.getDate() + days);
  value = escape(value) + ((days==null) ? "" : "; expires="+expDate.toUTCString());
  document.cookie = name + "=" + value;
}

function getCookie(name) {
  var i,x,y,ARRcookies = document.cookie.split(";");
  for (i = 0; i < ARRcookies.length; i++) {
    x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
    y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
    x = x.replace(/^\s+|\s+$/g,"");
    if (x == name) {
      return unescape(y);
    }
  }
}

function showElement(eID) {
  var target = document.getElementById(eID);
  target.style.visibility = 'visible';
  target.style.display = 'block';
}

function hideElement(eID) {
  var target = document.getElementById(eID);
  target.style.visibility = 'hidden';
  target.style.display = 'none';
}

function openWindow(address) {
  window.open(address, "_self");
}