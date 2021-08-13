//Initialize all tooltips
$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})

// Web Analytics
$("a[href='#daily_chart_pill']").on('shown.bs.tab', function (e) {
	daily_chart.render();
	daily_chart.render();
});

$("a[href='#weekly_chart_pill']").on('shown.bs.tab', function (e) {
	weekly_chart.render();
	weekly_chart.render();
});

$(".web_analytics_grid .col ul").each(function () {
	if ($(this).find("li").length > $(this).attr("max-elements")) {
		$(this).append("<span onclick='prevWebStats(this, " + $(this).attr('max-elements') + ")' style='user-select: none; display: inline-block; width: 20%; font-weight: bold; color: rgba(255, 255, 255, 0.25); letter-spacing: -3px; cursor: auto'><<</span><span onclick='nextWebStats(this, " + $(this).attr('max-elements') + ")' style='user-select: none; display: inline-block; width: 20%; font-weight: bold; color: white; letter-spacing: -3px; cursor: pointer'>>></span>");
		$(this).find("li").slice($(this).attr("max-elements")).hide();
	}
});

function prevWebStats(element, max_elements) {
	if ($(element).css('cursor') == 'pointer') {
		for (let i = 0; i < $(element).parent().find("li").length; i++) {
			if ($(element).parent().find("li:eq(" + i + ")").is(':visible')) {
				$(element).parent().find("li").slice(i).hide();
				$(element).parent().find("li").slice(((i - max_elements)), i).show();

				if (i == max_elements) {
					$(element).css('cursor', 'auto');
					$(element).css('color', 'rgba(255, 255, 255, 0.25)');
				}

				if ($(element).next('span').css('cursor') == 'auto') {
					$(element).next('span').css('cursor', 'pointer');
					$(element).next('span').css('color', 'white');
				}

				break;
			}
		}
	}
}

function nextWebStats(element, max_elements) {
	if ($(element).css('cursor') == 'pointer') {
		for (let i = $(element).parent().find("li").length - 1; i >= 0; i--) {
			if ($(element).parent().find("li:eq(" + i + ")").is(':visible')) {
				$(element).parent().find("li").slice(0, (i + 1)).hide();
				$(element).parent().find("li").slice((i + 1), ((i + max_elements + 1))).show();

				if ($(element).parent().find("li").length <= (i + max_elements)) {
					$(element).css('cursor', 'auto');
					$(element).css('color', 'rgba(255, 255, 255, 0.25)');
				}

				if ($(element).prev('span').css('cursor') == 'auto') {
					$(element).prev('span').css('cursor', 'pointer');
					$(element).prev('span').css('color', 'white');
				}

				break;
			}
		}
	}
}

$(".del-analytics-btn").click(function () {
	$("#del-analytics-modal").modal("show");
});

$("#del-analytics-modal .btn-primary").click(function () {
	$.post(window.location.pathname, { del_analytics: true }, function (res) {
		location.reload();
	});
});

// Logs
$("#delete_logs .btn-primary").click(function () {
	$.post(window.location.pathname, { delete_logs: true }, function () {
		location.reload();
	});
});

$("#logs-tabs .nav-link").click(function () {
	$(this).parent().find("button").css("opacity", "0.5");
	$(this).find("button").css("opacity", "1");
});

$(".del-log").click(function () {
	var log = $(this).attr("log-name");

	$("#delete_log").modal("show");
	$("#delete_log .btn-primary").off();

	$("#delete_log .btn-primary").click(function () {
		$.post(window.location.pathname, { delete_log: true, log_name: log }, function () {
			location.reload();
		});
	});
});
