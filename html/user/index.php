<?php require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db_inveltio.php"; ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php"; ?>
<?php require "functions.php"; ?>
<?php session_start(); ?>
<?php
if (!verifyLoggedIn()) {
	redirect("/login.php".(isset($_GET['redirect']) ? "?redirect={$_GET['redirect']}" : ""));
} else {
	$get_numbers = mysqli_query($db2, "SELECT uid, balance, name FROM users WHERE email = '{$_SESSION['user']}'");
	confirmQuery($get_numbers);
	if ($row = mysqli_fetch_assoc($get_numbers)) {
		$uid = $row['uid'];
		$balance = $row['balance'];
		$name = ucfirst(strtolower($row['name']));
		$invested_capital = (((getUserTotalBalance($db, 'admin')-getUserBalance($db, 'admin'))*100)/getUserTotalBalance($db, 'admin'))/100*$balance;

		$portfolio_history = mysqli_query($db2, "SELECT * FROM `{$uid}_cash`");
		confirmQuery($portfolio_history);
		$portfolio_history = mysqli_fetch_all($portfolio_history);

		$portfolio_history_dates = [];
		$portfolio_history_values = [];

		for ($i=0; $i < count($portfolio_history); $i++) { 
			$portfolio_history_dates[] = $portfolio_history[$i][0]; 
			$portfolio_history_values[] = $portfolio_history[$i][1];
		}

		$portfolio_history_dates = json_encode($portfolio_history_dates, false);
		$portfolio_history_values = json_encode($portfolio_history_values, false);

		$open_investments_percentages = [];
		$open_investments_names = [];
		$invested_stocks = [];
		$invested_bonds = [];
		$invested_others = [];
		$available_cash = round((getUserBalance($db, 'admin')*100)/getUserTotalBalance($db, "admin"), 2);

		$portfolio_distribution = mysqli_query($db, "SELECT `item` FROM `admin_portfolio` WHERE status='open' GROUP BY `item` ORDER BY `item`");
		confirmQuery($portfolio_distribution);

		while($row = mysqli_fetch_assoc($portfolio_distribution)) {
			$name_type = getItemNameType($db, $row['item']);
			$open_investments_percentages[] = round((getAllocatedCapital($db, $row['item'])*100)/getInvestedCash($db, "admin"), 2);
			$open_investments_names[] = $name_type[0];

			if(stringInVariable('stock', $name_type[1])) {
				$invested_stocks[] = getAllocatedCapital($db, $row['item']);
			}
			else if(stringInVariable('bond', $name_type[1])) {
				$invested_bonds[] = getAllocatedCapital($db, $row['item']);
			}
			else {
				$invested_others[] = getAllocatedCapital($db, $row['item']);
			}
		}

		$open_investments_percentages = json_encode($open_investments_percentages, false);
		$open_investments_names = json_encode($open_investments_names, false);

		$portfolio_distribution_names = ["Cash", "Stocks", "Bonds", "Others"];
		$portfolio_distribution_percentages = [$available_cash, round(((array_sum($invested_stocks))*100)/getUserTotalBalance($db, "admin"), 2), round(((array_sum($invested_bonds))*100)/getUserTotalBalance($db, "admin"), 2), round(((array_sum($invested_others))*100)/getUserTotalBalance($db, "admin"), 2)];

		$portfolio_distribution_names = json_encode($portfolio_distribution_names, false);
		$portfolio_distribution_percentages = json_encode($portfolio_distribution_percentages, false);

	} else {
		redirect("/login.php".(isset($_GET['redirect']) ? "?redirect={$_GET['redirect']}" : ""));
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">
	<!-- <meta name="viewport" content="width=device-width, initial-scale=0.001"> -->
	<link rel="icon" type="image/png" href="/register/media/favicon.png">
	<link href="https://fonts.googleapis.com/css?family=Heebo:100,300,400,500,700" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/register/css/normalize.min.css">
	<link rel="stylesheet" type="text/css" href="/user/styles.css">
	<script src="/register/js/jquery.min.js"></script>
	<script src="jquery-ui.min.js"></script>
	<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
	<title>Inveltio</title>
</head>
<script type="text/javascript">
	var initial_animation = true;
	$(document).ready(function(){
		var nav_height = $("#numbers_container nav").outerHeight(true)+$(".number:eq(0)").outerHeight(true)+$("#portfolio_history_chart").outerHeight(true);
		if($(window).width() <= 1100){
			nav_height = $("#numbers_container nav").outerHeight(true)+($(".number:eq(0)").outerHeight(true)*3);
		}
		$("#numbers").css("height", ($(window).height())+"px");

		$("body").animate({ opacity: 1 }, 750, "linear");

		$("#welcome").animate({ bottom: 0 }, 750, "easeInOutSine", function() {
			$("#welcome").animate({ bottom: 0 }, 750, "easeInOutSine", function() {
				$(this).animate({ opacity: 0, bottom: 96 }, 1000, "easeInOutSine");
				if($(window).width() > 1100) {
					$(".number").css("display", "inline-block");
					$("#portfolio_history_chart").css("display", "block");
				}
				else{
					$(".number").css("display", "block");
				}
				window.dispatchEvent(new Event('resize'));
				$("#numbers").animate({ height: nav_height }, 1000, "easeInOutSine", function() {
					$("#welcome").remove();
					$(".number").animate({ opacity: 1 }, 1000, "linear");
					$("#portfolio_history_chart").animate({ opacity: 1 }, 1000, "linear", function() {
						$("body").css("overflow", "auto");
						initial_animation = !initial_animation;
					});
				});
			});
		});
		$(window).resize(function(){
			if($(window).width() > 1100) {
				$(".number").css("display", "inline-block");
				$("#portfolio_history_chart").css("display", "block");
			}
			else{
				$(".number").css("display", "block");
				$("#portfolio_history_chart").css("display", "none");
			}
			if(!initial_animation){
				var nav_height = $("#numbers_container nav").outerHeight(true)+$(".number:eq(0)").outerHeight(true)+$("#portfolio_history_chart").outerHeight(true);
				if($(window).width() <= 1100){
					nav_height = $("#numbers_container nav").outerHeight(true)+($(".number:eq(0)").outerHeight(true)*3);
					$("#numbers").css("height", (nav_height)+"px");
				}
				else{
					$("#numbers").css("height", (nav_height)+"px");
				}
			}
		});
	});
</script>
<body>
	<section id="numbers">
		<div id="numbers_container">
			<nav>
				<ul>
					<li class="logo" style="text-align: left"><a href='/index.php'><img src="/register/media/logo.svg"><span>Inveltio</span></a></li
					><li style="text-align: right" class='btn_1'><a href='/logout.php'><span>LOGOUT</span></a></li>
				</ul>
			</nav>
			<p id="welcome">Welcome, <?php echo $name; ?></p>
			<div class="number">
				<p>$<?php echo number_format($balance, 2, '.', ','); ?></p>
				<span>total balance</span>
			</div
			><div class="number">
				<p>$<?php echo number_format($invested_capital, 2, '.', ','); ?></p>
				<span>invested capital</span>
			</div
			><div class="number">
				<p>$<?php echo number_format($balance-$invested_capital, 2, '.', ','); ?></p>
				<span>cash</span>
			</div>
			<div id="portfolio_history_chart"></div>
		</div>
	</section>
	<section id="pie_charts">
		<div id="open_investments">
			<div class="legend show_mobile">
				<h1>Open Investments</h1>
				<ul>
					
				</ul>
			</div>
			<div id="open_investments_chart"></div
			><div class="legend hide_mobile" style="text-align: right">
				<h1>Open Investments</h1>
				<ul>
					
				</ul>
			</div>
		</div>
		<div id="portfolio_distribution">
			<div class="legend">
				<h1>Portfolio Distribution</h1>
				<ul>
					<li><div style="background: rgba(66, 189, 249, 0.85)"></div><span>Cash</span></li>
					<li><div style="background: rgba(255, 247, 174, 0.85)"></div><span>Bonds</span></li>
					<li><div style="background: rgba(120, 255, 241, 0.85)"></div><span>Stocks</span></li>
					<li><div style="background: rgba(255, 181, 218, 0.85)"></div><span>Others</span></li>
				</ul>
			</div
			><div id="portfolio_distribution_chart"></div>
		</div>
	</section>
	<footer>
		<div id="logo_copyright">
			<a href='/index.php'><img src="/register/media/logo_white.svg"></a
			><p>© <?php echo date('Y'); ?></p>
		</div
		><div id="logout_up">
			<a href='/logout.php'><span>Logout</span></a>
		</div>
	</footer>

	<script type="text/javascript">
		var color_palette = ["RGBA(66, 189, 249, 0.85)", "RGBA(67, 227, 251, 0.85)", "RGBA(81, 255, 232, 0.85)", "RGBA(89, 252, 159, 0.85)", "RGBA(228, 253, 110, 0.85)", "RGBA(252, 195, 214, 0.85)", "RGBA(251, 175, 162, 0.85)", "RGBA(255, 248, 230, 0.85)", "RGBA(253, 234, 219, 0.85)", "RGBA(252, 218, 206, 0.85)", "RGBA(231, 216, 247, 0.85)", "RGBA(231, 183, 243, 0.85)", "RGBA(212, 185, 216, 0.85)", "RGBA(165, 222, 179, 0.85)", "RGBA(201, 228, 135, 0.85)", "RGBA(200, 241, 225, 0.85)", "RGBA(248, 241, 225, 0.85)", "RGBA(192, 227, 233, 0.85)", "RGBA(209, 205, 222, 0.85)", "RGBA(248, 156, 241, 0.85)"];
		var open_investments_names = <?php echo $open_investments_names; ?>;
		var open_investments_percentages = <?php echo $open_investments_percentages; ?>;
		var legend_colors = [];


		for (var i = 0; i < open_investments_names.length; i++) {
			if(typeof color_palette[i] !== 'undefined') {
				legend_colors.push(color_palette[i]);
			}

			if(i <= 6){
				$("#open_investments .legend ul").append("<li><span>"+open_investments_names[i]+"</span><div style='background: "+legend_colors[i]+"'></div></li>");
			}
			else if(i == 7){
				$("#open_investments .legend ul").append("<li class='into_the_legend collapsed' onclick='intoTheLegend(this)'><p>view more <img src='/register/media/plus.svg'></p></li>");
			}
			else{
				$("#open_investments .legend ul").append("<li class='hidden_legend'><span>"+open_investments_names[i]+"</span><div style='background: "+legend_colors[i]+"'></div></li>");
			}
		}

		function intoTheLegend(element) {
			if($(element).hasClass("collapsed")){
				$(element).remove();
				$(".hidden_legend").show();
				$("#open_investments .legend ul").append("<li class='into_the_legend expanded' onclick='intoTheLegend(this)'><p>view less <img src='/register/media/minus.svg'></p></li>");
			}
			else{
				$(element).remove();
				$(".hidden_legend").hide();
				$("#open_investments .legend ul").append("<li class='into_the_legend collapsed' onclick='intoTheLegend(this)'><p>view more <img src='/register/media/plus.svg'></p></li>");
			}
		}

		var portfolio_history_chart = [
			{
				x: <?php echo $portfolio_history_dates; ?>,
				y: <?php echo $portfolio_history_values; ?>,
				fill: 'tozeroy',
				fillcolor: 'rgba(54, 86, 144, 0.75)',
				type: 'scatter',
				line: {
				    color: 'rgba(54, 86, 144, 1)',
				    width: 1,
				    shape: 'spline',
				    smoothing: 0.5
				}
			}
		];

		var open_investments_chart = [
			{
				values: open_investments_percentages,
				labels: open_investments_names,
				type: 'pie',
				marker: {
					colors: legend_colors
				},
				automargin: false
			}
		];

		var portfolio_distribution_chart = [
			{
				values: <?php echo $portfolio_distribution_percentages; ?>,
				labels: <?php echo $portfolio_distribution_names; ?>,
				type: 'pie',
				textinfo: 'label+percent',
				insidetextorientation: 'radial',
				marker: {
					colors: ["RGBA(66, 189, 249, 0.85)", "RGBA(120, 255, 241, 0.85)", "RGBA(255, 247, 174, 0.85)", "RGBA(255, 181, 218, 0.85)"]
				},
				automargin: false
			}
		];

		var layout = {
			xaxis: {
				showgrid: false
			},
			yaxis: {
				range: [Math.min.apply(Math, <?php echo $portfolio_history_values; ?>), Math.max.apply(Math, <?php echo $portfolio_history_values; ?>)]
			},
			font: {
				family: 'Heebo, sans-serif',
				size: 16,
				color: '#696969'
			},
			margin: {
				pad: 20
			},
			plot_bgcolor: 'rgba(0, 0, 0, 0)',
			paper_bgcolor: 'rgba(0, 0, 0, 0)',
			modebar: {
				bgcolor: 'rgba(0, 0, 0, 0)',
				color: '#696969',
				activecolor: 'rgb(39, 62, 105)'
			}
		};

		var layout2 = {
			font: {
				family: 'Heebo, sans-serif',
				size: 16,
				color: '#fff'
			},
			plot_bgcolor: 'rgba(0, 0, 0, 0)',
			paper_bgcolor: 'rgba(0, 0, 0, 0)',
			modebar: {
				bgcolor: 'rgba(0, 0, 0, 0)',
				color: '#696969',
				activecolor: 'rgb(39, 62, 105)'
			},
			showlegend: false,
			margin: {
				"t": 0,
				"b": 0,
				"l": 0,
				"r": 0
			}
		};

		var layout3 = {
			font: {
				family: 'Heebo, sans-serif',
				size: 16,
				color: '#000'
			},
			plot_bgcolor: 'rgba(0, 0, 0, 0)',
			paper_bgcolor: 'rgba(0, 0, 0, 0)',
			modebar: {
				bgcolor: 'rgba(0, 0, 0, 0)',
				color: '#696969',
				activecolor: 'rgb(39, 62, 105)'
			},
			showlegend: false,
			margin: {
				"t": 0,
				"b": 0,
				"l": 0,
				"r": 0
			}
		};

		var config = {
			responsive: true
		};

		var config2 = {
			responsive: true,
			displayModeBar: false
		};


		Plotly.newPlot('portfolio_history_chart', portfolio_history_chart, layout, config);
		Plotly.newPlot('open_investments_chart', open_investments_chart, layout2, config2);
		Plotly.newPlot('portfolio_distribution_chart', portfolio_distribution_chart, layout3, config2);
	</script>
</body>
</html>