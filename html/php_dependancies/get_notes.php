<?php
if (isset($_POST['type'])) {
	require($_SERVER["DOCUMENT_ROOT"]."/php_dependancies/db.php");
	session_start();
	$table = $_SESSION["user"]."_".$_POST['type']."_notes";
	if (isset($_POST['item'])) {
		$query = mysqli_query($db, "SELECT * FROM `{$table}` WHERE item = '{$_POST['item']}'") or die(mysqli_error($db));
	} else {
		$query = mysqli_query($db, "SELECT * FROM `{$table}`") or die(mysqli_error($db));
	}
	$result = mysqli_fetch_all($query, MYSQLI_ASSOC);
	echo json_encode($result, true);
}
?>