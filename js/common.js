var minheight = 20;
var maxheight = 100;
var time = 1000;
var timer = null;
var toggled = false;

var doit;

window.onload = function() {

	//resize screen
	window.addEventListener("resize", function (){
		clearTimeout(doit);
		doit = setInterval(function(){
			var windowH = window.innerHeight;
			var pageH = document.getElementById("page").style.height;
			if(pageH <= (windowH-150)) {
				document.getElementById("page").style.height = ( (windowH - 110) + "px");
			}
		}, 1);
	});
	//windowResize();

	addContactInfo();
};

function windowResize() {
	var windowH = window.innerHeight;
	var pageH = document.getElementById("page").style.height;
	if(pageH <= (windowH-150) && window.innerWidth >= 767) {
			document.getElementById("page").style.height = ( (windowH - 110) + "px");
	}

}

function addContactInfo() {
	if(document.getElementById("brit_email"))
		document.getElementById("brit_email").innerHTML = "brittany.kos [at] colorado [dot] edu";
	if(document.getElementById("brit_phone"))
		document.getElementById("brit_phone").innerHTML = "720-270-5003";
}






document.getElementById('nav_expand').onclick = function(){
	if(toggled)
		document.getElementById('navigation').style.display = 'none';
	else
		document.getElementById('navigation').style.display = 'block';
	toggled = !toggled;
};





/* More button ont he publications page.  To read mroe of abstracts */
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
			'height' : '50px'
		})
		$this.html("Read more...")
		$this.attr("meta-truncated", 1)
	}
});