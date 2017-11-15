var $ = jQuery.noConflict();
// console.log('01000010 01111001 00100000 01001100 01101111 01101110 01100111 01100010 01100101 01100001 01110010 01100100 00100000 00111010 00111010 00100000 01000011 01110010 01100101 01100001 01110100 01100101 01100100 00100000 01110111 01101001 01110100 01101000 00001010 00100000 00101110 00111010 00111010 00111010 00101110 00100000 00100000 00100000 00101110 00111010 00111010 00111010 00101110 00001010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00101110 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00001010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00001010 00100111 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00100111 00001010 00100000 00100000 00100111 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00111010 00100111 00001010 00100000 00100000 00100000 00100000 00100111 00111010 00111010 00111010 00111010 00111010 00100111 00001010 00100000 00100000 00100000 00100000 00100000 00100000 00100111 00111010 00100111');

/*********************
ANONYMOUS FUNCTIONS
*********************/
$('.nav-header, .menu-close').hover(function(){
	$('.nav-header').css('will-change', 'auto');
}, function(){
	$('.nav-header').css('will-change', 'unset');
});
$('.nav-header').not('.menu-close').click(function(){
	$(this).removeClass('out').addClass('active');
	$('body').addClass('open-header');
});

$('.menu-close').click(function(e){
	$('.nav-header').removeClass('active').addClass('out');
	$('body').removeClass('open-header');
	e.stopPropagation();
});

$('.accordion-header').click(function(){
	console.log('accordion');
	$(this).toggleClass('active');
});

jQuery('body').click(function() {
    closeModal();
});

jQuery('.modal-close').click(function(e) {
    closeModal(e);
});

$(function(){
	videoIcon();
});


/*********************
DECLARED FUNCTIONS
*********************/
function videoIcon() {
	$('.video-preview').click(function(e){
		var arr = $(this).attr('class').split(' ');
  
  		$.each(arr, function(i, v){
  			if (v.match('^ytid-')) {
	    		v = v.replace(/ytid-/g, '');
	    		console.log(v);
    			ytInit(v);
    			$('.video-modal').addClass('active').fadeIn(300);
    			$('body').addClass('modal-active');
    		}
  		});

  		e.stopPropagation();
	});

	$('<div><span class="modal-close">✕</span></div>').hide().addClass('video-modal').append('<div id="player"></div>').appendTo('body');

    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
}

function toggleVideo(state) {
    var div = document.getElementById("popupVid");
    var iframe = div.getElementsByTagName("iframe")[0].contentWindow;
    div.style.display = state == 'hide' ? 'none' : '';
    func = state == 'hide' ? 'pauseVideo' : 'playVideo';
    iframe.postMessage('{"event":"command","func":"' + func + '","args":""}', '*');
}

function closeModal(e) {
    if (jQuery('body').hasClass('modal-active')) {
        jQuery('.video-modal').fadeOut(250);
        jQuery('body').removeClass('modal-active');
        jQuery('#player').remove();
        jQuery('.video-modal').append('<div id="player"></div>');
        e.stopPropagation();
    }
}

/*********************
HELPER METHODS
*********************/
function ytInit(id) {
    var player;
    player = new YT.Player('player', {
        height: '720',
        width: '1080',
        videoId: id,
        playerVars: {
            'color': 'white',
            'playsinline': 0,
            'rel': 0,
            'showinfo': 0,
            'autoplay': 1
        },
        events: {
            'onStateChange': onPlayerStateChange
        }
    });

    function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.ENDED) {
            closeModal();
        }
    }
}
