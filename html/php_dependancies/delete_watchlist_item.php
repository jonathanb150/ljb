<?php
	if (isset($_GET['symbol']) && isset($_GET['id'])) {
		session_start();
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php");
		deleteWatchlistItem($db, $_SESSION['user'], $_GET['symbol'], $_GET['id']);
	}
	header('Location: /watchlist.php');
?>
