<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php"); ?>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/http_operations.php"); ?>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php"); ?>
<?php 
	$curl = new HTTPOperations;
	$curl->http_headers = ["APCA-API-KEY-ID: PKGBVBVNTTB9QSRWD3Z2", "APCA-API-SECRET-KEY: GFc4OA1kt4h2bKKYjGrS9zxqHmHuKwcIPPnvtsJ7"];

	//var_dump($curl->cURL("https://paper-api.alpaca.markets/v2/account"));
	//var_dump($curl->cURLPost("https://paper-api.alpaca.markets/v2/orders", '{"symbol": "AAPL", "qty": "1", "side": "buy", "type": "market", "time_in_force": "day"}'));

	if(isset($_GET['check_symbol'])){
		echo checkSymbol($_GET['check_symbol']);
	}
	else if(isset($_POST["alpaca_asset"]) && isset($_POST["ljb_asset"]) && isset($_POST["interval"]) && isset($_POST["capital"]) && isset($_POST["entry_points"])) {
		$query = mysqli_query($db, "INSERT INTO admin_algorithmic (alpaca_asset, ljb_asset, check_interval, allocated_capital, entry_points, added_date) VALUES ('{$_POST["alpaca_asset"]}', '{$_POST["ljb_asset"]}', '{$_POST["interval"]}', '{$_POST["capital"]}', '{$_POST["entry_points"]}', ".time().")");
		confirmQuery($query);

		echo "success";
	}

	function checkSymbol($symbol){
		global $curl;
		$check_symbol = json_decode($curl->cURL("https://paper-api.alpaca.markets/v2/assets/{$symbol}"), true);

		if(is_array($check_symbol) && isset($check_symbol['tradable']) && isset($check_symbol['status']) && isset($check_symbol['symbol']) && isset($check_symbol['exchange'])){
			if($check_symbol['status'] == "active" && $check_symbol['tradable']){
				return json_encode(["symbol"=>$check_symbol['symbol'], "exchange"=>$check_symbol['exchange'], "tradable"=>$check_symbol['tradable']], true);
			}
		}
		return json_encode(["error"=>"Symbol not found."], true);
	}
?>