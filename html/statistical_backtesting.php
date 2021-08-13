<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<form class="analyze" onsubmit="return false;" id="step_1">
	<h1><i class="fas fa-search"></i>Search Item</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<div class='linearized_search_type' type='stock'>Stocks</div>
	<div class='linearized_search_type' type='index'>Indexes</div>
	<div class='linearized_search_type' type='currency'>Currencies</div>
	<div class='linearized_search_type' type='commodity'>Commodities</div>
	<div class='linearized_search_type' type='bond'>Bonds</div>
	<div class='linearized_search_type' type='us_fundamental'>Fundamentals</div>
	<div></div>
	<div id="search_results"></div>
	<h1><i class="fas fa-table"></i>Select Start Date</h1>
	<div class='date_container'>
		<input type="date" name="start_date" <?php if (isset($_POST["start_date"]) && strlen($_POST["start_date"]) > 0) {echo "value='{$_POST["start_date"]}'";} else { echo "value='".date("Y-m-d", time() - 31557600*10)."'";} ?>>
	</div>
	<button class="button" id="statistical_backtesting">Continue</button>
</form>
<img src="/img/ajax-loader-3.svg" id="ajax_loader" style="margin: 60px auto; display: none;">
<div id="backtesting" style="display: none; width: 95%; margin: 0 auto;">
	<h1 style="text-align: center; font-size: 45px; font-weight: 300"></h1>
	<div id="general_info" style="margin: 20px auto; display: inline-block; vertical-align: top; width: 100%;     border-radius: 4px; border: 1px solid rgba(49,60,71,.11); box-shadow: 1px 3px 0 rgba(49,60,71,.07); background: #FBFCFC;">
		<h1 style="padding: 20px 0; font-weight: 300; border-bottom: 1px solid rgba(49,60,71,.11)">Information for the past 3 years</h1>
		<img src="img/ajax-loader-3.svg" style="margin: 20px auto">
	</div>
	<div id="custom_market" style="display: none; margin: 20px auto; vertical-align: top; width: 100%;     border-radius: 4px; border: 1px solid rgba(49,60,71,.11); box-shadow: 1px 3px 0 rgba(49,60,71,.07); background: #FBFCFC;">
		<h1 style="padding: 20px 0; font-weight: 300; border-bottom: 1px solid rgba(49,60,71,.11)">Select custom market</h1>
		<div id="select_dates">
			<div id="select_market" style="display: inline-block; width: 40%; vertical-align: top">
				<h1 style="text-align: center; margin-bottom: 20px; font-weight: 300; font-size: 30px">Select Market</h1>
				<div>
					<div style="display: inline-block; width: 32.5%">
						<label style="display: block; text-align: center; margin: 0 auto 10px auto">Start Date</label>
						<input type="date" style="display: block; margin: 0 auto; border: 1px solid #d8d8d8;padding: 5px 0;">
					</div>
					<div style="display: inline-block; width: 32.5%">
						<label style="display: block; text-align: center; margin: 0 auto 10px auto">End Date</label>
						<input type="date" style="display: block; margin: 0 auto; border: 1px solid #d8d8d8;padding: 5px 0;">
					</div>
					<button id="select_custom_market" class="button" style="margin-top: 30px; margin-bottom: 30px">Select</button>
				</div>
			</div>
		</div>
	</div>
	<div style="text-align: center; margin: 30px auto; display: none;" id="main">	
		<div style="display: inline-block; vertical-align: top; width: 65%; margin-right: 2.5%">
			<div id="backtesting_window" style="border-radius: 4px; border: 1px solid rgba(49,60,71,.11); box-shadow: 1px 3px 0 rgba(49,60,71,.07); background: #FBFCFC; width: 100%;">
				<p style="background: rgba(49,60,71,.11); padding: 10px 0;"><label style="display: block; font-weight: 300;">Current Date</label><span style="font-weight: 500; font-size: 18px"></span></p>
				<div>
					<div>
						<h2>Go Forward</h2>
						<button class="button add_time" days="1">1 Day</button>
						<button class="button add_time" days="7">7 Days</button>
						<button class="button add_time" days="15">15 Days</button>
						<button class="button add_time" days="30">1 Month</button>
						<button class="button add_time" days="90">3 Months</button>
						<button class="button add_time" days="180">6 Months</button>
						<button class="button add_time" days="365">1 Year</button>
					</div>
					<div>
						<h2>Go Back</h2>
						<button class="button add_time" days="-1">1 Day</button>
						<button class="button add_time" days="-7">7 Days</button>
						<button class="button add_time" days="-15">15 Days</button>
						<button class="button add_time" days="-30">1 Month</button>
						<button class="button add_time" days="-90">3 Months</button>
						<button class="button add_time" days="-180">6 Months</button>
						<button class="button add_time" days="-365">1 Year</button>
					</div>
					<button class="button" id="buy" style="margin: 20px auto; width: 200px;">Buy</button>
				</div>
				<img src="img/ajax-loader-3.svg" style='margin: 158px auto;'>
			</div>
			<div id="item_price_chart" style="width: 100%; margin: 20px auto; height: 500px"></div>
		</div>
		<div id="transactions_window" style="display: inline-block; vertical-align: top; width: 30%;     border-radius: 4px; border: 1px solid rgba(49,60,71,.11); box-shadow: 1px 3px 0 rgba(49,60,71,.07); background: #FBFCFC;">
			<h1 style="padding: 20px 0; font-weight: 300; border-bottom: 1px solid rgba(49,60,71,.11)">Transactions</h1>
			<div id="transactions_list" style="margin: 30px auto">
				<span style="font-style: italic">No transactions yet...</span>
			</div>
			<div style="border-top: 1px solid rgba(49,60,71,.11)">
				<div style="display: inline-block; width: 45%; box-shadow: 1px 0px 0px 0px rgba(49,60,71,.11); text-align: left; padding: 15px 0 15px 5%">
					<div>Profit/Loss %: <span id="p_l_percentage">0.00</span><b>%</b></div>
					<div>Profit/Loss $: <b>$</b><span id="p_l_dollar">0.00</span></div>
				</div
				><div style="display: inline-block; width: 45%; text-align: left; padding: 15px 0 15px 5%">
					<div>Open positions: <span id="o_positions">0</div>
					<div>Closed positions: <span id="c_positions">0</div>
				</div>
			</div>
			<div style="border-top: 1px solid rgba(49,60,71,.11)">
				<div style="display: inline-block; width: 45%; box-shadow: 1px 0px 0px 0px rgba(49,60,71,.11); text-align: left; padding: 15px 0 15px 5%">
					<div>Total Capital: <b>$</b><span id="total_capital">1000.00</span></div>
				</div
				><div style="display: inline-block; width: 45%; text-align: left; padding: 15px 0 15px 5%">
					<div>Invested Capital: <b>$</b><span id="invested_capital">0.00</span></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="/js/item_search.js"></script>
<script type="text/javascript">
	itemSearch("statistical_backtesting", $("input[name='item_search']"), $("#search_results"));
	$(".linearized_search_type").click(function() {
		$(".linearized_search_type").removeClass("linearized_search_selected");
		$(this).addClass("linearized_search_selected");
		items = null;
		itemSearch($(this).attr("type"), $("input[name='item_search']"), $("#search_results"));
		$("#search_results").hide();
		$("#search_results").html("");
	});
</script>
<script type="text/javascript">
//GENERAL VARIABLES
var fetch_graphs = 0;
var script_running = false;
var bull_finish = false;
var bear_finish = false;
var general_finish = false;

//USER TRANSACTION VARIABLES
var total_capital = $("#total_capital").html();
var invested_capital = $("#invested_capital").html();
var p_l_percentage = $("#p_l_percentage").html();
var p_l_dollar = $("#p_l_dollar").html();
var o_positions = $("#o_positions").html();
var c_positions = $("#c_positions").html();
var current_date;
var current_price;
var modal;
var item_data;

//STEP 1: SELECT ITEM AND START DATE
$("#statistical_backtesting").click(function() {
	if($("input[name='selected_item']").length == 1 && $("input[name='start_date']").val().length > 0) {
		var step_1_query = "/algorithms/Others/backtestingGraph.py '"+$("input[name='selected_item']").val()+"' '"+$("input[name='start_date']").val()+"'";

		$("#step_1").fadeOut(250, function() {
			$("#ajax_loader").fadeIn(250);
		});
		$.post("/php_dependancies/analysis_scripts.php", {query: step_1_query}, function(data){
			$.post("/php_dependancies/get_item_data.php", {item: $("input[name='selected_item']").val(), start_date: -1, end_date: -1}, function(data){
				try {
					item_data = $.parseJSON(data);
				}
				catch(err) {}
				$.get("/graphs/backtestingGraph.html", function(graph){
					end_date = new Date($("input[name='start_date']").val());
					end_date.setDate(end_date.getDate()-(365*5));
					end_date = end_date.toISOString().slice(0,10);
					/*$.get("/php_dependancies/analysis.php", {item: $("input[name='selected_item']").val(), start_date: end_date, end_date: $("input[name='start_date']").val(), find_markets: true}, function(suggestions) {
						var suggestions = $.parseJSON(suggestions);
						if (suggestions['bull_markets'] != null && suggestions['bear_markets'] != null) {
							var bear = -1;
							var bull = -1;
							var last_bear = 0;
							var last_bull = 0;
							for (var i = 0; i < suggestions['bear_markets'].length; i++) {
								var start = new Date(suggestions['bear_markets'][i][0]);
								var end = new Date(suggestions['bear_markets'][i][1]);
								if (end - start > last_bear) {
									bear = i;
									last_bear = end-start;
								}
							}
							for (var i = 0; i < suggestions['bull_markets'].length; i++) {
								var start = new Date(suggestions['bull_markets'][i][0]);
								var end = new Date(suggestions['bull_markets'][i][1]);
								if (end - start > last_bull) {
									bull = i;
									last_bull = end-start;
								}
							}

							if (bull != -1) {
								$("#bull_market_selection").parent().parent().append("<ul style='margin: 20px; background: #f5f5f5; display: inline-block'><li style='list-style-type: none; padding: 10px 10px 0 10px;'>Start Date: <span>"+suggestions['bull_markets'][bull][0]+"</span></li><li style='list-style-type: none; padding: 0 10px 10px 10px;'>End Date: <span>"+suggestions['bull_markets'][bull][1]+"</span></li><li style='list-style-type: none; font-weight: 700; background: #ff9393; color: white; cursor: pointer; padding: 10px' onclick='$(this).parent().remove();'>Delete</li></ul>");
							}
							if (bear != -1) {
								$("#bear_market_selection").parent().parent().append("<ul style='margin: 20px; background: #f5f5f5; display: inline-block'><li style='list-style-type: none; padding: 10px 10px 0 10px;'>Start Date: <span>"+suggestions['bear_markets'][bear][0]+"</span></li><li style='list-style-type: none; padding: 0 10px 10px 10px;'>End Date: <span>"+suggestions['bear_markets'][bear][1]+"</span></li><li style='list-style-type: none; font-weight: 700; background: #ff9393; color: white; cursor: pointer; padding: 10px' onclick='$(this).parent().remove();'>Delete</li></ul>");
							}
						}
					});*/
					$("#ajax_loader").hide();

					var general = "/algorithms/Others/statistical_analysis_prebacktesting.py '"+$("input[name='selected_item']").val()+"' '"+$("input[name='start_date']").val()+"'";

					$("#backtesting > h1:eq(0)").html("Backtesting for "+$("input[name='selected_item']").val());
					$("#backtesting").fadeIn(250);
					var pre_backtesting_interval = setInterval(function() {
						if(fetch_graphs == 0) {
							preBacktestingInfo(general, "#general_info");
						}
						else if(fetch_graphs == 3) {
							script_running = false;

							$("#custom_market").show();
							$("#custom_market > h1:eq(0)").after(graph);
							
							$("#main").fadeIn(250, function() {
								$("#backtesting_window > p span").html($("input[name='start_date']").val());
								current_date = $("input[name='start_date']").val();
								var backtesting_query = "/algorithms/Others/statistical_backtesting.py '"+$("input[name='selected_item']").val()+"' '"+current_date+"'";
								startBacktesting(backtesting_query);
								$('html, body').animate({
								    scrollTop: ($('#backtesting_window').offset().top)
								},0);
							});
							clearInterval(pre_backtesting_interval);
						}
					}, 100);
				});
			});
		});

		//STEP 2: SELECT BEAR AND BULL MARKETS
		$("#select_custom_market").click(function() {
			var	start_date = $(this).prev("div").prev("div").find("input").val();
			var end_date = $(this).prev("div").find("input").val();

			if(start_date != null && end_date != null && start_date.length > 0 && end_date.length > 0) {
				var market = "/algorithms/Others/statistical_analysis_prebacktesting.py '"+$("input[name='selected_item']").val()+"' '"+end_date+"' '"+start_date+"'";
				$("#custom_market table").remove();
				$("#custom_market div[backtesting_info]").remove();
				$(this).after("<img src='/img/ajax-loader-3.svg' style='display: block; margin: 30px auto;' id='custom_market_loader'>");
				$(this).hide();
				preBacktestingInfo(market, "#custom_market");
			}
			else {
				alert("Please select a valid date range.");
			}
		});

		//GO FORWARD IN TIME
		$("#backtesting_window .add_time").click(function() {
			current_date = new Date($("#backtesting_window > p span").html());
			var days = parseInt($(this).attr("days"));
			current_date.setDate(current_date.getDate()+days);
			current_date = current_date.toISOString().slice(0,10);

			$("#backtesting_window > p span").html(current_date);
			$("#backtesting_window img").show();
			$("#backtesting_window > div").hide();

			var backtesting_query = "/algorithms/Others/statistical_backtesting.py '"+$("input[name='selected_item']").val()+"' '"+current_date+"'";

			startBacktesting(backtesting_query);
		});
	}
	else if($("input[name='start_date']").val().length == 0) {
		alert("Please select a starting date.");
	}
	else {
		alert("Please select an item.");
	}
});

//FUNCTIONS
function plotItemPrices() {
	$("#item_price_chart").html("");

	var filter_end = new Date(current_date);
	filter_end = filter_end.getTime();

	var filtered_data = filterPriceData(item_data, (filter_end-(94608000*1000)), filter_end);

	var item_price_chart = [
	{
		x: filtered_data['dates'],
		y: filtered_data['values'],
		type: 'scatter',
		mode: 'lines'
	}
	];

	var layout = {
		title: $("input[name='selected_item']").val()+' Price History'
	};

	Plotly.newPlot('item_price_chart', item_price_chart, layout);
}
function filterPriceData(data, start_date, end_date) {
	var filtered_data = {"dates": [], "values": []};
	var index_start = 0;
	var index_end = data['dates'].length-1;

	for (var i = 0; i < data['dates'].length; i++) {
		var unix = new Date(data['dates'][i]);
		unix = unix.getTime();
		
		if(unix-start_date >= 0) {
			if(unix-start_date > 0) {
				index_start = i-1;
			}
			else{
				index_start = i;
			}
			break;
		}	
	}
	for (var i = 0; i < data['dates'].length; i++) {
		var unix = new Date(data['dates'][i]);
		unix = unix.getTime();
		
		if(unix-end_date >= 0) {
			if(unix-end_date > 0) {
				index_end = i-1;
			}
			else{
				index_end = i;
			}
			break;
		}	
	}
	for (var i = index_start; i <= index_end; i++) {
		filtered_data['dates'].push(data['dates'][i]);
		filtered_data['values'].push(data['values'][i]);
	}

	return filtered_data;
}
function selectData(element) {
	$(element).parent().find("button").removeAttr("selected_data");
	$(element).attr("selected_data", "");
	$("#main").fadeOut(250, function() {
		$("#main").before("<img id='change_data_loader' style='margin: 30px auto' src='/img/ajax-loader-3.svg'>");
	});
}

function updateSessionValues() {
	$("#total_capital").html(parseFloat(total_capital).toFixed(2));
	$("#invested_capital").html(parseFloat(invested_capital).toFixed(2));
	$("#p_l_percentage").html(parseFloat(p_l_percentage).toFixed(2));
	$("#p_l_dollar").html(parseFloat(p_l_dollar).toFixed(2));
	$("#o_positions").html(o_positions);
	$("#c_positions").html(c_positions);
}

function preBacktestingInfo(script, append) {
	if(!script_running) {
		script_running = true;
		$.post("/php_dependancies/analysis_scripts.php", {query: script}, function(data){
			try {
				data = $.parseJSON(data);
			} catch(err) {
				console.log(data);
				console.log(err);
				alert("Invalid date range selected.");
				//script_running = false;
			}

			if(data['table'] != null) {
				var graphs_order = 0;
				$(append).append(arrayToTableNoSort(data['table']));
				$.get("/graphs/pre_backtestingGraph_boxplot.html", function(graph){
					var interval = setInterval(function() {
						if(graphs_order == 0) {
							$(append).append("<div backtesting_info style='display: inline-block; width: 95%; margin-bottom: 20px;'>"+graph+"</div>");
							fetch_graphs++;
							graphs_order++;
							clearInterval(interval);
						}
					}, 100);
				});
				$.get("/graphs/pre_backtestingGraph_scatter.html", function(graph){
					var interval = setInterval(function() {
						if(graphs_order == 2) {
							$(append).append("<div backtesting_info style='display: inline-block; width: 95%;'>"+graph+"</div>");
							fetch_graphs++;
							graphs_order++;
							$(append).children("img").remove();
							$("#select_custom_market").show();
							$("#custom_market_loader").remove();
							script_running = false;
							clearInterval(interval);
						}
					}, 100);
				});
				$.get("/graphs/pre_backtestingGraph_days.html", function(graph){
					var interval = setInterval(function() {
						if(graphs_order == 1) {
							$(append).append("<div backtesting_info style='display: inline-block; width: 95%; margin-bottom: 20px;'>"+graph+"</div>");
							fetch_graphs++;
							graphs_order++;
							clearInterval(interval);
						}
					}, 100);
				});
			}
		});
	}
}

$("#buy").click(function() {
	modal = new jBox('Modal', {
		content: '<p style="text-align: center; font-size: 18px;">Current Price: <b>$</b>'+current_price+'</p><p style="text-align: center; font-size: 18px;">Available Cash: <b>$</b>'+(total_capital-invested_capital)+'</p><input style="font-size: 20px; border: 1px solid #cacaca; padding: 5px; margin: 20px auto; font-weight: 300" type="text" placeholder="How much..."><button onclick="buyItem($(this).parent().find(\'input\').val());" class="button">Submit</button>'
	});

	modal.open();
});

function sellItem(index) {
	var bought_price = parseFloat($("#transactions_list > div:eq("+index+") div[sell_item]").attr("bought_price"));
	var bought_date = $("#transactions_list > div:eq("+index+") div[sell_item]").attr("bought_date");
	var bought_quantity = parseFloat($("#transactions_list > div:eq("+index+") div[sell_item]").attr("invested_capital"));
	var roi = ((parseFloat(current_price)*bought_quantity)/bought_price);

	o_positions = parseInt(o_positions)-1;
	c_positions = parseInt(c_positions)+1;
	invested_capital = parseFloat(invested_capital)-bought_quantity;
	total_capital = parseFloat(total_capital)-bought_quantity+roi;
	p_l_dollar = total_capital-1000;
	p_l_percentage = (total_capital*100/1000)-100;
	updateSessionValues();

	$("#transactions_list > div:eq("+index+") div[sell_item]").removeAttr("onclick");
	$("#transactions_list > div:eq("+index+") div[sell_item]").removeAttr("data-confirm");
	$("#transactions_list > div:eq("+index+")").html("<div><div>Bought Date: "+bought_date+"</div><div>Sold Date: "+current_date+"</div><div>Bought Price: $"+bought_price+"</div><div>Sold Price: $"+current_price+"</div><div>Invested Capital: $"+bought_quantity+"</div><div>Return: $"+roi.toFixed(2)+" ("+((roi*100/bought_quantity)-100).toFixed(2)+"%)</div></div><div style='background: #ff6363'>CLOSED</div><div style='background: #3fcaff' onclick='viewInformation(this);' view_information>"+$("#transactions_list > div:eq("+index+")").find("div[view_information]").html()+"</div>");

	$("#backtesting_window table").each(function() {
		$("#transactions_list > div:eq("+index+")").find("div[view_information]").append("<div style='display: none' sold_information><table style='width: 95%;'>"+$(this).html()+"</table></div>");
	});
}

function buyItem(quantity) {
	if(total_capital-invested_capital >= quantity && $.isNumeric(quantity) && quantity > 0) {
		modal.close();
		$("#transactions_list > span").hide();
		$("#transactions_list").append("<div><div><div>Bought Date: "+current_date+"</div><div>Bought Price: $"+current_price+"</div><div>Invested Capital: $"+quantity+"</div></div><div onclick='sellItem("+($("#transactions_list > div").length == 0 ? '0' : $("#transactions_list > div").last().index())+");' data-confirm='Are you sure you want to close this position?' bought_date='"+current_date+"' bought_price='"+current_price+"' invested_capital='"+quantity+"' sell_item>OPEN <span>(Click to Sell)</span></div><div style='background: #3fcaff' onclick='viewInformation(this);' view_information><i class='fas fa-chart-bar'></i>VIEW INFORMATION</div></div>");
		invested_capital = parseFloat(invested_capital) + parseFloat(quantity);
		o_positions = parseInt(o_positions) + 1;
		updateSessionValues();
		new jBox('Confirm', {
			attach: $("#transactions_list > div").last().find("div[sell_item]"),
			confirmButton: 'Yes',
			cancelButton: 'No'
		});
		$("#backtesting_window table").each(function() {
			$("#transactions_list > div").last().find("div[view_information]").append("<div style='display: none' bought_information><table style='width: 95%;'>"+$(this).html()+"</table></div>");
		});
	}
	else if(!$.isNumeric(quantity) || quantity <= 0) {
		alert("Enter a valid amount.");
	}
	else{
		alert("You poor bruh.");
	}
}

function viewInformation(element) {
	var content = "<h2 style='text-align: center;'>Information When Bought</h2>";

	for (var i = 0; i < $(element).find("div[bought_information]").length; i++) {
		content += $(element).find("div[bought_information]:eq("+i+")").html();
	}

	if($(element).find("div[sold_information]").length > 0) {
		content += "<h2 style='text-align: center; margin-top: 20px'>Information When Sold</h2>";
	
		for (var i = 0; i < $(element).find("div[sold_information]").length; i++) {
			content += $(element).find("div[sold_information]:eq("+i+")").html();
		}
	}

	var modal = new jBox("Modal", {
		content: content,
		overlay: false,
		closeButton: true,
		title: "Position Information",
		draggable: true,
		width: "30%"
	});

	modal.open();
}

/*$("#backtesting_window > p span").html('2009-01-01');
current_date = '2009-01-01';
$("#backtesting_window img").show();
$("#backtesting_window > div").hide();
$("#main").show();
$("#backtesting").show();
startBacktesting("galaxy s10");*/

function startBacktesting(query) {
	//query = "/algorithms/Others/statistical_backtesting.py 'AAPL' '2009-01-01' '2017-01-01 2018-01-01' '2017-01-01 2018-01-01'";
	console.log(query);
	plotItemPrices();

	$.post("/php_dependancies/analysis_scripts.php", {query: query}, function(data){
		try {
			data = $.parseJSON(data);
		} catch(err) {
			console.log(data);
			console.log(err);
		}

		$("#backtesting_window img").hide();
		$("#backtesting_window > div").show();

		if(data['table1'] != null && data['table2'] != null) {
			$("#backtesting_window > div").find("table").remove();
			$("#backtesting_window > div").prepend(arrayToTableNoSort(data['table1']));
			$("#backtesting_window > div").prepend(arrayToTableNoSort(data['table2']));
			current_price = parseFloat(data['table2'][1][0]);
		}
	});
}

function arrayToTableNoSort(array) {
	var response = "<table><thead>";
	for (var a = 0; a < 1; a++) { 
		for (var b = 0; b < array[a].length; b++) { 
			response += "<th>"+array_values(array[a])[b]+"</th>";
		}	
	}
	response += "</thead><tbody>";
	for (var a = 1; a < array.length; a++) { 
		response += "<tr>";
		for (var b = 0; b < array[a].length; b++) { 
			arrayValue = array_values(array[a])[b];
			response += "<td>"+arrayValue+"</td>";
		}	
		response += "</tr>";
	}
	response += "</tbody></table>";
	return response;
}
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>