function array_values(input) {
  var tmpArr = [];
  var key = '';

  for (key in input) {
    tmpArr[tmpArr.length] = input[key];
  }

  return tmpArr;
}

function refreshDataTable(table) {
	$(table).addClass("display");
	$(table).addClass("hover");
	$(table).css("width", "100%");
	$(table).wrap("<div style='text-align: center;' class='tableContainer'><div style='display: inline-block; width: 95%; margin: 0 auto;'></div></div>")
	$(table).each(function() {
		if ($(this).hasClass("dataTableDesc")) {
			$(this).DataTable({order: [[0, 'desc']]});
		} else if ($(this).hasClass("noSort")) {
			$(this).DataTable({"order": []});
		} else if ($(this).hasClass("smallTable")) {	
			$(this).DataTable({"lengthMenu": [5, 10]});
		} else {
			$(this).DataTable();
		}
	});
}

function arrayToTable(array) {
	var response = "<table class='dataTable smallTable'><thead>";
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

$(document).ready(function() {
	$.fn.slideFadeToggle  = function(speed, easing, callback) {
        return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
	}; 

	//Set both PE Ratios
	function setPERatios($element) {
		//Item Name
		var itemName = $element.html().split(" -")[0];
		var endDate = $("input[name=endDate]").val();
		var initDate = $("input[name=startDate]").val();
		$(".peratio-division").find("span").hide();
		$(".peratio-division").find("img").show();
		$(".peratio-division").find("i").remove();
		$("input[name='1d_pe_ratio']").hide();
		$("#peratio-container").find(".tableContainer").remove();
		$("input[name='1y_pe_ratio']").hide();
		$("input[name='3y_pe_ratio']").hide();
		$("input[name='which_pe']").hide();
		$(".peratio-division").find("label").hide();
		$("input[name='manual_pe_ratio']").val("");

		//Ajax
		$.get("/php_dependancies/fetch_peratio.php", {stock: itemName, endDate: endDate, initDate: initDate}, function(result) {
			console.log(result);
			if (result != null && result.length > 0) {
				var jsonResult = jQuery.parseJSON(result);
			} else {
				var jsonResult = null;
			}
			$(".peratio-division").find("img").hide();
			if (jsonResult != null && 'peToday' in jsonResult && 'pe1YAvg' in jsonResult && 'pe3YAvg' in jsonResult && 'table' in jsonResult) {
				$("input[name='1d_pe_ratio']").val(jsonResult['peToday'].toFixed(3));
				$("input[name='1y_pe_ratio']").val(jsonResult['pe1YAvg'].toFixed(3));
				$("input[name='3y_pe_ratio']").val(jsonResult['pe3YAvg'].toFixed(3));
				$("input[name='1d_pe_ratio']").show();
				$("input[name='1y_pe_ratio']").show();
				$("input[name='3y_pe_ratio']").show();
				$("input[name='which_pe']").show();
				$(".peratio-division").find("label").show();
				$("#peratio-container").append(arrayToTable(jsonResult['table']));
				refreshDataTable($("#peratio-container").find("table").get(0));
				$("#peratio-container").find(".tableContainer").css('margin', '15px 0 15px 0');
			} else {
				$(".peratio-division:eq(0)").append("<i class='fas fa-exclamation-circle' style='font-size: 24px; position: relative; top: 12px;'></i>");
				$(".peratio-division:eq(1)").append("<i class='fas fa-exclamation-circle' style='font-size: 24px; position: relative; top: 12px;'></i>");
				$(".peratio-division:eq(2)").append("<i class='fas fa-exclamation-circle' style='font-size: 24px; position: relative; top: 12px;'></i>");
			}
		});
	}
	function updateIndex() {
		$(".main-form select[name='selectedStock']").next("p").next("div").remove();
		$(".main-form select[name='selectedStock']").next("p").remove();
		var table = $(".main-form select[name='selectedStock']").val() + "_1d";
		$.post("/php_dependancies/check_if_outdated.php", {table: table}, function(result) {
			if (result > 1) {
				$(".main-form select[name='selectedStock']").after("<p style='padding: 5px; font-size: 14px; font-weight: 400; font-style: italic;'>Data is "+result+" day(s) old.</p><div id='update_now' style='background: #7F7F7F'>UPDATE</div>");
				$("#update_now").unbind("click");
				$("#update_now").click(function() {
					$(this).hide();
					$(this).prev("p").hide();
					$(this).after("<img src='/img/ajax-loader-2.gif' height='35' style='margin: 5px auto 10px auto; display: block;'>");
					$.post("/php_dependancies/update_prices.php", {symbol: $(".main-form select[name='selectedStock']").val()}, function(result) {
					    $("#update_now").next("img").hide();
						$("#update_now").prev("p").html(result);
						$("#update_now").prev("p").show();
					});
				});
			} else {
				$(".main-form select[name='selectedStock']").after("<p style='padding: 5px; font-size: 14px; font-weight: 400; font-style: italic;'>Up to date!<i class='fas fa-check' style='margin-left: 7px;'></i></p>");
			}
		});
	}
	updateIndex();
	$(".main-form select[name='selectedStock']").change(function() {
		updateIndex();
	});

	var stocks;
	$.post( "/php_dependancies/stock_search.php", function( data ) {
	  if (data != null && data.length > 0) {
	  	stocks = data.sort();
	  }
	});

	if ($(".selected").length > 0) {
		setPERatios($(".selected"));
	}
	
	$('input[name=endDate]').change(function() {
		if ($(".selected").length > 0) {
			setPERatios($(".selected"));
		}
	});

	$("#stock_search").keyup(function(key){
		$("#search_results").css("display", "none");
		$("#search_results").html("");
		if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46 && stocks != null && stocks.length > 0) {
			var currentText = $("#stock_search").val().toLowerCase().trim();
			if (currentText != null && currentText.length > 0) {
				var searchResults = [];
				for (var i = 0; i <= stocks.length - 1; i++) {
					if (stocks[i].toLowerCase().indexOf(currentText) >= 0) {
						searchResults.push(stocks[i]);
					}
					if (searchResults.length >= 1000) {
						break;
					}
				}
				if (searchResults.length > 0) {
					$("#search_results").css("display", "inline-block");
					for (var b = 0; b <= searchResults.length - 1; b++) {
						$("#search_results").append("<div style='padding: 10px; font-weight: 500; cursor: pointer; transition: 0.1s all;'>" + searchResults[b] + "</div>");
					}
					$("#search_results div").unbind("click");
					$("#search_results div").click(function() {
						if (!$(this).hasClass("selected")) {
							//Reset PE
							$("#stock_search").val($(this).html());
							setPERatios($(this));
							$("#search_results div").css("background", "#FBFCFC");
							$("#search_results div").hide();
							$(this).show();
							$(this).css("background", "rgba(49,60,71,.11)");
							$(this).addClass("selected");
							if ($(this).html() != null && $(this).html().length > 0 && $(this).html().indexOf(" -") > 0) {
								var updateNow = $(this).html().trim().split(" -")[0].trim().toUpperCase();
								var stockName = $(this).html().trim().split(" -")[0].trim().toUpperCase() + "_1d";
								$.post("/php_dependancies/set_stock.php", {stock: stockName}, function(result) {}); 
								$.post("/php_dependancies/check_if_outdated.php", {table: stockName}, function(result) {
									if (result.length != 0) {
										if (result > 1) {
											$(".selected").after("<p class='dataStatus' style='padding: 10px; font-size: 16px; font-weight: 300; font-style: italic; display: none;'>Data is "+result+" day(s) old.</p><div id='update_now' style='background: #7F7F7F'>UPDATE</div>");
											$("#update_now").unbind("click");
											$("#update_now").click(function() {
												$(this).hide();
												$(this).prev("p").hide();
												$(this).after("<img src='/img/ajax-loader-2.gif' height='35' style='margin: 15px 0 10px 0;'>"); 
												$.post("/php_dependancies/update_prices.php", {symbol: updateNow}, function(result) {
												    $("#update_now").next("img").hide();
													$("#update_now").prev("p").html(result);
													$("#update_now").prev("p").show();
												});
											});
										} else {
											$(".selected").after("<p class='dataStatus' style='padding: 10px; font-size: 16px; font-weight: 300; font-style: italic; display: none;'>Data is up to date!</p>");
										}
										$(".dataStatus").slideFadeToggle(500);
									}
								});
							}
						}
					});
				}
			}
		} else {
			return false;
		}
	});	
	$("input[name='manual_pe_ratio']").keypress(function(key){
		if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
			return false;
		}
	});
});