var minheight = 20;
var maxheight = 100;
var time = 1000;
var timer = null;

var doit;

window.onload = function() {

	addContactInfo();

	groupToggle();

	positionTimeline();
	
	showHideGroup();
};




/*
 * Navigation
 * - Opens and closes the navigation when the hamburger button gets pressed.
 */
var $btn = document.getElementById('navButtonContainer'),
    $nav = document.getElementById('navigation');

$btn.onclick = function(){
    if ($nav.classList.contains('open')) {
		$nav.classList.add('close');
		$nav.classList.remove('open');
    } else {
		$nav.classList.remove('close');
		$nav.classList.add('open');
    }
};



/*
 * Add Contact Info
 * - 
 */
function addContactInfo() {
	if(document.getElementById("brit_email"))
		document.getElementById("brit_email").innerHTML = "brittany.kos [at] colorado [dot] edu";
	if(document.getElementById("brit_phone"))
		document.getElementById("brit_phone").innerHTML = "720-270-5003";
}



/*
 * Groups
 * - Toggle for groups
 * - Defaults to close, if screen is large, opens toggles
 */
function groupToggle() {
	// toggle open if screen is large enough
	// from: https://stackoverflow.com/questions/3514784/what-is-the-best-way-to-detect-a-mobile-device
	if( !(/Android|webOS|iPhone|iPad|iPod|pocket|psp|kindle|avantgo|blazer|midori|Tablet|Palm|maemo|plucker|phone|BlackBerry|symbian|IEMobile|mobile|ZuneWP7|Windows Phone|Opera Mini/i.test(navigator.userAgent)) || (window.innerWidth > 768) ) {
		var $groups = document.getElementsByClassName('group');
		for (var i=0; i<$groups.length; i++) {
			$groups[i].classList.remove('close');
	  	   	$groups[i].classList.add('open');
		}
	}

	// Set up tap/click listeners
	var $groupsheader = document.getElementsByClassName('group-header');
	for (var i=0; i<$groupsheader.length; i++) {
		$groupsheader[i].addEventListener("touchend", groupToggleTapOrClick, false);
		$groupsheader[i].addEventListener("mouseup", groupToggleTapOrClick, false);
	}
}

function groupToggleTapOrClick(event) {
	event.preventDefault();
	$parent = this.parentElement;
    if ($parent.classList.contains('close')) {
 		$parent.classList.remove('close');
 	  	$parent.classList.add('open');
    } else {
		$parent.classList.remove('open');
 		$parent.classList.add('close');
    }
	return false;
}



/*
 * Timeline
 * - positions timeline items
 */
function positionTimeline() {
	var grid = $("#timeline-grid");
	var gridSize = Number(grid.attr("data-size"));
	var gridHeightpx = grid.height();
	var labelWidthpx = $(".timeline-label").width();
	var graphWidthpx = grid.width()-labelWidthpx-20;
	var unitWidthpx = graphWidthpx/gridSize;

	$(".timeline-item").each(function() {
		var itemStart = $(this).attr("data-start");
		var itemLen = $(this).attr("data-len");
		var itemLeftpx = ((itemStart-1)*unitWidthpx) - 2;
		var itemWidthpx = ((itemLen*unitWidthpx)-4);
		if (itemLen == 1) { itemLeftpx -= 1; itemWidthpx += 2; }

		var itemWidthpct = (itemWidthpx / graphWidthpx) * 100;
		var itemLeftpct = (itemLeftpx / graphWidthpx) * 100;

		// first, move the items left, not animated
		$(this).css({"margin-left": itemLeftpct + '%', overflow: "visible"});

		// expand the width
		$(this).animate({width: itemWidthpct + "%"},750);

		// drag the timeline markers down
		if ($(this).parent().parent().hasClass("timeline-header")) {
			$(this).animate({height: gridHeightpx + 'px'},750);
		}
	});

}




/*
 * Show Hide Group
 * - button that shows/hides more details
 */
function showHideGroup() {
	var $sh_btns = document.getElementsByClassName('sh-btn');
	for (var i=0; i<$sh_btns.length; i++) {
		$sh_btns[i].onclick = function() {
			$parent = this.parentElement;
		    if ($parent.classList.contains('sh-open')) {
				$parent.classList.add('sh-close');
				$parent.classList.remove('sh-open');
		    } else {
				$parent.classList.remove('sh-close');
				$parent.classList.add('sh-open');
		    }
		};
	}
}
