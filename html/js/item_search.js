var items = null;

function itemSearch(type, search_element, results_element){
	if(items == null){
		$.get("/php_dependancies/linearized_search.php", {type: type}, function(data){
			if (data != null && data.length > 0) {
				items = data.sort();
			}
		});
	}

	$(search_element).on("keyup click", function(key){
		$("input[name=selected_item]").remove();
		$(results_element).css("display", "none");
		$(results_element).html("");
		if (items != null && items.length > 0) {
			var currentText = $(search_element).val().toLowerCase().trim();
			if (currentText != null) {
				var searchResults = [];
				for (var i = 0; i <= items.length - 1; i++) {
					if (items[i][0].toLowerCase().indexOf(currentText) >= 0 || items[i][1].toLowerCase().indexOf(currentText) >= 0) {
						searchResults.push(items[i][0] + " - " + items[i][1]);
					}
					if (searchResults.length >= 1000) {
						break;
					}
				}
				if (searchResults.length > 0) {
					$(results_element).css("display", "inline-block");
					for (var b = 0; b <= searchResults.length - 1; b++) {
						$(results_element).append("<div style='padding: 10px; font-weight: 500; cursor: pointer; transition: 0.1s all;'>" + searchResults[b] + "</div>");
					}
					$(results_element).find("div").unbind("click");
					$(results_element).find("div").click(function() {
						if (!$(this).hasClass("selected")) {
							//Reset PE
							$(search_element).val($(this).html());
							setPERatios($(this));
							$(results_element).find("div").css("background", "#FBFCFC");
							$(results_element).find("div").hide();
							$(this).show();
							$(this).css("background", "rgba(49,60,71,.11)");
							$(this).addClass("selected");
							$("input[name='selected_item']").remove();
							$("input[name='selected_table']").remove();
							$(".analyze").append("<input type='hidden' name='selected_item' value='"+$(this).html().split(" -")[0]+"'>");
						}
					});
				}
			}
		} else {
			return false;
		}
	});	
}
//Set both PE Ratios
function setPERatios($element) {
	//Item Name
	var item_name = $element.html().split(" -")[0];
	var end_date = $("input[name=end_date]").val();
	var start_date = $("input[name=start_date]").val();
	$(".peratio_division").find("span").hide();
	$(".peratio_division").find("img").show();
	$(".peratio_division").find("i").remove();
	$("input[name='1d']").hide();
	$("#peratio_container").find(".tableContainer").remove();
	$("input[name='1y']").hide();
	$("input[name='3y']").hide();
	$("input[name='which_pe']").hide();
	$(".peratio_division").find("label").hide();
	$("input[name='custom_pe']").val("");

	//Ajax
	$.get("/php_dependancies/fetch_peratio.php", {item: item_name, end_date: end_date, start_date: start_date}, function(result) {
		if (result != null && result.length > 0) {
			var jsonResult = jQuery.parseJSON(result);
		} else {
			var jsonResult = null;
		}
		$(".peratio_division").find("img").hide();
		if (jsonResult != null && 'peToday' in jsonResult && 'pe1YAvg' in jsonResult && 'pe3YAvg' in jsonResult && 'table' in jsonResult) {
			$("input[name='1d']").val(jsonResult['peToday'].toFixed(3));
			$("input[name='1y']").val(jsonResult['pe1YAvg'].toFixed(3));
			$("input[name='3y']").val(jsonResult['pe3YAvg'].toFixed(3));
			$("input[name='1d']").show();
			$("input[name='1y']").show();
			$("input[name='3y']").show();
			$("input[name='which_pe']").show();
			$(".peratio_division").find("label").show();
			$("#peratio_container").find("table").each(function(){
				$(this).remove();
			});
			$("#peratio_container").find(".tableContainer").each(function(){
				$(this).remove();
			});
			$("#peratio_container").append(arrayToTableSmall(jsonResult['table']));
			refreshDataTable($("#peratio_container").find("table").get(0));
			$("#peratio_container").find(".tableContainer").css('margin', '15px 0 15px 0');
		} else {
			$(".peratio_division:eq(0)").append("<i class='fas fa-exclamation-circle' style='font-size: 24px; position: relative; top: 12px;'></i>");
			$(".peratio_division:eq(1)").append("<i class='fas fa-exclamation-circle' style='font-size: 24px; position: relative; top: 12px;'></i>");
			$(".peratio_division:eq(2)").append("<i class='fas fa-exclamation-circle' style='font-size: 24px; position: relative; top: 12px;'></i>");
		}
	});
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
		} else if ($(this).hasClass("showAll")) {	
			$(this).DataTable({"lengthMenu": [100, "All"]});
		} else {
			$(this).DataTable();
		}
	});
}

function arrayToTableSmall(array) {
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

function arrayToTableShowAll(array) {
	var response = "<table class='dataTable showAll'><thead>";
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

function arrayToTable(array) {
	var response = "<table class='dataTable'><thead>";
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

function array_values(input) {
  var tmpArr = [];
  var key = '';

  for (key in input) {
    tmpArr[tmpArr.length] = input[key];
  }

  return tmpArr;
}

$("input[name='custom_pe']").keypress(function(key){
	if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
		return false;
	}
});

$("input[name='end_date']").change(function(){
	if($("input[name='selected_item']").length == 1 && $("#search_results").find("div").length == 1){
		setPERatios($("#search_results").find("div"));
	}
});

if($(".selected").length == 1){
	setPERatios($(".selected"));
}