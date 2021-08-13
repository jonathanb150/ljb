<?php require "php_dependancies/db.php"; ?>
<?php require "php_dependancies/functions.php"; ?>
<?php 
$select_stocks = mysqli_query($db, "SELECT symbol FROM items WHERE type LIKE '%stock%'");
confirmQuery($select_stocks);

$select_stocks = mysqli_fetch_all($select_stocks);
$items = "";

for ($i=0; $i < count($select_stocks); $i++) { 
	$items .= $select_stocks[$i][0]." "; 
}

$items = trim($items);

$py_filter = shell_exec("python3.7 -W ignore /var/www/ljb.solutions/html/algorithms/filters/bigmarketcap_filter.py '{$items}' '".date("Y-m-d", time())."'");
$py_filter = json_decode(stripslashes($py_filter), true);

updateItems();
$py_filter = shell_exec("python3.7 -W ignore /var/www/ljb.solutions/html/algorithms/filters/longtermbullmarket_filter.py '{$items}' '".date("Y-m-d", time())."'");
$py_filter = json_decode(stripslashes($py_filter), true);

updateItems();
$py_filter = shell_exec("python3.7 -W ignore /var/www/ljb.solutions/html/algorithms/filters/shortterm_filter.py '{$items}' '".date("Y-m-d", time())."'");
$py_filter = json_decode(stripslashes($py_filter), true);

updateItems();
$py_filter = shell_exec("python3.7 -W ignore /var/www/ljb.solutions/html/algorithms/filters/5%droplast2months.py '{$items}' '".date("Y-m-d", time())."'");
$py_filter = json_decode(stripslashes($py_filter), true);

updateItems();
var_dump($items);

function updateItems() {
	global $items, $py_filter;
	$items = "";

	if(isset($py_filter) && is_array($py_filter) && isset($py_filter['table']) && count($py_filter['table']) > 1) {
		for ($i=1; $i < count($py_filter['table']); $i++) { 
			$items .= $py_filter['table'][$i][0]." "; 
		}

		$items = trim($items);
	}
}
?>