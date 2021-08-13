$(document).ready(function() {
	//Tab System 
	function isVisible($element) {
		if ($element.css("display") == "none") {
			return false;
		}
		return true;
	}
	function selectedTab($element) {
		$element.parent().find("h1").css("color", "#7C7C7C");
		$element.parent().find("h1").css("box-shadow", "inset 0px -5px 0px -2px #ADADAD");
		$element.parent().find("h1").removeClass("selected-tab");
		$element.css("color", "#636363");
		$element.css("box-shadow", "inset 0px -5px 0px -2px #7F7F7F");
		$element.addClass("selected-tab");
	}
	function changeTab($element) {
		$element.parent().find("table").not($element).animate({
			opacity: 0
		}, 250, function() {
			$element.parent().find("table").not($element).hide();
			$element.css("opacity", "0");
			$element.css("display", "table");
			$element.animate({
				opacity: 1
			}, 250);
		});	
	}
	$("#home h1").hover(function() {
		if (!$(this).hasClass("selected-tab")) {
			$(this).css("color", "#636363");
			$(this).css("box-shadow", "inset 0px -5px 0px -2px #7F7F7F");
		}
	}, function() {
		if (!$(this).hasClass("selected-tab")) {
			$(this).css("color", "#7C7C7C");
			$(this).css("box-shadow", "inset 0px -5px 0px -2px #ADADAD");
		}
	})
	$("#home h1").click(function() {
		changeTab($("#"+$(this).attr("tab")));
		selectedTab($("#"+$(this).attr("tab")+"-tab"));
	});

	//Expand table content
	$("table td").hover(function() {
		$(this).css("white-space", "normal");
	}, function() {
		$(this).css("white-space", "nowrap");
	});

	$(".edit-comment").click(function() {
		$("#edit-comment-textarea").val(null);
		var item = $(this).attr("value").replace("_1d", "");
		$("#edit-comment-container h2").remove();
		$("#edit-comment-container").prepend("<h2>Edit comment for "+item+"</h2>")
		$("#edit-comment-container").show();
		$("#edit-comment-submit").unbind("click");
		$("#edit-comment-submit").click(function() {
			$.post("/php_dependancies/edit_watchlist_comment.php", {symbol: item, comment: $("#edit-comment-textarea").val()}, function(result) {
				if (result.length > 0) {
					location.reload();
				}
			});
		});
	});

	$(".home-tables-content div:eq(4)").click(function() {
		var item = $(this).attr("value").replace("_1d", "");
		window.location.assign("analyze_stocks.php?item="+item);
	});
});