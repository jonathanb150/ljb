$(document).ready(function() {
	var currentLink = window.location.href;
	if (currentLink.indexOf("roi") >= 0){
		$("#sorting-box a:eq(0)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(0)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	} else if (currentLink.indexOf("risk") >= 0) {
		$("#sorting-box a:eq(1)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(1)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	} else if (currentLink.indexOf("potential") >= 0) {
		$("#sorting-box a:eq(2)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(2)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	} else if (currentLink.indexOf("cap") >= 0) {
		$("#sorting-box a:eq(3)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(3)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	} else if (currentLink.indexOf("change_6m") >= 0) {
		$("#sorting-box a:eq(4)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(4)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	} else if (currentLink.indexOf("change_3y") >= 0) {
		$("#sorting-box a:eq(5)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(5)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	} else if (currentLink.indexOf("change_1y") >= 0) {
		$("#sorting-box a:eq(5)").css("background", "#E3E3E3");
		$("#sorting-box a:eq(5)").css("box-shadow", "inset 0px -4px 0px -2px #ADADAD");
	}
	$(".expand-financials").click(function() {
		//Remove previous ones
		$(this).parent().parent().find(".financial-expand").remove();
		//Add toggle
		$(this).parent().after("<td class='financial-expand' colspan='"+$("table:eq(0) th").length+"' style='display: none;'><table style='width: 500px; font-size: 12px;'><thead><th style='font-size: 12px;'>Time Frame</th><th style='font-size: 12px;'>Profit Change (YoY)</th><th style='font-size: 12px;'>Revenue Change (YoY)</th><th style='font-size: 12px;'>Equity Change (YoY)</th><th style='font-size: 12px;'>EPS Change (YoY)</th><th style='font-size: 12px;'>Cash Change (YoY)</th></thead><tbody><tr><td>3 Months</td><td>"+$(this).attr("profit3m")+"</td><td>"+$(this).attr("revenue3m")+"</td><td>"+$(this).attr("equity3m")+"</td><td>"+$(this).attr("eps3m")+"</td><td>"+$(this).attr("cash3m")+"</td></tr><tr><td>6 Months</td><td>"+$(this).attr("profit6m")+"</td><td>"+$(this).attr("revenue6m")+"</td><td>"+$(this).attr("equity6m")+"</td><td>"+$(this).attr("eps6m")+"</td><td>"+$(this).attr("cash6m")+"</td></tr><tr><td>1 Year</td><td>"+$(this).attr("profit1y")+"</td><td>"+$(this).attr("revenue1y")+"</td><td>"+$(this).attr("equity1y")+"</td><td>"+$(this).attr("eps1y")+"</td><td>"+$(this).attr("cash1y")+"</td></tr><tr><td>3 Years</td><td>"+$(this).attr("profit3y")+"</td><td>"+$(this).attr("revenue3y")+"</td><td>"+$(this).attr("equity3y")+"</td><td>"+$(this).attr("eps3y")+"</td><td>"+$(this).attr("cash3y")+"</td></tr></tbody></table></td>");
		$(this).parent().next("td").toggle(300);
	});
	//Expand table content
	$("table td").hover(function() {
		$(this).css("white-space", "normal");
	}, function() {
		$(this).css("white-space", "nowrap");
	});
});
