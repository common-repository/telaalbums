$(".telatabs").tabs({ 
  show: { effect: "slide", direction: "left", duration: 200, easing: "easeOutBack" } ,
  hide: { effect: "slide", direction: "right", duration: 200, easing: "easeInQuad" } 
});
$("document").ready(function() {
	
	document.querySelector( ".contents" ).style.visibility = "visible";
})