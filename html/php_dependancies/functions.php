<?php
class Timer {
    private $time = null;
    public function __construct() {
        $this->time = microtime(true);
    }

    public function __destruct() {
        echo '<div style="display: none;">'.(microtime(true)-$this->time).' seconds.</div>';
    }
}

function displayAllErrors() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function stringInVariable($a, $b) {
	if (strpos($b, $a) !== FALSE) {
		return true;
    }
    return false;
}

function lastPortfolioChange($db, $admin){
	$table = $admin."_portfolio_history";
	
	if(tableExists($db, $table)){
		$getLastTwoDays = mysqli_query($db, "SELECT total_balance FROM `{$table}` ORDER BY date DESC LIMIT 2");
		confirmQuery($getLastTwoDays);

		$getLastTwoDays = mysqli_fetch_all($getLastTwoDays);

		if(count($getLastTwoDays) == 2){
			$day_one = $getLastTwoDays[0][0];
			$day_two = $getLastTwoDays[1][0];

			$change = (double) ((($day_one*100)/$day_two)-100);

			return (double) $change;
		}
	}

	return 0;
}
function getClients($db) {
	$query = mysqli_query($db, "SELECT username FROM users WHERE admin = 0");
	confirmQuery($query);

	$array = [];

	while($row = mysqli_fetch_assoc($query)) {
		$array[] = $row['username'];
	}

	return $array;
}
function getItemTable($db, $symbol){
	$query = mysqli_query($db, "SELECT tableName FROM items WHERE symbol = '{$symbol}' OR tableName = '{$symbol}'") or die("Error.");
	while($row = mysqli_fetch_assoc($query)){
		return $row["tableName"];
	}
	return false;
}
function unreadNotification($db, $user){
	$notification_table = $user."_notifications";
	$unread_notification = mysqli_query($db, "SELECT id FROM `{$notification_table}` WHERE status = 'unread'") or die("Error.");

	if(mysqli_num_rows($unread_notification) > 0){
		return mysqli_num_rows($unread_notification);
	}
	else{
		return 0;
	}
}
function unixTimeDifference($past) {
    $difference = time() - $past;
    if ($difference <= 60) {
        return '1 minute';
    } else if ($difference > 60 && $difference < 3600) {
        return (int)($difference / 60).((int)($difference / 60) > 1 ? ' minutes' : ' minute');
    } else if ($difference >= 3600 && $difference < 86400) {
        return (int)($difference / 3600).((int)($difference / 3600) > 1 ? ' hours' : ' hour');
    } else if ($difference >= 86400) {
        return (int)($difference / 86400).((int)($difference / 86400) > 1 ? ' days' : ' day');
    }
}
function apiRequest($symbol, $base_url){
	sleep(1);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $base_url.strtoupper($symbol));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	/*if(stringInVariable("stooq", $base_url)) {
		curl_setopt($ch, CURLOPT_PROXY, "94.156.35.182:3128");
	}*/

	$data = curl_exec($ch);
	return $data;
}

function apiProcessFRED($user_input, $item_name, $type, $label, $db, $ticker, $source, $is_etf){
	$item_name = strtoupper($item_name);
	$array = csvToArray($user_input);
	if(count($array)>1){
		if(!tableExists($db, strtoupper($item_name))){
			addItemTable($db, $item_name, $type, $label, $ticker, $source, $is_etf);
		}
		else{
			$check_current = mysqli_query($db,"SELECT id FROM `{$item_name}`");
			confirmQuery($check_current);
			if(mysqli_num_rows($check_current) < count($array)){
				$delete = mysqli_query($db, "TRUNCATE `{$item_name}`");
				confirmQuery($delete);
			}
			else{
				return true;
			}
		}
		for ($i=1; $i < count($array); $i++) { 
			if(is_array($array[$i]) && count($array[$i]) == 2 && is_numeric($array[$i][1])){
				$date = mysqli_escape_string($db, $array[$i][0]);
				$value = mysqli_escape_string($db, $array[$i][1]);
				$query = mysqli_query($db, "INSERT INTO `{$item_name}` (date, value) VALUES ('{$date}', '{$value}')");
				confirmQuery($query);
			}
		}

		return true;
	}

	return false;
}

function apiProcessSTOOQ($user_input, $item_name, $type, $label, $db, $ticker, $source, $is_etf){
	$item_name = strtoupper($item_name);
	$array = csvToArray($user_input);
	if(count($array)>1){
		if(!tableExists($db, strtoupper($item_name))){
			addItemTable($db, $item_name, $type, $label, $ticker, $source, $is_etf);
		}
		else{
			$check_current = mysqli_query($db,"SELECT id FROM `{$item_name}`");
			confirmQuery($check_current);
			if(mysqli_num_rows($check_current) < count($array)){
				$delete = mysqli_query($db, "TRUNCATE `{$item_name}`");
				confirmQuery($delete);
			}
			else{
				return true;
			}
		}
		for ($i=1; $i < count($array); $i++) { 
			if(is_array($array[$i]) && count($array[$i]) >= 5){
				$date = mysqli_escape_string($db, $array[$i][0]);
				$open = mysqli_escape_string($db, $array[$i][1]);
				$high = mysqli_escape_string($db, $array[$i][2]);
				$low = mysqli_escape_string($db, $array[$i][3]);
				$close = mysqli_escape_string($db, $array[$i][4]);
				$volume = isset($array[$i][5]) ? mysqli_escape_string($db, $array[$i][5]) : 0;
				$query = mysqli_query($db, "INSERT INTO `{$item_name}` (date, open, high, low, close, volume) VALUES ('{$date}', '{$open}', '{$high}', '{$low}', '{$close}', '{$volume}')");
				confirmQuery($query);
			}
		}

		return true;
	}

	return false;
}

function apiProcessYAHOO($item_name, $type, $label, $db, $ticker, $source, $is_etf){
	$item_name = strtoupper($item_name);
	$crumb = "Not needed anymore";
	if($crumb != null){
		if(!tableExists($db, strtoupper($item_name))){
			addItemTable($db, $item_name, $type, $label, $ticker, $source, $is_etf);
		}

		$result = updateItem($db, $ticker, $crumb);
		if ($result != null) {
			return true;
		}
	}

	return false;
}

function csvToArray($csv){
	$array = array();

	if($csv != null && !empty($csv)){
		$data = explode(PHP_EOL, $csv);
		foreach ($data as $line) {
			$array[] = str_getcsv($line);	
		}
	}

	return $array;
}

function addItemTable($db, $symbol, $type, $label, $ticker, $source, $is_etf) {
	$table_name = mysqli_escape_string($db, trim(strtoupper($symbol)));
	$table_name = str_replace("-", "_", $table_name);
	if(strpos($type, "fundamental") == FALSE && strpos($type, "bond") == FALSE){
		$create_table = mysqli_query($db, "CREATE TABLE IF NOT EXISTS `{$table_name}` (
			`id` int(255) NOT NULL AUTO_INCREMENT,
			`date` date NOT NULL,
			`open` varchar(255) NOT NULL,
			`high` varchar(255) NOT NULL,
			`low` varchar(255) NOT NULL,
			`close` varchar(255) NOT NULL,
			`volume` varchar(255) NOT NULL,
			PRIMARY KEY (`date`),
			KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;");
	}
	else{
		$create_table = mysqli_query($db, "CREATE TABLE IF NOT EXISTS `{$table_name}` (
			`id` int(255) NOT NULL AUTO_INCREMENT,
			`date` date NOT NULL,
			`value` varchar(255) NOT NULL,
			PRIMARY KEY (`date`),
			KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;");
	}
	confirmQuery($create_table);
				
	if (!tableStringValueExists($db, "items", "tableName", $table_name) && !tableStringValueExists($db, "items", "symbol", mysqli_escape_string($db, trim(strtoupper($symbol))))) {
		$stockName = mysqli_escape_string($db, trim($label));
		$stockSymbol = mysqli_escape_string($db, trim(strtoupper($symbol)));
		$item_type = mysqli_escape_string($db, trim($type));
		$api_ticker = mysqli_escape_string($db, trim($ticker));
		$api_source = mysqli_escape_string($db, trim($source));
		$is_it_etf = mysqli_escape_string($db, trim($is_etf));
		$addStock = mysqli_query($db, "INSERT into items (name, symbol, yahooSymbol, tableName, dateCreated, type, apiTicker, apiSource, is_etf) VALUES ('{$stockName}', '{$stockSymbol}', '{$stockSymbol}', '{$table_name}', NOW(), '{$item_type}', '{$api_ticker}', '{$api_source}', '{$is_it_etf}')");
		confirmQuery($addStock);
	}
}

function getEconomyHealth($db, $user) {
	$economyHealth = mysqli_fetch_assoc(mysqli_query($db, "SELECT economy_health FROM users WHERE username = '{$user}'"))['economy_health'];
	$economyHealth = json_decode($economyHealth, true);
	if (is_array($economyHealth) && count($economyHealth) == 4) {
		return (string)($economyHealth['Economy'].' '.$economyHealth['Interest Rates'].' '.$economyHealth['Stock Market'].' '.$economyHealth['Bonds']);
	} 
}
function editPortfolio($db, $user) {
	$investments = getOpenInvestments($db, $user);
	$currentBalance = (double)getUserTotalBalance($db, $user);
	$portfolioDistribution = shell_exec('python3.7 '.$_SERVER['DOCUMENT_ROOT'].'/algorithms/Portfolio/portfolioAnalysis.py \''.implode(" ", $investments['item']).'\' \''.implode(" ", $investments['capital']).'\' \''.$currentBalance.'\'');
	if (!empty($portfolioDistribution) && is_array(json_decode($portfolioDistribution, true))) {
		$portfolioDistribution = json_decode($portfolioDistribution, true);
		$graphs_table = $user."_graphs";
		$arrays_table = $user."_arrays";
		$graph = array();
		$graph['graph1'] = $portfolioDistribution['graph1'];
		$graph['graph2'] = $portfolioDistribution['graph2'];
		$graph = json_encode($graph, true); 
		$graph = mysqli_escape_string($db, $graph);
		$array = array();
		$array['currentDist'] = $portfolioDistribution['currentDist']; 
		$array['standardDist'] = $portfolioDistribution['standardDist']; 
		$array = json_encode($array, true); 
		$array = mysqli_escape_string($db, $array);
		mysqli_query($db, "UPDATE {$graphs_table} SET graph = '{$graph}' WHERE identifier = 'portfolio'") or die('Error');
		mysqli_query($db, "UPDATE {$arrays_table} SET array = '{$array}' WHERE identifier = 'portfolio'") or die('Error');
	}
}
function getTotalNetProfit($db, $user) {
	$query = mysqli_query($db, "SELECT ((((sold_price+0)*(allocated_capital+0)) / (bought_price+0)) - (allocated_capital+0)) AS 'return' FROM `{$user}_portfolio` WHERE status = 'closed'");
	if (mysqli_num_rows($query) > 0) {
		$totalNetProfit = 0;
		while ($row = mysqli_fetch_assoc($query)) {
			$totalNetProfit += (double)$row['return'];
		}
		return (double)$totalNetProfit;
	}
	return 0;
}
function getInvestedCash($db, $user) {
	$query = mysqli_query($db, "SELECT sum(allocated_capital+0) FROM {$user}_portfolio WHERE status = 'open'");
	$invested_cash = mysqli_fetch_assoc($query)['sum(allocated_capital+0)'];
	return (double)$invested_cash;
}
function getOpenInvestments($db, $user) {
	$query = mysqli_query($db, "SELECT item, allocated_capital, bought_price FROM {$user}_portfolio WHERE status = 'open'");
	$array = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$array['item'][] = $row['item'];
		$array['capital'][] = ((double) $row['allocated_capital']/(double) $row['bought_price'])*((double) getCurrentPrice($row['item']));
	}
	return $array;
}
function getUserTotalBalance($db, $user) {
	$open_investments = mysqli_query($db, "SELECT item, bought_price, allocated_capital FROM {$user}_portfolio WHERE status = 'open'");
	$array = array();
	while ($row = mysqli_fetch_assoc($open_investments)) {
		$array[] = (((double)(getCurrentPrice($row['item']))*(double)$row['allocated_capital'])/(double)($row['bought_price']))-(double)($row['allocated_capital']);
	}
	$fetch_balance = mysqli_query($db, "SELECT balance FROM users WHERE username = '{$user}'") or die("Error.");
	$encoded_balance = mysqli_fetch_assoc($fetch_balance)['balance'];
	return (double)$encoded_balance+(double)array_sum($array);
}
function getUserBalance($db, $user) {
	$fetch_balance = mysqli_query($db, "SELECT balance FROM users WHERE username = '{$user}'") or die("Error.");
	$encoded_balance = mysqli_fetch_assoc($fetch_balance)['balance'];
	return (double)$encoded_balance - getInvestedCash($db, $user);
}
function addUserBalance($db, $user, $quantity) {
	if (is_numeric($quantity)) {
		$fetch_balance = mysqli_query($db, "SELECT balance FROM users WHERE username = '{$user}'") or die("Error.");
		$currentBalance = (double)mysqli_fetch_assoc($fetch_balance)['balance'];
		if (is_numeric($currentBalance) && getUserBalance($db, $user, $quantity) + $quantity >= 0) {
			$newBalance = $currentBalance + $quantity;
			mysqli_query($db, "UPDATE users SET balance = {$newBalance} WHERE username = '{$user}'") or die("Error.");
			return true;
		}
	}
	return false;
}
function arrayToTable($array) {
	$response = null;
	if (!is_array($array)) {
		$array = json_decode($array, true);
	}
	if (is_array($array)) {
		$response .= "<table><thead>";
		for ($a = 0; $a < 1; $a++) { 
			if (is_array($array[$a])) {
				for ($b = 0; $b < sizeof($array[$a]); $b++) { 
					$response .= "<th>".array_values($array[$a])[$b]."</th>";
				}	
			}
		}
		$response .= "</thead><tbody>";
		for ($a = 1; $a < sizeof($array); $a++) { 
			if (is_array($array[$a])) {
				$response .= "<tr>";
				for ($b = 0; $b < sizeof($array[$a]); $b++) { 
					$arrayValue = is_numeric(array_values($array[$a])[$b]) ? number_format(array_values($array[$a])[$b], 3) : array_values($array[$a])[$b];
					$response .= "<td>".$arrayValue."</td>";
				}	
				$response .= "</tr>";
			}
		}
		$response .= "</tbody></table>";
		return $response;
	}
	return $response;
}
function arrayToTableSorteable($array) {
	$response = null;
	if (!is_array($array)) {
		$array = json_decode($array, true);
	}
	if (is_array($array)) {
		$response .= "<table class='dataTable'><thead>";
		for ($a = 0; $a < 1; $a++) { 
			if (is_array($array[$a])) {
				for ($b = 0; $b < sizeof($array[$a]); $b++) { 
					$response .= "<th>".array_values($array[$a])[$b]."</th>";
				}	
			}
		}
		$response .= "</thead><tbody>";
		for ($a = 1; $a < sizeof($array); $a++) { 
			if (is_array($array[$a])) {
				$response .= "<tr>";
				for ($b = 0; $b < sizeof($array[$a]); $b++) { 
					$arrayValue = is_numeric(array_values($array[$a])[$b]) ? number_format(array_values($array[$a])[$b], 3) : array_values($array[$a])[$b];
					$response .= "<td>".$arrayValue."</td>";
				}	
				$response .= "</tr>";
			}
		}
		$response .= "</tbody></table>";
		return $response;
	}
	return $response;
}
function arrayToTableSorteableNoHeader($array) {
	$response = null;
	if (!is_array($array)) {
		$array = json_decode($array, true);
	}
	if (is_array($array)) {
		$response .= "<table class='dataTable smallTable'><thead style='display:none;'>";
		$response .= "<th></th></thead><tbody>";
		for ($a = 0; $a < sizeof($array); $a++) { 
			$response .= "<tr>";
			$response .= "<td>".$array[$a]."</td>";
			$response .= "</tr>";
		}
		$response .= "</tbody></table>";
		return $response;
	}
	return $response;
}
function getLastFundamental($db, $table_name, $fundamental) {
	if (tableExists($db, $table_name)) {
		$query = mysqli_query($db, "SELECT date FROM `{$table_name}` WHERE type = '{$fundamental}' ORDER BY date DESC LIMIT 1");
		confirmQuery($query);
		if ($row = mysqli_fetch_assoc($query)) {
			return $row['date']; 
		}
	}
	return null;
}
function deleteWatchlistItem($db, $user, $item, $id) {
	$user_table = $user."_watchlist";
	$symbol = mysqli_escape_string($db, $item);
	$delete = mysqli_query($db, "DELETE FROM {$user_table} WHERE item = '{$symbol}' AND watchlist_id = {$id}");
}
function deleteFromAllWatchlists($db, $user, $item) {
	$user_table = $user."_watchlist";
	$symbol = mysqli_escape_string($db, $item);
	$delete = mysqli_query($db, "DELETE FROM {$user_table} WHERE item = '{$symbol}'");
}
function zacksAPIRequest($item, $tag) {
	//URL
	$url = "https://widget3.zacks.com/data/chart/json/{$item}/{$tag}/www.zacks.com";

	//cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);

	//Return
	if ($data != null) {
		$jsonData = array();
		$jsonData = json_decode(stripslashes($data), true);
		if ($jsonData != null && is_array($jsonData) && sizeof($jsonData) > 0 && !isset($jsonData['Error'])) {
			$jsonData = array_values($jsonData);
			if (sizeof($jsonData) == 1) {
				return $jsonData[0];
			} else if (sizeof($jsonData) == 2) {
				return $jsonData[1];
			} else if (sizeof($jsonData) > 2) {
				return $jsonData[2];
			}
		}
	}	
	return null;
}
function insertFundamental($db, $table_name, $array, $tag, $multiply) {
	if (is_array($array)) {
		$date = array_keys($array);
		$value = array_values($array);
		$counter = 0;
		for ($i = 0; $i < sizeof($array); $i++) { 
			$current_date = date_format(date_create($date[$i]), "Y-m-d");
			if ($value[$i] != null && $value[$i] != "N/A" && is_numeric($value[$i])) {
				$current_value = $value[$i];
				if ($multiply) {
					$current_value = (double)$current_value * 1000000;
				}
				$query = mysqli_query($db, "INSERT INTO {$table_name} (type, date, value) VALUES ('{$tag}', '{$current_date}', '{$current_value}')");
				confirmQuery($query);
			}
			$counter++;
		}
		if ($counter == sizeof($array)) {
			return true;
		} else {
			return false;
		}
	}
	return false;
}
function updateFundamentals($db, $item) {
	if (zacksAPIRequest($item, "eps_diluted") != null) {
		$item = str_replace(".", "_", $item);
		$tableName = $item."_f";
		$createTable = mysqli_query($db, "CREATE TABLE IF NOT EXISTS {$tableName} (
			`type` varchar(255) NOT NULL,
			`date` date NOT NULL,
			`value` varchar(255) NOT NULL
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
		confirmQuery($createTable);
		$item = str_replace("_", ".", $item);
		$eps = zacksAPIRequest($item, "eps_diluted");
		$debt = zacksAPIRequest($item, "debt_lt_total_ttm");
		$revenue = zacksAPIRequest($item, "revenue");
		$share_holders_equity = zacksAPIRequest($item, "share_holders_equity");
		$profit = zacksAPIRequest($item, "net_income");
		$cash = zacksAPIRequest($item, "cash");
		$market_cap = zacksAPIRequest($item, "market_cap");
		$total_assets = zacksAPIRequest($item, "assets_total");
		$pe_ratio = zacksAPIRequest($item, "pe_ratio");

		if (insertFundamental($db, $tableName, $eps, "EPS", false) &&
		insertFundamental($db, $tableName, $debt, "Debt", true) &&
		insertFundamental($db, $tableName, $revenue, "Revenue", true) &&
		insertFundamental($db, $tableName, $share_holders_equity, "Equity", true) &&
		insertFundamental($db, $tableName, $profit, "Profit", true) &&
		insertFundamental($db, $tableName, $cash, "Cash", true) &&
		insertFundamental($db, $tableName, $market_cap, "MarketCap", true) &&
		insertFundamental($db, $tableName, $total_assets, "Assets", true) &&
		insertFundamental($db, $tableName, $pe_ratio, "PERatio", false)) {
			return true;
		} else {
			$drop_table = mysqli_query($db, "DROP TABLE `{$tableName}`");
			confirmQuery($drop_table);
			return false;
		}
	} else {
		return false;
	}
}
function getAllItemsWithoutFundamentals($db) {
	$get_tables_1d = mysqli_query($db, "SHOW TABLES LIKE '%_1d'");
	confirmQuery($get_tables_1d);
	$get_tables_1d = array_merge(...mysqli_fetch_all($get_tables_1d));
	$get_tables_f = mysqli_query($db, "SHOW TABLES LIKE '%_f'");
	confirmQuery($get_tables_f);
	$get_tables_f = array_merge(...mysqli_fetch_all($get_tables_f));

	$array = array();
	for ($i = 0; $i < sizeof($get_tables_1d); $i++) { 
		if (!in_array(str_replace("_1d", "_f", $get_tables_1d[$i]), $get_tables_f)) {
			$query = mysqli_query($db, "SELECT * FROM items WHERE tableName = '{$get_tables_1d[$i]}' AND type = 'stock'");
			confirmQuery($query);
			if (mysqli_num_rows($query) > 0) {
				$array[] = str_replace("_", ".", str_replace("_1d", "", $get_tables_1d[$i]));
			}
		}
	}

	return $array;
}
function nextItemWithoutFundamentals($db) {
	$get_tables_1d = mysqli_query($db, "SHOW TABLES LIKE '%_1d'");
	confirmQuery($get_tables_1d);
	$get_tables_1d = array_merge(...mysqli_fetch_all($get_tables_1d));
	$get_tables_f = mysqli_query($db, "SHOW TABLES LIKE '%_f'");
	confirmQuery($get_tables_f);
	$get_tables_f = array_merge(...mysqli_fetch_all($get_tables_f));

	for ($i = 0; $i < sizeof($get_tables_1d); $i++) { 
		if (!in_array(str_replace("_1d", "_f", $get_tables_1d[$i]), $get_tables_f)) {
			$query = mysqli_query($db, "SELECT * FROM items WHERE tableName = '{$get_tables_1d[$i]}' AND type = 'stock'");
			confirmQuery($query);
			if (mysqli_num_rows($query) > 0) {
				return str_replace("_", ".", str_replace("_1d", "", $get_tables_1d[$i]));
			}
		}
	}

	return null;
}
//Redirect
function redirect($location) {
	header("Location: {$location}");
	exit;
}
//Verify that user is logged in
function verifyLoggedIn() {
	if (isset($_SESSION["user"]) && !filter_var($_SESSION['user'], FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}
//Verify that user is logged in
function verifyClientLoggedIn() {
	if (isset($_SESSION["user"]) && filter_var($_SESSION['user'], FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}
//Get username
function getUser() {
	if (isset($_SESSION["user"])) {
		return trim(ucfirst($_SESSION["user"]));
	} 
	return false;
}
//Check if user is admin
function isAdmin($db, $user) {
	$is_admin = mysqli_query($db, "SELECT admin FROM users WHERE username = '{$user}'");
	confirmQuery($is_admin);
	if ($row = mysqli_fetch_assoc($is_admin)) {
		if ($row['admin'] == 1) {
			return true;
		}
	}
	return false;
}
//Confirm DB Query
function confirmQuery($query) {
	if (!$query) {
		die("We are experiencing connection issues.");
		return false;
	}
	return true;
}
//Check if table exists on a database
function tableExists($db, $table) {
	if ($result = $db->query("SHOW TABLES LIKE '".$table."'")) {
		if($result->num_rows == 1) {
			return true;
		}
	}
	return false;
}
//Check length of table
function tableLength($db, $table) {
	if (tableExists($db, $table)) {
		$size = mysqli_query($db, "SELECT * FROM `{$table}`");
		confirmQuery($size);
		return sizeof(mysqli_fetch_all($size));
	}
	return 0;
}
//Check if table column exists
function tableColumnExists($db, $table, $column) {
	if (tableExists($db, $table)) {
		$query = mysqli_query($db, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
		confirmQuery($query);
		if (mysqli_num_rows($query) > 0) {
			return true;
		}
	}
	return false;
}
//Check if table value exists on a table
function tableStringValueExists($db, $table, $name, $value) {
	if (tableExists($db, $table)) {
		if ($row = mysqli_fetch_assoc(mysqli_query($db, "SELECT {$name} from {$table} WHERE {$name} = '{$value}'"))) {
			return true;
		}
	}
	return false;
}
//Get Yahoo Finance Crumb
function getOnlyCrumb($symbol) {
	//Url
	//$crumbURL = "https://finance.yahoo.com/quote/{$symbol}/history?p={$symbol}";
	$crumbURL = "https://finance.yahoo.com/quote/{$symbol}/key-statistics?p={$symbol}";

	//Cookie
	$headers = [
				'Host: finance.yahoo.com',
				'Connection: keep-alive',
				'Cache-Control: max-age=0',
				'Upgrade-Insecure-Requests: 1',
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
				'Accept-Encoding: gzip, deflate, br',
				'Accept-Language: en,es;q=0.9,es-ES;q=0.8,en-US;q=0.7',
				'Cookie: GUCS=AQJ33QyP; B=0va0u29e234is&b=3&s=ke; EuConsent=BOZT7gkOZT7gtAOABCDEB7qAAAAid6fJfe7f98fR9v_lVkR7Gn6MwWiTwEQ4PUcH9ATzwQJhegZg0HcIydxJAoQQMERALYJCDEgSkjMSoAiGgpQwoMosABwYEA; GUC=AQABAQFcIt1c_UIbUQPy&s=AQAAALx0uBJS&g=XCGSdA'
				];
	//cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $crumbURL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$fetch_crumb = curl_exec($ch);
	if (curl_getinfo($ch)['http_code'] == 200 && $fetch_crumb != null && strlen($fetch_crumb) > 0 && strpos($fetch_crumb, '"CrumbStore":{"crumb":"') !== FALSE) {
		$crumb = explode("\"", explode("\"CrumbStore\":{\"crumb\":\"", $fetch_crumb)[1])[0];
		return $crumb;
	}
}
function getCrumb($symbol) {
	//Return Array
	$crumb = null;
	$mc = null;
	$so = null;
	$div = null;

	//Url
	//$crumbURL = "https://finance.yahoo.com/quote/{$symbol}/history?p={$symbol}";
	$crumbURL = "https://finance.yahoo.com/quote/{$symbol}/key-statistics?p={$symbol}";

	//Cookie
	$headers = [
				'Host: finance.yahoo.com',
				'Connection: keep-alive',
				'Cache-Control: max-age=0',
				'Upgrade-Insecure-Requests: 1',
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
				'Accept-Encoding: gzip, deflate, br',
				'Accept-Language: en,es;q=0.9,es-ES;q=0.8,en-US;q=0.7',
				'Cookie: GUCS=AQJ33QyP; B=0va0u29e234is&b=3&s=ke; EuConsent=BOZT7gkOZT7gtAOABCDEB7qAAAAid6fJfe7f98fR9v_lVkR7Gn6MwWiTwEQ4PUcH9ATzwQJhegZg0HcIydxJAoQQMERALYJCDEgSkjMSoAiGgpQwoMosABwYEA; GUC=AQABAQFcIt1c_UIbUQPy&s=AQAAALx0uBJS&g=XCGSdA'
				];
	//cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $crumbURL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$fetch_crumb = curl_exec($ch);
	if (curl_getinfo($ch)['http_code'] == 200 && $fetch_crumb != null && strlen($fetch_crumb) > 0 && strpos($fetch_crumb, '"CrumbStore":{"crumb":"') !== FALSE) {
		$crumb = explode("\"", explode("\"CrumbStore\":{\"crumb\":\"", $fetch_crumb)[1])[0];
	}
	for ($i = 0; $i < 1000; $i++) { 
		if (curl_getinfo($ch)['http_code'] == 200 && $fetch_crumb != null && strlen($fetch_crumb) > 0 && strpos($fetch_crumb, 'Market Cap (intraday)</span><!-- react-text: '.($i-2).' --> <!-- /react-text --><!-- react-text: '.($i-1).' --><!-- /react-text --><sup aria-label="Shares outstanding is taken from the most recently filed quarterly or annual report and Market Cap is calculated using shares outstanding." data-reactid="'.$i.'">5</sup></td><td class="Fz(s) Fw(500) Ta(end)" data-reactid="'.($i+1).'">') !== FALSE) {
			$mc = explode("<", explode("Market Cap (intraday)</span><!-- react-text: ".($i-2)." --> <!-- /react-text --><!-- react-text: ".($i-1)." --><!-- /react-text --><sup aria-label=\"Shares outstanding is taken from the most recently filed quarterly or annual report and Market Cap is calculated using shares outstanding.\" data-reactid=\"".$i."\">5</sup></td><td class=\"Fz(s) Fw(500) Ta(end)\" data-reactid=\"".($i+1)."\">", $fetch_crumb)[1])[0];
			break;
		}
	}
	for ($i = 0; $i < 1000; $i++) { 
		if (curl_getinfo($ch)['http_code'] == 200 && $fetch_crumb != null && strlen($fetch_crumb) > 0 && strpos($fetch_crumb, 'Shares Outstanding</span><!-- react-text: '.($i-2).' --> <!-- /react-text --><!-- react-text: '.($i-1).' --><!-- /react-text --><sup aria-label="Shares outstanding is taken from the most recently filed quarterly or annual report and Market Cap is calculated using shares outstanding." data-reactid="'.$i.'">5</sup></td><td class="Fz(s) Fw(500) Ta(end)" data-reactid="'.($i+1).'">') !== FALSE) {
			$so = explode("<", explode("Shares Outstanding</span><!-- react-text: ".($i-2)." --> <!-- /react-text --><!-- react-text: ".($i-1)." --><!-- /react-text --><sup aria-label=\"Shares outstanding is taken from the most recently filed quarterly or annual report and Market Cap is calculated using shares outstanding.\" data-reactid=\"".$i."\">5</sup></td><td class=\"Fz(s) Fw(500) Ta(end)\" data-reactid=\"".($i+1)."\">", $fetch_crumb)[1])[0];
			break;
		}
	}
	for ($i = 0; $i < 1000; $i++) { 
		if (curl_getinfo($ch)['http_code'] == 200 && $fetch_crumb != null && strlen($fetch_crumb) > 0 && strpos($fetch_crumb, '5 Year Average Dividend Yield</span><!-- react-text: '.($i-2).' --> <!-- /react-text --><!-- react-text: '.($i-1).' --><!-- /react-text --><sup aria-label="Data provided by Morningstar, Inc." data-reactid="'.$i.'">4</sup></td><td class="Fz(s) Fw(500) Ta(end)" data-reactid="'.($i+1).'">') !== FALSE) {
			$div = explode("<", explode("5 Year Average Dividend Yield</span><!-- react-text: ".($i-2)." --> <!-- /react-text --><!-- react-text: ".($i-1)." --><!-- /react-text --><sup aria-label=\"Data provided by Morningstar, Inc.\" data-reactid=\"".$i."\">4</sup></td><td class=\"Fz(s) Fw(500) Ta(end)\" data-reactid=\"".($i+1)."\">", $fetch_crumb)[1])[0];
			break;
		}
	}

	if (strpos($mc, "B") !== FALSE) {
		$mc = str_replace("B", "", $mc);
		$mc = (double)$mc * 1000000000;
	} else if (strpos($mc, "T") !== FALSE) {
		$mc = str_replace("T", "", $mc);
		$mc = (double)$mc * 1000000000000;
	} else if (strpos($mc, "M") !== FALSE) {
		$mc = str_replace("M", "", $mc);
		$mc = (double)$mc * 1000000;
	}
	if (strpos($so, "B") !== FALSE) {
		$so = str_replace("B", "", $so);
		$so = (double)$so * 1000000000;
	} else if (strpos($so, "T") !== FALSE) {
		$so = str_replace("T", "", $so);
		$so = (double)$so * 1000000000000;
	} else if (strpos($so, "M") !== FALSE) {
		$so = str_replace("M", "", $so);
		$so = (double)$so * 1000000;
	}
	global $db;
	$update_mc = mysqli_query($db, "UPDATE items SET market_cap = '$mc', shares_outstanding = '{$so}', 5_yr_div_avg = '{$div}' WHERE symbol = '{$symbol}' OR yahooSymbol = '{$symbol}'");

	if ($crumb != null) {
		return $crumb;
	}
	return null;
}
//Update
function addHourlyData($db, $api_ticker, $item) {
	$currentTime = time();
	$downloadURL = "https://query1.finance.yahoo.com/v8/finance/chart/%5EGSPC?symbol={$api_ticker}&period1=".($currentTime-604800*2)."&period2={$currentTime}&interval=60m&includePrePost=true&events=div%7Csplit%7Cearn&lang=en-US&region=US&corsDomain=finance.yahoo.com";
	$headers_json = [
					'Host: query1.finance.yahoo.com',
					'Connection: keep-alive',
					'Origin: https://finance.yahoo.com',
					'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
					'Accept: */*',
					'Referer: '."https://finance.yahoo.com/quote/{$api_ticker}/history?p={$api_ticker}",
					'Accept-Encoding: gzip, deflate, br',
					'Accept-Language: en,es;q=0.9,es-ES;q=0.8,en-US;q=0.7',
					'Cookie: GUCS=AQJ33QyP; B=0va0u29e234is&b=3&s=ke; EuConsent=BOZT7gkOZT7gtAOABCDEB7qAAAAid6fJfe7f98fR9v_lVkR7Gn6MwWiTwEQ4PUcH9ATzwQJhegZg0HcIydxJAoQQMERALYJCDEgSkjMSoAiGgpQwoMosABwYEA; GUC=AQABAQFcIt1c_UIbUQPy&s=AQAAALx0uBJS&g=XCGSdA'
	];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $downloadURL);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_CAINFO, "/var/www/ljb.solutions/html/php_dependancies/DigiCertHighAssuranceEVRootCA.crt");
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	$fetch_json = curl_exec($ch);
	$json = json_decode($fetch_json, true);
	if ($json != null && !isset($json['chart']['error']) && isset($json['chart']['result'][0]['timestamp']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['open']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['high']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['low']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['close']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['volume']) && sizeof($json['chart']['result'][0]['timestamp']) > 0 && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['open']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['high']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['low']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['close']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['volume'])) {
		//Add to database
		$table_name = mysqli_query($db, "SELECT hourly_data_table FROM items WHERE symbol = '".($item != null ? $item : $api_ticker)."'");
		confirmQuery($table_name);
		$table_name = mysqli_fetch_all($table_name);
		if (sizeof($table_name) == 1) {
			$table_name = $table_name[0][0];
			if (sizeof($json['chart']['result'][0]['timestamp']) > 0) {
				//Clear table
				$clearTable = mysqli_query($db, "TRUNCATE TABLE `{$table_name}`");
				confirmQuery($clearTable);
				//Add
				$row = 0;
				for ($i = 0; $i < sizeof($json['chart']['result'][0]['timestamp']); $i++) { 
					$date = date("Y-m-d H:i:s", $json['chart']['result'][0]['timestamp'][$i]);
					$open = trim(number_format($json['chart']['result'][0]['indicators']['quote'][0]['open'][$i], 4, ".", ""));
					$high = trim(number_format($json['chart']['result'][0]['indicators']['quote'][0]['high'][$i], 4, ".", ""));
					$low = trim(number_format($json['chart']['result'][0]['indicators']['quote'][0]['low'][$i], 4, ".", ""));
					$close = trim(number_format($json['chart']['result'][0]['indicators']['quote'][0]['close'][$i], 4, ".", ""));
					$volume = trim(number_format($json['chart']['result'][0]['indicators']['quote'][0]['volume'][$i], 0, "", ""));
					$duplicateCheck = mysqli_query($db, "SELECT open FROM `{$table_name}` WHERE date = '{$date}'");
					confirmQuery($duplicateCheck);
					if (sizeof(mysqli_fetch_all($duplicateCheck)) == 0 && 
						!empty($close) && is_numeric($close) && $close > 0) {
						$addData = mysqli_query($db, "INSERT into `{$table_name}` (date, open, high, low, close, volume) VALUES ('{$date}', '{$open}', '{$high}', '{$low}', '{$close}', '{$volume}')");
						confirmQuery($addData);
						$row++;
					}
				}
				if($row > 0) {
					return "1";
				}
			}
		}
	}
}
function updateItem($db, $api_ticker, $crumb) {
	$currentTime = time();
	$minusUnix = -time();
	/*$downloadURL = "https://query1.finance.yahoo.com/v8/finance/chart/%5EGSPC?symbol={$api_ticker}&period1={$minusUnix}&period2={$currentTime}&interval=1d&includePrePost=true&events=div%7Csplit%7Cearn&lang=en-US&region=US&crumb={$crumb}&corsDomain=finance.yahoo.com";*/
	$downloadURL = "https://query1.finance.yahoo.com/v8/finance/chart/{$api_ticker}?symbol={$api_ticker}&period1={$minusUnix}&period2={$currentTime}&interval=1d&includePrePost=true&events=div%7Csplit%7Cearn&lang=en-US&region=US&corsDomain=finance.yahoo.com";
	$headers_json = [
					'Host: query1.finance.yahoo.com',
					'Connection: keep-alive',
					'Origin: https://finance.yahoo.com',
					'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
					'Accept: */*',
					'Referer: '."https://finance.yahoo.com/quote/{$api_ticker}/history?p={$api_ticker}",
					'Accept-Encoding: gzip, deflate, br',
					'Accept-Language: en,es;q=0.9,es-ES;q=0.8,en-US;q=0.7',
					'Cookie: GUCS=AQJ33QyP; B=0va0u29e234is&b=3&s=ke; EuConsent=BOZT7gkOZT7gtAOABCDEB7qAAAAid6fJfe7f98fR9v_lVkR7Gn6MwWiTwEQ4PUcH9ATzwQJhegZg0HcIydxJAoQQMERALYJCDEgSkjMSoAiGgpQwoMosABwYEA; GUC=AQABAQFcIt1c_UIbUQPy&s=AQAAALx0uBJS&g=XCGSdA'
	];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $downloadURL);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_CAINFO, "/var/www/ljb.solutions/html/php_dependancies/DigiCertHighAssuranceEVRootCA.crt");
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	$fetch_json = curl_exec($ch);
	$json = json_decode($fetch_json, true);
	if ($json != null && !isset($json['chart']['error']) && isset($json['chart']['result'][0]['timestamp']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['open']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['high']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['low']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['close']) && isset($json['chart']['result'][0]['indicators']['quote'][0]['volume']) && sizeof($json['chart']['result'][0]['timestamp']) > 0 && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['open']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['high']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['low']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['close']) && sizeof($json['chart']['result'][0]['timestamp']) == sizeof($json['chart']['result'][0]['indicators']['quote'][0]['volume'])) {
		//Add to database
		$table_name = mysqli_query($db, "SELECT tableName FROM items WHERE apiTicker = '{$api_ticker}'");
		confirmQuery($table_name);
		$table_name = mysqli_fetch_all($table_name);
		if (sizeof($table_name) == 1) {
			$table_name = $table_name[0][0];
			if (tableLength($db, $table_name) < sizeof($json['chart']['result'][0]['timestamp'])) {
				//Clear table
				$clearTable = mysqli_query($db, "TRUNCATE TABLE `{$table_name}`");
				confirmQuery($clearTable);
				//Add
				$row = 0;
				for ($i = 0; $i < sizeof($json['chart']['result'][0]['timestamp']); $i++) { 
					$date = date("Y-m-d", $json['chart']['result'][0]['timestamp'][$i]);
					$open = $json['chart']['result'][0]['indicators']['quote'][0]['open'][$i];
					$high = $json['chart']['result'][0]['indicators']['quote'][0]['high'][$i];
					$low = $json['chart']['result'][0]['indicators']['quote'][0]['low'][$i];
					$close = $json['chart']['result'][0]['indicators']['quote'][0]['close'][$i];
					$volume = $json['chart']['result'][0]['indicators']['quote'][0]['volume'][$i];
					$duplicateCheck = mysqli_query($db, "SELECT open FROM `{$table_name}` WHERE date = '{$date}'");
					confirmQuery($duplicateCheck);
					if (sizeof(mysqli_fetch_all($duplicateCheck)) == 0) {
						$addData = mysqli_query($db, "INSERT into `{$table_name}` (date, open, high, low, close, volume) VALUES ('{$date}', '{$open}', '{$high}', '{$low}', '{$close}', '{$volume}')");
						confirmQuery($addData);
						$row++;
					}
				}
				return "{$api_ticker} was updated successfully.<br>{$row} queries were executed.";
			} else {
				return "The data is up to date with Yahoo Finance.";
			}
		}
	} 
	return null;
}
//Check if item is on watchlist
function onWatchlist($db, $user, $item) {
	$watchlist_table = $user."_watchlist";
	if (tableExists($db, $watchlist_table)) {
		$item = str_replace("_1d", "", $item);
		$get_watchlist = mysqli_query($db, "SELECT * FROM `{$watchlist_table}` WHERE item = '{$item}'");
		confirmQuery($get_watchlist);
		if (mysqli_num_rows($get_watchlist) > 0) {
			return true;
		}
	}
	return false;
}
//Check if item is on portfolio
function onPortfolio($db, $user, $item) {
	$portfolio_table = $user."_portfolio";
	if (tableExists($db, $portfolio_table)) {
		$item = str_replace("_1d", "", $item);
		$get_portfolio = mysqli_query($db, "SELECT * FROM `{$portfolio_table}` WHERE item = '{$item}' AND status = 'open'");
		confirmQuery($get_portfolio);
		if (mysqli_num_rows($get_portfolio) > 0) {
			return true;
		}
	}
	return false;
}
//Current Price
function getCurrentPrice($item) {
	global $db;
	$get_price = mysqli_query($db, "SELECT tableName FROM items WHERE ".(is_numeric($item) ? "id = {$item}" : "symbol = '{$item}'"));
	confirmQuery($get_price);
	if ($row = mysqli_fetch_assoc($get_price)) {
		$get_price = mysqli_query($db, "SELECT close FROM `{$row['tableName']}` ORDER BY date DESC LIMIT 1");
		confirmQuery($get_price);
		if ($row3 = mysqli_fetch_assoc($get_price)) {
			return (double)$row3['close'];
		}
	}
	return 0;
}
//Current Price Ticker
function getCurrentPriceTicker($db, $item) {
	$get_price = mysqli_query($db, "SELECT tableName FROM items WHERE apiTicker = '{$item}'");
	confirmQuery($get_price);
	if ($row = mysqli_fetch_assoc($get_price)) {
		$get_price = mysqli_query($db, "SELECT close FROM `{$row['tableName']}` ORDER BY date DESC LIMIT 1");
		confirmQuery($get_price);
		if ($row3 = mysqli_fetch_assoc($get_price)) {
			return (double)$row3['close'];
		}
	}
	return 0;
}
function getPriceFromDate($item, $date) {
	global $db;
	$get_price = mysqli_query($db, "SELECT tableName FROM items WHERE ".(is_numeric($item) ? "id = {$item}" : "symbol = '{$item}'"));
	confirmQuery($get_price);
	if ($row = mysqli_fetch_assoc($get_price)) {
		$get_price = mysqli_query($db, "SELECT close FROM `{$row['tableName']}` WHERE date <= '{$date}' ORDER BY date DESC LIMIT 1");
		confirmQuery($get_price);
		if ($row3 = mysqli_fetch_assoc($get_price)) {
			return (double)$row3['close'];
		}
	}
	return 0;
}
function getMarketCapFromDate($table, $date) {
	global $db;
	$get_cap = mysqli_query($db, "SELECT value FROM `{$table}` WHERE type = 'MarketCap' AND date <= '{$date}' ORDER BY date DESC LIMIT 1");
	confirmQuery($get_cap);
	if ($result = mysqli_fetch_assoc($get_cap)) {
		return $result['value'];
	}
	return 0;
}
function getCurrentTargetPrice($item) { 
	global $db;
	$query = mysqli_query($db, "SELECT recommended FROM items WHERE symbol = '{$item}'");
	confirmQuery($query);
	if ($row = mysqli_fetch_assoc($query)) {
		$json = $row["recommended"];
		$json = json_decode($json, true);
		if ($json != null && is_array($json) && isset($json['target_mediumTerm'])) {
			return $json['target_mediumTerm'];
		}
	}
	return 0;
}
//Fetch current PE Ratio
function getPERatio($item) {
	for ($i = 0; $i < 2; $i++) { 
		//URL
		$item = strtoupper(trim($item));

		$url = "https://widget3.zacks.com/data/chart/json/{$item}/pe_ratio/www.zacks.com";

		//cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);

		//jSON
		$jsonData = array();
		$jsonData = json_decode($data, true);

		if (isset($jsonData["monthly_pe_ratio"]) && sizeof($jsonData["monthly_pe_ratio"]) > 0) {
			$dates = array_keys($jsonData["monthly_pe_ratio"]);
			$PEArray = array();
			for ($i = 0; $i < 120; $i++) { 
				if (is_numeric($jsonData["monthly_pe_ratio"][$dates[$i]])) {
					$PEArray[] = $jsonData["monthly_pe_ratio"][$dates[$i]];
				}
				if (count($PEArray) == 12) {
					break;
				}
			}
			if (count($PEArray) > 0) {
				$PEAverage = array_sum($PEArray) / count($PEArray);
				return number_format($PEAverage, 2, '.', '');
			} 
		}
		curl_close($ch);
	}
	return 0;
}
//Fetch PE Ratio for desired time
function getPERatioHistorical($item, $current_date_unix) {
	$last_year = $current_date_unix - 31557600;
	//URL
	$item = strtoupper(trim($item));
	$item = str_replace("_", ".", $item);

	$url = "https://widget3.zacks.com/data/chart/json/{$item}/pe_ratio/www.zacks.com";

	//cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);

	//jSON
	$jsonData = array();
	$jsonData = json_decode($data, true);

	if (is_array($jsonData) && isset($jsonData["monthly_pe_ratio"]) && sizeof($jsonData["monthly_pe_ratio"]) > 0) {
		$dates = array_keys($jsonData["monthly_pe_ratio"]);
		$PEArray = array();
		for ($i = 0; $i < sizeof($dates); $i++) { 
			if (strtotime($dates[$i]) <= $current_date_unix && strtotime($dates[$i]) >= $last_year && is_numeric($jsonData["monthly_pe_ratio"][$dates[$i]])) {
				$PEArray[] = $jsonData["monthly_pe_ratio"][$dates[$i]];
			}
		}
		if (count($PEArray) > 0) {
			$PEAverage = array_sum($PEArray) / count($PEArray);
			return number_format($PEAverage, 2, '.', '');
		}  
	}
	curl_close($ch);
	return 0;
}
function getPERatioHistoricalFromDB($db, $item, $current_date_unix) {
	$last_year = $current_date_unix - 31557600;
	$table_name = $item."_f";
	if (tableExists($db, $table_name)) {
		$currentDate = date("Y-m-d", $current_date_unix);
		$last_year = date("Y-m-d", $last_year);
			
		$query = mysqli_query($db, "SELECT value FROM `{$table_name}` WHERE type = 'PERatio' AND date <= '{$currentDate}' AND date >= '{$last_year}'");
		confirmQuery($query);

		$pe_ratio = array();
		while ($row = mysqli_fetch_assoc($query)) {
			if (is_numeric($row['value'])) {
				$pe_ratio[] = (double)$row['value'];
			}
		}

		if (count($pe_ratio) > 0) {
			$PEAverage = array_sum($pe_ratio) / count($pe_ratio);
			return number_format($PEAverage, 2, '.', '');
		}
	}
	return 0;
}
function getPERatioHistorical5Years($item, $current_date_unix) {
	$last_year = $current_date_unix - (31557600*5);
	//URL
	$item = strtoupper(trim($item));
	$item = str_replace("_", ".", $item);

	$url = "https://widget3.zacks.com/data/chart/json/{$item}/pe_ratio/www.zacks.com";

	//cURL
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);

	//jSON
	$jsonData = array();
	$jsonData = json_decode($data, true);

	if (is_array($jsonData) && isset($jsonData["monthly_pe_ratio"]) && sizeof($jsonData["monthly_pe_ratio"]) > 0) {
		$dates = array_keys($jsonData["monthly_pe_ratio"]);
		$PEArray = array();
		for ($i = 0; $i < sizeof($dates); $i++) { 
			if (strtotime($dates[$i]) <= $current_date_unix && strtotime($dates[$i]) >= $last_year && is_numeric($jsonData["monthly_pe_ratio"][$dates[$i]])) {
				$PEArray[] = $jsonData["monthly_pe_ratio"][$dates[$i]];
			}
		}
		if (count($PEArray) > 0) {
			$PEAverage = array_sum($PEArray) / count($PEArray);
			return number_format($PEAverage, 2, '.', '');
		} 
	}
	curl_close($ch);
	return 0;
}
function orderNestedArrays($array, $key) {
	$key_array = array();
	$result_array = array();

	for ($i = 0; $i < sizeof($array); $i++) { 
		$key_array[] = $array[$i][$key];
	}
	asort($key_array);
	$key_array = array_keys($key_array);

	for ($i = 0; $i < sizeof($key_array); $i++) { 
		$result_array[$i] = $array[$key_array[$i]];
	}

	return $result_array;
}

function hasGoodFundamentals($db, $item){
	$query = mysqli_query($db, "SELECT fundamentals_status FROM items WHERE symbol = '{$item}' OR apiTicker = '{$item}'");
	confirmQuery($query);

	if($row = mysqli_fetch_assoc($query)){
		if($row['fundamentals_status'] == null){
			return false;
		}
		else {
			$fundamentals_status = json_decode($row['fundamentals_status'], true);

			if(count($fundamentals_status) == 0) {
				return false;
			}
			else if($fundamentals_status[count($fundamentals_status)-1]['value'] == 1) {
				return true;
			}
		}
	}

	return false;
}
?>
