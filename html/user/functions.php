<?php
function confirmQuery($query) {
	if (!$query) {
		die("We are experiencing connection issues.");
		return false;
	}
	return true;
}
function verifyLoggedIn() {
	if (isset($_SESSION["user"])) {
		return true;
	}
	return false;
}
function redirect($location) {
	header("Location: {$location}");
	exit;
}
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
function getInvestedCash($db, $user) {
	$query = mysqli_query($db, "SELECT sum(allocated_capital+0) FROM {$user}_portfolio WHERE status = 'open'");
	$invested_cash = mysqli_fetch_assoc($query)['sum(allocated_capital+0)'];
	return (double)$invested_cash;
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
function getItemNameType($db, $symbol){
	$query = mysqli_query($db, "SELECT name, type FROM items WHERE symbol = '{$symbol}' OR tableName = '{$symbol}' OR apiTicker = '{$symbol}'")  or die("Error.");
	while($row = mysqli_fetch_assoc($query)){
		return [$row["name"], $row["type"]];
	}
	return false;
}
function getAllocatedCapital($db, $symbol){
	$query = mysqli_query($db, "SELECT SUM(allocated_capital) FROM admin_portfolio WHERE item = '{$symbol}' AND status = 'open'")  or die("Error.");
	while($row = mysqli_fetch_assoc($query)){
		return $row["SUM(allocated_capital)"];
	}
	return false;
}
function stringInVariable($a, $b) {
	if (strpos($b, $a) !== FALSE) {
		return true;
    }
    return false;
}
?>