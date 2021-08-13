<?php require "db.php"; ?>
<?php require "functions.php"; ?>
<?php
session_start();

if(isset($_POST['get_position_note']) && isset($_SESSION['user'])){
	$query = mysqli_query($db, "SELECT note FROM `{$_SESSION['user']}_portfolio` WHERE id = '{$_POST['get_position_note']}'");
	confirmQuery($query);

	if($row = mysqli_fetch_assoc($query)){
		echo $row['note'];
	}
}
?>