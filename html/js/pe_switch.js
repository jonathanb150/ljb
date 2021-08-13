$(document).ready(function() {
	$(".multi-pe").click(function() {
		for (var i = 0; i < $(".multi-pe").length; i++) {
			var current_pe = parseFloat($(".multi-pe:eq("+i+") span").html()).toFixed(3);
			var new_pe = parseFloat($(".multi-pe:eq("+i+")").attr("pe2")).toFixed(3);
			$(".multi-pe:eq("+i+") span").html(new_pe);
			$(".multi-pe:eq("+i+")").attr("pe2", current_pe);
		}	
		var current_pe = $(".multi-pe-header span").html()
		var new_pe = $(".multi-pe-header").attr("pe2");
		$(".multi-pe-header span").html(new_pe);
		$(".multi-pe-header").attr("pe2", current_pe);
	});
});