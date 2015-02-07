/* ========================================================================
 * Bootstrap: dropdown.js v3.3.2
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */
+function(e){"use strict";function i(r){if(r&&r.which===3)return;e(t).remove();e(n).each(function(){var t=e(this);var n=s(t);var i={relatedTarget:this};if(!n.hasClass("open"))return;n.trigger(r=e.Event("hide.bs.dropdown",i));if(r.isDefaultPrevented())return;t.attr("aria-expanded","false");n.removeClass("open").trigger("hidden.bs.dropdown",i)})}function s(t){var n=t.attr("data-target");if(!n){n=t.attr("href");n=n&&/#[A-Za-z]/.test(n)&&n.replace(/.*(?=#[^\s]*$)/,"")}var r=n&&e(n);return r&&r.length?r:t.parent()}function o(t){return this.each(function(){var n=e(this);var i=n.data("bs.dropdown");if(!i)n.data("bs.dropdown",i=new r(this));if(typeof t=="string")i[t].call(n)})}var t=".dropdown-backdrop";var n='[data-toggle="dropdown"]';var r=function(t){e(t).on("click.bs.dropdown",this.toggle)};r.VERSION="3.3.2";r.prototype.toggle=function(t){var n=e(this);if(n.is(".disabled, :disabled"))return;var r=s(n);var o=r.hasClass("open");i();if(!o){if("ontouchstart"in document.documentElement&&!r.closest(".navbar-nav").length){e('<div class="dropdown-backdrop"/>').insertAfter(e(this)).on("click",i)}var u={relatedTarget:this};r.trigger(t=e.Event("show.bs.dropdown",u));if(t.isDefaultPrevented())return;n.trigger("focus").attr("aria-expanded","true");r.toggleClass("open").trigger("shown.bs.dropdown",u)}return false};r.prototype.keydown=function(t){if(!/(38|40|27|32)/.test(t.which)||/input|textarea/i.test(t.target.tagName))return;var r=e(this);t.preventDefault();t.stopPropagation();if(r.is(".disabled, :disabled"))return;var i=s(r);var o=i.hasClass("open");if(!o&&t.which!=27||o&&t.which==27){if(t.which==27)i.find(n).trigger("focus");return r.trigger("click")}var u=" li:not(.divider):visible a";var a=i.find('[role="menu"]'+u+', [role="listbox"]'+u);if(!a.length)return;var f=a.index(t.target);if(t.which==38&&f>0)f--;if(t.which==40&&f<a.length-1)f++;if(!~f)f=0;a.eq(f).trigger("focus")};var u=e.fn.dropdown;e.fn.dropdown=o;e.fn.dropdown.Constructor=r;e.fn.dropdown.noConflict=function(){e.fn.dropdown=u;return this};e(document).on("click.bs.dropdown.data-api",i).on("click.bs.dropdown.data-api",".dropdown form",function(e){e.stopPropagation()}).on("click.bs.dropdown.data-api",n,r.prototype.toggle).on("keydown.bs.dropdown.data-api",n,r.prototype.keydown).on("keydown.bs.dropdown.data-api",'[role="menu"]',r.prototype.keydown).on("keydown.bs.dropdown.data-api",'[role="listbox"]',r.prototype.keydown)}(jQuery)

//download.js v3.1, by dandavis; 2008-2014. [CCBY2] see http://danml.com/download.html for tests/usage
// v1 landed a FF+Chrome compat way of downloading strings to local un-named files, upgraded to use a hidden frame and optional mime
// v2 added named files via a[download], msSaveBlob, IE (10+) support, and window.URL support for larger+faster saves than dataURLs
// v3 added dataURL and Blob Input, bind-toggle arity, and legacy dataURL fallback was improved with force-download mime and base64 support. 3.1 improved safari handling.

// https://github.com/rndme/download
function download(e,t,n){function d(e){var t=e.split(/[:;,]/),n=t[1],r=t[2]=="base64"?atob:decodeURIComponent,i=r(t.pop()),s=i.length,o=0,u=new Uint8Array(s);for(o;o<s;++o)u[o]=i.charCodeAt(o);return new l([u],{type:n})}function v(e,t){if("download"in a){a.href=e;a.setAttribute("download",c);a.innerHTML="downloading...";u.body.appendChild(a);setTimeout(function(){a.click();u.body.removeChild(a);if(t===true){setTimeout(function(){r.URL.revokeObjectURL(a.href)},250)}},66);return true}if(typeof safari!=="undefined"){e="data:"+e.replace(/^data:([\w\/\-\+]+)/,i);if(!window.open(e)){if(confirm("Displaying New Document\n\nUse Save As... to download, then click back to return to this page.")){location.href=e}}return true}var n=u.createElement("iframe");u.body.appendChild(n);if(!t){e="data:"+e.replace(/^data:([\w\/\-\+]+)/,i)}n.src=e;setTimeout(function(){u.body.removeChild(n)},333)}var r=window,i="application/octet-stream",s=n||i,o=e,u=document,a=u.createElement("a"),f=function(e){return String(e)},l=r.Blob||r.MozBlob||r.WebKitBlob||f;l=l.call?l.bind(r):Blob;var c=t||"download",h,p;if(String(this)==="true"){o=[o,s];s=o[0];o=o[1]}if(String(o).match(/^data\:[\w+\-]+\/[\w+\-]+[,;]/)){return navigator.msSaveBlob?navigator.msSaveBlob(d(o),c):v(o)}h=o instanceof l?o:new l([o],{type:s});if(navigator.msSaveBlob){return navigator.msSaveBlob(h,c)}if(r.URL){v(r.URL.createObjectURL(h),true)}else{if(typeof h==="string"||h.constructor===f){try{return v("data:"+s+";base64,"+r.btoa(h))}catch(m){return v("data:"+s+","+encodeURIComponent(h))}}p=new FileReader;p.onload=function(e){v(this.result)};p.readAsDataURL(h)}return true}

/**
 * SATC Functionality
 *
 */
var satcOnClick = function(ele) {
	function hasClass(element, cls) {
	    return (' ' + element.className + ' ').indexOf(' ' + cls + ' ') > -1;
	}
	var satcEventEle = null;
	while(ele.parentNode) {
		if (hasClass(ele, 'satc-event')) {
			satcEventEle = ele;
			break;
		}
    	ele = ele.parentNode;
	}
	var inputElements = satcEventEle.children[1].children;
	var eventDetails = {};
	for (var key in inputElements) {
		if (inputElements[key].tagName == 'INPUT') {
			eventDetails[inputElements[key].name] = inputElements[key].value;
		}
	}
	console.log(eventDetails['description']);
	// Build vcalendar string ...
	var vEvent = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:alpinesbsolutions.com\nBEGIN:VEVENT\nUID:"+eventDetails['uid']+"\nDTSTAMP;TZID=UTC:"+eventDetails['now']+"Z\nDTSTART;TZID=UTC:"+eventDetails['start_date']+"Z\nSEQUENCE:0\nTRANSP:OPAQUE\nDTEND;TZID=UTC:"+eventDetails['end_date']+"Z\nLOCATION:"+eventDetails['location']+"\nSUMMARY:"+eventDetails['summary']+"\nDESCRIPTION:"+eventDetails['description']+"\nEND:VEVENT\nEND:VCALENDAR";
	console.log(eventDetails['filename']);
	if (typeof eventDetails['filename'] === undefined) {
		filename = 'event.ics';
	} else {
		
		// Force the ics extention
		filename = eventDetails['filename'].split('.ics')[0] + '.ics';
	}
	
	download(vEvent, filename, "text/plain");
};

/**
 * iCal Example
 * 
 * 
BEGIN:VCALENDAR
VERSION:2.0
PRODID:AddToCalendar.com
BEGIN:VEVENT
UID:54d542f88a3e5
DTSTAMP;TZID=UTC:20150206T224056
DTSTART;TZID=UTC:20150213T010000
SEQUENCE:0
TRANSP:OPAQUE
DTEND;TZID=UTC:20150213T020000
LOCATION:Check your inbox for the special viewing link provided from Erika 
 
 Ferenczi.
SUMMARY:Turning Efforts Into Profits Webinar with Erika Ferenczi
DESCRIPTION:See you at \"Turning Efforts Into Profits\"\nPhone: (425) 440-5
 
 100\nAccess Code: 314755#\n\nCome to Learn:\n- What are the best avenue
 s t
 o find more and better clients.\n- The marketing strategies you need
  to im
 plement to fill your bucket of leads and increase your income.\n-
  The simp
 le yet powerful secret that has allowed me to create 5 figure 
 paydays. \n-
  How to get done everything on your to do list.\n- How and 
 what to delegat
 e so you can focus on what matters. \n- And Much More! \
 n\nSee you there!\
 n\n--\nhttp://addtocalendar.com
END:VEVENT
END:VCALENDAR 
 * 
 */