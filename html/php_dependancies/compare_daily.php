<?php require 'functions.php'; ?>
<?php
require 'db.php';
if (isset($_POST['ticker']) && isset($_POST['compare_ticker'])) {
	$ticker = getItemTable($db, $_POST['ticker']);
	$compare_ticker = getItemTable($db, $_POST['compare_ticker']);

	$time = date("Y-m-d", time());
	$last_year = date("Y-m-d", time() - 31557600);
	$ticker_query = mysqli_query($db, "SELECT date, close FROM `{$ticker}` WHERE date <= '{$time}' AND date >= '{$last_year}' ORDER by date DESC");
	$compare_ticker_query = mysqli_query($db, "SELECT date, close FROM `{$compare_ticker}` WHERE date <= '{$time}' AND date >= '{$last_year}' ORDER by date DESC");
	$spx = mysqli_query($db, "SELECT date, close FROM `SPX` WHERE date <= '{$time}' AND date >= '{$last_year}' ORDER by date DESC");
	$ticker_query = mysqli_fetch_all($ticker_query);
	$compare_ticker_query = mysqli_fetch_all($compare_ticker_query);
	$spx = mysqli_fetch_all($spx);

	if(count($ticker_query) > 0 && count($compare_ticker_query) > 0 && count($spx) > 0) {
		if(strtotime($spx[0][0]) < strtotime($ticker_query[0][0])) {
			for ($i=0; $i < count($ticker_query); $i++) {
				if(strtotime($ticker_query[$i][0]) == strtotime($spx[0][0])) {
					$ticker_query = array_splice($ticker_query, $i);
					break;
				}
			}
		}

		if(strtotime($spx[0][0]) < strtotime($compare_ticker_query[0][0])) {
			for ($i=0; $i < count($compare_ticker_query); $i++) {
				if(strtotime($compare_ticker_query[$i][0]) == strtotime($spx[0][0])) {
					$compare_ticker_query = array_splice($compare_ticker_query, $i);
					break;
				}
			}
		}

		if(strtotime($spx[0][0]) == strtotime($ticker_query[0][0]) && strtotime($ticker_query[0][0]) == strtotime($compare_ticker_query[0][0])) {
			echo "<table id='compare_daily_table' class='dataTable noSort'><thead><th>Date</th><th>{$_POST['ticker']}</th><th>SP500</th><th>{$_POST['compare_ticker']}</th></thead><tbody>";

			for ($i=0; $i < count($spx)-1; $i++) {
				$spx_change = round((($spx[$i][1]*100)/$spx[$i+1][1])-100, 2);
				$ticker_change = round((($ticker_query[$i][1]*100)/$ticker_query[$i+1][1])-100, 2);
				$compare_ticker_change = round((($compare_ticker_query[$i][1]*100)/$compare_ticker_query[$i+1][1])-100, 2);

				echo "<tr><td>{$spx[$i][0]}</td>";
				echo "<td style='color: ".($ticker_change > $spx_change ? 'green':'red').";'>".$ticker_change."%</td>";
				echo "<td>".$spx_change."%</td>";
				echo "<td>".$compare_ticker_change."%</td></tr>";
			}

			echo "</tbody></table>";
		}
	}
}
?>