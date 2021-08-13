$(document).ready(function() {
	$(".info").hover(function() {
		$(this).next("div").remove();
		if ($(this).attr("value") != null && $(this).attr("value").length > 0) {
			$(this).parent().parent().parent().parent().css("z-index", "1");
			$(this).after("<div style='position: absolute; background: #5d5d5d; width: 300px; color: white; font-size: 12px; box-shadow: 0 0 5px 0 #BDBDBD; padding: 10px; opacity: 0;'>"+($(this).attr("value"))+"</div>");
			$(this).next("div").animate({opacity: '1'}, "fast");
		}
	}, function() {
		$(this).next("div").remove();
		$(this).parent().parent().parent().parent().css("z-index", "0");
	}); 
});
