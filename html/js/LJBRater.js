function LJBRater (boxTitles, container) {
	let rateBoxTitles = boxTitles;
	for (let i = 0; i < rateBoxTitles.length; i++) {
		$(container).prepend('<div class="rate_container" box-header="'+rateBoxTitles[i]+'" style="margin: 15px;"><div class="rate_text">'+rateBoxTitles[i]+'</div><div class="rate_choices"><div class="rate_positive"><i class="fas fa-thumbs-up" is-pressed="0"></i></div><div class="rate_negative"><i class="fas fa-thumbs-down"></i></div></div></div>');
		$(container + " .rate_container[box-header='"+rateBoxTitles[i]+"'] i").click(function() {
			if ($(this).hasClass("fa-thumbs-up")) {
				$(this).attr("is-pressed", "1");
			} else {
				$(this).parent().prev("div").find("i").attr("is-pressed", "0");
			}
		});
	}
	$(container + " .rate_container i").each(function() {
		$(this).click(function() {
			$(this).parent().parent().children("div").removeClass("rate_selected");
			$(this).parent().addClass("rate_selected");
		});
	});
	this.checkRating = function(x) {
		if (typeof x === 'string' || x instanceof String) {
			return $(container + " .rate_container[box-header='"+x+"'] i:eq(0)").attr("is-pressed");
		}
		return 0;
	};
	this.modifyRating = function(x, y) {
		$(container + " .rate_container[box-header='"+x+"'] i:eq(0)").attr("is-pressed", y);
		$(container + " .rate_container[box-header='"+x+"'] i:eq("+(y == 1 ? 0 : 1)+")").parent().addClass("rate_selected");
	}
}

function LJBStarRater (boxTitles, container) {
	let rateBoxTitles = boxTitles;
	let rateStatus = [0, 0, 0];
	for (let i = 0; i < rateBoxTitles.length; i++) {
		$(container).prepend('<div class="rate_container" current-rating="0" box-header="'+rateBoxTitles[i]+'" style="margin: 15px;"><div class="rate_text">'+rateBoxTitles[i]+'</div><div class="rate_choices star"><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i></div></div>');
	}
	$(container + " .rate_container i").click(function() {
		$(this).parent().find("i").removeClass("fas");
		$(this).parent().find("i").removeClass("rate_selected");
		$(this).parent().find("i").addClass("far");
		var rateCounter = 0;
		for (var i = 0; i <= $(this).index(); i++) {
			$(this).parent().find("i:eq("+i+")").removeClass('far');
			$(this).parent().find("i:eq("+i+")").addClass('fas');
			$(this).parent().find("i:eq("+i+")").addClass("rate_selected");
			rateCounter++;
		}
		$(this).parent().parent().attr('current-rating', rateCounter);
	});

	this.checkRating = function(x) {
		if (typeof x === 'string' || x instanceof String) {
			return $(container + " .rate_container[box-header='"+x+"']").attr("current-rating");
		}
		return 1;
	};
	this.modifyRating = function(x, y) {
		$(container + " .rate_container[box-header='"+x+"'] i:eq("+(y-1)+")").trigger('click');
	}
}