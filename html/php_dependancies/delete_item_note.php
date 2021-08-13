<?php
	if (isset($_POST['note_id'])) {
		session_start();
		require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
		function tableExists($db, $table) {
			if ($result = $db->query("SHOW TABLES LIKE '".$table."'")) {
				if($result->num_rows == 1) {
					return true;
				}
			}
			return false;
		}
		if (isset($_SESSION["user"])) {
			$table = $_SESSION["user"] . "_item_notes";
			if (tableExists($db, $table) && is_numeric($_POST['note_id'])) {
				mysqli_query($db, "DELETE FROM `{$table}` WHERE id = {$_POST['note_id']}") or die(mysqli_error($db));
				echo "success";
			}
		}
	}
?>