<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<div id="filter_options">
	<div class="super-container" style="width: 100%">
		<div class="super-container-header">
			<i class="fas fa-filter"></i>Filtering Options
		</div>
		<div></div>
		<div class="general-container" style="margin: 0 auto; width: 100%; max-width: 100%">
			<nav style="display:none">
				<h1 class="general-container-selected"></h1>
			</nav>
			<div class="general-container-content" style="width: 100%">
				<h3>Initial Filter:</h3>
				<div>
					<button class="button" file="mediumtermtrend_filter.py">Medium term trend (2y)</button>
					<button class="button" file="longtermtrend_filter.py">Long term trend (3y)</button>
					<button class="button" file="fundamentalstatus_filter.py">Good fundamentals</button>
					<button class="button" file="longtermbullmarket_filter.py">Long term bull market</button>
					<button class="button" file="bullmarket_filter.py">Bull market</button>
					<button class="button" file="bearmarket_filter.py">Long term bear market</button>
				</div> 
				<h3>By Dividend:</h3>
				<div>
					<button class="button" file="1%dividend_filter.py">1+% dividend</button>
					<button class="button" file="2%dividend_filter.py">2+% dividend</button>
					<button class="button" file="3%dividend_filter.py">3+% dividend</button>
				</div>
				<h3>By Market Cap:</h3>
				<div>
					<button class="button" file="bigmarketcap_filter.py">10b+ market cap</button>
					<button class="button" file="massivemarketcap_filter.py">100b+ market cap</button>
				</div>
				<h3>By Moving Averages:</h3>
				<div>
					<button class="button" file="shortterm_filter.py">Close to 50/100 SMA</button>
					<button class="button" file="longterm_filter.py">Close to 200 SMA</button>
				</div>
				<h3>By Drops:</h3>
				<div>
					<button class="button" file="5%droplast2months.py">5+% last 2 months</button>
					<button class="button" file="recent15%drop_filter.py">15+% last month</button>
					<button class="button" file="10%droplastquarter_filter.py">10+% last quarter</button>
					<button class="button" file="recent15%drop_filter.py">15+% last month</button>
					<button class="button" file="15%drop3months_filter.py">15+% last 3 months</button>
					<button class="button" file="20%drop6months_filter.py">20+% last 6 months</button>
					<button class="button" file="30%drop1year_filter.py">30+% last year</button>
				</div>
				<h3>By Rises:</h3>
				<div>
					<button class="button" file="15%uplastyear.py">15+% last year</button>
				</div>
				<h3>Date</h3>
				<div>
					<input style="background: #656565;color: white;font-weight: 700;font-size: 16px;padding: 5px;" type="date" id="filter_date" value="<?php echo date("Y-m-d"); ?>">
				</div>
				<div>
					<button class="button"><i class="fas fa-reply"></i>Undo</button>
					<button class="button"><i class="fas fa-undo"></i>Reset</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="filter_table" style="margin-top: 40px; width: 60%">
	<table>
		<thead>
			<th>Stocks</th>
		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>
<script type="text/javascript">
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
				$(this).DataTable({"lengthMenu": [25, 50, 100]});
			}
		});
	}

	function arrayToTable(array) {
		var response = "";
		response += "<table><thead>";
		for (var a = 0; a < 1; a++) { 
			for (var b = 0; b < array[a].length; b++) { 
				response += "<th>"+array[a][b]+"</th>";
			}		
		}
		response += "</thead><tbody>";
		for (var a = 1; a < array.length; a++) { 
			response += "<tr>";
			for (var b = 0; b < array[a].length; b++) { 
				var arrayValue = $.isNumeric(array[a][b]) ? array[a][b] : array[a][b];
				response += "<td>"+arrayValue+"</td>";
			}	
			response += "</tr>";
		}
		response += "</tbody></table>";
		return response;
	}

	function resetFilter() {
		current_filter = "";
		for (var i = 0; i < stocks.length; i++) {
			current_filter = current_filter + stocks[i].replace("_1d", "") + " ";
		}
	}

	var query = "/algorithms/filters/";
	var stocks = <?php echo $_SESSION['filter_stocks']; ?>;
	var stock_names = <?php echo $_SESSION['filter_names']; ?>;
	var current_filter = "";
	var filter_process = false;
	var undo_filter = [];
	var undo_counter = 0;

	for (var i = 0; i < stocks.length; i++) {
		current_filter = current_filter + stocks[i].replace("_1d", "") + " ";
		$("#filter_table tbody").append("<tr><td><a href='/analyze_stocks.php?item="+stocks[i].replace("_1d", "")+"' target='_blank'><i class='fas fa-chart-bar'></i>"+stocks[i].replace("_1d", "")+" - "+stock_names[stocks[i].replace("_1d", "")]+"</a></td></tr>");
		if(i == stocks.length-1){
			refreshDataTable($("#filter_table table").get(0));
		}
	}

	$("#filter_options button").click(function() {
		if(!filter_process && $(this).attr("file")){
			filter_process = true;
			var file = $(this).attr("file");
			$("#filter_table").html("<img src='/img/ajax-loader-3.svg' style='margin: 30px auto 47.5px auto'>");
			$(this).addClass('selected_filter');
			var this_button = $(this);
			$.post("/php_dependancies/analysis_scripts.php", {query: (query+file+" '"+current_filter.trim()+"' '"+$("#filter_date").val()+"'")}, function(data){
				//console.log(data);
				filter_process = false;
				var data = data.replace(" [],", "");
				try{
					data = $.parseJSON(data);
				}
				catch(err){
					console.log(err);
				}
				if(data != null && data['table'] != null && data['table'].length > 1){
					undo_counter++;
					$(this_button).append("<p>"+undo_counter+"</p>");
					undo_filter[undo_counter] = data['table'];
					current_filter = "";
					for (var i = 1; i < data['table'].length; i++) {
						current_filter = current_filter + data['table'][i][0].replace("_1d", "") + " ";
					}
					$("#filter_table").html("");
					for (var i = 1; i < data['table'].length; i++) {
						data['table'][i][0] = "<a href='/analyze_stocks.php?item="+data['table'][i][0]+"' target='_blank'><i class='fas fa-chart-bar'></i>"+data['table'][i][0]+" - "+stock_names[data['table'][i][0]]+"</a>";
					}
					$("#filter_table").append(arrayToTable(data['table']));
					
					refreshDataTable($("#filter_table table").get(0));
				}
				else {
					if(data == null || data['table'] == null){
						alert("Error.");
					}
					else{
						alert("Nothing was found with the selected filter.");
					}
					$(this_button).removeClass('selected_filter');
					if (undo_filter.length > 0) {
						$("#filter_table").html("");
						$("#filter_table").append(arrayToTable(undo_filter[undo_counter]));
						refreshDataTable($("#filter_table table").get(0));
					}
					/*$(".button p").remove();
					$(".button").removeClass("selected_filter");
					$("#filter_table").html('<table><thead><th>Stocks</th></thead><tbody></tbody></table>');
					resetFilter();
					for (var i = 0; i < stocks.length; i++) {
						$("#filter_table tbody").append("<tr><td><a href='/analyze_stocks.php?item="+stocks[i].replace("_1d", "")+"' target='_blank'><i class='fas fa-chart-bar'></i>"+stocks[i].replace("_1d", "")+" - "+stock_names[stocks[i].replace("_1d", "")]+"</a></td></tr>");
						if(i == stocks.length-1){
							refreshDataTable($("#filter_table table").get(0));
						}
					}
					undo_counter = 0;*/
				}
			});
		}
		else if(!filter_process){
			filter_process = true;
			if($(this).text().trim() == "Reset" || undo_counter <= 1){
				$(".button p").remove();
				$(".button").removeClass("selected_filter");
				$("#filter_table").html('<table><thead><th>Stocks</th></thead><tbody></tbody></table>');
				resetFilter();
				for (var i = 0; i < stocks.length; i++) {
					$("#filter_table tbody").append("<tr><td><a href='/analyze_stocks.php?item="+stocks[i].replace("_1d", "")+"' target='_blank'><i class='fas fa-chart-bar'></i>"+stocks[i].replace("_1d", "")+" - "+stock_names[stocks[i].replace("_1d", "")]+"</a></td></tr>");
					if(i == stocks.length-1){
						refreshDataTable($("#filter_table table").get(0));
					}
				}
				undo_counter = 0;
			}
			else{
				if(undo_filter.length > 0 && undo_filter.length >= undo_counter-2){
					$("#filter_options .button").each(function() {
						if ($(this).find("p").length == 1 && $(this).find("p:eq(0)").text() == undo_counter) {
							$(this).find("p").remove();
							$(this).removeClass("selected_filter");
						}
					});
					$("#filter_table").html("");
					$("#filter_table").append(arrayToTable(undo_filter[undo_counter-1]));
					refreshDataTable($("#filter_table table").get(0));
					undo_counter--;
				}
			}
			filter_process = false;
		}
	});
</script>