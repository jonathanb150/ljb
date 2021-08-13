<?php
	if (isset($_POST['note']) && isset($_POST['title'])) {
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
		session_start();
		$table = $_SESSION["user"] . "_economy_notes";
		$note = mysqli_escape_string($db, $_POST['note']);
		$title = mysqli_escape_string($db, $_POST['title']);
		mysqli_query($db, "INSERT INTO `{$table}` (title, note, date) VALUES ('{$title}', '{$note}', NOW())") or die(mysqli_error($db));
		echo "success";
	}
?>