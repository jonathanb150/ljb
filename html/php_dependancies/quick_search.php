<?php require 'functions.php'; ?>
<?php require "/var/www/ljb.solutions/html/php_dependancies/scraper/simple_html_dom.php";  ?>
<?php
require 'db.php';
if (isset($_POST['ticker']) && isset($_POST['inspect']) && isset($_POST['latest'])) {
	$command = "python3.7 {$_SERVER['DOCUMENT_ROOT']}/algorithms/Others/quickSearch.py '{$_POST['ticker']}' {$_POST['latest']}";
	$exec = shell_exec($command);
	if (is_array(json_decode($exec, true))) {
		$output = json_decode($exec, true);
		echo arrayToTable($output['table']);
		echo "<a href='/analyze.php?item={$_POST['ticker']}' target='blank'><button class='button'>Analyze</button></a>";
		echo "<div>";
		if(hasGoodFundamentals($db, $_POST['ticker'])){
			echo "<p style='margin-top: 20px; text-align: center'>Current Fundamentals Status: <span style='color: #4bbd7e; font-weight: 700;'>Good</span></p>";
		}
		else{
			echo "<p style='margin-top: 20px; text-align: center'>Current Fundamentals Status: <span style='color: #bd4b4b; font-weight: 700;'>Bad</span></p>";
		}
		echo "<button class='button' style='margin-top: 20px; background: #bd4b4b;' onclick='markFundamentals(\"{$_POST['ticker']}\", 0, this)'>Mark Fundamentals as Bad</button>";
		echo "<button class='button' style='margin-top: 20px; background: #4bbd7e' onclick='markFundamentals(\"{$_POST['ticker']}\", 1, this)'>Mark Fundamentals as Good</button>";
		echo "</div>";
		$query = mysqli_query($db, "SELECT fundamentals_status FROM items WHERE symbol = '{$_POST['ticker']}' OR apiTicker = '{$_POST['ticker']}'") or die(mysqli_error($db));

		if($row = mysqli_fetch_assoc($query)) {
			$json = json_decode($row['fundamentals_status'], true);

			if(is_array($json) && count($json) > 0) {
				$data = [];

				for ($i=0; $i < count($json); $i++) { 
					$data[] = Array(t=>$json[$i]["date"], y=>$json[$i]["value"]);
				}

				$data = json_encode($data);

				$_SESSION['q_mark_fundamentals_chart'] = $data;

				//echo '<canvas id="q_mark_fundamentals_chart" width="800" height="400"></canvas>';
			}
		}
	} else {
		echo "fail";
	}
}
else if(isset($_POST['ticker']) && isset($_POST['basic'])){
	$get_api = getAPI($db, $_POST['ticker']);

	switch($get_api) {
		case "stooq":
			$stooq_price = (float) lastStooqPrice($db, $_POST['ticker']);
			if($stooq_price != -1){
				$current_price = (float) getCurrentPriceTicker($db, $_POST['ticker']);
				$change = (($stooq_price*100)/$current_price)-100;
				$array = [$change, $stooq_price];
				echo json_encode($array, true);
			}
			else{
				echo "fail";
			}
			break;
		case "yahoo":
			$yahoo_price = (float) lastYahooPrice($_POST['ticker']);
			if($yahoo_price != -1){
				$current_price = (float) getCurrentPriceTicker($db, $_POST['ticker']);
				$change = (($yahoo_price*100)/$current_price)-100;
				$array = [$change, $yahoo_price];
				echo json_encode($array, true);
			}
			else{
				echo "fail";
			}
			break;
		default:
			echo "fail";
	}
}

function getAPI($db, $ticker){
	$get_api = mysqli_query($db, "SELECT apiSource FROM items WHERE apiTicker = '{$ticker}'");
	confirmQuery($get_api);
	if(mysqli_num_rows($get_api) == 1){
		$get_api = mysqli_fetch_assoc($get_api)["apiSource"];
		
		return $get_api;
	}

	return false;
}

function lastYahooPrice($ticker) {
	$get_price = file_get_contents("https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?symbol={$ticker}&period1=".time()."&period2=".time()."&interval=1m&includePrePost=true&events=div%7Csplit%7Cearn&lang=en-US&region=US&corsDomain=finance.yahoo.com");

	$get_price = json_decode($get_price, true);

	if(is_array($get_price) && isset($get_price["chart"]["result"][0]["meta"]["regularMarketPrice"])) {
		return $get_price["chart"]["result"][0]["meta"]["regularMarketPrice"];
	}

	return -1;
}

function lastStooqPrice($db, $ticker){
	$check_type = mysqli_query($db, "SELECT type FROM items WHERE apiTicker = '{$ticker}'");
	confirmQuery($check_type);
	if(mysqli_num_rows($check_type) == 1){
		$check_type = mysqli_fetch_assoc($check_type)["type"];
		if(strpos($check_type, "stock") !== FALSE){
			$country = explode("_", $check_type)[0];
			$ticker = substr_count(strtolower($ticker), ".") >= 1 ? strtolower($ticker) : strtolower($ticker).".".$country;
		}
		else{
			$ticker = strtolower($ticker);
		}
		$base_url = "https://stooq.com/q/?s=";
		$html = apiRequest($ticker, $base_url);
		$html = str_get_html($html);
		if ($html != null && is_numeric(trim($html->find("#f18", 2)->plaintext))) {
			return trim($html->find("#f18", 2)->plaintext);
		}
	}

	return -1;
}
?>
