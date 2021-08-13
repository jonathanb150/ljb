<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<div class="info-header">
	<h1><i class="fas fa-question-circle"></i>Information</h1>
	<li>Recommended items indicate that it's a good moment to buy.</li>
	<li>Potential items are those were their true price is higher than their current price.</li>
	<li>Companies are analyzed based on their fundamentals and medium term entry points are calculated based on technical analysis.</li>
	<li>Large Cap Growing refers to the top 100 stocks in Market Cap, sorted by their change in price during the last 3 years.</li>
	<li>After choosing a stock please run a complete analysis before deciding to add it to the portfolio.</li>
</div>
<div></div>
<div class="general-container">
	<nav>
		<h1 class='general-container-selected'><i class="fas fa-chart-line"></i>Stocks</h1>
		<h1><i class="fas fa-coins"></i>Currencies</h1>
		<h1><i class="fas fa-gas-pump"></i>Commodities</h1>
	</nav>
	<div class='general-container-content' style='padding: 0px;'>
		<?php
			//Get Data
			$get_recommended = mysqli_query($db, "SELECT id, symbol, recommended, market_cap, sector FROM items WHERE recommended != ''") or die(mysqli_error($db));
			$table_1 = array();
			$table_2 = array();
			$table_3 = array();
			$table_4 = array();
			if ($get_recommended != null && mysqli_num_rows($get_recommended) > 0) {
				while ($row = mysqli_fetch_assoc($get_recommended)) {
					$item = $row['symbol'];
					$market_cap = (float)$row['market_cap'];
					$sector = $row['sector'];
					$recommended = json_decode($row['recommended'], true);
					if ($recommended != null && is_array($recommended)) {
						$recommended['item_name'] = $item;
						$recommended['sector'] = $sector;
						$recommended['market_cap'] = $market_cap;
						if (isset($recommended['table']) && $recommended['table'] == 1) {
							$table_3[] = $recommended;
						} else if (isset($recommended['table']) && $recommended['table'] == 2) {
							$table_4[] = $recommended;
						} else if (isset($recommended['table']) && $recommended['table'] == 3) {
							$table_1[] = $recommended;
						} else if (isset($recommended['table']) && $recommended['table'] == 4) {
							$table_2[] = $recommended;
						}
					}
				}
			} else {
				echo "Nothing yet...";
			}
			//Display tables
			$table_headers = ['Inmediate Buying Opportunities', 'Potential Buying Opportunities', 'Inmediate Buying Opportunities For 60 Biggest in Market Cap', 'Potential Buying Opportunities For 60 Biggest in Market Cap'];
			echo "<div class='general-container' style='background: white; box-shadow: none; max-width: 98%;'>
						<nav>
							<h1 style='font-weight: 300;' class='general-container-selected'>Recommended Large Cap Growing</h1>
							<h1 style='font-weight: 300;'>Potential Large Cap Growing</h1>
							<h1 style='font-weight: 300;'>Recommended Medium Cap</h1>
							<h1 style='font-weight: 300;'>Potential Medium Cap</h1>
						</nav>";
			for ($i = 1; $i < 5; $i++) { 
				$table = ${"table_".$i};
				if ($i < 3) {
					$table = orderNestedArrays($table, "order");
				}
				echo "<div class='general-container-content' style='padding: 10px;'>";
				if (sizeof($table) > 0) {
					echo "<table class='dataTable noSort'>";
					echo "<thead>";
						if ($i <= 2) {
							echo "<th>Sorting</th>";
						}
						echo "<th>Item</th>";
						echo "<th>Current<br>Price</th>";
						echo "<th>Target<br>Price<i class='fas fa-info-circle info' value=\"Expected price in 6-12 months based on the current PE Ratio and also on financial changes.\"></i></th>";
						echo "<th>RoI<i class='fas fa-info-circle info' value=\"Last year's return on investment.\"></i></th>";
						echo "<th>Safety<i class='fas fa-info-circle info' value=\"Safety level: Measurement of how risky the company is based on revenue, profit, debt, cash and RoI of the last year. 10 being extremely safe and 0 being very risky.\"></i></th>";
						echo "<th>M. Cap (M)</th>";
						echo "<th>Sector</th>";
						echo "<th>Financial<br>Info</th>";
						echo "<th>Updated</th>";
					echo "</thead>";
					echo "<tbody>";
					for ($a = 0; $a < sizeof($table); $a++) { 
						echo "<tr>";
							if ($i <= 2) {
								echo "<td><b>{$table[$a]['order']}</b></td>";
							}
							echo "<td><a href='/analyze_stocks.php?item={$table[$a]['item_name']}' target='_blank'><i class='far fa-chart-bar' style='margin-right: 5px; color: #414141;'></i>{$table[$a]['item_name']}</a></td>";
							echo "<td>".round((float)$table[$a]['currentPrice'], 3)."</td>";
							echo "<td>".round((float)$table[$a]['target_mediumTerm'], 3)."</td>";
							echo isset($table[$a]['currentRoi']) ? "<td>".round((float)$table[$a]['currentRoi'], 3)."</td>" : "<td>0</td>";
							echo "<td>".round((float)$table[$a]['riskRewardRatio'], 3)."</td>";
							echo "<td>".number_format((int)($table[$a]['market_cap']/1000000), 0, ".", ",")."</td>";
							echo "<td>{$table[$a]['sector']}</td>";
							echo "<td onclick='showFinancials(this)' style='cursor: pointer;' item='{$table[$a]['item_name']}'><i class='fas fa-plus'></i></td>";
							echo "<td>{$table[$a]['date']}</td>";
						echo "</tr>";
					}
					echo "</tbody>";
					echo "</table>";
					for ($b = 0; $b < sizeof($table); $b++) {
						echo "<h2 class='financials-header' style='font-weight: 300; font-size: 22px; display: none;'>Financials for <span style='font-weight: 500;'>{$table[$b]['item_name']}</span></h2>";
						echo '<div class=\'financials\' financial-for="'.$table[$b]['item_name'].'" style="display: none;">'.arrayToTable($table[$b]['financial_table']).'</div>';
					}	
					echo "</div>";
				} else {
					echo "No recommendations.</div>";
				}
			}
			echo "</div>";
		?>
	</div>
	<div class='general-container-content'>Nothing yet...</div>
	<div class='general-container-content'>Nothing yet...</div>
</div>
<script type="text/javascript">
	function showFinancials(x) {
		var item = $(x).attr("item");
		$(".financials").hide();
		$(".financials-header").hide();
		if (item != null && item.length > 0) {
			$("div[financial-for='"+item+"']").prev(".financials-header").show();
			$("div[financial-for='"+item+"']").show();
		}
	}
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>