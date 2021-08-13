<?php require "db.php"; ?>
<?php require "functions.php"; ?>
<?php 
	session_start();

	if(isset($_SESSION['user']) && isset($_POST['task'])){
		$tasks_table = $_SESSION['user']."_daily_tasks";
		
		if(tableExists($db, $tasks_table)){
			confirmQuery(mysqli_query($db, "UPDATE `{$tasks_table}` SET status = 1, count = count + 1 WHERE task = '{$_POST['task']}'"));
		}
	}
	else if(isset($_SESSION['user']) && isset($_POST['get_report'])) {
		$query = mysqli_query($db, "SELECT daily_tasks_report FROM users WHERE username = '{$_SESSION['user']}'");
		confirmQuery($query);

		if(mysqli_num_rows($query) > 0) {
			echo mysqli_fetch_assoc($query)['daily_tasks_report'];
		}
	}
?>
