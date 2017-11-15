var $ = jQuery.noConflict();

var file = $('#main_css-css'),
	filePath = file.attr('href'),
	loadCount = 0;

$(function() {
	var cron = 2 * 1000; //10 seconds

	if (file.length) {
		window.setInterval(function(){
			console.log("Fetching");
			checkLastModified(filePath);
		}, cron);
	}
});

function checkLastModified(path) {
	var res; //globalize response

	$.ajax({
        type: 'HEAD', //only get request header
        url: path, //set path dynamically
        complete: function(xhr) {
        	res = xhr.getResponseHeader('Last-Modified');
        	cookStyle(res);
        }
});
}

function cookStyle(res) {
	if (readCookie('pf_css') == null) { //if no cookie for file
		createCookie('pf_css', res, 0.5); //write cookie with expiry of half a day
		refreshStyle(); //refresh CSS
	} else {
		var currTS = readCookie('pf_css'); //read cookie

		if (currTS !== res) { //check if res == cookieTime
			console.log('res: ' + res + 'cookie: ' + currTS);
			createCookie('pf_css', res, 0.5);
			refreshStyle(); //if no match, refresh stylesheet
		} else {
			return; //otherwise return
		}
	}
}

function refreshStyle() {
	console.log('Found newer CSS and refreshed');
	file.attr('href', filePath + '?loadCount=' + loadCount);
	loadCount = loadCount + 1;
}


//LIB
function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name,"",-1);
}