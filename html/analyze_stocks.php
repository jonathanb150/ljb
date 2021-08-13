<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script src="https://cdn.quilljs.com/1.1.9/quill.js"></script>
<link href="https://cdn.quilljs.com/1.1.9/quill.snow.css" rel="stylesheet">
<?php
// system('rm -r /var/www/ljb.solutions/html/graphs/*');
// die();
$error = "";
if(isset($_POST["analyze"]) && isset($_POST["selected_item"])){
	$item_symbol = mysqli_escape_string($db, trim($_POST["selected_item"]));
	$item_table = mysqli_query($db, "SELECT tableName, name FROM items WHERE symbol = '{$item_symbol}'");
	confirmQuery($item_table);
	if(mysqli_num_rows($item_table) == 1){
		$item_table = mysqli_fetch_all($item_table);
		$item_name = $item_table[0][1];
		$item_table = $item_table[0][0];
	}
	if (!isset($_POST["start_date"]) || strlen($_POST["start_date"]) <= 0) {
		$error = "Please select a start date.";
	} else if (!isset($_POST["end_date"]) || strlen($_POST["end_date"]) <= 0) {
		$error = "Please select an end date.";
	} else if (isset($_POST["which_pe"]) && isset($_POST["custom_pe"])){
		$pe_1 = (float) ($_POST["which_pe"] == "1d" ? $_POST["1d"] : ($_POST["which_pe"] == "1y" ? $_POST["1y"] : $_POST["3y"]));
		$pe_2 = (float) (empty($_POST["custom_pe"]) ? 0 : $_POST["custom_pe"]);
	}
}
else if(isset($_POST["analyze"]) && !isset($_POST["selected_item"])){
	$error="Please select an item to analyze.";
}
?>

<form class="analyze" onsubmit="return analyzeForm();" method="POST">
	<h1><i class="fas fa-search"></i>Search Stock</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<?php 
	if ((!isset($_POST['analyze']) || isset($_POST['resubmit'])) && isset($_GET['item']) && strlen($_GET['item']) > 0) {
		$check_get = mysqli_query($db, "SELECT tableName, symbol, name FROM items WHERE symbol = '{$_GET['item']}'");
		confirmQuery($check_get);
		if (mysqli_num_rows($check_get) > 0 && $row = mysqli_fetch_assoc($check_get)) {
			$get = $row['symbol']." - ".$row['name'];
			if (!isset($_POST['resubmit'])) {
				echo "<input type='hidden' name='selected_item' value='{$row['symbol']}'>";
				echo "<input type='hidden' name='selected_table' value='{$row['tableName']}'>";
				echo "<input type='hidden' name='selected_name' value='{$row['name']}'>";
			}
		}
	}
	?>
	<div id="search_results" <?php if (isset($get)) { echo "style='display: inline-block'";}?>>
		<?php 
			if (isset($get)) {
				echo "<div style='padding: 10px; font-weight: 500; cursor: pointer; transition: all 0.1s; background: rgb(173, 173, 173);'' class='selected'>{$get}</div>";
			}
		?>
	</div>
	<h1><i class="fas fa-table"></i>Select Date Range</h1>
	<div class='date_container'>Start Date<input type="date" name="start_date" <?php if (isset($_POST["start_date"]) && strlen($_POST["start_date"]) > 0) {echo "value='{$_POST["start_date"]}'";} else { echo "value='".date("Y-m-d", time() - 31557600*4)."'";} ?>></div>
	<div class='date_container'>End Date<input type="date" name="end_date" <?php if (isset($_POST["end_date"]) && strlen($_POST["end_date"]) > 0) {echo "value='{$_POST["end_date"]}'";} else { echo "value='".date("Y-m-d", time())."'";} ?>></div>
	<h1><i class="fas fa-percentage"></i>Set P/E Ratio</h1>
	<div id="peratio_container">
		<div class="peratio_division">
			<div class="pe_header">End Date</div>
			<img class="ajax_loader" src="/img/ajax-loader-2.gif">
			<input type="text" name="1d" id="1d" readonly>
			<label>Use this</label><input type="radio" name="which_pe" value="1d">
			<span>Select stock...</span>
		</div
		><div class="peratio_division">
			<div class="pe_header">1 Year Average</div>
			<img class="ajax_loader" src="/img/ajax-loader-2.gif">
			<input type="text" name="1y" id="1y" readonly>
			<label>Use this</label><input type="radio" name="which_pe" value="1y" checked>
			<span>Select stock...</span>
		</div
		><div class="peratio_division">
			<div class="pe_header">3 Year Average</div>
			<img class="ajax_loader" src="/img/ajax-loader-2.gif">
			<input type="text" name="3y" id="3y" readonly>
			<label>Use this</label><input type="radio" name="which_pe" value="3y">
			<span>Select stock...</span>
		</div
		><div class="peratio_division">
			<div class="pe_header">Set Manually</div>
			<input type="text" name="custom_pe" id="custom_pe" autocomplete="off">
		</div>
	</div>
	<h1 style="margin-bottom: 20px"><i class="fas fa-server"></i>Tangible Assets</h1>
	<input type="checkbox" name="tangible" id="tangible">
	<label class='checkbox_label' for="tangible"></label>
	<div></div>
	<input type="submit" name="analyze" class="button" value="Analyze">
	<?php 
	if(empty($error) && isset($_POST["selected_item"]) && isset($_POST["start_date"]) && isset($_POST["end_date"])){
		echo "<input type='hidden' name='selected_item' value='{$_POST['selected_item']}'>";
		echo "<input type='hidden' name='selected_table' value='".$item_table."'>";
		echo "<input type='hidden' name='selected_start_date_2' value='".date("Y-m-d", (strtotime($_POST['start_date']) - 31557600))."'>";
		echo "<input type='hidden' name='selected_start_date' value='{$_POST['start_date']}'>";
		echo "<input type='hidden' name='selected_end_date' value='{$_POST['end_date']}'>";
		echo "<input type='hidden' name='selected_pe_1' value='{$pe_1}'>";
		echo "<input type='hidden' name='selected_pe_2' value='{$pe_2}'>";
		echo "<input type='hidden' name='selected_tangible' value='".(isset($_POST['tangible']) ? 'true' : "")."'>";
		echo "<input type='hidden' name='selected_name' value='{$item_name}'>";
	}
	?>
	
</form>
<?php 
if(!empty($error)){
	echo "<div class='super-container'><h1 class='super-container-header'>Error</h1><div></div><div class='general-container' style='margin: 0 auto; width: 100%;'><nav><h1 class='general-container-selected' style='display: none;'></h1></nav><div class='general-container-content'>{$error}</div></div></div>";
	require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php");
	die();
}
?>
<div id="analysis_results" class="super-container" style='width: 90%; margin: 30px auto 0 auto; display: <?php echo isset($_POST["selected_item"]) ? "block" : "none"; ?>'>
	<?php 
	if(isset($_POST["selected_item"])){
		$header = (onWatchlist($db, $_SESSION['user'], $_POST["selected_item"]) ? "<i class='far fa-eye' style='margin-right: 10px;'></i>" : "").(onPortfolio($db, $_SESSION['user'], $_POST["selected_item"]) ? "<i class='fas fa-briefcase' style='margin-right: 10px;'></i>" : "")."{$item_name} Analysis<img src='/img/ajax-loader.gif' style='margin-left: 10px; vertical-align: middle;' id='analysis_loader'>";
	}
	?>
	<h1 class="super-container-header"><?php echo $header; ?></h1>
	<div></div>
	<div class="general-container" style='margin: 0 auto; width: 100%;'>
		<nav>
			<h1 class='general-container-selected'><i class="fas fa-chart-area"></i>Overview</h1>
			<h1><i class="fas fa-file-invoice-dollar"></i>Financials</h1>
			<h1><i class="fas fa-book"></i>Historical</h1>
			<h1><i class="fas fa-chart-area"></i>Statistical</h1>
			<h1><i class="fas fa-star-half-alt"></i>LJB Score</h1>
			<h1><i class="fas fa-calculator"></i>Entry Points</h1>
			<h1><i class="fas fa-folder-plus"></i>Actions</h1>
			<h1 id='note_list_header' onclick="getNotes('economy', this)"><i class="fas fa-sticky-note"></i>Notes</h1>
			<h1 id='note_add_header'><i class="fas fa-plus"></i>Add Note</h1>
			<h1 style='display: none;' id='note_edit_header'><i class="fas fa-pen-square"></i>Edit</h1>
			<h1 id='attachments_header' onclick="getAttachments(this)"><i class="fas fa-file-alt"></i>Attachments</h1>
			<h1><i class='fas fa-tags'></i>Tags</h1>
		</nav>
		<div id="overview" class='general-container-content' style='width: 100%;'><img id="overview_loader" src="/img/ajax-loader-2.gif"></div>
		<div id="financials"class='general-container-content' style='width: 100%;'><img id="financials_loader" src="/img/ajax-loader-2.gif">
			<?php
			if (empty($error) && isset($_POST['selected_item'])) {
				echo "<h3>SEC Forms</h3><a target = '_blank' href = 'https://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&CIK={$_POST['selected_item']}&type=10-k&dateb=&owner=exclude&count=40'><button class = 'button' style='margin: 15px auto;'>View SEC Forms</button></a>";
			}
			?>
		</div>
		<div id="historical"class='general-container-content' style='width: 100%;'><img id="historical_loader" src="/img/ajax-loader-2.gif"></div>
		<div id="statistical" class='general-container-content' style='width: 100%;'><img id="statistical_loader" src="/img/ajax-loader-2.gif"></div>
		<div class='general-container-content' style='width: 100%;'>
			<img id="ljb_loader" src="/img/ajax-loader-2.gif">
			<div id="ljb_container">
				<div style="margin: -15px 0 30px 0;background: #f1f1f1;"><div style="font-size: 55px;display: inline-block; vertical-align: middle;font-weight: 500;color: #989898;margin-right: 10px;">1</div><div style="font-size: 35px;font-weight: 100;display: inline-block;vertical-align: middle;color: #b3b3b3;">Compare stock to competitors</div></div>
				<div style='margin: 0 auto;'>
					<input type="text" id="linearized_search" placeholder='Compare to...'>
					<div class='linearized_search_type linearized_search_selected' type='stock'>Stocks</div>
					<div class='linearized_search_type' type='sector'>Sectors</div>
				</div>
				<div id="linearized_search_results" style='display: none;'>
					
				</div>
				<div id="linearized_selections" style='width: 80%'>
					<span style='font-style: italic;'>No selections...</span>
				</div>
				<div id="ljb_score_container" style='margin-top: 15px; width: 100%;'>
					<h2 style='padding: 0; font-weight: 300; font-size: 30px; text-transform: none; color: #bbbbbb; margin-bottom: -20px;'>Metrics for
						<?php
						if(isset($_POST['selected_item'])){
							echo " ".$_POST['selected_item'];
						}
						?>	
					</h2>
				</div>
				<button class="button" id='compare_stock' style='margin-top: 30px;'>Compare</button>
				<div style="margin: 30px 0;background: #f1f1f1;"><div style="font-size: 55px;display: inline-block; vertical-align: middle;font-weight: 500;color: #989898;margin-right: 10px;">2</div><div style="font-size: 35px;font-weight: 100;display: inline-block;vertical-align: middle;color: #b3b3b3;">Rate the company</div></div>
				<div id="rate_stock"></div>
				<div style="margin: 30px 0;background: #f1f1f1;"><div style="font-size: 55px;display: inline-block; vertical-align: middle;font-weight: 500;color: #989898;margin-right: 10px;">3</div><div style="font-size: 35px;font-weight: 100;display: inline-block;vertical-align: middle;color: #b3b3b3;">Calculate LJB Score</div></div>
				<button class="button" id='ljb_score' style='margin-top: 30px; margin-bottom: 15px;'>Calculate</button>
				<div id="LJBFinalContainer" style='display: none;'></div>
			</div>
		</div>
		<div id="entry_points"class='general-container-content' style='width: 100%;'>
			<label style="font-weight: 500;margin: 0 auto 10px auto;display: block; font-size: 24px;">Current Price</label>
			<input type="text" class="input" value="0" id="current_price" readonly>
			<label style="font-weight: 500;margin: 10px auto;display: block; font-size: 24px;">Total Capital</label>
			<input type="text" class="input" value="0" id="total_capital">
			<table>
				<thead>
					<th>Positions</th>
					<th>%</th>
					<th>$</th>
					<th>Entry Price</th>
					<th>Profit</th>
					<th>Loss</th>
					<th>Risk/Reward</th>
				</thead>
				<tbody>
					<tr>
						<td>Entry 1</td>
						<td><input type="text" value="0" id="entry_1_percent"></td>
						<td><input type="text" value="0" id="entry_1_capital"></td>
						<td><input type="text" value="0" id="entry_1_price"></td>
						<td id="profit_1">0</td>
						<td id="loss_1">0</td>
						<td id="risk_reward_1">0</td>
					</tr>
					<tr>
						<td>Entry 2</td>
						<td><input type="text" value="0" id="entry_2_percent"></td>
						<td><input type="text" value="0" id="entry_2_capital"></td>
						<td><input type="text" value="0" id="entry_2_price"></td>
						<td id="profit_2">0</td>
						<td id="loss_2">0</td>
						<td id="risk_reward_2">0</td>
					</tr>	
					<tr>
						<td>Entry 3</td>
						<td><input type="text" value="0" id="entry_3_percent"></td>
						<td><input type="text" value="0" id="entry_3_capital"></td>
						<td><input type="text" value="0" id="entry_3_price"></td>
						<td id="profit_3">0</td>
						<td id="loss_3">0</td>
						<td id="risk_reward_3">0</td>
					</tr>	
				</tbody>
			</table>
			<ul>
				<li><label>Best Case</label> <input type="text" id="best_case" class="input" value="0" style="margin-bottom: 20px"></li>
				<li><label>Worst Case</label> <input type="text" id="worst_case" class="input" value="0"></li>
			</ul>
			<ul>
				<li><label style="display: block">Total Return</label><input id="best_return_capital" type="text" class="input" value="$0" readonly style="display: inline-block; margin: 0 10px 10px 10px"><input id="best_return_percent" type="text" class="input" value="0%" readonly style="display: inline-block; margin: 0 10px 10px 10px"></li>
				<li><label style="display: block">Total Return</label><input id="worst_return_capital" type="text" class="input" value="$0" readonly style="display: inline-block; margin: 0 10px 10px 10px"><input id="worst_return_percent" type="text" class="input" value="0%" readonly style="display: inline-block; margin: 0 10px 10px 10px"></li>
			</ul>
			<ul>
				<li><label style="display: block">Risk/Reward</label><input id="risk_reward_total" type="text" class="input" value="N/A" readonly style="display: inline-block; margin: 0 10px 10px 10px"></li>
			</ul>
		</div>
		<script src="/js/entry_calculator.js"></script>
		<div id="actions" class='general-container-content' style='width: 100%;'>
			<?php 
			if(empty($error) && isset($_POST['selected_item'])){
				echo "<button class='add-to' id='add_watchlist'><i class='far fa-eye' style='margin-right: 5px;'></i>ADD TO WATCHLIST</button>";
				echo "<form onsubmit='return addToPortfolio()' class='add-to-form' id='add_portfolio' method='POST' action='/add_to_portfolio.php'><button class='add-to'><i class='fas fa-clipboard-list' style='margin-right: 5px;'></i>ADD TO PORTFOLIO</button></form>";
				echo "<div>";
				if(hasGoodFundamentals($db, $_POST['selected_item'])){
					echo "<p style='margin-top: 20px; text-align: center'>Current Fundamentals Status: <span style='color: #4bbd7e; font-weight: 700;'>Good</span></p>";
				}
				else{
					echo "<p style='margin-top: 20px; text-align: center'>Current Fundamentals Status: <span style='color: #bd4b4b; font-weight: 700;'>Bad</span></p>";
				}
				echo "<button class='button' style='margin-top: 20px; background: #bd4b4b;' onclick='markFundamentals(\"{$_POST['selected_item']}\", 0, this)'>Mark Fundamentals as Bad</button>";
				echo "<button class='button' style='margin-top: 20px; background: #4bbd7e' onclick='markFundamentals(\"{$_POST['selected_item']}\", 1, this)'>Mark Fundamentals as Good</button>";
				echo "</div>";
			}
			?>
			<?php 
			$query = mysqli_query($db, "SELECT fundamentals_status FROM items WHERE symbol = '{$_POST['selected_item']}' OR apiTicker = '{$_POST['selected_item']}'") or die(mysqli_error($db));

			if($row = mysqli_fetch_assoc($query)) {
				$json = json_decode($row['fundamentals_status'], true);

				if(is_array($json) && count($json) > 0) {
					$data = [];

					for ($i=0; $i < count($json); $i++) { 
						$data[] = Array(t=>$json[$i]["date"], y=>$json[$i]["value"]);
					}

					$data = json_encode($data);

					echo '<canvas id="mark_fundamentals_chart" width="800" height="400"></canvas>';
				}
			}
			?>
			<script>
				if($("#mark_fundamentals_chart").length == 1 && "<?php echo $data ?>" != "") {
					new Chart($("#mark_fundamentals_chart"), {
						type: "line",
						data: {
							datasets: [{
								label: "Fundamentals Status",
								data: <?php echo $data ?>
							}]
						},
						options: {
							responsive: false,
							title: {
								display: true,
								text: 'Fundamentals Status History'
							},
							scales: {
								xAxes: [{
									type: 'time',
									time: {
										unit: 'week'
									},
									distribution: 'series',
									display: true,
									ticks: {
										source: 'data'
									}
								}],
								yAxes: [{
									display: true,
									ticks: {
										stepSize: 1.0,
										max: 1
									}
								}]
							},
							tooltips: {
								mode: 'index'
							}
						}
					});
				}
			</script>
		</div>
		<div class='general-container-content' style='width: 80%'>
			<?php
				$item_notes_table = $_SESSION["user"] . "_item_notes";
				$notes = mysqli_query($db, "SELECT title, note, date, id FROM `{$item_notes_table}` WHERE item = '{$selectedStock}' ORDER BY id ASC");
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
		<div class='general-container-content' style='width: 80%'>
			<input type="file" id="note_attachment">
			<button class="button" id='attachment_upload' style = "margin: 15px auto; display: block;" <?php if(isset($_POST['selected_item'])){echo "item = '{$_POST['selected_item']}'";} ?>>Upload</button>
		</div>
		<div class="general-container-content" id="tags_container" style="width: 80%">
			<div>
				<h3>Tags</h3>
				<ul id="tags_list">
					<?php 
					$tags_table = $_SESSION['user']."_tags";

					if(tableExists($db, $tags_table)){
						$get_tags = mysqli_query($db, "SELECT tag FROM `{$tags_table}`");
						confirmQuery($get_tags);

						while($row = mysqli_fetch_assoc($get_tags)){
							echo "<li><span onclick='addTag(this, \"add\");'>{$row['tag']}</span><span onclick='removeTag(this,\"{$row['tag']}\")'>x</span></li>";
						}
					}
					?>
				</ul>
				<input type="text" class="input" style="font-size: 18px; margin: 15px auto 10px auto;">
				<button class="button" id="add_tag">Add Tag</button>
			</div>
			<div>
				<h3>Selected Tags</h3>
				<ul id="selected_tags">
					<?php 
						if(isset($item_symbol)){
							$get_tags = mysqli_query($db, "SELECT tags FROM items WHERE symbol = '{$item_symbol}'");
							confirmQuery($get_tags);

							if($row = mysqli_fetch_assoc($get_tags)){
								$tags_array = json_decode($row['tags']);

								for ($i=0; $i < count($tags_array); $i++) { 
									echo "<li><span onclick='addTag(this, \"remove\");'>{$tags_array[$i]}</span></li>";
								}
							}
						}
					?>
				</ul>
			</div>
			<div id="tags_match_table" style="width: 80%;"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$("#add_tag").click(function() {
		var tag = $(this).prev("input").val().trim();

		if(tag.length > 0){
			$(this).parent().find("ul").prepend("<li><span onclick='addTag(this, \"add\");'>"+tag+"</span><span onclick='removeTag(this,\""+tag+"\")'>x</span></li>");

			$.post("/php_dependancies/tags_actions.php", {add_tag: tag}, function(data){});
		}
		else{
			alert("Please input the tag.");
		}
	});

	$("li span").hover(function() {
		if($(this).parent().find("span").length == 2 && $(this).index() == 1){
			$(this).prev("span").css("background", "#c54242");
		}
	}, function() {
		if($(this).parent().find("span").length == 2 && $(this).index() == 1){
			$(this).prev("span").css("background", "gray");
		}
	});

	function removeTag(element, tag) {
		$(element).parent().fadeOut(250, function() {
			$(element).parent().remove();
		});
		$.post("/php_dependancies/tags_actions.php", {delete_tag: tag}, function(data){
			$("#tags_match_table").html(data);
			tags_modal.position();
		});
	}

	function addTag(element, action) {
		if(action == "add" && !verifySelectedTag($(element).text().trim())){
			$("#selected_tags").prepend("<li><span onclick='addTag(this, \"remove\")'>"+$(element).text().trim()+"</span></li>");
		}
		else if(!verifyTag($(element).text().trim())){
			$("#tags_list").prepend("<li><span onclick='addTag(this, \"add\")'>"+$(element).text().trim()+"</span><span onclick='removeTag(this,\'"+$(element).text().trim()+"\')'>x</span></li>");
		}
		$(element).parent().remove();

		$.post("/php_dependancies/tags_actions.php", {item: $("input[name='selected_item']").val(), current_tags: getSelectedTags()}, function(data){});
	}

	function getSelectedTags(){
		var current_tags = [];
		for (var i = 0; i < $("#selected_tags li").length; i++) {
			current_tags.push($("#selected_tags li:eq("+i+")").text().trim()); 
		}
		current_tags = JSON.stringify(current_tags);

		return current_tags;
	}

	function verifyTag(tag){
		for (var i = 0; i < $("#tags_list li").length; i++) {
			if($("#tags_list li:eq("+i+")").find("span:eq(0)").text().trim() == tag){
				return true;
			} 
		}

		return false;
	}

	function verifySelectedTag(tag){
		for (var i = 0; i < $("#selected_tags li").length; i++) {
			if($("#selected_tags li:eq("+i+")").find("span:eq(0)").text().trim() == tag){
				return true;
			} 
		}

		return false;
	}
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>
<script src="/js/item_search.js"></script>
<link href="/css/LJBRater.css" rel="stylesheet">
<script src="/js/LJBRater.js"></script>
<script type="text/javascript">
	//ITEM SEARCH
	itemSearch("stock", $("input[name='item_search']"), $("#search_results"));
	//FORM SUBMIT
	function analyzeForm() {
		if ($("input[name='selected_item']").length == 1) {
			$(".analyze").attr("action", "/analyze_stocks.php?item="+$("input[name='selected_item']").val()+"#analysis_results");
			$(".analyze").append("<input type='hidden' name='resubmit'>");
			return true;
		} else {
			alert('Please select an item to analyze.');
			return false;
		}
		
	}
	//PYTHON
	if($("input[name='selected_item']").length == 1 && $("input[name='selected_start_date']").length == 1 && $("input[name='selected_end_date']").length == 1 && $("input[name='selected_tangible']").length == 1 && $("input[name='selected_pe_1']").length == 1){
		var analysis_loader = setInterval(function() {
			if ($("#ljb_loader").length == 0 && $("#overview_loader").length == 0 && $("#financials_loader").length == 0 && $("#historical_loader").length == 0 && $("#statistical_loader").length == 0) {
				$("#analysis_loader").remove();
				clearInterval(analysis_loader);
			}
		}, 100);
		var fundamental_query = "/algorithms/LongTerm/fundamentalAnalysisStocks.py '"+$("input[name='selected_item']").val()+"' "+$("input[name='selected_pe_1']").val()+" "+$("input[name='selected_pe_2']").val()+" '"+$("input[name='selected_tangible']").val()+"' '"+$("input[name='selected_start_date_2']").val()+"' '"+$("input[name='selected_end_date']").val()+"'";
		var technical_query = "/algorithms/LongTerm/TechnicalAnalysisStocks.py '"+$("input[name='selected_table']").val()+"' '"+$("input[name='selected_start_date']").val()+"' '"+$("input[name='selected_end_date']").val()+"'";
		var ljb_query = "/algorithms/LongTerm/LJBCalculations/LJBcalculations.py '"+$("input[name='selected_item']").val()+"' '"+$("input[name='selected_start_date_2']").val()+"' '"+$("input[name='selected_end_date']").val()+"'";
		var pe_historical_query = "/algorithms/LongTerm/peRatioHistorical.py "+$("input[name='selected_item']").val();
		var historical_data_query = "/algorithms/LongTerm/historicalData.py '"+$("input[name='selected_item']").val()+"' '"+$("input[name='selected_end_date']").val()+"'";
		var historical_prices_query = "/algorithms/LongTerm/historicalPrices.py '"+$("input[name='selected_item']").val()+"'";
		var statistical_query = "/algorithms/Others/statistical_analysis_prebacktesting.py '"+$("input[name='selected_item']").val()+"' '"+$("input[name='selected_end_date']").val()+"' '"+$("input[name='selected_start_date']").val()+"'";
		
		var overview_counter = financials_counter = historical_counter = statistical_counter = 0;
		var ljb_safety = 0;
		var c_fundamental = c_statistical = c_technical = c_ljb = c_pe_history = c_data_history = c_price_history = false;

		var max_concurrent = 2;
		var concurrent = 0;

		var market_cap_selected = null;
		var revenue_to_income_selected = null;
		var market_cap_to_revenue_selected = null;
		var cash_to_debt_selected = null;
		var selectedLJBMetrics = null;

		function statisticalAnalysis() {
			var start_date = $("input[name='statistical_start_date']").val();
			var end_date = $("input[name='statistical_end_date']").val();
			
			if(start_date != null && start_date.length > 0 && end_date != null && end_date.length > 0) {
				window.open("/download_statistical_analysis.php?start_date="+encodeURI(start_date)+"&end_date="+encodeURI(end_date)+"&item="+$("input[name='selected_item']").val(), "_blank");
			}
			else {
				alert("Please select a start and end date.");
			}
		}

		var concurrent_interval = setInterval(function() {
			if (concurrent < max_concurrent && !c_technical) {
				concurrent++;
				c_technical = true;
				$.post("/php_dependancies/analysis_scripts.php", {query: technical_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					$("#overview").append("<h3>General Information</h3>");
					if(data['table'] != null){
						$("#current_price").val(data['table'][1][0]);
						$("#overview").append(arrayToTableNoSort(data['table']));
					}
					else{
						$("#overview").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
					}
					overview_counter++;
					var overview_interval = setInterval(function(){
						if(overview_counter == 3){
							clearInterval(overview_interval);
							$.get("/graphs/plot1Stocks.html", function(graph){
								$("#overview").append(graph);
								$.get("/php_dependancies/analysis.php", {item: $("input[name='selected_item']").val(), start_date: $("input[name='selected_start_date']").val(), end_date: $("input[name='selected_end_date']").val(), find_markets: true}, function(data) {
									var data = data.replace(" [],", "");
									try{
										data = $.parseJSON(data);
									}
									catch(err){
										console.log(err);
									}	

									if(data['bull_markets'] != null) {
										$("#overview").append("<h3>Bull Markets</h3>");
										$("#overview").append(arrayToTable(data['bull_markets']));
										refreshDataTable($("#overview").find("table").get($("#overview").find("table").length-1));
									}
									if(data['avg_bull_duration'] != null) {
										$("#overview").append("<p style='font-size: 20px; margin-bottom: 15px;'>In average, the prices rise for "+data['avg_bull_duration']+" days.</p>");
									}
									if(data['bear_markets'] != null) {
										$("#overview").append("<h3>Bear Markets</h3>");
										$("#overview").append(arrayToTable(data['bear_markets']));
										refreshDataTable($("#overview").find("table").get($("#overview").find("table").length-1));
									}
									if(data['avg_bear_duration'] != null) {
										$("#overview").append("<p style='margin-bottom: 15px; font-size: 20px;'>In average, the prices drop for "+data['avg_bear_duration']+" days.</p>");
									}

									$("#overview_loader").remove();
								});
							});
						}
					}, 500);
				});
			}
			if (concurrent < max_concurrent && !c_fundamental) {
				c_fundamental = true;
				concurrent++;
				$.post("/php_dependancies/analysis_scripts.php", {query: fundamental_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					var overview_interval = setInterval(function(){
						if(overview_counter == 1){
							if(data['tablepe1'] != null){
								ljb_safety = data['tablepe1'][1][3];
								$("#ljb_container").show();
								$("#ljb_loader").remove();
								$("#overview").append("<h3>Information for P/E Ratio of "+$("input[name='selected_pe_1']").val()+"</h3>");
								$("#overview").append(arrayToTableNoSort(data['tablepe1']));
							}
							else{
								$("#overview").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							if(data['tablepe2'] != null){
								$("#overview").append("<h3>Information for P/E Ratio of "+$("input[name='selected_pe_2']").val()+"</h3>");
								$("#overview").append(arrayToTableNoSort(data['tablepe2']));
							}
							overview_counter++;
						}
						else if(overview_counter == 2){
							$("#overview").append("<h3>Historical Information</h3>");
							if(data['historical'] != null){
								$("#overview").append(arrayToTable(data['historical']));
								refreshDataTable($("#overview").find("table").get($("#overview").find("table").length-1));
							}
							else{
								$("#overview").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							overview_counter++;
							clearInterval(overview_interval);
						}
					}, 500);
					$("#financials").append("<h3>Change (Year Over Year)</h3>");
					if(data['table1'] != null){
						$("#financials").append(arrayToTable(data['table1']));
						refreshDataTable($("#financials").find("table").get($("#financials").find("table").length-1));
					}
					else{
						$("#financials").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
					}
					financials_counter++;
					if(data['portfolio'] != null){
						$.post("/php_dependancies/portfolio_session.php", {session: JSON.stringify(data['portfolio']), item: $("input[name='selected_item']").val()}, function(data){});
					}
					var financials_interval = setInterval(function(){
						if(financials_counter == 2){
							$.get("/graphs/plot1Fundamentals.html", function(graph){
								$("#financials").append(graph);
								financials_counter++;
							});
							$.get("/graphs/plot1FundamentalsChange.html", function(graph){
								$("#financials").append(graph);
								financials_counter++;
							});
							clearInterval(financials_interval);
						}
					}, 500);

					var remove_loader = setInterval(function(){
						if(financials_counter == 4){
							$("#financials_loader").remove();
							clearInterval(remove_loader);
						}
					}, 500); 
				});
			}
			if (concurrent < max_concurrent && !c_statistical) {
				concurrent++;
				c_statistical = true;
				$.post("/php_dependancies/analysis_scripts.php", {query: statistical_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					if(data['table'] != null) {
						$("#statistical").append("<h3>Statistical Tables</h3>");
						$("#statistical").append(arrayToTableNoSort(data['table']));
					}
					$("#statistical").append("<h3>Statistical Graphs</h3>");
					var get_progress = false;
					var statistical_interval = setInterval(function(){
						if(statistical_counter == 0 && !get_progress){
							get_progress = true;
							$.get("/graphs/pre_backtestingGraph_boxplot.html", function(graph){
								$("#statistical").append("<div style='display: inline-block; width: 95%; margin: 20px 0; height: 800px'>"+graph+"</div>");
								statistical_counter++;
								get_progress = false;
							});
						}
						else if(statistical_counter == 1 && !get_progress) {
							get_progress = true;
							$.get("/graphs/pre_backtestingGraph_days.html", function(graph){
								$("#statistical").append("<div style='display: inline-block; width: 95%; margin: 20px 0; height: 800px'>"+graph+"</div>");
								statistical_counter++;
								get_progress = false;
							});
						}
						else if(statistical_counter == 2 && !get_progress) {
							get_progress = true;
							$.get("/graphs/pre_backtestingGraph_scatter.html", function(graph){
								$("#statistical").append("<div style='display: inline-block; width: 95%; margin: 20px 0; height: 800px'>"+graph+"</div>");
								statistical_counter++;
								get_progress = false;
							});
						}
						else if(statistical_counter == 3 && !get_progress) {
							get_progress = true;
							$.get("/graphs/pre_backtestingGraph_scatter2.html", function(graph){
								$("#statistical").append("<div style='display: inline-block; width: 95%; margin: 20px 0; height: 800px'>"+graph+"</div>");
								statistical_counter++;
								get_progress = false;
							});
						}
						else if(statistical_counter == 4 && !get_progress) {
							if(data['table2'] != null) {
								$("#statistical").append("<h3>Statistical Tables</h3>");
								$("#statistical").append(arrayToTableNoSort(data['table2']));
							}
							$("#statistical_loader").remove();
							clearInterval(statistical_interval);
						}
					}, 100);
				});
			}
			if (concurrent < max_concurrent && !c_pe_history) {
				concurrent++;
				c_pe_history = true;
				$.post("/php_dependancies/analysis_scripts.php", {query: pe_historical_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					$("#historical").append("<h3>Historical Data Excel Document</h3>");
					$("#historical").append('<div class="attachment_container excel" style="margin: 20px auto;"><div class="attachment_type"><i class="fas fa-file-excel"></i></div><div class="attachment_name">DCF_data.xls</div><a target="_blank" href="/graphs/DCF_data.xls"><div class="attachment_download"><i class="fas fa-download"></i></div></a></div>');
					$("#historical").append("<h3>Statistical Analysis</h3>");
					$("#historical").append("<div class='date_container' style='margin: 20px; vertical-align: bottom'>Start Date<input type='date' name='statistical_start_date'></div><div class='date_container' style='margin: 20px; vertical-align: bottom'>End Date<input type='date' name='statistical_end_date'></div>");
					$("#historical").append('<div class="attachment_container excel" style="margin: 20px; vertical-align: bottom"><div class="attachment_type"><i class="fas fa-file-excel"></i></div><div class="attachment_name">Download CSV</div><div class="attachment_download" onclick="statisticalAnalysis();"><i class="fas fa-download"></i></div></div>');
					$("#historical").append("<h3>P/E Ratio History</h3>");
					if(data['table'] != null){
						$("#historical").append(arrayToTableShowAll(data['table']));
						refreshDataTable($("#historical").find("table").get($("#historical").find("table").length-1));
					}
					else{
						$("#historical").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
					}
					historical_counter++;
				});
			}
			if (concurrent < max_concurrent && !c_data_history) {
				concurrent++;
				c_data_history = true;
				$.post("/php_dependancies/analysis_scripts.php", {query: historical_data_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					var financials_interval = setInterval(function(){
						if(financials_counter == 1){
							financials_counter++;
							$("#financials").append("<h3>Quarterly Data</h3>");
							if (data['table1'] != null) {
								$("#financials").append(arrayToTable(data['table1']));
								refreshDataTable($("#financials").find("table").get($("#financials").find("table").length-1));
							}
							else{
								$("#financials").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							$("#financials").append("<h3>Yearly Data</h3>");
							if (data['table2'] != null) {
								$("#financials").append(arrayToTable(data['table2']));
								refreshDataTable($("#financials").find("table").get($("#financials").find("table").length-1));
							}
							else{
								$("#financials").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							clearInterval(financials_interval);
						}
					}, 500);
					var historical_interval = setInterval(function(){
						if(historical_counter == 1){
							$("#historical").append("<h3>Previous Buying Opportunities</h3>");
							if(data['table3'] != null){
								$("#historical").append("<div id='prev_buying_op'>"+arrayToTable(data['table3']))+"</div>";
								var selected_eps, selected_expected_growth, selected_pe = 0;
								historicalChanges();
								refreshDataTable($("#historical").find("table").get($("#historical").find("table").length-1));
							}
							else{
								$("#historical").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							historical_counter++;
							clearInterval(historical_interval);
						}
					}, 500);
				});
			}
			if (concurrent < max_concurrent && !c_price_history) {
				concurrent++;
				c_price_history = true;
				$.post("/php_dependancies/analysis_scripts.php", {query: historical_prices_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					var historical_interval = setInterval(function(){
						if(historical_counter == 2){
							historical_counter++;
							$("#historical").append("<h3>Monthly Price Change</h3>");
							if (data['table1'] != null) {
								$("#historical").append(arrayToTable(data['table1']));
								refreshDataTable($("#historical").find("table").get($("#historical").find("table").length-1));
							}
							else{
								$("#historical").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							$("#historical").append("<h3>Monthly Price Averages</h3>");
							if (data['table3'] != null) {
								$("#historical").append(arrayToTableShowAll(data['table3']));
								refreshDataTable($("#historical").find("table").get($("#historical").find("table").length-1));
							}
							else{
								$("#historical").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
							$("#historical").append("<h3>Yearly Price Change</h3>");
							if (data['table2'] != null) {
								$("#historical").append(arrayToTable(data['table2']));
								refreshDataTable($("#historical").find("table").get($("#historical").find("table").length-1));
							}
							else{
								$("#historical").append("<div style='font-style: italic; margin: 15px 0;'>No data...</div>");
							}
						}
						else if(historical_counter == 3) {
							$.post("/php_dependancies/compare_daily.php", {ticker: $("input[name='selected_item']").val(), compare_ticker: "TSLA"}, function(compare_daily_data) {
								$("#historical").append("<h3>Daily Change</h3>");
								$("#historical").append('<input type="text" id="compare_daily" placeholder="Compare to..." style="padding: 5px; font-size: 24px; font-weight: 300; box-shadow: 0px 0px 1px 0px rgba(49,60,71,.33); transition: 0.2s all; display: block; margin: 15px auto 15px auto;">');
								$("#historical").append("<div id='compare_daily_results'></div>");
								$("#compare_daily").keyup(function(key) {
									if (quickSearch != null && quickSearch.length > 0) {
										var currentText = $("#compare_daily").val().toLowerCase().trim();
										if (currentText != null && currentText.length > 0) {
											var searchResults = [];
											for (var i = 0; i <= quickSearch.length - 1; i++) {
												if (quickSearch[i][0].toLowerCase().indexOf(currentText) >= 0 ||
													quickSearch[i][1].toLowerCase().indexOf(currentText) >= 0) {
													searchResults.push(quickSearch[i]);
												}
											}
										}
										$('#compare_daily_results').html('');
										for (var i = 0; i < searchResults.length; i++) {
											$('#compare_daily_results').append("<div class='compare_daily_result' style='display: inline-block; padding: 5px; font-weight: 700; background: #656565; cursor: pointer; color: white; margin: 5px;'>"+searchResults[i][0]+"</div>");
											if (i >= 9) {
												break;
											}
										}
										$(".compare_daily_result").unbind("click");
										$(".compare_daily_result").click(function() {
											$(".compare_daily_result").css("background", "#656565");
											$(this).css("background", "#ffc800");
											$(".compare_daily_result").removeClass("selected_compare_daily");
											$(this).addClass("selected_compare_daily");
											$("#compare_daily_table").parent().parent().parent().remove();
											$.post("/php_dependancies/compare_daily.php", {ticker: $("input[name='selected_item']").val(), compare_ticker: $(".selected_compare_daily").html()}, function(compare_daily_data) {
												$("#compare_daily_results").after(compare_daily_data);
												refreshDataTable($("#compare_daily_table"));
											});
										});
									} else {
										$('#compare_daily_results').html('');
									}
								});
								$("#historical").append(compare_daily_data);
								refreshDataTable($("#historical").find("table").get($("#historical").find("table").length-1));

								$("#historical_loader").remove();
							});

							clearInterval(historical_interval);
						}
					}, 500);
				});
			}
			if (concurrent < max_concurrent && !c_ljb) {
				concurrent++;
				c_ljb = true;
				$.post("/php_dependancies/analysis_scripts.php", {query: ljb_query}, function(data){
					concurrent--;
					var data = data.replace(" [],", "");
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}
					if(data['table'] != null){
						$("#ljb_score_container").append(arrayToTableNoSort(data['table']));
						market_cap_selected = data['table'][1][0];
						revenue_to_income_selected = data['table'][1][4];
						market_cap_to_revenue_selected = data['table'][1][2];
						cash_to_debt_selected = data['table'][1][5];
						selectedLJBMetrics = $("#ljb_score_container").html();
					}
				});
				clearInterval(concurrent_interval);
			}

		}, 50);
	}
</script>
<script type="text/javascript">
	//NOTES
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
	function historicalChanges() {
		$("#prev_buying_op tr").each(function(){
			var eps = $(this).find("td:eq(3)");
			var expected_growth = $(this).find("td:eq(2)");
			var pe = $(this).find("td:eq(4)");
			var true_val_cell = $(this).find("td:eq(5)");
			$(this).find("td:eq(2) i:eq(0), td:eq(3) i:eq(0), td:eq(4) i:eq(0)").css("cursor", "pointer");
			$(this).find("td:eq(2), td:eq(3), td:eq(4)").css("transition", "0.2s all");
			$(this).find("td:eq(2)").html("<span>"+$(this).find("td:eq(2)").html()+"</span>");
			$(this).find("td:eq(3)").html("<span>"+$(this).find("td:eq(3)").html()+"</span>");
			$(this).find("td:eq(4)").html("<span>"+$(this).find("td:eq(4)").html()+"</span>");
			$(this).find("td:eq(2)").append("<i class='fas fa-edit' style='margin-left: 7.5px;'></i><i class='fas fa-redo-alt' style='margin-left: 2.5px;' default_value = '"+$(expected_growth).text().trim()+"'></i>");
			$(this).find("td:eq(3)").append("<i class='fas fa-edit' style='margin-left: 7.5px;'></i><i class='fas fa-redo-alt' style='margin-left: 2.5px;' default_value = '"+$(eps).text().trim()+"'></i>");
			$(this).find("td:eq(4)").append("<i class='fas fa-edit' style='margin-left: 7.5px;'></i><i class='fas fa-redo-alt' style='margin-left: 2.5px;' default_value = '"+$(pe).text().trim()+"'></i>");
			$(this).find("td:eq(2) i:eq(0), td:eq(3) i:eq(0), td:eq(4) i:eq(0)").click(function(){
				var user_input = window.prompt("Please enter the desired value.");
				user_input = user_input.trim();

				if(!$.isNumeric(user_input)){
					alert("Your input must be a number.");
				}
				else{
					$(this).parent().find("span").html(user_input);
				}

				selected_eps = parseFloat(eps.text().trim());
				selected_expected_growth = parseFloat(expected_growth.text().trim());
				selected_pe = parseFloat(pe.text().trim());
				var true_value = (selected_eps*selected_pe*Math.pow(((selected_expected_growth/100)+1),3))/Math.pow(1.15, 3);
				true_val_cell.html(true_value.toFixed(2));
			});
			$(this).find("td:eq(2) i:eq(1), td:eq(3) i:eq(1), td:eq(4) i:eq(1)").click(function(){
				$(this).parent().find("span").html($(this).attr("default_value"));
				selected_eps = parseFloat(eps.text().trim());
				selected_expected_growth = parseFloat(expected_growth.text().trim());
				selected_pe = parseFloat(pe.text().trim());
				var true_value = (selected_eps*selected_pe*Math.pow(((selected_expected_growth/100)+1),3))/Math.pow(1.15, 3);
				true_val_cell.html(true_value.toFixed(2));
			});
		});
	}
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
			var selectedStock = $("input[name='selected_item']").val();
			tabContent.html("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='notes_loading'>");
			$.post("/php_dependancies/get_notes.php", {type: "item", item: selectedStock}, function(data) {
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
		$.post("/php_dependancies/delete_item_note.php", {note_id: note_id}, function(data) {
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
		var selectedStock = $("input[name='selected_item']").val();
		$.post("/php_dependancies/add_item_note.php", {item: selectedStock, note: noteContent, title: $(".add-note").next(".set_note_title").val().trim()}, function(data) {
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
		$.post("/php_dependancies/edit_item_note.php", {note_id: $(this).attr("note_id"), note_content: $(".note_editor .ql-editor").html(), title: $(".note_editor").next(".set_note_title").val().trim()}, function(data) {
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
</script>
<script type="text/javascript">
	//ATTACHMENTS
	function deleteAttachment(path){
		$.post("/php_dependancies/user_attachments.php", {delete_path: path}, function (data){
			$("#attachments_header").trigger("click");
		});
	}

	function getAttachments(element){
		var selectedStock = $("input[name='selected_item']").val();
		var tabHeaderIndex = $(element).index();
		var tabContent = $(element).parent().parent().children("div:eq("+tabHeaderIndex+")");
		tabContent.find(".attachment_container").remove();
		tabContent.find("#no_attachments").remove();
		tabContent.append("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='attachments_loading'>");
		$.post("/php_dependancies/user_attachments.php", {item: selectedStock}, function(data) {
			if (data != null && data.length > 0) {
				var attachments = $.parseJSON(data);
				if (attachments.document_name != null && attachments.document_name.length > 0) {
					for (var i = 0; i < attachments.document_name.length; i++) {
						tabContent.append('<div class="attachment_container '+attachments.document_type[i]+'"><div class="attachment_type"><i class="fas fa-file-'+attachments.document_type[i]+'"></i></div><div class="attachment_name">'+attachments.document_name[i]+'</div><a target="_blank" href="'+attachments.path[i]+'"><div class="attachment_download"><i class="fas fa-download"></i></div></a><div class="attachment_delete" onclick="deleteAttachment(\''+attachments.path[i]+'\')"><i class="fas fa-trash"></i></div></div>');
					}
				} else {
					tabContent.append("<span id='no_attachments' style='font-style: italic;'>Nothing here...</span>");
				}
			} else {
				tabContent.append("<span id='no_attachments' style='font-style: italic;'>Nothing here...</span>");
			}
			$("#attachments_loading").remove();
		});		
	}

	$('#attachment_upload').on('click', function() {
		$(this).hide();
		$(this).after("<img id='attachment_loading' style = 'display: block; margin: 5px auto 0 auto;' src='/img/ajax-loader-2.gif'>");
		var file_data = $('#note_attachment').prop('files')[0];   
		var form_data = new FormData();                  
		form_data.append('file', file_data);
		form_data.append("item", $(this).attr("item"));              
		$.ajax({
			url: "/php_dependancies/attachment_upload.php",
			dataType: 'text',
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,                         
			type: 'post',
			success: function(php_script_response){
        		if(php_script_response == "Success"){
        			$("#note_attachment").val("");
        			$("#attachments_header").trigger("click");
        		}
        		else{
        			alert("Error");
        		}
        		$("#attachment_upload").show();
        		$("#attachment_loading").remove();
        	}
    	});
	});
</script>
<script type="text/javascript">
	//WATCHLIST ADD
	var watchlist_request = false;

	$('#add_watchlist').click(function(){
		if (!watchlist_request) {
			watchlist_request = true;
			var myModalLoading = new jBox('Modal', {
				content: '<img src="/img/ajax-loader-2.gif">'
			}); 
			myModalLoading.open();
			$.get('/watchlist_actions.php', {get_watchlists: true, symbol: $("input[name='selected_table']").val()}, function(data) {
				myModalLoading.close();
				if (data != "fail") {
					var myModal = new jBox('Modal', {
						width: $(window).innerWidth()
					}); 
					myModal.open();
					myModal.setContent(data);

				} else {
					alert('Error!');
					console.log(data);
				}
				watchlist_request = false;
			});
		}
	});

	//PORTFOLIO ADD
	function addToPortfolio() {
		if ($("#LJBFinal").length && $.isNumeric($("#LJBFinal").html())) {
			$("#add_portfolio").append("<input style='display: none;' type='hidden' value='"+($("#LJBFinal").html())+"' name='LJBScore'>");
			return true;
		} else {
			alert('You need to calculate the LJB Score to be able to add this item to the portfolio.');
			return false;
		}
	}
</script>
<script type="text/javascript">
	//LJB SCORE
	function numberOfSelections() {
		return $("#linearized_selections .linearized_selection_container").length;
	}
	function deleteSelection(selection) {
		$(selection).parent().parent().parent().remove();
		if (numberOfSelections() > 0) {
			$("#linearized_selections span").hide();
		} else {
			$("#linearized_selections span").show();
		}
	}
	function addSelection(selection, searchType) {
		var symbol = $(selection).attr("symbol");
		var tableName = $(selection).attr("tableName");
		if (numberOfSelections() < 1000000) {
			$("#linearized_selections").append("<div class='linearized_selection_container' tableName='"+symbol+"'><div class='linearized_selection_header'><div>"+symbol+"</div><div><i class='fas fa-minus-square' onclick='deleteSelection(this)'></i></div></div><div class='linearized_selection_type'>"+searchType+"</div></div>");
		}
		if (numberOfSelections() > 0) {
			$("#linearized_selections span").hide();
		} else {
			$("#linearized_selections span").show();
		}
	}
	var searchResult = null;
	var searchType = null;
	function searchItems(itemType) {
		$("#linearized_search").val("");
		$("#linearized_search_results").html("");
		searchResult = null;
		searchType = null;
		$.get("/php_dependancies/linearized_search.php", {type: itemType}, function(data) {
		  if (data != null && data.length > 0) {
		  	searchResult = data.sort();
		  	searchType = ucFirst(itemType);
		  	if (searchType.indexOf("fundamental") >= 0) {
		  		searchType = "Fundamental";
		  	}
		  }
		});
	}
	function ucFirst(string) {
	    return string.charAt(0).toUpperCase() + string.slice(1);
	}
	$(".linearized_search_type").click(function() {
		$(".linearized_search_type").removeClass("linearized_search_selected");
		$(this).addClass("linearized_search_selected");
		searchItems($(this).attr("type"));
		$("#linearized_search_results").hide();
		$("#linearized_search_results").html("");
	});
	searchItems("stock");
	$("#linearized_search").on("click keyup", function(key) {
		if (searchResult != null && Array.isArray(searchResult) && searchResult.length > 0) {
			$("#linearized_search_results").hide();
			$("#linearized_search_results").html("");
			var currentText = $("#linearized_search").val().toLowerCase().trim();
			for (var i = 0; i < searchResult.length; i++) {
				if (!Array.isArray(searchResult[i])) {
					if (searchResult[i].toLowerCase().trim().indexOf(currentText) >= 0) {
						$("#linearized_search_results").show();
						$("#linearized_search_results").append("<div symbol='"+searchResult[i]+"' tableName='"+searchResult[i]+"' onclick='addSelection(this, \""+searchType+"\")'>" + searchResult[i] + "</div>");
					}
				} else {
					if (searchResult[i][0].toLowerCase().trim().indexOf(currentText) >= 0 || searchResult[i][1].toLowerCase().trim().indexOf(currentText) >= 0) {
						$("#linearized_search_results").show();
						$("#linearized_search_results").append("<div symbol='"+searchResult[i][0]+"' tableName='"+searchResult[i][2]+"' onclick='addSelection(this, \""+searchType+"\")'>" + searchResult[i][0] + " - " + searchResult[i][1] + "</div>");
					}
				}	
			}
		}
	});
	var LJBScore = 0;
	$("#compare_stock").click(function() {
		$("#ljb_score_container").html("");
		$("#ljb_score_container").hide();
		$(this).hide();
		$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='linearized_loading'>");
		var tableNames = "";
		$(".linearized_selection_container").each(function() {
			tableNames += $(this).attr("tableName") + " ";
		});
		var startDate = $("input[name='selected_start_date']").val();
		var endDate = $("input[name='selected_end_date']").val();
		var selectedStock = $("input[name='selected_item']").val();
		var sectorOrStock = searchType == "Sector" ? 1 : 0;
		$.post("/php_dependancies/ljb_score.php", {stocks: tableNames, startDate: startDate, endDate: endDate, sector: sectorOrStock}, function(data) {
			if (data != "fail") {
				data = jQuery.parseJSON(data);
				$("#ljb_score_container").show();
				$("#ljb_score_container").html(selectedLJBMetrics);
				$("#ljb_score_container").append("<h2 style='padding: 0; font-weight: 300; font-size: 30px; text-transform: none; color: #bbbbbb; margin-bottom: -20px;'>Metrics for selected comparison</h2>");
				$("#ljb_score_container").append(data.html);
				//Calculate LJB Score
				var cash_to_debt_comparison = data['table'][1][5];
				var market_cap_comparison = data['table'][1][0];
				var market_cap_to_revenue_comparison = data['table'][1][2];
				var revenue_to_income_comparison = data['table'][1][4];
				LJBScore = 0;
				if (cash_to_debt_selected > cash_to_debt_comparison*1.1) {
					LJBScore += 3.5;
				} else if (cash_to_debt_selected < cash_to_debt_comparison*0.9) {
					LJBScore += 0;
				} else {
					LJBScore += 1.75;
				}
				if (market_cap_to_revenue_selected > market_cap_to_revenue_comparison*1.1) {
					LJBScore += 0;
				} else if (market_cap_to_revenue_selected < market_cap_to_revenue_comparison*0.9) {
					LJBScore += 3;
				} else {
					LJBScore += 1.5;
				}
				if (revenue_to_income_selected > revenue_to_income_comparison*1.1) {
					LJBScore += 0;
				} else if (revenue_to_income_selected < revenue_to_income_comparison*0.9) {
					LJBScore += 3.5;
				} else {
					LJBScore += 1.75;
				}
				if (LJBScore < 10) {
					LJBScore = LJBScore.toFixed(1);
				}
				$("#ljb_score_container").append('<div style=" display: inline-block; border: 2px solid #e2e2e2; margin-top: 10px;"><div style=" background: #e2e2e2; padding: 10px; font-size: 18px; color: gray; font-weight: 400;">SCORE</div><div style=" padding: 5px; font-size: 18px; color: #777777; font-weight: 700;" id="compare_score">'+LJBScore+'</div></div>');
			} else {
				alert("Error!");
			}
			$("#linearized_loading").remove();
			$("#compare_stock").show();
		});
	});
	var rateStock = new LJBRater(['Brand', 'Management', 'Competitive Advantage'], "#rate_stock");
	var ratingScore = 0;
	$("#ljb_score").click(function() {
		ratingScore = 0;
		if (rateStock.checkRating("Brand") == 1) {
			ratingScore += 10/3;
		}
		if (rateStock.checkRating("Management") == 1) {
			ratingScore += 10/3;
		}
		if (rateStock.checkRating("Competitive Advantage") == 1) {
			ratingScore += 10/3;
		}
		if (LJBScore != 0) {
			var LJBScoreFinal = (LJBScore*0.6) + (ratingScore*0.3) + (ljb_safety*0.1);
			LJBScoreFinal = LJBScoreFinal.toFixed(2);
			$("#LJBFinalContainer").show();
			$("#LJBFinalContainer").html('<div style=" display: inline-block; border: 2px solid #e2e2e2; margin-top: 10px;"><div style=" background: #e2e2e2; padding: 15px; font-size: 22px; color: gray; font-weight: 400;">SCORE</div><div style=" padding: 5px; font-size: 22px; color: #777777; font-weight: 700;" id="LJBFinal">'+LJBScoreFinal+'</div></div>');
		} else {
			alert("You need to finish the first step before being able to calculate the final score.");
		}
	});
</script>