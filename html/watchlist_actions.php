<?php require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php"; ?>
<?php 
session_start();
$watchlists_table = $_SESSION["user"]."_watchlists";
$watchlist_table = $_SESSION["user"]."_watchlist";

//RENAME WATCHLIST
if(isset($_GET['watchlist_id']) && isset($_GET['rename_to']) && strlen($_GET['rename_to']) > 0){
	$rename2 = mysqli_escape_string($db, $_GET['rename_to']);
	mysqli_query($db, "UPDATE `{$watchlists_table}` SET name = '{$rename2}' WHERE id = {$_GET['watchlist_id']}") or die("Connection problems.");
	echo "1";
}
else if(isset($_GET['watchlist_id']) && isset($_GET['delete'])){
	mysqli_query($db, "DELETE FROM `{$watchlists_table}` WHERE id = {$_GET['watchlist_id']}") or die("Connection problems.");
	mysqli_query($db, "DELETE FROM `{$watchlist_table}` WHERE watchlist_id = {$_GET['watchlist_id']}") or die("Connection problems.");
	echo "1";
}
else if(isset($_GET['get_watchlists']) && isset($_GET['symbol'])){
	$get_user_watchlists = mysqli_query($db, "SELECT * FROM `{$watchlists_table}`") or die("Connection problems.");
	while($row = mysqli_fetch_assoc($get_user_watchlists)){
		echo "<form method = 'POST' action = '/php_dependancies/add_to_watchlist.php'>";
		echo "<button class = 'button' style = 'display: block; margin: 15px auto'>Add to {$row['name']}</button><input type = 'hidden' value = '{$_GET['symbol']}' name = 'add-to-watchlist'><input type = 'hidden' value = '{$row['id']}' name = 'id'></form>";
	}
}
else if(isset($_GET['watchlist_id']) && isset($_GET['watchlist_item']) && isset($_GET['target_price']) && (is_numeric($_GET['target_price']) || $_GET['target_price'] == '')){
	$watchlist_id = mysqli_escape_string($db, $_GET['watchlist_id']);
	$watchlist_item = mysqli_escape_string($db, $_GET['watchlist_item']);
	$target_price = mysqli_escape_string($db, $_GET['target_price']);

	mysqli_query($db, "UPDATE `{$watchlist_table}` SET target_price = '{$target_price}' WHERE watchlist_id = {$watchlist_id} AND item = '{$watchlist_item}'") or die(mysqli_error($db));
	echo "1";
}
else if(isset($_GET['watchlist_id']) && isset($_GET['watchlist_item']) && isset($_GET['selling_price']) && (is_numeric($_GET['selling_price']) || $_GET['selling_price'] == '')){
	$watchlist_id = mysqli_escape_string($db, $_GET['watchlist_id']);
	$watchlist_item = mysqli_escape_string($db, $_GET['watchlist_item']);
	$selling_price = mysqli_escape_string($db, $_GET['selling_price']);

	mysqli_query($db, "UPDATE `{$watchlist_table}` SET selling_price = '{$selling_price}' WHERE watchlist_id = {$watchlist_id} AND item = '{$watchlist_item}'") or die(mysqli_error($db));
	echo "1";
}
else if(isset($_GET['watchlist_id']) && isset($_GET['watchlist_item']) && isset($_GET['min_expected']) && isset($_GET['max_expected'])){
	$watchlist_id = mysqli_escape_string($db, $_GET['watchlist_id']);
	$watchlist_item = mysqli_escape_string($db, $_GET['watchlist_item']);
	$min_expected = mysqli_escape_string($db, $_GET['min_expected']);
	$max_expected = mysqli_escape_string($db, $_GET['max_expected']);

	if($min_expected != -2){
		mysqli_query($db, "UPDATE `{$watchlist_table}` SET min_expected = '{$min_expected}' WHERE watchlist_id = {$watchlist_id} AND item = '{$watchlist_item}'") or die(mysqli_error($db));
	}
	if($max_expected != -2){
		mysqli_query($db, "UPDATE `{$watchlist_table}` SET max_expected = '{$max_expected}' WHERE watchlist_id = {$watchlist_id} AND item = '{$watchlist_item}'") or die(mysqli_error($db));
	}
	
	echo "1";
}
else{
	echo "0";
}
?>