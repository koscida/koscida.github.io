$(document).ready(function() {
	
	//resize screen
	var doit;
	$(window).resize(function () {
		clearTimeout(doit);
		doit = setTimeout(windowResize, 100);
	});
	windowResize();
	
	$("#nav_expand").click(function(){
		$("#navigation").slideToggle();
	});
	
	addContactInfo();
});

function windowResize() {
	//alert("hi");
	var winH = $(window).height();
	var pageH = $("#page").height();
	var pageH2 = $("header").height() + $("#content").height();
	//alert("winH: " + winH + "\npageH: " + pageH + "\npageH2: " + pageH2);
	if(pageH <= (winH-150))
		$("#page").height(winH - 150);
	
}

function addContactInfo() {
	$("#brit_email").html("brittany.kos [at] colorado [dot] edu");
	$("#brit_phone").html("720-270-5003");
}