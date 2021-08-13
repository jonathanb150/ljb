<?php
	if (isset($_POST['note_id']) && isset($_POST['note_content']) && isset($_POST['title'])) {
		session_start();
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
		$table = $_SESSION["user"] . "_item_notes";
		if (is_numeric($_POST['note_id'])) {
			$note = mysqli_escape_string($db, $_POST['note_content']);
			$title = mysqli_escape_string($db, $_POST['title']);
			if ($title != null && strlen(trim($title)) > 0) {
				mysqli_query($db, "UPDATE `{$table}` SET note = '{$note}', date = NOW(), title = '{$title}' WHERE id = {$_POST['note_id']}") or die(mysqli_error($db));
			} else {
				mysqli_query($db, "UPDATE `{$table}` SET note = '{$note}', date = NOW() WHERE id = {$_POST['note_id']}") or die(mysqli_error($db));
			}
			echo "success";
		}
	}
?>