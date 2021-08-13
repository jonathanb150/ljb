$(".navbar-anchor").hover(function() {
	var width = $(this).outerWidth();
	var height = $(this).outerHeight();
	$(this).find(".dropdown").css("width", width+"px");
	$(this).find(".dropdown").css("top", height+"px");
	if ($(this).parent().attr("class") != null) {
		$(this).find(".dropdown").css("top", "0px");
		$(this).find(".dropdown").css("left", width+"px");
	}
	var prevHeight = $(this).find(".dropdown:eq(0)").css("height");
	$(this).find(".dropdown:eq(0)").css("height", "0px");
	$(this).find(".dropdown:eq(0)").css("opacity", "0");
	$(this).find(".dropdown:eq(0)").animate({ height: prevHeight, opacity: 1},200);
}, function() {});
