<?php
	if (isset($_POST['note']) && isset($_POST['title']) && isset($_POST['item'])) {
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
		session_start();
		$table = $_SESSION["user"] . "_item_notes";
		$note = mysqli_escape_string($db, $_POST['note']);
		$title = mysqli_escape_string($db, $_POST['title']);
		$item = mysqli_escape_string($db, $_POST['item']);
		mysqli_query($db, "INSERT INTO `{$table}` (item, title, note, date) VALUES ('{$item}', '{$title}', '{$note}', NOW())") or die(mysqli_error($db));
		echo "success";
	}
?>