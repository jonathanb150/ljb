<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
<script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
<?php require "db.php"; ?>
<?php 
if(isset($_GET["item"])) {
	$query = mysqli_query($db, "SELECT fundamentals_status FROM items WHERE symbol = '{$_GET["item"]}' OR apiTicker = '{$_GET["item"]}'") or die(mysqli_error($db));

	if($row = mysqli_fetch_assoc($query)) {
		$json = json_decode($row['fundamentals_status'], true);

		if(is_array($json) && count($json) > 0) {
			$labels = []; 
			$data = [];

			for ($i=0; $i < count($json); $i++) { 
				$labels[] = $json[$i]["date"];
				$data[] = Array(t=>$json[$i]["date"], y=>$json[$i]["value"]);
			}

			$labels = json_encode($labels);
			$data = json_encode($data);
		}
	}
}
?>
<canvas id="mark_fundamentals_chart" width="800" height="400" style="margin: 5% auto;"></canvas>
<script type="text/javascript">
	Chart.defaults.global.elements.line.tension = 0;
	Chart.defaults.global.elements.line.backgroundColor = "rgba(115, 152, 208, 0.1)";
	Chart.defaults.global.elements.line.borderWidth = 1;
	Chart.defaults.global.elements.line.fill = "bottom";
	Chart.defaults.global.elements.line.borderColor = "rgba(115, 152, 208, 0.5)";
	Chart.defaults.global.elements.point.backgroundColor = "rgba(115, 152, 208, 1)";
	Chart.defaults.global.defaultFontFamily = "'Lato', sans-serif";

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
		                stepSize: 1.0
		            }
				}]
			},
			tooltips: {
				mode: 'index'
			}
		}
	});
</script>