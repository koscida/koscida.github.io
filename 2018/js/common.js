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
 * Navigation for mobile. Opens and closes the navigation when the hamburger button gets pressed.
 */
var toggled = false;

document.getElementById('navigationIcon').onclick = function(){
	var $this = document.getElementById('navigationIcon');
	if(toggled) {
		$this.classList.add("fa-bars");
		$this.classList.remove("fa-close");
		document.getElementById('navigationLarge').style.display = 'none';
	} else {
		$this.classList.add("fa-close");
		$this.classList.remove("fa-bars");
		document.getElementById('navigationLarge').style.display = 'block';
	}
	toggled = !toggled;
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