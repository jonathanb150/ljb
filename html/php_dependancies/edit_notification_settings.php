<?php require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php"; ?>
<?php session_start(); ?>
<?php 
$user = $_SESSION['user'];
if(isset($_POST['notification'])){
	$notification = json_decode($_POST['notification'], true);
	if(is_array($notification) && count($notification) == 3){
		$get_user_settings = mysqli_query($db, "SELECT notification_settings FROM users WHERE username = '{$user}'") or die("Error.");
		$get_user_settings = mysqli_fetch_all($get_user_settings);
		$get_user_settings = $get_user_settings[0][0];
		$get_user_settings = json_decode($get_user_settings, true);

		if(is_array($get_user_settings)){
			$get_user_settings[$notification['notification_type']][$notification['notification_name']] = $notification['notification_status'];
			$get_user_settings = json_encode($get_user_settings, true);

			mysqli_query($db, "UPDATE users SET notification_settings = '{$get_user_settings}' WHERE username = '{$user}'") or die("Error.");

			echo "1";
		} 
	}
}
else if(isset($_POST['get_user_notifications'])){
	$get_user_settings = mysqli_query($db, "SELECT notification_settings FROM users WHERE username = '{$user}'") or die("Error.");
	$get_user_settings = mysqli_fetch_all($get_user_settings);

	if(isset($get_user_settings[0][0])){
		echo $get_user_settings[0][0];
	}
}
else{
	echo "0";
}

?>