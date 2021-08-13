<?php
session_start();
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php");
$portfolio_table = $_SESSION["user"]."_portfolio";
$graphs_table = $_SESSION["user"]."_graphs";
$arrays_table = $_SESSION["user"]."_arrays";
if (tableExists($db, $portfolio_table) && isset($_GET['type'])) {
	$get_portfolio = mysqli_query($db, "SELECT * FROM {$portfolio_table} WHERE status = 'open' AND term = '{$_GET['type']}'");
	confirmQuery($get_portfolio);
	$avg_change = [];
	$avg_profit = [];
	if (mysqli_num_rows($get_portfolio) > 0) {
		echo
		"<table id='portfolio' class='dataTable'>".
		"<thead>".
		"<th>Item</th>".
		"<th>Current<br>Price</th>".
		"<th>Bought<br>Price</th>".
		"<th>Allocated<br>Shares</th>".
		"<th>Target<br>Price</th>".
		"<th>Selling<br>Price</th>".
		"<th>Change</th>".
		"<th>Gains/Loss</th>".
		"<th>SP500</th>".
		"<th>Added</th>".
		"<th>Edit</th>".
		"<th>Close</th>".
		"<th>Notes & Tags</th>".
		"<th>Buy More</th>".
		"</thead><tbody>";
		while ($row = mysqli_fetch_assoc($get_portfolio)) {
			$current_price = (float)number_format(getCurrentPrice($row['item']), 10, ".","");
			$bought_price = (float)number_format($row['bought_price'], 10, ".","");
			$allocated_capital = (float)number_format($row['allocated_capital'], 10, ".","");
			$change = (float)number_format((($current_price*100)/$bought_price)-100, 2, ".","");
			$gains_loss = (float)number_format(($current_price*$allocated_capital)/$bought_price, 2, ".","");
			$avg_change[] = $change;
			$avg_profit[] = $gains_loss-$allocated_capital;
			echo 
			"<tr>".
			"<td><a href='/analyze.php?item={$row['item']}' target='_blank'><i class='far fa-chart-bar' style='margin-right: 5px; color: #414141;'></i>{$row['item']}</a></td>".
			"<td>".(number_format((float)getCurrentPrice($row['item']), 2, '.', ''))."</td>".
			"<td>".(number_format((float)$row['bought_price'], 2, '.', ''))."</td>".
			"<td>".number_format(($row['allocated_capital']/$row['bought_price']),3,".","")."</td>".
			"<td>".(number_format((float)$row['target_price'], 2, '.', ''))."</td>".
			"<td>".(number_format((float)$row['selling_price'], 2, '.', ''))."</td>".
			"<td style='font-weight: 700; color:".($change >= 0 ? "#00d827" : "#ff0000")."'>".(number_format($change, 2, '.', ''))."%</td>".
			"<td style='font-weight: 700; color:".($gains_loss-$allocated_capital >= 0 ? "#00d827" : "#ff0000")."'>$ ".(number_format($gains_loss-$allocated_capital, 2, '.', ''))."</td>";
			$sp_change = spChange($db, $row['date_added'], date("Y-m-d"));
			echo "<td style='font-weight: 700; color:".($change-$sp_change >= 0 ? "#00d827" : "#ff0000")."'>".(number_format($sp_change, 2, '.', ''))."%</td>";
			echo
			"<td>{$row['date_added']}</td>".
			"<td value='{$row['item']}'><a href='/edit_portfolio.php?item={$row['id']}'><i class='fas fa-edit'></i></a></td>".
			"<td value='{$row['item']}'><a href='/close_portfolio_item.php?item={$row['id']}'><i class='fas fa-money-check-alt'></i></a></td>".
			"<td onclick=\"selectedStock = '{$row['item']}'; selectedPositionId = {$row['id']}; showNotes(selectedStock);\" class='item_notes'><i class='fas fa-plus'></i></td>".
			"<td><form method='POST' action='/add_to_portfolio.php'><button style='background:none; border:none'><i class='fas fa-plus' style='font-size:14px;'></i></button><input name='LJBScore' type='hidden' value='auto'><input name='portfolio[]' type='hidden' value='0'><input name='portfolio[Name]' type='hidden' value='{$row['item']}'></form></td>".
			"</tr>";

		}
		echo "</tbody></table>";
		echo portfolioProfitChange($db, $portfolio_table);
	} else {
		echo "Nothing here...";
	}
} else {
	echo "Nothing here...";
}
if(!isset($_GET['type'])) {
	$get_graphs = mysqli_query($db, "SELECT graph FROM {$graphs_table} WHERE identifier = 'portfolio'");
	$get_tables = mysqli_query($db, "SELECT array FROM {$arrays_table} WHERE identifier = 'portfolio'");
	$get_tables2 = mysqli_query($db, "SELECT economy_health FROM users WHERE username = '{$_SESSION['user']}'");
	if (mysqli_num_rows($get_tables) == 1 && mysqli_num_rows($get_tables2) == 1) {
		$portfolioTables = json_decode(mysqli_fetch_assoc($get_tables)['array'], true);
		$portfolioTables2 = json_decode(mysqli_fetch_assoc($get_tables2)['economy_health'], true);
		$recommended_dist = $portfolioTables2['distribution'];
		echo '<div style="display: inline-block; width: 42.5%;"><h2>Standard Distribution</h2>'.arrayToTable($portfolioTables['standardDist']).'</div>';
		echo '<div style="display: inline-block; width: 42.5%;"><h2>Current Distribution</h2>'.arrayToTable($portfolioTables['currentDist']).'</div>';
		echo '<h2>Recommended Distribution</h2>'.arrayToTable($recommended_dist);
	}
	if (mysqli_num_rows($get_graphs) == 1) {
		$portfolioGraphs = json_decode(mysqli_fetch_assoc($get_graphs)['graph'], true);
		echo '<div style="text-align: center;"><div style="display: inline-block; width: 45%;">';
		echo $portfolioGraphs['graph1'];
		echo "</div><div style='display: inline-block; width: 45%;'";
		echo $portfolioGraphs['graph2'];
		echo "</div></div>";
	}
}

function portfolioProfitChange($db, $portfolio_table) {
	$get_portfolio = mysqli_query($db, "SELECT * FROM {$portfolio_table} WHERE status = 'open'");
	confirmQuery($get_portfolio);
	$avg_change = [];
	$avg_profit = [];
	if (mysqli_num_rows($get_portfolio) > 0) {
		while ($row = mysqli_fetch_assoc($get_portfolio)) {
			$current_price = (float)number_format(getCurrentPrice($row['item']), 10, ".","");
			$bought_price = (float)number_format($row['bought_price'], 10, ".","");
			$allocated_capital = (float)number_format($row['allocated_capital'], 10, ".","");
			$change = (float)number_format((($current_price*100)/$bought_price)-100, 2, ".","");
			$gains_loss = (float)number_format(($current_price*$allocated_capital)/$bought_price, 2, ".","");
			$avg_change[] = $change;
			$avg_profit[] = $gains_loss-$allocated_capital;
		}
		return "<p>Average Change: <span class='portfolio_total_avg' style='font-weight: 700; color:".(array_sum($avg_change)/count($avg_change) >= 0 ? "#00d827" : "#ff0000")."'>".round((array_sum($avg_change)/count($avg_change)),2)."%</span></p><p>Total Profit/Loss: <span class='portfolio_total_avg' style='font-weight: 700; color:".(array_sum($avg_profit) >= 0 ? "#00d827" : "#ff0000")."'>$".round(array_sum($avg_profit), 2)."</span></p>";	
	}

	return "";
}

function spChange($db, $start_date, $end_date) {
	$s_date = mysqli_query($db, "SELECT close FROM SPX WHERE date >= '{$start_date}' ORDER BY date ASC LIMIT 1");
	confirmQuery($s_date);
	$e_date = mysqli_query($db, "SELECT close FROM SPX WHERE date <= '{$end_date}' ORDER BY date DESC LIMIT 1");
	confirmQuery($e_date);

	$s_date = mysqli_fetch_all($s_date);
	$e_date = mysqli_fetch_all($e_date);

	if(isset($s_date[0][0]) && isset($e_date[0][0])) {
		$change = (((float) $e_date[0][0])*100/((float) $s_date[0][0]))-100;
		return round($change, 2);
	}
}
?>