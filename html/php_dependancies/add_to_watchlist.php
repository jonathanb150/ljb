<?php
if (isset($_POST["add-to-watchlist"]) && isset($_POST['id'])) {
	session_start();
	require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
	$table = mysqli_escape_string($db, $_POST["add-to-watchlist"]);
	$query = mysqli_query($db, "SELECT symbol FROM items WHERE tableName = '{$table}'") or die(mysqli_error($db));
	$query = mysqli_fetch_assoc($query);
	if ($row = $query) {
		$symbol = $row["symbol"];
		if ($symbol != null && strlen($symbol) > 0) {
			$user_table = $_SESSION['user']."_watchlist";
			$query = mysqli_query($db, "INSERT INTO {$user_table} (item, date_added, watchlist_id) VALUES ('{$symbol}', NOW(), {$_POST['id']})") or die(mysqli_error($db));
			header("Location: /watchlist.php");
		}
	}
}
echo "Error!";
var_dump($_POST);
?>