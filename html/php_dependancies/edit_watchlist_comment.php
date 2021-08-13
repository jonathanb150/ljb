<?php
	if (isset($_POST['symbol']) && isset($_POST['comment'])) {
		session_start();
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
		$user_table = $_SESSION['user']."_watchlist";
		$comment = mysqli_escape_string($db, $_POST['comment']);
		$symbol = mysqli_escape_string($db, $_POST['symbol']);
		$check = mysqli_query($db, "UPDATE {$user_table} SET comment = '{$comment}' WHERE item = '{$symbol}'") or die(mysqli_error($db));
		echo "Success";
	}
?>