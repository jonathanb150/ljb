<?php  
require("/var/www/ljb.solutions/html/php_dependancies/db.php");
require("/var/www/ljb.solutions/html/php_dependancies/functions.php");
?>
<?php
//Never stop running
set_time_limit(0);  
ignore_user_abort(true);
$time_start = microtime(true); //Timer

//Fundamentals
$fundamentals = ['EPS', 'Assets', 'Equity', 'MarketCap', 'Profit', 'Revenue', 'Cash', 'Debt', 'FreeCashFlow', 'TotalExpenses', 'DividendYield'];
$zacks_tags = ['eps_diluted', 'assets_total', 'share_holders_equity', 'market_cap', 'net_income', 'revenue', 'cash', 'debt_lt_total_ttm', 'free_cash_flow', 'expenses_total', 'dividend_yield'];
$not_multiply = ['EPS', 'DividendYield'];

//Select
$select_all = mysqli_query($db, "SELECT symbol from items WHERE type LIKE '%stock%' ORDER BY id ASC");
$symbols = mysqli_fetch_all($select_all);
if (mysqli_num_rows($select_all) > 0 && $symbols != null && sizeof($symbols) > 0) {
	$success = 0;
	$success_array = array();
	$fail = 0;
	$fail_symbols = array();
	for ($i = 0; $i < sizeof($symbols); $i++) { 
		$current_symbol = $symbols[$i][0];
		$current_table = $current_symbol."_f";
		$counter = 0;
		$failBool = false;
		if (!tableExists($db, $current_table)) {
			confirmQuery(mysqli_query($db, "CREATE TABLE `{$current_table}` (
				`type` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`date` date NOT NULL,
				`value` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"));
		}
		for ($f = 0; $f < sizeof($fundamentals); $f++) { 
			//Get last fundamental entry we have
			$last_entry = getLastFundamental($db, $current_table, $fundamentals[$f]);
			if ($last_entry != null && strtotime($last_entry) != FALSE && is_numeric(strtotime($last_entry))) {
				//Get Zacks data
				$item = str_replace("_", ".", $current_symbol);
				$zacks_data = zacksAPIRequest($item, $zacks_tags[$f]);
				$notNA = false;
				if ($zacks_data != null && is_array($zacks_data) && sizeof($zacks_data) > 0) { //If we were able to get data
					if (strtotime($last_entry) < strtotime(array_keys($zacks_data)[0])) { //If date of last entry is bigger
						for ($in = 0; $in < sizeof($zacks_data); $in++) {  //Update
							$date = array_keys($zacks_data);
							$value = array_values($zacks_data);
							$current_date = date_format(date_create($date[$in]), "Y-m-d");
							if ($value[$in] != null && $value[$in] != "N/A" && is_numeric($value[$in]) && strtotime($last_entry) < strtotime($current_date)) {
								$notNA = true;
								$current_value = $value[$in];
								if ($fundamentals[$f] != 'EPS' && $fundamentals[$f] != 'DividendYield') {
									$current_value = (float)$current_value * 1000000;
								}
								$query = mysqli_query($db, "INSERT INTO `{$current_table}` (type, date, value) VALUES ('{$fundamentals[$f]}', '{$current_date}', '{$current_value}')");
								confirmQuery($query);
							} else if ($value[$in] != null && $value[$in] != "N/A" && is_numeric($value[$in]) && strtotime($last_entry) >= strtotime($current_date)) {
								break;
							}
						}
						if ($notNA && $fundamentals[$f] != 'MarketCap' && $fundamentals[$f] != 'DividendYield' && !isset($success_array[$current_symbol])) {
							$success_array[$current_symbol] = null;
						} 
						if ($notNA && $fundamentals[$f] != 'MarketCap' && $fundamentals[$f] != 'DividendYield') {
							$success_array[$current_symbol][] = $fundamentals[$f];
						}
						echo $current_symbol . " - Updated! ({$fundamentals[$f]})<br>";
					}
				} else {
					echo $current_symbol . " - Issue accessing Zacks ({$fundamentals[$f]})<br>";
					$fail++;
					$fail_symbols[] = $current_symbol;
					$failBool = true;
					break;
				}
			} else if ($last_entry == null) {
				//Get Zacks data
				$item = str_replace("_", ".", $current_symbol);
				$zacks_data = zacksAPIRequest($item, $zacks_tags[$f]);
				$notNA = false;
				if ($zacks_data != null && is_array($zacks_data) && sizeof($zacks_data) > 0) { //If we were able to get data
					for ($in = 0; $in < sizeof($zacks_data); $in++) {  //Update
						$date = array_keys($zacks_data);
						$value = array_values($zacks_data);
						$current_date = date_format(date_create($date[$in]), "Y-m-d");
						if ($value[$in] != null && $value[$in] != "N/A" && is_numeric($value[$in])) {
							$notNA = true;
							$current_value = $value[$in];
							if ($fundamentals[$f] != 'EPS' && $fundamentals[$f] != 'DividendYield') {
								$current_value = (float)$current_value * 1000000;
							}
							$query = mysqli_query($db, "INSERT INTO `{$current_table}` (type, date, value) VALUES ('{$fundamentals[$f]}', '{$current_date}', '{$current_value}')");
							confirmQuery($query);
						}
					}
					if ($notNA && $fundamentals[$f] != 'MarketCap' && $fundamentals[$f] != 'DividendYield' && !isset($success_array[$current_symbol])) {
						$success_array[$current_symbol] = null;
					} 
					if ($notNA && $fundamentals[$f] != 'MarketCap' && $fundamentals[$f] != 'DividendYield') {
						$success_array[$current_symbol][] = $fundamentals[$f];
					}
					echo $current_symbol . " - Updated! ({$fundamentals[$f]})<br>";
				} else {
					echo $current_symbol . " - Issue accessing Zacks ({$fundamentals[$f]})<br>";
					$fail++;
					$fail_symbols[] = $current_symbol;
					$failBool = true;
					break;
				}
			}
		}
		if (!$failBool) {
			$success++;
		}
	}
	if ($success > 0 || $fail > 0) {
		$failed_report = "";
		for ($i = 0; $i < sizeof($fail_symbols); $i++) { 
			$failed_report .= $fail_symbols[$i]."<br>";
		}
		$total = sizeof($symbols);
		$report = "{$total} total items.<br>Successfully attempted to update the fundamentals of {$success} items.<br>Failed to update the fundamentals of {$fail} items.<br>" . ($fail > 0 ? ("<br>The following items failed:<br>".$failed_report) : "");
		if (sizeof($success_array) > 0) {
			$report .= "There is new financial information for the following items:<br>";
			for ($s_a = 0; $s_a < sizeof($success_array); $s_a++) { 
				$report .= array_keys($success_array)[$s_a] . " - ";
				for ($s_b = 0; $s_b < sizeof($success_array[(array_keys($success_array)[$s_a])]); $s_b++) { 
					$report .= $success_array[(array_keys($success_array)[$s_a])][$s_b];
					if ($s_b != (sizeof($success_array[(array_keys($success_array)[$s_a])]) - 1)) {
						$report .= ", ";
					}
				}
				$report .= "<br>";
			}
		} else {
			$report .= "There is no new financial information for any item.<br>";
		}
		$time_end = microtime(true);
		$execution_time = number_format((($time_end - $time_start)/60), 2);
		$report .= "<br>Execution Time: {$execution_time} minutes.";
		echo "<br>Execution Time: {$execution_time} minutes.";
		$report = mysqli_escape_string($db, $report);
		mysqli_query($db, "INSERT INTO logs (type, log, date) VALUES ('Update Fundamentals', '{$report}', NOW())") or die(mysqli_error($db));
	} 
} else {
	mysqli_query($db, "INSERT INTO logs (type, log, date) VALUES ('Update Fundamentals', 'Unfortunately, the update was not possible because access to the database was not available.', NOW())") or die(mysqli_error($db));
}
?>