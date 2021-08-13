<?php require "db.php"; ?>
<?php require "functions.php"; ?>
<?php 
session_start();

if(isset($_POST['type']) && isset($_SESSION['user'])){
	$query = mysqli_query($db, "SELECT reminders FROM users WHERE username = '{$_SESSION['user']}'");
	confirmQuery($query);

	if($row = mysqli_fetch_assoc($query)){
		$array = json_decode($row['reminders'], true);

		if(is_array($array) && isset($array[$_POST['type']])){
			$array[$_POST['type']] = false;

			$array = json_encode($array, true);

			confirmQuery(mysqli_query($db, "UPDATE users SET reminders = '{$array}' WHERE username = '{$_SESSION['user']}'"));
		}
	}

}

?>