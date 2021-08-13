<?php
session_start();
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php");
if(isset($_SESSION['user'])){
	//Table
	$portfolio_table = $_SESSION["user"]."_portfolio";
	if (tableExists($db, $portfolio_table)) {
		$get_portfolio = mysqli_query($db, "SELECT * FROM {$portfolio_table} WHERE status = 'closed'");
		confirmQuery($get_portfolio);
		if (mysqli_num_rows($get_portfolio) > 0) {
			echo
			"<div style='margin: 20px auto;'><table id='portfolio_history' class='dataTable'>".
			"<thead>".
			"<th>Item</th>".
			"<th>Bought<br>Price</th>".
			"<th>Sold<br>Price</th>".
			"<th>Allocated<br>Capital</th>".
			"<th>Change</th>".
			"<th>Gains/Loss</th>".
			"<th>Bought Date</th>".
			"<th>Sold Date</th>".
			"<th>Duration</th>".
			"</thead><tbody>";
			while ($row = mysqli_fetch_assoc($get_portfolio)) {
				$bought_price = (float)number_format($row['bought_price'], 10, ".","");
				$sold_price = (float)number_format($row['sold_price'], 10, ".","");
				$allocated_capital = (float)number_format($row['allocated_capital'], 10, ".","");
				$change = (float)number_format((($sold_price*100)/$bought_price)-100, 2, ".","");
				$gains_loss = (float)number_format(($sold_price*$allocated_capital)/$bought_price, 2, ".","");
				echo 
				"<tr>".
				"<td><a href='/analyze.php?item={$row['item']}' target='_blank'><i class='far fa-chart-bar' style='margin-right: 5px; color: #414141;'></i>{$row['item']}</a></td>".
				"<td>".(number_format((float)$row['bought_price'], 2, '.', ''))."</td>".
				"<td>".(number_format((float)$row['sold_price'], 2, '.', ''))."</td>".
				"<td><b>$</b> ".(number_format((float)$row['allocated_capital'], 2, '.', ','))."</td>".
				"<td style='font-weight: 700; color:".($change >= 0 ? "#00d827" : "#ff0000")."'>".(number_format($change, 2, '.', ''))."%</td>".
				"<td style='font-weight: 700; color:".($gains_loss-$allocated_capital >= 0 ? "#00d827" : "#ff0000")."'>$ ".(number_format($gains_loss-$allocated_capital, 2, '.', ''))."</td>";
				echo
				"<td>{$row['date_added']}</td>".
				"<td>{$row['date_closed']}</td>".
				"<td>".((strtotime($row['date_closed'])-strtotime($row['date_added']))/86400)." days</td>".
				"</tr>";

			}
			echo "</tbody></table></div>";
			echo "<div style='font-size: 16px; margin-bottom: 20px;'>Total Profit/Loss: <b>$".round((double)mysqli_fetch_all(mysqli_query($db, "SELECT sum(((((sold_price*100)/bought_price)-100)*allocated_capital)/100) as total_profit FROM `admin_portfolio` where status = 'closed'"))[0][0], 2)."</b></div>";
		}
	}

	//Graph
	$history_table = $_SESSION['user']."_portfolio_history";
	if(tableExists($db, $history_table)){
		$data = mysqli_query($db, "SELECT * FROM `{$history_table}`");
		confirmQuery($data);
		$data = mysqli_fetch_all($data);
		if(count($data) > 0){
			$dates = "";
			$invested_capital = "";
			$total_balance = "";
			$cash = "";

			for ($i=0; $i < count($data); $i++) { 
				$dates .= $data[$i][0].($i<count($data)-1 ? "," : "");
				$invested_capital .= $data[$i][2].($i<count($data)-1 ? "," : "");
				$total_balance .= $data[$i][1].($i<count($data)-1 ? "," : "");
				$cash .= $data[$i][3].($i<count($data)-1 ? "," : "");
			}

			$portfolio_history = shell_exec("python3.7 -W ignore {$_SERVER["DOCUMENT_ROOT"]}/algorithms/Others/portfolioChart.py '{$total_balance}' '{$cash}' '{$invested_capital}' '{$dates}'");
			$portfolio_history = json_decode($portfolio_history, true);
			echo $portfolio_history['graph'];
		}
	}
}
?>