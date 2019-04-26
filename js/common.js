var minheight = 20;
var maxheight = 100;
var time = 1000;
var timer = null;

var doit;

window.onload = function() {

	addContactInfo();
};

function addContactInfo() {
	if(document.getElementById("brit_email"))
		document.getElementById("brit_email").innerHTML = "brittany.kos [at] colorado [dot] edu";
	if(document.getElementById("brit_phone"))
		document.getElementById("brit_phone").innerHTML = "720-270-5003";
}





/*
 * Navigation
 * Opens and closes the navigation when the hamburger button gets pressed.
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




/* More button on the publications page.  To read more of abstracts */
$('.readmore').click(function(e) {
    e.stopPropagation();
	$this = $(this)
	if($this.attr("meta-truncated") == 1) {
		$this.prev().css({
			'height' : 'auto'
		})
		$this.html("Read less...")
		$this.attr("meta-truncated", 0)
	} else {
		$this.prev().css({
			'height' : '81px'
		})
		$this.html("Read more...")
		$this.attr("meta-truncated", 1)
	}
});