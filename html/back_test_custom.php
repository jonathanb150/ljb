<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php
$resultMessage = null;  
if (isset($_POST['submit']) && isset($_POST['year']) && isset($_POST['fundamental']) && isset($_POST['quantity'])) {
	$year = strtotime($_POST['year']);
	$fundamental = mysqli_escape_string($db, $_POST['fundamental']);
	$quantity = (int)mysqli_escape_string($db, $_POST['quantity']);
	if ($fundamental == 'Market Cap') {
		$query = mysqli_query($db, "SELECT symbol FROM items WHERE type = 'stock' AND pe_ratio_1y != 'none' ORDER BY market_cap+0 DESC LIMIT {$quantity}");
		confirmQuery($query);
		$items = null;
		while ($row = mysqli_fetch_assoc($query)) {
			$items[] = $row['symbol'];
		}
		if (sizeof($items) == $quantity) {
			$itemsString = null;
			$pe = null;
			$peCount = 0;
			for ($i = 0; $i < sizeof($items); $i++) { 
				$thisPe = getPERatioHistorical($items[$i], (int)$_POST['year']);
				if ($thisPe != 0) {
					$itemsString .= $items[$i]." ";
					$pe .=  $thisPe . " ";
					$peCount++;
				}
			}
			$itemsString = trim($itemsString);
			$pe = trim($pe);
			$growth = "Growth";
			$tangible = "";
			$start_date = date("Y-m-d", ((int)$_POST['year'] - 31557600));
			$end_date = date("Y-m-d", (int)$_POST['year']);

			if ($pe != null && $peCount > 0) {
				$command = escapeshellcmd("sudo python3.5 ".$_SERVER['DOCUMENT_ROOT']."/algorithms/Backtesting/recommendedTrial.py '{$itemsString}' '{$pe}' '{$growth}' '{$tangible}' '{$start_date}' '{$end_date}'");
				$output = shell_exec($command);
				$resultMessage .= "sudo python3.5 ".$_SERVER['DOCUMENT_ROOT']."/algorithms/Backtesting/recommendedTrial.py '{$itemsString}' '{$pe}' '{$growth}' '{$tangible}' '{$start_date}' '{$end_date}'<br>";
				$resultMessage .= $output;
			} else {
				$resultMessage = "Historical PE Ratios couldn't be obtained for any company.";
			}
		}
	} else {
		$query = mysqli_query($db, "SELECT symbol FROM items WHERE type = 'stock' AND pe_ratio_1y != 'none'");
		confirmQuery($query);
		$sortSymbols = array();
		$sortValues = array();
		$sortKeys = array();
		$items = array();
		while ($row = mysqli_fetch_assoc($query)) {
			$f_table = $row['symbol'] . "_f";
			$query2 = mysqli_query($db, "SELECT value FROM {$f_table} WHERE type = '{$fundamental}' ORDER BY date DESC LIMIT 4");
			confirmQuery($query2);
			if (mysqli_num_rows($query2) == 4) {
				$result = mysqli_fetch_all($query2);
				$sum = 0;
				for ($i = 0; $i < 4; $i++) { 
					$sum = $sum + (float)$result[$i][0];
				}
				$sortSymbols[] = $row['symbol'];
				$sortValues[] = $sum;
			}
		}
		if (sizeof($sortValues) >= $quantity) {
			arsort($sortValues);
			for ($i = 0; $i < $quantity; $i++) { 
				$sortKeys[] = array_keys($sortValues)[$i];
			}
			for ($i = 0; $i < $quantity; $i++) { 
				$items[] = $sortSymbols[$sortKeys[$i]];
			}
		}
		if (sizeof($items) == $quantity) {
			$itemsString = null;
			$pe = null;
			$peCount = 0;
			for ($i = 0; $i < sizeof($items); $i++) { 
				$thisPe = getPERatioHistorical($items[$i], (int)$_POST['year']);
				if ($thisPe != 0) {
					$itemsString .= $items[$i]." ";
					$pe .=  $thisPe . " ";
					$peCount++;
				}
			}
			$itemsString = trim($itemsString);
			$pe = trim($pe);
			$growth = "Growth";
			$tangible = "";
			$start_date = date("Y-m-d", ((int)$_POST['year'] - 31557600));
			$end_date = date("Y-m-d", (int)$_POST['year']);

			if ($pe != null && $peCount > 0) {
				$command = escapeshellcmd("sudo python3.5 ".$_SERVER['DOCUMENT_ROOT']."/algorithms/Backtesting/recommendedTrial.py '{$itemsString}' '{$pe}' '{$growth}' '{$tangible}' '{$start_date}' '{$end_date}'");
				$output = shell_exec($command);
				$resultMessage = $output;
			} else {
				$resultMessage = "Historical PE Ratios couldn't be obtained for any company.";
			}
		}
	}
}
else if (isset($_POST['submit'])) {
	$resultMessage = 'Please fill all the inputs.';
}
?>
<form class='main-form' method="POST"> 
	<h1 style="margin-bottom: 15px;">Backtest options</h1>
	<select name='quantity'>
		<?php  
			for ($i = 5; $i < 51; $i++) { 
				echo "<option value='{$i}'>{$i}</option>";
			}
		?>
	<select>
	<label>Biggest stocks by</label>
	<select name='fundamental'>
		<option value='Market Cap'>Market Cap</option>
		<option value="profit">Profit</option>
		<option value="revenue">Revenue</option>
		<option value="eps">EPS</option>
		<option value="equity">Equity</option>
		<option value="cash">Cash</option>
		<option value="debt">Debt</option>
	<select>
	<label>Year</label>
	<select name='year'>
		<?php  
			for ($i = 0; $i < 36; $i++) { 
				$unix = 315620491 + ($i*31557600);
				$year = date("Y", $unix);
				echo "<option value='{$unix}'>{$year}</option>";
			}
		?>
	<select>
	<input type="submit" name="submit" value="Submit" style="margin-top: 20px;">
</form>
<div></div>
<div id="resultMessage" style='display: <?php if ($resultMessage == null) { echo 'none'; } else { echo 'inline-block'; }?>;'>
	<?php echo $resultMessage; ?>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>