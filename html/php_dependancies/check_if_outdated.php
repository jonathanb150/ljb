<?php
if (isset($_POST['table'])) {
	require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
	require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php");
	if (tableExists($db, $_POST['table'])) {
		$last_entry = mysqli_query($db, "SELECT date FROM `{$_POST['table']}` ORDER BY date DESC LIMIT 1");
		confirmQuery($last_entry);
		$last_entry = mysqli_fetch_all($last_entry);
		if (sizeof($last_entry) > 0) {
			$unix = strtotime($last_entry[0][0]);
			if ($unix != FALSE) {
				$diff = (int)((time() - $unix) / 86400);
				echo $diff;
			}
		}
	}
}
?>