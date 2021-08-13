<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script src="https://cdn.quilljs.com/1.1.9/quill.js"></script>
<link rel="stylesheet" type="text/css" href="/css/LJBRater.css">
<link href="https://cdn.quilljs.com/1.1.9/quill.snow.css" rel="stylesheet">
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
				$(this).DataTable();
			}
		});
	}

	function deleteSelection(selection) {
		var selection_count = $(selection).parent().parent().parent().parent().children("div").length;
		if (selection_count == 1) {
			$(selection).parent().parent().parent().parent().find("span").show();
		} else {
			$(selection).parent().parent().parent().parent().find("span").hide();
		}
		$(selection).parent().parent().parent().remove();
	}

	function addSelection(selection, searchType) {
		var symbol = $(selection).attr("symbol");
		var tableName = $(selection).attr("tableName");
		var plot_type = $(selection).parent().attr("id");
		if ((plot_type == "correlation_search_results" && $(selection).parent().next("div").children("div").length < 2) || (plot_type == "linearized_search_results" && $(selection).parent().next("div").children("div").length < 10)) {
			$(selection).parent().next("div").append("<div class='linearized_selection_container' tableName='"+tableName+"'><div class='linearized_selection_header'><div>"+symbol+"</div><div><i class='fas fa-minus-square' onclick='deleteSelection(this)'></i></div></div><div class='linearized_selection_type'>"+searchType+"</div></div>");
		}
		else{
			alert(plot_type == "correlation_search_results" ? "You can't add more than two items." : "You can't add more than 10 items.");
		}

		if($(selection).parent().next("div").children("div").length > 0){
			$(selection).parent().next("div").find("span").hide();
		}
		else{
			$(selection).parent().next("div").find("span").show();
		}
	}
	$(document).ready(function() {
		function searchItems(itemType, element) {
			$(element).val("");
			$(element).children("div:eq(1)").html("");
			searchResult = null;
			searchType = null;
			$.get("/php_dependancies/linearized_search.php", {type: itemType}, function(data) {
				if (data != null && data.length > 0) {
					searchResult = data.sort();
					searchType = itemType.replace("_", " ").toUpperCase();
					if (searchType.indexOf("fundamental") >= 0) {
						searchType = "Fundamental";
					}
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
		function ucFirst(string) {
			return string.charAt(0).toUpperCase() + string.slice(1);
		}
		var searchResult = null;
		var searchType = null;
		searchItems("stock", $("#linearized_search"));
		$(".linearized_search_type").click(function() {
			$(".linearized_search_type").removeClass("linearized_search_selected");
			$(this).addClass("linearized_search_selected");
			searchItems($(this).attr("type"));
			$("#linearized_search_results").hide();
			$("#linearized_search_results").html("");
		});
		$(".correlation_search_type").click(function() {
			$(".correlation_search_type").removeClass("correlation_search_selected");
			$(this).addClass("correlation_search_selected");
			searchItems($(this).attr("type"));
			$("#correlation_search_results").hide();
			$("#correlation_search_results").html("");
		});
		$("#linearized_search, #correlation_search").on("click keyup", function(key) {
			if (searchResult != null && Array.isArray(searchResult) && searchResult.length > 0) {
				$(this).parent().next("div").hide();
				$(this).parent().next("div").html("");
				var currentText = $(this).val().toLowerCase().trim();
				for (var i = 0; i < searchResult.length; i++) {
					if (searchResult[i][0].toLowerCase().trim().indexOf(currentText) >= 0 || searchResult[i][1].toLowerCase().trim().indexOf(currentText) >= 0) {
						$(this).parent().next("div").show();
						$(this).parent().next("div").append("<div symbol='"+searchResult[i][0]+"' tableName='"+searchResult[i][2]+"' onclick='addSelection(this, \""+searchType+"\")'>" + searchResult[i][0] + " - " + searchResult[i][1] + "</div>");
					}
				}
			}
		});
		$("#linearized_plot").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='linearized_loading'>");
			var tableNames = "";
			var itemNames = "";
			$("#linearized_selections .linearized_selection_container").each(function() {
				tableNames += $(this).attr("tableName") + " ";
				itemNames += $(this).attr("tableName").replace("_1d", "").replace("_f", "") + " ";
			});
			$.post("/php_dependancies/linearized_plot.php", {query: tableNames, startDate: $(".linearized_date_container #startDate").val(), endDate: $(".linearized_date_container #endDate").val()}, function(data) {
				if (data != "fail") {
					var linearized_result = "<div class='sort_result'><div class='sort_header' onclick='expandPlot(this);'><div class='sort_title'>"+(itemNames)+"</div><div class='sort_expand'><i class='fas fa-plus'></i></div></div><div class='plotContainer' style='display: none;'>"+data+"</div></div>";
					$("#plot_results").show();
					$("#linearized_results").trigger("click");
					$("#plot_results .general-container-content:eq(0)").append(linearized_result);
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#linearized_loading").remove();
				$("#linearized_plot").show();
			});
		});
		$("#correlation_plot").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='correlation_loading'>");
			var tableNames = "";
			var itemNames = "";
			$("#correlation_selections .linearized_selection_container").each(function() {
				tableNames += $(this).attr("tableName") + " ";
				itemNames += $(this).attr("tableName").replace("_1d", "").replace("_f", "") + " ";
			});
			$.post("/php_dependancies/correlation_plot.php", {query: tableNames, startDate: $(".correlation_date_container #startDate").val(), endDate: $(".correlation_date_container #endDate").val()}, function(data) {
				if (data != "fail") {
					if (data != null && data.length > 0) {
						var corrjson = $.parseJSON(data);
						var tableHtml = arrayToTable(corrjson['table']);
						var correlation_result = "<div class='sort_result'><div class='sort_header' onclick='expandPlot(this);'><div class='sort_title'>"+(itemNames)+"</div><div class='sort_expand'><i class='fas fa-plus'></i></div></div><div class='plotContainer' style='display: none;'>"+corrjson['graph']+tableHtml+"<ul style='margin-top: 15px; margin-bottom: 15px; font-size: 20px; display: inline-block; text-align: left;'><li>Above 0.6: strong positive correlation.</li><li>Below 0.6: strong negative correlation.</li><li>In between: no strong correlation.</li></ul></div></div>";
						$("#plot_results").show();
						$("#correlation_results").trigger("click");
						$("#plot_results .general-container-content:eq(1)").append(correlation_result);
					}
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#correlation_loading").remove();
				$("#correlation_plot").show();
			});
		});
		$("#fundamentals_button").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='fundamentals_loading'>");
			$("#fundamentals_graph").hide();
			$.post("/php_dependancies/fundamentals_graph.php", {startDate: $("#startDateFund").val(), endDate: $("#endDateFund").val(), country: $("#fund_country").val()}, function(data) {
				if (data != "fail") {
					$("#fundamentals_graph").show();
					$("#fundamentals_graph").html(data);
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#fundamentals_loading").remove();
				$("#fundamentals_button").show();
			});
		});
		$("#correlation_header").click(function() {
			$(".correlation_search_type:eq(0)").trigger("click");
		});
		$("#linearized_header").click(function() {
			$(".linearized_search_type:eq(0)").trigger("click");
		});
	});
function expandPlot(element){
	$(element).parent().find(".plotContainer").toggle(0);
	if($(element).parent().find(".sort_expand i").hasClass("fa-plus")){
		$(element).parent().find(".sort_expand i").addClass("fa-minus");
		$(element).parent().find(".sort_expand i").removeClass("fa-plus");
	}
	else{
		$(element).parent().find(".sort_expand i").removeClass("fa-minus");
		$(element).parent().find(".sort_expand i").addClass("fa-plus");
	}
	window.dispatchEvent(new Event('resize'));
}
</script>
<div class='super-container' style='width: 45%;'>
	<h1 class="super-container-header">Market Overview</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 class='general-container-selected'><i class="fas fa-chart-line"></i>SP&500 Sectors</h1>
			<h1><i class="fas fa-coins"></i>Currencies</h1>
			<h1><i class="fas fa-gas-pump"></i>Commodities</h1>
			<h1><i class="fas fa-percentage"></i>Interest Rates</h1>
		</nav>
		<?php 
		for ($a = 0; $a < 3; $a++) { 
			echo "<div class='general-container-content'>";
			$script = $a == 0 ? 'Sector Index Analysis' : ($a == 1 ? 'Currencies Analysis' : 'Commodities Analysis');
			$thead = $a == 0 ? 'Sector' : ($a == 1 ? 'Currency' : 'Commodity');
		$query = mysqli_query($db, "SELECT output, date FROM economy WHERE script = '{$script}' ORDER BY date DESC LIMIT 1") or die(mysqli_error($db));
			if ($row = mysqli_fetch_assoc($query)) {
				$array = json_decode(stripslashes($row['output']), true);
				echo "<table class='dataTable'>";
				echo "<thead>";
				echo "<th>{$thead}</th>";
				echo "<th>Last 3 Months</th>";
				echo "<th>Last 6 Months</th>";
				echo "<th>Last Year</th>";
				echo "<th>Last 3 Years</th>";
				echo "</thead>";
				echo "<tbody>";
				for ($i = 0; $i < sizeof($array); $i++) { 
					$itemSymbol = str_replace("_1d", "", $array[array_keys($array)[$i]][4]);
					$itemDescripion = array_keys($array)[$i];
					echo "<tr>";
					echo "<td>".$itemSymbol."<i class='fas fa-info-circle info' value=\"{$itemDescripion}\"></i></td>";
					echo "<td>".number_format((float)$array[array_keys($array)[$i]][0], 3)."%</td>";
					echo "<td>".number_format((float)$array[array_keys($array)[$i]][1], 3)."%</td>";
					echo "<td>".number_format((float)$array[array_keys($array)[$i]][2], 3)."%</td>";
					echo "<td>".number_format((float)$array[array_keys($array)[$i]][3], 3)."%</td>";
					echo "</tr>";
				}
				echo "</tbody>";
				echo "</table>";
			} 
			echo "</div>";
		} 
		?>
		<div class="general-container-content">
			<?php 
			$ir_analysis = mysqli_query($db, "SELECT output, date FROM economy WHERE script = 'Interest Rate Analysis' ORDER BY date DESC LIMIT 1") or die(mysqli_error($db));
			if ($row = mysqli_fetch_assoc($ir_analysis)) {
				$array = json_decode(($row['output']), true);
				if (is_array($array)) {
					echo arrayToTable($array['US Interest Rates']);
				}
			}
			?>
		</div>
	</div>
</div>
<div class='super-container' style='width: 45%;'>
	<h1 class="super-container-header">Interest Rate Information</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 class='general-container-selected'><i class="fas fa-percentage"></i>Interest Rates</h1>
			<h1><i class="fas fa-chart-area"></i>Yield Curve</h1>
		</nav>
		<div class='general-container-content' style='min-width: 90%;'>
			<?php
			mysqli_data_seek($ir_analysis, 0);
			if ($row = mysqli_fetch_assoc($ir_analysis)) {
				$array = json_decode(($row['output']), true);
				if (is_array($array)) {
					echo $array['Ir Plot'];
				}
			}
			?>
		</div>
		<div class='general-container-content' style='min-width: 90%;'>
			<?php
			mysqli_data_seek($ir_analysis, 0);
			if ($row = mysqli_fetch_assoc($ir_analysis)) {
				$array = json_decode(($row['output']), true);
				if (is_array($array)) {
					echo $array['Yield Curve'];
				}
			}
			?>
		</div>
	</div>
</div>
<div class='super-container' style='width: 90%;'>
	<h1 class="super-container-header">Macroeconomic Indicators</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 class='general-container-selected' id='linearized_header'>Linearized Plot</h1>
			<h1 id='correlation_header'>Correlations</h1>
			<h1>Economic Indicators</h1>
		</nav>
		<div class='general-container-content' style='min-width: 90%;'>
			<div style='margin: 0 auto;'>
				<input type="text" id="linearized_search" placeholder='Search...'>
				<div class='linearized_search_type linearized_search_selected' type='stock'>Stocks</div>
				<div class='linearized_search_type' type='index'>Indexes</div>
				<div class='linearized_search_type' type='currency'>Currencies</div>
				<div class='linearized_search_type' type='commodity'>Commodities</div>
				<div class='linearized_search_type' type='bond'>Bonds</div>
				<div class='linearized_search_type' type='us_fundamental'>Fundamentals</div>
			</div>
			<div id="linearized_search_results" style='display: none;'>
				
			</div>
			<div id="linearized_selections">
				<span style='font-style: italic;'>No selections...</span>
			</div>
			<div style='margin: 0 auto;'>
				<div class='linearized_date_container'>
					<label>Start Date</label>
					<input type="date" id="startDate" <?php echo "value=\"".date("Y-m-d", time() - 31557600*4)."\""; ?>>
				</div>
				<div class='linearized_date_container'>
					<label>End Date</label>
					<input type="date" id="endDate" <?php echo "value=\"".date("Y-m-d", time())."\""; ?>>
				</div>
			</div>
			<button class="button" id='linearized_plot'>Plot</button>
		</div>
		<div class='general-container-content' style='min-width: 90%;'>
			<div style='margin: 0 auto;'>
				<input type="text" id="correlation_search" placeholder='Search...'>
				<div class='correlation_search_type correlation_search_selected' type='stock'>Stocks</div>
				<div class='correlation_search_type' type='index'>Indexes</div>
				<div class='correlation_search_type' type='currency'>Currencies</div>
				<div class='correlation_search_type' type='commodity'>Commodities</div>
				<div class='correlation_search_type' type='us_fundamental'>Fundamentals</div>
			</div>
			<div id="correlation_search_results" style='display: none;'>
				
			</div>
			<div id="correlation_selections">
				<span style='font-style: italic;'>No selections...</span>
			</div>
			<div style='margin: 0 auto;'>
				<div class='correlation_date_container'>
					<label>Start Date</label>
					<input type="date" id="startDate" <?php echo "value=\"".date("Y-m-d", time() - 31557600*4)."\""; ?>>
				</div>
				<div class='correlation_date_container'>
					<label>End Date</label>
					<input type="date" id="endDate" <?php echo "value=\"".date("Y-m-d", time())."\""; ?>>
				</div>
			</div>
			<button class="button" id='correlation_plot'>Plot</button>
		</div>
		<div class="general-container-content" style='min-width: 90%;'>
			<div id="fundamentals_graph" style=' height: 800px;'>
				<?php 
			$fund_analysis = mysqli_query($db, "SELECT output, date FROM economy WHERE script = 'Fundamentals Analysis' ORDER BY date DESC LIMIT 1") or die(mysqli_error($db));
				if ($row = mysqli_fetch_assoc($fund_analysis)) {
					$array = json_decode(($row['output']), true);
					if (is_array($array)) {
						echo $array['graph'];
					}
				}
				?>
			</div>
			<div style='margin: 0 auto;'>
				<div class='linearized_date_container'>
					<label>Start Date</label>
					<input type="date" id="startDateFund" <?php echo "value=\"".date("Y-m-d", time() - 31557600*4)."\""; ?>>
				</div>
				<div class='linearized_date_container'>
					<label>End Date</label>
					<input type="date" id="endDateFund" <?php echo "value=\"".date("Y-m-d", time())."\""; ?>>
				</div>
				<div class='linearized_date_container'>
					<label>Choose Country</label>
					<select id="fund_country">
						<?php 
							$query = mysqli_query($db, "SELECT type FROM items WHERE type LIKE '%fundamental%' GROUP BY type");
							confirmQuery($query);

							while($row = mysqli_fetch_assoc($query)){
								if($row['type'] == "us_fundamental"){
									echo "<option selected value='{$row['type']}'>".strtoupper(str_replace("_", " ", $row['type']))."</option>";
								}
								else{
									echo "<option value='{$row['type']}'>".strtoupper(str_replace("_", " ", $row['type']))."</option>";
								}
							}
						 ?>
					</select>
				</div>
			</div>
			<button class="button" id='fundamentals_button'>Change Date Range</button>
		</div>
	</div>
</div>
<div class='super-container' id='plot_results' style='width: 90%; display: none;'>
	<h1 class="super-container-header">Plot Results</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 class='general-container-selected' id='linearized_results'>Linearized Results</h1>
			<h1 id='correlation_results'>Correlations Results</h1>
		</nav>
		<div class="general-container-content" style='width: 90%'></div>
		<div class="general-container-content" style='width: 90%'></div>
	</div>
</div>
<div class='super-container' style='width: 90%;'>
	<h1 class="super-container-header">Notes</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 id='note_list_header' class='general-container-selected' onclick="getNotes('economy', this)"><i class="fas fa-sticky-note"></i>Notes</h1>
			<h1 id='note_add_header'><i class="fas fa-plus"></i>Add</h1>
			<h1 style='display: none;' id='note_edit_header'><i class="fas fa-pen-square"></i>Edit</h1>
		</nav>
		<div class='general-container-content' style='width: 80%'>
			<?php
			$economy_notes_table = $_SESSION["user"] . "_economy_notes";
			$notes = mysqli_query($db, "SELECT title, note, date, id FROM `{$economy_notes_table}` ORDER BY id ASC");
			if ($notes != null && mysqli_num_rows($notes) > 0) {
				while ($row = mysqli_fetch_assoc($notes)) {
					$note_date = $row['date'];
					$note_content = $row['note'];
					$note_title = strlen($row['title']) > 0 ? $row['title'] : strip_tags($row['note']);
					if ($note_title == null || strlen($note_title) <= 0) {
						$note_title = "New Note";
					}
					echo '<div class="note_container" note_id="'.$row['id'].'">
					<div class="note_header">
					<div class="note_title"><div>'.$note_title.'</div></div>
					<div class="note_date">'.$note_date.'</div>
					<div class="note_delete" onclick="deleteNote('.$row['id'].')" data-confirm="Do you really want to delete this note?"><i class="fas fa-trash" note_id="'.$row['id'].'"></i></div>
					<div class="note_edit" onclick="editNote(this)"><i class="fas fa-pen-square"  note_id="'.$row['id'].'"></i></div>				
					<div class="note_expand" onclick="expandNote(this)"><i class="fas fa-plus"></i></div>
					</div>
					<div class="editor note_content">'.$note_content.'</div>
					</div>';
				}	
			} else {
				echo "<span style='font-style: italic;'>Nothing here...</span>";
			}
			?>
		</div>
		<div class='general-container-content' style='width: 80%;'>
			<div class="add-note" style='height: 500px; margin-bottom: 15px; font-size: 18px;'></div>
			<input type="text" class="set_note_title" placeholder='Title...'>
			<button class="button" id='add_note'>Add Note</button>
		</div>
		<div class='general-container-content' style='width: 80%;'>
			<div class="note_editor" style='height: 500px; margin-bottom: 15px; font-size: 18px;'></div>
			<input type="text" class="set_note_title" placeholder='Title...'>
			<button class="button" id='edit_note'>Confirm</button>
		</div>
	</div>
</div>
<div class='super-container' style='width: 90%;'>
	<h1 class="super-container-header">Economy Health</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav style='display: none;'>
			<h1 class='general-container-selected'></h1>
		</nav>
		<div class='general-container-content'>
			<div id="economy_health">
				<h3 style="font-size: 20px; color: #565656;">Recommended Distribution</h3>
				<?php 
				$economy_health = mysqli_query($db, "SELECT economy_health FROM users WHERE username = '".$_SESSION['user']."'");
				confirmQuery($economy_health);
				$economy_health = mysqli_fetch_all($economy_health);
				$economy_health = json_decode($economy_health[0][0], true);
				$get_recommended_dist = $economy_health['distribution'][1];

				$get_answers = $economy_health['answers'];
				?>
				<div class="rate_container" style="margin: 15px;">
					<div class="rate_text">Stocks</div>
					<div style="background: #d8d8d8; color: #5a5a5a;font-weight: 400; padding: 0 10px;"><?php echo $get_recommended_dist[0]; ?></div>
				</div>
				<div class="rate_container" style="margin: 15px;">
					<div class="rate_text">Cash</div>
					<div style="background: #d8d8d8; color: #5a5a5a;font-weight: 400; padding: 0 10px;"><?php echo $get_recommended_dist[1]; ?></div>
				</div>
				<div class="rate_container" style="margin: 15px;">
					<div class="rate_text">Bonds</div>
					<div style="background: #d8d8d8; color: #5a5a5a;font-weight: 400; padding: 0 10px;"><?php echo $get_recommended_dist[2]; ?></div>
				</div>
				<div class="rate_container" style="margin: 15px;">
					<div class="rate_text">Others</div>
					<div style="background: #d8d8d8; color: #5a5a5a;font-weight: 400; padding: 0 10px;"><?php echo $get_recommended_dist[3]; ?></div>
				</div>
			</div>
			<button class="button" id="economy_questionnaire_button" style="margin: 20px auto;"><i class="fab fa-wpforms"></i>
				Review Economy
			</button>
		</div>
	</div>
</div>
<div style="display: none" id="economy_questionnaire">
	<div class="question_container">
		<h1>Are interest rates high?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_1" <?php echo $get_answers[0] == 1 ? 'checked' : '';?> >
		<input type="radio" value='2' name="question_1"<?php echo $get_answers[0] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_1"<?php echo $get_answers[0] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_1"<?php echo $get_answers[0] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_1"<?php echo $get_answers[0] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Are interest rates rising?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_2" <?php echo $get_answers[1] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_2" <?php echo $get_answers[1] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_2" <?php echo $get_answers[1] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_2" <?php echo $get_answers[1] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_2" <?php echo $get_answers[1] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Have interest rates been low for a long time?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_3" <?php echo $get_answers[2] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_3" <?php echo $get_answers[2] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_3" <?php echo $get_answers[2] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_3" <?php echo $get_answers[2] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_3" <?php echo $get_answers[2] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Is inflation high?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_4" <?php echo $get_answers[3] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_4" <?php echo $get_answers[3] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_4" <?php echo $get_answers[3] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_4" <?php echo $get_answers[3] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_4" <?php echo $get_answers[3] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Is inflation rising?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_5" <?php echo $get_answers[4] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_5" <?php echo $get_answers[4] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_5" <?php echo $get_answers[4] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_5" <?php echo $get_answers[4] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_5" <?php echo $get_answers[4] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Are we at full employment?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_6" <?php echo $get_answers[5] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_6" <?php echo $get_answers[5] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_6" <?php echo $get_answers[5] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_6" <?php echo $get_answers[5] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_6" <?php echo $get_answers[5] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Is unemployment decreasing?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_7" <?php echo $get_answers[6] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_7" <?php echo $get_answers[6] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_7" <?php echo $get_answers[6] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_7" <?php echo $get_answers[6] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_7" <?php echo $get_answers[6] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Are asset prices (stocks, real estate) hitting new highs?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_8" <?php echo $get_answers[7] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_8" <?php echo $get_answers[7] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_8" <?php echo $get_answers[7] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_8" <?php echo $get_answers[7] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_8" <?php echo $get_answers[7] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Is the stock market at historically high PEs?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_9" <?php echo $get_answers[8] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_9" <?php echo $get_answers[8] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_9" <?php echo $get_answers[8] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_9" <?php echo $get_answers[8] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_9" <?php echo $get_answers[8] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Have we been in a bull market for more than four years?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_10" <?php echo $get_answers[9] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_10" <?php echo $get_answers[9] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_10" <?php echo $get_answers[9] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_10" <?php echo $get_answers[9] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_10" <?php echo $get_answers[9] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Are experts expecting a recession in the next two years?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_11" <?php echo $get_answers[10] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_11" <?php echo $get_answers[10] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_11" <?php echo $get_answers[10] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_11" <?php echo $get_answers[10] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_11" <?php echo $get_answers[10] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Do you have any idea of why you should get exposure to the stock market?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_12" <?php echo $get_answers[11] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_12" <?php echo $get_answers[11] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_12" <?php echo $get_answers[11] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_12" <?php echo $get_answers[11] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_12" <?php echo $get_answers[11] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Do you have any idea of why you should get more exposure to commodities or Forex?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_13" <?php echo $get_answers[12] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_13" <?php echo $get_answers[12] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_13" <?php echo $get_answers[12] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_13" <?php echo $get_answers[12] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_13" <?php echo $get_answers[12] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<div class="question_container">
		<h1>Do you have any idea of why you should get more exposure to bonds or cash?</h1>
		<label>Not at all</label>
		<input type="radio" value='1' name="question_14" <?php echo $get_answers[13] == 1 ? 'checked' : '';?>>
		<input type="radio" value='2' name="question_14" <?php echo $get_answers[13] == 2 ? 'checked' : '';?>>
		<input type="radio" value='3' name="question_14" <?php echo $get_answers[13] == 3 ? 'checked' : '';?>>
		<input type="radio" value='4' name="question_14" <?php echo $get_answers[13] == 4 ? 'checked' : '';?>>
		<input type="radio" value='5' name="question_14" <?php echo $get_answers[13] == 5 ? 'checked' : '';?>>
		<label>Strong Yes</label>
	</div>
	<button class="button" id="submit_questionnaire">Submit</button>
</div>
<script type="text/javascript">
	function verifyAllSelected(){
		for (var i = 1; i <= $(".question_container").length; i++) {
			if($("input[name='question_"+i+"']:checked").val() == null){
				return false;
			}
		}

		return true;
	}
	//ECONOMY QUESTIONNAIRE
	$("#economy_questionnaire_button").click(function(){
		var questionnaire_modal = new jBox('Modal', {
			content: $("#economy_questionnaire")
		}); 
		questionnaire_modal.open();
		questionnaire_modal.position();

		//ECONOMY QUESTIONNAIRE
		$("#submit_questionnaire").unbind("click");
		$("#submit_questionnaire").click(function(){
			if(verifyAllSelected()){
				var questions_array = [];

				for (var i = 1; i <= $(".question_container").length; i++) {
					questions_array[i-1] = $("input[name='question_"+i+"']:checked").val();
				}

				$.post("/php_dependancies/economy_questionnaire.php", {questions_array: JSON.stringify(questions_array)}, function(data){
					if(data != null && $.parseJSON(data).length == 4){
						data = $.parseJSON(data);
						$(".rate_container:eq(0)").children("div:eq(1)").html(data[0]);
						$(".rate_container:eq(1)").children("div:eq(1)").html(data[1]);
						$(".rate_container:eq(2)").children("div:eq(1)").html(data[2]);
						$(".rate_container:eq(3)").children("div:eq(1)").html(data[3]);
						questionnaire_modal.close();
					}
					else{
						alert("Error.");
					}
				});
			}
			else{
				alert("You haven't answered all the questions in the questionnaire.");
			}
		});
	});
</script>
<script>
	var editorCounter = 0;
	var toolbarOptions = [
	  ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
	  ['blockquote', 'code-block'],

	  [{ 'header': 1 }, { 'header': 2 }],               // custom button values
	  [{ 'list': 'ordered'}, { 'list': 'bullet' }],
	  [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
	  [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
	  [{ 'direction': 'rtl' }],                         // text direction

	  [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
	  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

	  [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
	  [{ 'align': [] }],

	  ['clean'],                                         // remove formatting button
	  ['link', 'image']
	  ];
	  var noteAction = 0;
	  function generateEditors() {
	  	$(".editor").each(function() {
	  		$(this).addClass("editor"+editorCounter);
	  		if ($(this).hasClass("note_content")) {
	  			new Quill((".editor"+editorCounter), {
	  				theme: 'snow',
	  				readOnly: true,
	  				modules: {
	  					toolbar: false
	  				}
	  			});
	  		} else {
	  			new Quill((".editor"+editorCounter), {
	  				theme: 'snow',
	  				modules: {
	  					toolbar: toolbarOptions
	  				},
	  				placeholder: 'Note...'
	  			});
	  		}
	  		editorCounter++;
	  	});
	  }
	  function notesToHTML(notes) {
	  	var HTMLNotes = "";
	  	for (var i = 0; i < notes.length; i++) {
	  		var title = notes[i]['title'].trim().length > 0 ? notes[i]['title'] : $(notes[i]['note']).text();
	  		if (title == null || title.trim().length <= 0) {
	  			title = "New Note";
	  		}
	  		HTMLNotes += '<div class="note_container" note_id="'+notes[i]['id']+'">';
	  		HTMLNotes += '<div class="note_header">';
	  		HTMLNotes += '<div class="note_title"><div>'+title+'</div></div>';
	  		HTMLNotes += '<div class="note_date">'+notes[i]['date']+'</div>';
	  		HTMLNotes += '<div class="note_delete" onclick="deleteNote('+notes[i]['id']+')" data-confirm="Do you really want to delete this note?"><i class="fas fa-trash" note_id="'+notes[i]['id']+'"></i></div>';
	  		HTMLNotes += '<div class="note_edit" onclick="editNote(this)"><i class="fas fa-pen-square" note_id="'+notes[i]['id']+'"></i></div>';			
	  		HTMLNotes += '<div class="note_expand" onclick="expandNote(this)"><i class="fas fa-plus"></i></div>';
	  		HTMLNotes += '</div>';
	  		HTMLNotes += '<div class="editor note_content">'+notes[i]['note']+'</div>';
	  		HTMLNotes += '</div>';
	  	}
	  	return HTMLNotes;
	  }
	  function getNotes(type, element) {
	  	if (noteAction == 0) {
	  		noteAction = 1;
	  		$(".set_note_title").val("");
	  		var tabHeaderIndex = $(element).index();
	  		var tabContent = $(element).parent().parent().children("div:eq("+tabHeaderIndex+")");
	  		tabContent.html("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='notes_loading'>");
	  		$.post("/php_dependancies/get_notes.php", {type: "economy"}, function(data) {
	  			if (data != null && data.length > 0) {
	  				var notes = $.parseJSON(data);
	  				if (notes.length > 0) {
	  					tabContent.html(notesToHTML(notes));
	  					generateEditors();
	  					new jBox('Confirm', {
	  						confirmButton: 'Yes',
	  						cancelButton: 'No'
	  					});
	  				} else {
	  					tabContent.html("<span style='font-style: italic;'>Nothing here...</span>");
	  				}
	  			} else {
	  				alert("There was an issue fetching your notes!");
	  			}
	  			$("#notes_loading").remove();
	  			noteAction = 0;
	  		});
	  	}
	  }
	  function expandNote(element) {
	  	$(element).parent().next(".note_content").slideFadeToggle(250);
	  	if ($(element).html().indexOf("plus") > 0) {
	  		$(element).html($(element).html().replace("plus", "minus"));
	  	} else {
	  		$(element).html($(element).html().replace("minus", "plus"));
	  	}
	  }
	  function editNote(element) {
	  	var noteContent = $(element).parent().next(".note_content").find(".ql-editor").html();
	  	var note_id = $(element).find("i").attr("note_id");
	  	$("#note_edit_header").show();
	  	$("#note_edit_header").trigger("click");
	  	$("#edit_note").attr("note_id", note_id);
	  	$(".note_editor .ql-editor").html(noteContent);
	  }
	  function deleteNote(note_id) {
	  	$.post("/php_dependancies/delete_economy_note.php", {note_id: note_id}, function(data) {
	  		if (data == "success") {
	  			$(".note_container[note_id="+note_id+"]").slideFadeToggle(500, function() {
	  				$(".note_container[note_id="+note_id+"]").remove();
	  			});
	  		} else {
	  			alert("Error!");
	  			console.log(data);
	  		}
	  	});
	  }
	  $(document).ready(function() {
		//Generate editors
		new Quill((".note_editor"), {
			theme: 'snow',
			modules: {
				toolbar: toolbarOptions
			},
			placeholder: 'Note...'
		});
		new Quill((".add-note"), {
			theme: 'snow',
			modules: {
				toolbar: toolbarOptions
			},
			placeholder: 'Note...'
		});
		generateEditors();

		//Generate jBox
		new jBox('Confirm', {
			confirmButton: 'Yes',
			cancelButton: 'No'
		});

		//Add & Edit note
		$("#add_note").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='add_note_loading'>");
			var noteContent = $(".add-note .ql-editor").html();
			$.post("/php_dependancies/add_economy_note.php", {note: noteContent, title: $(".add-note").next(".set_note_title").val().trim()}, function(data) {
				if (data == "success") {
					$(".add-note .ql-editor").html("");
					$("#note_list_header").trigger("click");
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#add_note_loading").remove();
				$("#add_note").show();
			});
		});
		$("#edit_note").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='edit_note_loading'>");
			$.post("/php_dependancies/edit_economy_note.php", {note_id: $(this).attr("note_id"), note_content: $(".note_editor .ql-editor").html(), title: $(".note_editor").next(".set_note_title").val().trim()}, function(data) {
				if (data == "success") {
					$(".note_editor .ql-editor").html("");
					$("#note_list_header").trigger("click");
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#edit_note_loading").remove();
				$("#edit_note").show();
				$("#note_edit_header").hide();
			});
		});
	});
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>