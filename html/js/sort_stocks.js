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
	//FORM
	function submitSort() {
		if($("input[name='num_stocks']").val() == null || $("input[name='num_stocks']").val().length <= 0 || $("input[name='start_date']").val() == null || $("input[name='start_date']").val().length <= 0 || $("input[name='end_date']").val() == null || $("input[name='end_date']").val().length <= 0){
			return true;
		}
		else{
			$("#sort_error").remove();
			$("#sort_results").show();
			$("#sort_loading").css("display","block");
			$("input[name='sort']").hide();
			//PYTHON
			var date_interval = $("input[name='end_date']").val().split("-")[0] - $("input[name='start_date']").val().split("-")[0];
			var query = "/algorithms/Sort/sortingStocks.py "+$("input[name='num_stocks']").val()+" '"+$("select[name='change_in_2'] option:selected").val()+"' '"+$("select[name='sector'] option:selected").val()+"' '"+$("select[name='change_in'] option:selected").val()+"' "+date_interval+" '"+$("input[name='start_date']").val()+"' '"+$("input[name='end_date']").val()+"'";
			var title = $("input[name='num_stocks']").val()+", "+$("select[name='change_in_2'] option:selected").html()+", "+$("select[name='sector'] option:selected").html()+", "+$("select[name='change_in'] option:selected").html()+", "+$("input[name='start_date']").val()+", "+$("input[name='end_date']").val();
			$.post("/php_dependancies/analysis_scripts.php", {query: query}, function(data){
				var data = data.replace(" [],", "");
				try{
					data = $.parseJSON(data);
				}
				catch(err){
					console.log(err);
				}
				$(".super-container-header img").remove();
				if (data['stocks'] != null && data['change'] != null) {
					var resultTable = "<div class='sort_result'><div class='sort_header' onclick='expandSort(this);'><div class='sort_title'>"+title+"</div><div class='sort_expand'><i class='fas fa-plus'></i></div></div><table style='margin:15px auto;' class='dataTable noSort'><thead><th>Stock</th><th>Change</th></thead><tbody>";
					for (var i = 0; i < data['stocks'].length; i++) { 
						resultTable += "<tr>";
						resultTable += "<td><a href='/analyze_stocks.php?item="+data['stocks'][i]+"&term=long' target='_blank'><i class='far fa-chart-bar' style='margin-right: 5px; color: #414141;'></i>"+data['stocks'][i]+"</a></td>";
						resultTable += "<td>"+data['change'][i].toFixed(4)+"%</td>";
						resultTable += "</tr>";
					}
					resultTable += "</tbody></table></div>";
					$("#sort_results .general-container-content").append(resultTable);
					refreshDataTable($("#sort_results .general-container-content").find("table").get($("#sort_results .general-container-content").find("table").length-1));
				}
				$("#sort_loading").hide();
				$("input[name='sort']").show();
			});
		}

		return false;
	}

	function expandSort(element){
		$(element).parent().find(".tableContainer").toggle(0);
		if($(element).parent().find(".sort_expand i").hasClass("fa-plus")){
			$(element).parent().find(".sort_expand i").addClass("fa-minus");
			$(element).parent().find(".sort_expand i").removeClass("fa-plus");
		}
		else{
			$(element).parent().find(".sort_expand i").removeClass("fa-minus");
			$(element).parent().find(".sort_expand i").addClass("fa-plus");
		}
	}
	//INPUT VERIFICATION
	$("input[name='num_stocks']").keypress(function(key){
		if ((key.charCode < 48 || key.charCode > 57)) {
			return false;
		}
	});
