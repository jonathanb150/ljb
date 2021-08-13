<?php
session_start();
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db_inveltio.php");
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php");
require($_SERVER['DOCUMENT_ROOT']."/natsulytics/natsulytics.php");

//Timer
$t = new Timer;
if (!verifyLoggedIn()) {
	redirect("/login.php".(isset($_GET['redirect']) ? "?redirect={$_GET['redirect']}" : ""));
}
?> 
<?php 
//Auto read notifications | After 1 week
$notifications_table = $_SESSION["user"]."_notifications";
mysqli_query($db, "UPDATE `{$notifications_table}` SET status = 'read' WHERE status = 'unread' AND ".time()."-date >= 604800") or die(mysqli_error($db));

//Auto delete notifications | After 2 weeks
$notifications_table = $_SESSION["user"]."_notifications";
mysqli_query($db, "DELETE FROM `{$notifications_table}` WHERE ".time()."-date >= 1209600") or die(mysqli_error($db));

//Hidden content
if (!isset($_SESSION['stocksBySector'])) {
	$stocksBySector = shell_exec('python3.7 '.$_SERVER['DOCUMENT_ROOT'].'/algorithms/Others/stocksByIndex.py');
	if ($stocksBySector != null && !empty($stocksBySector) && is_array(json_decode($stocksBySector, true))) {
		$_SESSION['stocksBySector'] = json_decode($stocksBySector, true);
	}
}
if (!isset($_SESSION['stocksByIndex'])) {
	$stocksByIndex = mysqli_query($db, "SELECT index_components FROM index_components WHERE id = 1");
	confirmQuery($stocksByIndex);
	$stocksByIndex = mysqli_fetch_all($stocksByIndex);
	if ($stocksByIndex != null && count($stocksByIndex) == 1 && is_array(json_decode($stocksByIndex[0][0], true))) {
		$_SESSION['stocksByIndex'] = json_decode($stocksByIndex[0][0], true);
	}
}
if (!isset($_SESSION['quickSearch'])) {
	//Quick search
	$_SESSION['quickSearch'] = mysqli_query($db, "SELECT apiTicker, name, type, tableName, symbol FROM items WHERE type LIKE '%stock%' OR type LIKE '%index%' OR type LIKE '%currency%' OR type LIKE '%commodity%'") or die('Error');
	$only_stocks = mysqli_fetch_all($_SESSION['quickSearch']);
	$array = [];
	$array2 = [];

	for ($i=0; $i < count($only_stocks); $i++) {
		if(strpos($only_stocks[$i][2], "stock") !== false){
			$array[] = $only_stocks[$i][3];
			$array2[$only_stocks[$i][4]] = $only_stocks[$i][1];
		}
	}

	$_SESSION['filter_stocks'] = json_encode($array);
	$_SESSION['filter_names'] = json_encode($array2, true);
	$_SESSION['quickSearch'] = json_encode($only_stocks);
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=0.001">
	<link rel="shortcut icon" type="image/x-icon" href="/img/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="/css/styles.css">
	<link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	<script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="/js/info.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
	<script type="text/javascript" src='https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'></script>
	<title>LJB Finance</title>
	<script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v0.5.1/dist/jBox.all.min.js"></script>
	<link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v0.5.1/dist/jBox.all.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css">
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>

	<?php 
	if(date("l") == "Sunday" && !isset($_SESSION['tasks_report'])){
		$_SESSION['tasks_report'] = true;
		echo "<script type='text/javascript' src='/js/tasks_report.js'></script>";
	}
	?>
</head>
<script type="text/javascript">
	//CHART JS OPTIONS
	Chart.defaults.global.elements.line.tension = 0;
	Chart.defaults.global.elements.line.backgroundColor = "rgba(115, 152, 208, 0.1)";
	Chart.defaults.global.elements.line.borderWidth = 1;
	Chart.defaults.global.elements.line.fill = "bottom";
	Chart.defaults.global.elements.line.borderColor = "rgba(115, 152, 208, 0.5)";
	Chart.defaults.global.elements.point.backgroundColor = "rgba(115, 152, 208, 1)";
	Chart.defaults.global.defaultFontFamily = "'Lato', sans-serif";

	$.fn.slideFadeToggle  = function(speed, easing, callback) {
        return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
	}; 
	$(document).ready(function() {
		var inProcess = false;
		try {
			$(".general-container").each(function() {
				var selectedTab = $(this).find(".general-container-selected").index();
				$(this).children("div").hide();
				$(this).children("div:eq("+selectedTab+")").show();
			});
			$(".general-container nav h1, .general-container select").on("click change", function() {
				$(".plotly-graph-div").css("width", "100%");
				window.dispatchEvent(new Event('resize'));
				var currentThis = $(this);
				if (currentThis.prop('tagName') == 'SELECT' && currentThis.attr("id") != "fund_country" && currentThis.attr("id") != "select_fee_dividend") {
					for (var i = 0; i < currentThis.find('option').length; i++) {
						if (currentThis.find("option:eq("+i+")").html() == currentThis.val()) {
							currentThis = currentThis.find("option:eq("+i+")");
						}
					}
				} else if (currentThis.attr("id") == "fund_country" || currentThis.attr("id") == "select_fee_dividend") {
					return false;
				}
				if (!inProcess) {
					inProcess = true;
					var selectedTab = currentThis.index();
					var currentTab = currentThis.parent().children("general-container-selected").index();
					currentThis.parent().children().removeClass("general-container-selected");
					currentThis.parent().children("h1:eq("+selectedTab+")").addClass("general-container-selected");
					currentThis.parent().children("option:eq("+selectedTab+")").addClass("general-container-selected");
				}

				//Fade animation
				currentThis.parent().parent().children("div").fadeOut(150).promise().done(function() {
					window.dispatchEvent(new Event('resize'));
					$(this).parent().children("div:eq("+selectedTab+")").fadeIn(150, function() {
						inProcess = false;
						window.dispatchEvent(new Event('resize'));
					});
				});
			});
			$(".dataTable").addClass("display");
			$(".dataTable").addClass("hover");
			$(".dataTable").css("width", "100%");
			$(".dataTable").each(function() {
				if (!$(this).parent().parent().parent().hasClass("tableContainer")) {
					$(this).wrap("<div style='text-align: center;' class='tableContainer'><div style='display: inline-block; width: 95%; margin: 0 auto;'></div></div>")
				}
			});
			$(".dataTable").each(function() {
				if (!$(this).attr('role')) {
					if ($(this).hasClass("dataTableDesc")) {
						$(this).DataTable({order: [[0, 'desc']]});
					} else if ($(this).hasClass("noSort")) {
						$(this).DataTable({"order": []});
					} else if ($(this).hasClass("smallTable")) {	
						$(this).DataTable({"lengthMenu": [5, 10]});
					} else if ($(this).hasClass("showAll")) {	
						$(this).DataTable({"lengthMenu": ["All"]});
					} else if ($(this).hasClass("smallNoSort")) {	
						$(this).DataTable({"lengthMenu": [5,10], "order": []});
					} else {
						$(this).DataTable();
					}
				}
			});
		} catch(err) {
			console.log(err);
    		$("body").css("opacity", "1");
		}
		$("body").animate({'opacity': '1'}, 250);
	});
</script>
<body>
	<?php 
	$tasks_table = $_SESSION['user']."_daily_tasks";
	$tasks = mysqli_query($db, "SELECT task, status, type FROM `{$tasks_table}` ORDER BY type");
	confirmQuery($tasks);
	$task_categories = mysqli_query($db, "SELECT type FROM `{$tasks_table}` GROUP BY `type` ORDER BY type");
	confirmQuery($task_categories);
	$tasks = mysqli_fetch_all($tasks);
	$task_categories = mysqli_fetch_all($task_categories);
	?>
	<div id="toggle_daily"><i class="fas fa-list"></i></div>
	<div id="daily_tasks">
		<i class="fas fa-times"></i>
		<div class="general-container" style="border: 2px solid #808080;background: #3c3c3c; width: 95%; max-width: 100%;">
			<nav>
				<?php
				for ($i=0; $i < count($task_categories); $i++) { 
					if($i == 0){
						echo "<h1 style='color:white;' class='general-container-selected'>{$task_categories[$i][0]}</h1>";
					}
					else{
						echo "<h1 style='color:white;'>{$task_categories[$i][0]}</h1>";
					}
				}
				?>
			</nav>
			<?php
				for ($i=0; $i < count($task_categories); $i++) { 
					echo "<div class='general-container-content'>";
					echo "<ul>";
					for ($b=0; $b < count($tasks); $b++) { 
						if ($tasks[$b][2] == $task_categories[$i][0]) {
							echo "<li>{$tasks[$b][0]}<input type='radio' ".($tasks[$b][1] == 1 ? "checked" : "")."></li>";
						}
					}
					echo "</ul>";
					echo "</div>";
				}
			?>
		</div>
		<button style="background: none; margin: 10px auto; font-size: 14px;; font-weight: 700; color: white; cursor: pointer" onclick="$('body').append('<script type=&quot;text/javascript&quot; src=&quot;/js/tasks_report.js&quot;></script>')"><i class="fas fa-file-alt"></i>View Last Week's Report</button>
	</div>
	<script type="text/javascript">
		$("#toggle_daily").click(function() {
			$(this).hide();
			$("#daily_tasks").show();
		});
		$("#daily_tasks > i").click(function() {
			$("#toggle_daily").css("animation-delay","0ms");
			$(this).parent().hide();
			$("#toggle_daily").show();
		});
		$("#daily_tasks li input").click(function() {
			var task = $(this).parent().text().trim();

			if(!$(this).attr("checked")){
				$.post("/php_dependancies/daily_tasks.php", {task: task}, function(data){});
			}

			$(this).attr("checked","");
		});
	</script>
	<div id="toggle_hidden"><i class="fas fa-angle-double-right"></i></div>
	<div id="hidden_content">
		<div class="quicksearch-container">
			<input type="text" id="quicksearch" placeholder="Search...">
			<div class="quicksearch-results">

			</div>
		</div>
		<script type="text/javascript">
			function markFundamentals(item, status, element){
				new jBox('Notice', {
				    content: (status == 0 ? 'Succesfully marked fundamentals as bad.' : 'Succesfully marked fundamentals as good.'),
				    color: (status == 0 ? 'red' : 'green')
				});
				$(element).parent().fadeOut(250);
				$.post("/php_dependancies/mark_fundamentals_status.php", {item: item, status: status}, function(data){});
			}
		</script>
		<button style="margin: 10px auto 10px auto; color: white; font-weight: 700; padding: 5px; cursor: pointer; background: #545454;" onclick="viewReminders();">View Reminders</button>
		<script type="text/javascript">
			function viewReminders(){
				$.get("/php_dependancies/get_reminders.php", function(data){
					try{
						data = $.parseJSON(data);
					}
					catch(err){
						console.log(err);
					}

					if(data != null && data.length == 2){
						var portfolio_modal = new jBox("Modal", {
							content: data[0],
							overlay: false,
							closeButton: true,
							title: "Portfolio Reminder",
							draggable: true
						});
						var watchlist_modal = new jBox("Modal", {
							content: data[1],
							overlay: false,
							closeButton: true,
							onClose: function() {if(portfolio_modal != null){
								portfolio_modal.open();
								}
							},
							title: "Watchlist Reminder",
							draggable: true
						});
						watchlist_modal.open();
					}
					else{
						alert("Error");
					}
				});
			}
		</script>
		<h3>Companies by Sector</h3>
		<?php 
		if (isset($_SESSION['stocksBySector']) && is_array($_SESSION['stocksBySector']) && count($_SESSION['stocksBySector']) > 0) {
			echo '<div class="general-container" style="width: 95%;">';
			echo '<select>';
			for ($i = 0; $i < count(array_keys($_SESSION['stocksBySector'])); $i++) {
				if ($i == 0) {
					echo '<option class="general-container-selected">'.array_keys($_SESSION['stocksBySector'])[$i].'</option>';
				} else {
					echo '<option>'.array_keys($_SESSION['stocksBySector'])[$i].'</option>';
				}
			}
			echo '</select>';
			for ($i = 0; $i < count(array_values($_SESSION['stocksBySector'])); $i++) { 
				echo '<div class="general-container-content">';
				echo arrayToTableSorteableNoHeader(array_values($_SESSION['stocksBySector'])[$i]);
				echo '</div>';
			}
			echo '</div>';
		}
		echo "<h3>Companies by Index</h3>";
		if (isset($_SESSION['stocksByIndex']) && is_array($_SESSION['stocksByIndex']) && count($_SESSION['stocksByIndex']) > 0) {
			echo '<div class="general-container" style="width: 95%;">';
			echo '<select>';
			for ($i = 0; $i < count(array_keys($_SESSION['stocksByIndex'])); $i++) { 
				if ($i == 0) {
					echo '<option class="general-container-selected">'.str_replace("&", "", array_keys($_SESSION['stocksByIndex'])[$i]).'</option>';
				} else {
					echo '<option>'.str_replace("&", "", array_keys($_SESSION['stocksByIndex'])[$i]).'</option>';
				}
			}
			echo '</select>';
			for ($i = 0; $i < count(array_values($_SESSION['stocksByIndex'])); $i++) { 
				echo '<div class="general-container-content">';
				echo arrayToTableSorteableNoHeader(array_values($_SESSION['stocksByIndex'])[$i]);
				echo '</div>';
			}
			echo '</div>';
		}
		?>
		<h3>US Stock Market Crashes</h3>
		<div style="margin: 15px auto; background: white; border-radius: 4px; padding: 15px 0; width: 90%">
			<table class="dataTable smallNoSort">
				<thead>
					<th>Dates</th>
					<th>Timeframe</th>
					<th>Drop</th>
				</thead>
				<tbody>
					<tr>
						<td>01. Sep 1902 - Dec 1903</td>
						<td>15 Months</td>
						<td>30%</td>
					</tr>
					<tr>
						<td>02. Oct 1907 - Dec 1907</td>
						<td>3 Months</td>
						<td>39%</td>
					</tr>
					<tr>
						<td>03. Aug 1909 - Jan 1915</td>
						<td>65 Months</td>
						<td>31%</td>
					</tr>
					<tr>
						<td>04. Oct 1916 - Dec 1917</td>
						<td>14 Months</td>
						<td>34%</td>
					</tr>
					<tr>
						<td>05. Nov 1919 - Sep 1921</td>
						<td>23 Months</td>
						<td>30%</td>
					</tr>
					<tr>
						<td>06. Sep 1929 - Dec 1932</td>
						<td>39 Months</td>
						<td>85%</td>
					</tr>
					<tr>
						<td>07. Jul 1933 - Mar 1935</td>
						<td>20 Months</td>
						<td>25%</td>
					</tr>
					<tr>
						<td>08. Mar 1937 - Jun 1938</td>
						<td>15 Months</td>
						<td>50%</td>
					</tr>
					<tr>
						<td>09. Nov 1938 - Jun 1942</td>
						<td>43 Months</td>
						<td>35%</td>
					</tr>
					<tr>
						<td>10. Apr 1946 - Jun 1949</td>
						<td>38 Months</td>
						<td>26%</td>
					</tr>
					<tr>
						<td>11. Dec 1961 - Oct 1962</td>
						<td>10 Months</td>
						<td>22%</td>
					</tr>
					<tr>
						<td>12. Jan 1973 - Dec 1974</td>
						<td>23 Months</td>
						<td>30%</td>
					</tr>
					<tr>
						<td>13. Aug 1987 - Dec 1987</td>
						<td>5 Months</td>
						<td>27%</td>
					</tr>
					<tr>
						<td>14. Aug 2000 - Mar 2003</td>
						<td>31 Months</td>
						<td>46%</td>
					</tr>
					<tr>
						<td>15. Sep 2007 - Mar 2009</td>
						<td>18 Months</td>
						<td>50%</td>
					</tr>
				</tbody>
			</table>
		</div>
		<p style="color: #b9b9b9; font-weight: 700; font-style: italic; margin: 15px auto">*AVG Drop: 37.3%<br>*AVG Drop Timeframe: 24.13 Months</p>
		<p style="color: #b9b9b9; font-style: italic; margin: 15px auto">On average, crashes occur every six and a half years. However, they most commonly occur every 10 or three years.<br>66% of the time recessions begin during the fourth quarter (Aug - Dec).</p>
	</div>
	<script type="text/javascript">
		$("#toggle_hidden").click(function(){
			if($(this).next("div").css("left") != "0px"){
				$(this).find("i").removeClass("fa-angle-double-right");
				$(this).find("i").addClass("fa-angle-double-left");
				$(this).next("div").animate({"left": "0"}, {duration:150, queue: false, easing: 'swing'});
				$(this).next("div").animate({"opacity": "1"}, {duration:150, queue: false, easing: 'swing'});
				$(this).animate({"left": "650px"}, {duration:0, queue: false, easing: 'swing'});
			}
			else{
				$(this).find("i").removeClass("fa-angle-double-left");
				$(this).find("i").addClass("fa-angle-double-right");
				$(this).next("div").animate({"left": "-650px"}, {duration:150, queue: false, easing: 'swing'});
				$(this).next("div").animate({"opacity": "0"}, {duration:150, queue: false, easing: 'swing'});
				$(this).animate({"left": "0"}, {duration:0, queue: false, easing: 'swing'});
			}
		});
		var window_height = $(window).height();
		var button_height = $("#toggle_hidden").height();
		$("#toggle_hidden").css("top", ((window_height/2)-(button_height/2))+"px");
		$(window).resize(function(){
			window_height = $(window).height();
			$("#toggle_hidden").css("top", ((window_height/2)-(button_height/2))+"px");
		});
	</script>
<header id='navbar'>
	<section class="logoAndInfo">
		<a href="/"><div id="logo"><div>LJB</div><div>Finance</div></div></a>
	</section>
	<a href="/index.php" <?php if (strpos($_SERVER['REQUEST_URI'], "index.php") !== FALSE || $_SERVER['REQUEST_URI'] == "/") { echo "style='background: #595959; font-weight: 500; box-shadow: inset 0 -5px 0 -2px #717171'"; } ?>><i class="fas fa-home"></i>Account</a>
	<a href="/watchlist.php" <?php if (strpos($_SERVER['REQUEST_URI'], "watchlist.php") !== FALSE) { echo "style='background: #595959; font-weight: 500; box-shadow: inset 0 -5px 0 -2px #717171'"; } ?>><i class="far fa-eye"></i>Watchlist</a>
	<div class="navbar-anchor" <?php if ((strpos($_SERVER['REQUEST_URI'], "analyze") !== FALSE)) { echo "style='background: #595959; font-weight: 500; box-shadow: inset 0 -5px 0 -2px #717171'"; } ?>>
		<i class="fas fa-chart-bar"></i>Analysis
		<div class='dropdown' style='min-width: 200px;'>
			<a href='/analyze_stocks.php'><i class='fas fa-chart-bar'></i>Analyze Stocks</a>
			<a href='/analyze_indexes.php'><i class='fas fa-chart-bar'></i>Analyze Indexes</a>
			<a href='/analyze_etfs.php'><i class='fas fa-chart-bar'></i>Analyze ETFs</a>
			<a href='/analyze_currencies.php'><i class='fas fa-coins'></i>Analyze Currencies</a>
			<a href='/analyze_commodities.php'><i class='fas fa-gas-pump'></i>Analyze Commodities</a>
		</div>
	</div>
	<a href="/global_analysis.php" <?php if (strpos($_SERVER['REQUEST_URI'], "global_analysis.php") !== FALSE) { echo "style='background: #595959; font-weight: 500; box-shadow: inset 0 -5px 0 -2px #717171'"; } ?>><i class="fas fa-globe-americas"></i>Global Analysis</a>
	<a href="/news.php" <?php if (strpos($_SERVER['REQUEST_URI'], "news.php") !== FALSE) { echo "style='background: #595959; font-weight: 500; box-shadow: inset 0 -5px 0 -2px #717171'"; } ?>><i class="fas fa-newspaper"></i>News</a>
	<?php 
	$unread_notifications = unreadNotification($db, $_SESSION["user"]);
	?>
	<a href="/notification_center.php" <?php if (strpos($_SERVER['REQUEST_URI'], "notification_center.php") !== FALSE) { echo "style='background: #595959; font-weight: 500; box-shadow: inset 0 -5px 0 -2px #717171'"; } ?>><i class="fas fa-envelope"></i>Notifications<?php 
	if($unread_notifications > 0){
		echo " (<span id='unread_notifications_count'>".$unread_notifications."</span>)";
	}
	?></a>
	<div class="navbar-anchor"><i class="fas fa-wrench"></i>Tools
		<div class='dropdown' style='min-width: 250px;'>
			<a href='/algorithmic_trading.php'><i class="fas fa-coins"></i>Algorithmic Trading</a>
			<a href='/statistical_backtesting.php'><i class="fas fa-chart-pie"></i>Statistical Backtesting</a>
			<a href="/filter_items.php"><i class="fas fa-filter"></i>Filter Stocks</a>
			<a href="/sort_stocks.php"><i class="fas fa-sort"></i>Sort Stocks</a>
			<a href='/recommended.php'><i class='fas fa-star'></i>Recommended</a>
			<a href="/entry_points.php"><i class="fas fa-calculator"></i>Entry Points</a>
			<a href="/tags.php"><i class="fas fa-tags"></i>Tags</a>
			<a href="/db_operations.php"><i class="fas fa-database"></i>Operations</a>
			<a href="/hourly_data.php"><i class="fas fa-clock"></i>Hourly Data</a>
			<a href="/logs.php"><i class="fas fa-file-alt"></i>Logs</a>
		</div>
	</div>
	<a href="/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</header>
<script type="text/javascript" src="/js/menu.js"></script>
<script type="text/javascript">
	//Quick search
	var quickSearch = <?php echo $_SESSION['quickSearch']; ?>;
	var quickRequestProgress = false;
	$("#quicksearch").keyup(function(key) {
		if (quickSearch != null && quickSearch.length > 0) {
			var currentText = $("#quicksearch").val().toLowerCase().trim();
			if (currentText != null && currentText.length > 0) {
				var searchResults = [];
				for (var i = 0; i <= quickSearch.length - 1; i++) {
					if (quickSearch[i][0].toLowerCase().indexOf(currentText) >= 0 ||
						quickSearch[i][1].toLowerCase().indexOf(currentText) >= 0) {
						searchResults.push(quickSearch[i]);
				}
			}
			$('.quicksearch-results').html('');
			for (var i = 0; i < searchResults.length; i++) {
				$('.quicksearch-results').append("<div class='quicksearch-result'><div><div>"+searchResults[i][0]+"</div><div>"+searchResults[i][1]+"</div></div><img src='/img/ajax-loader-3.svg'><div>"+searchResults[i][2].replace("_", " ").toUpperCase()+"</div></div>");
				if (i >= 4) {
					break;
				}
			}
			$(".quicksearch-result").unbind("click");
			$(".quicksearch-result").click(function() {
				if(!$(this).attr("latest_loaded")){
					if (!quickRequestProgress) {
						quickRequestProgress = true;
						var this_loader = $(this).find("img").get(0);
						var this_ticker = $(this).children("div:eq(0)").children("div:eq(0)");
						var this_element = $(this); 
						$(this_loader).show();
						$(this).attr("latest_loaded", true);
						var symbol = $(this).find("div:eq(0)").find("div:eq(0)").html();
						$(this).attr("ticker", symbol);
						$.post('/php_dependancies/quick_search.php', {ticker: symbol, basic: true}, function(data) {
							$(this_loader).remove();
							if (data != "fail") {
								data = $.parseJSON(data);
								if(data[0] != null && data[1] != null){
									var change = data[0]>=0 ? "<span style='color: #52ff71; font-weight:700; font-size: 14px'>(" : "<span style='color: #ff5252; font-weight:700; font-size: 14px'>(";
									$(this_ticker).append(" <span style='margin-left:2px; font-weight:400; font-size: 16px'>"+data[1].toFixed(2)+"</span> "+change+data[0].toFixed(2)+"%)</span>");
									$(this_element).attr("latest_loaded", data[1]);
								}
							} else {
								alert('Error!');
								console.log(data);
							}
							quickRequestProgress = false;

						});
					}
				}
				else{
					if (!quickRequestProgress) {
						quickRequestProgress = true;
						var myModalLoading = new jBox('Modal', {
							content: '<img src="/img/ajax-loader-2.gif">'
						}); 
						myModalLoading.open();
						var symbol = $(this).attr("ticker");
						$.post('/php_dependancies/quick_search.php', {ticker: symbol, inspect: true, latest: $(this).attr("latest_loaded")}, function(data) {
							myModalLoading.close();
							if (data != "fail") {
								var myModal = new jBox('Modal', {
									width: $(window).innerWidth()
								}); 
								myModal.open();
								myModal.setContent(data+'<img src="/img/ajax-loader-3.svg" style="display: block; margin: 30px auto 20px auto;">');
								$.get("/graphs/quickSearch.html", function(graph){
									myModal.setContent(data+graph);
								});
							} else {
								alert('Error!');
								console.log(data);
							}
							quickRequestProgress = false;
						});
					}
				}
			});
		} else {
			$('.quicksearch-results').html('');
		}
	}
});
</script>
<?php 
$_SESSION['portfolio_reminder'] = "[]";
$_SESSION['watchlist_reminder_good'] = "[]";
$_SESSION['watchlist_reminder_bad'] = "[]";
$get_reminder_status = mysqli_query($db, "SELECT reminders FROM users WHERE username = '{$_SESSION['user']}'");
confirmQuery($get_reminder_status);
$get_reminder_status = mysqli_fetch_all($get_reminder_status);

if(count($get_reminder_status) == 1){
	$get_reminder_status = json_decode($get_reminder_status[0][0], true);
}

if(is_array($get_reminder_status) && isset($get_reminder_status['watchlist']) && $get_reminder_status['watchlist'] == true){
	echo "<script type='text/javascript'>var watchlist_reminders_status = true;</script>";
	$get_watchlist_items = mysqli_query($db, "SELECT item, min_expected, max_expected FROM `".$_SESSION['user']."_watchlist`");
	confirmQuery($get_watchlist_items);

	$watchlist_reminder_good = [];
	$watchlist_reminder_bad = [];
	while ($row = mysqli_fetch_assoc($get_watchlist_items)) {
		if(!empty($row['min_expected']) && is_numeric($row['min_expected']) && !empty($row['max_expected']) && is_numeric($row['max_expected'])){
			$current_price = (float) getCurrentPrice($row['item']);

			if((((float) $row['max_expected'] - $current_price) > ($current_price - (float) $row['min_expected']))){
				if (hasGoodFundamentals($db, $row['item'])) {
					$watchlist_reminder_good[] = [$row['item'], round(((float) ($current_price-$row['min_expected'])/(float) ($row['max_expected']-$current_price)),2)];
				} else {
					$watchlist_reminder_bad[] = [$row['item'], round(((float) ($current_price-$row['min_expected'])/(float) ($row['max_expected']-$current_price)),2)];
				}
				
			}
		}
	}

	$_SESSION['watchlist_reminder_good'] = json_encode($watchlist_reminder_good);
	$_SESSION['watchlist_reminder_bad'] = json_encode($watchlist_reminder_bad);
}
else{
	echo "<script type='text/javascript'>var watchlist_reminders_status = false;</script>";
}
if(is_array($get_reminder_status) && isset($get_reminder_status['portfolio']) && $get_reminder_status['portfolio'] == true){
	echo "<script type='text/javascript'>var portfolio_reminders_status = true;</script>";
	$get_portfolio_items = mysqli_query($db, "SELECT item, date_added FROM `".$_SESSION['user']."_portfolio` WHERE status = 'open'");
	confirmQuery($get_portfolio_items);

	$portfolio_reminder = [];

	while($row = mysqli_fetch_assoc($get_portfolio_items)){
		$week_day = date("l", strtotime($row['date_added']));

		if(date('l') == $week_day && !in_array($row['item'], $portfolio_reminder)){
			$portfolio_reminder[] = $row['item'];
		}
	}

	$_SESSION['portfolio_reminder'] = json_encode($portfolio_reminder);
}
else{
	echo "<script type='text/javascript'>var portfolio_reminders_status = false;</script>";
}
?>
<script type="text/javascript">
	var portfolio_modal = null;
	var watchlist_modal = null;
	var portfolio_reminder = null;
	var watchlist_reminder_good = null;
	var watchlist_reminder_bad = null;

	if(portfolio_reminders_status == true){
		portfolio_reminder = <?php echo $_SESSION['portfolio_reminder']; ?>;
		try{
			portfolio_reminder = $.parseJSON(portfolio_reminder);
		}
		catch(err){
			console.log(err);
		}
		var portfolio_content = "<h3 style='text-align: center;'>Check the following positions</h3><ul>";
		for (var i = 0; i < portfolio_reminder.length; i++) {
			if(portfolio_reminder[i] != null){
				portfolio_content += "<li style='list-style-type: none; text-align: center;'>"+portfolio_reminder[i]+"</li>";
			}
		}
		if(portfolio_reminder.length == 0){
			portfolio_content += "<li style='list-style-type: none; text-align: center; font-style: italic'>Nothing here...</li>";
		}
		portfolio_content += "</ul><button class='button' style='margin-top: 20px; font-size: 14px;' onclick='portfolio_modal.close(); dontShowAgain(\"portfolio\");'>Don't Show Again</button>";
		portfolio_modal = new jBox("Modal", {
			content: portfolio_content,
			overlay: false,
			closeButton: true,
			title: "Portfolio Reminder",
			draggable: true
		});
		portfolio_modal.close();
	}
	if(watchlist_reminders_status == true){
		watchlist_reminder_good = <?php echo $_SESSION['watchlist_reminder_good']; ?>;
		watchlist_reminder_bad = <?php echo $_SESSION['watchlist_reminder_bad']; ?>;
		try{
			watchlist_reminder_good = $.parseJSON(watchlist_reminder_good);
			watchlist_reminder_bad = $.parseJSON(watchlist_reminder_bad);
		}
		catch(err){
			console.log(err);
		}
		var watchlist_content = "<h3 style='text-align: center;'>Good Fundamentals & Good Risk/Reward Ratio</h3><ul>";
		for (var i = 0; i < watchlist_reminder_good.length; i++) {
			if(watchlist_reminder_good[i][0] != null){
				watchlist_content += "<li style='list-style-type: none; text-align: center;'>"+watchlist_reminder_good[i][0]+" ("+watchlist_reminder_good[i][1]+"/1)</li>";
			}
		}
		if(watchlist_reminder_good.length == 0){
			watchlist_content += "<li style='list-style-type: none; text-align: center; font-style: italic'>Nothing here...</li>";
		}
		watchlist_content += "</ul>";

		watchlist_content += "<h3 style='margin-top: 10px; text-align: center;'>Good Risk/Reward Ratio</h3><ul>";
		for (var i = 0; i < watchlist_reminder_bad.length; i++) {
			if(watchlist_reminder_bad[i][0] != null){
				watchlist_content += "<li style='list-style-type: none; text-align: center;'>"+watchlist_reminder_bad[i][0]+" ("+watchlist_reminder_bad[i][1]+"/1)</li>";
			}
		}
		if(watchlist_reminder_bad.length == 0){
			watchlist_content += "<li style='list-style-type: none; text-align: center; font-style: italic'>Nothing here...</li>";
		}
		watchlist_content += "</ul><button class='button' style='margin-top: 20px; font-size: 14px;' onclick='watchlist_modal.close(); dontShowAgain(\"watchlist\");'>Don't Show Again</button>";

		watchlist_modal = new jBox("Modal", {
			content: watchlist_content,
			overlay: false,
			closeButton: true,
			onClose: function() {if(portfolio_modal != null){
				//portfolio_modal.open();
				}
			},
			title: "Watchlist Reminder",
			draggable: true
		});
		//watchlist_modal.open();
	}

function dontShowAgain(type){
	$.post("/php_dependancies/reminders_actions.php", {type: type}, function(data){});
}
</script>