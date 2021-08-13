<?php require $_SERVER['DOCUMENT_ROOT']."/register/php/dependencies/user.php"; ?>
<?php session_start(); ?>
<?php
if(isset($_GET['activation_hash'])) {
	$activate_user = NewUser::activateUser($_GET['activation_hash']);

	if(is_numeric($activate_user) && $activate_user > 0) {
		$_SESSION['account_setup'] = $activate_user;
		redirect("/account_setup.php");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes, viewport-fit=cover">
	<link rel="icon" type="image/png" href="/register/media/favicon.png">
	<link href="https://fonts.googleapis.com/css?family=Heebo:100,300,400,500,700" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/register/css/normalize.min.css">
	<link rel="stylesheet" type="text/css" href="/register/css/styles.css">
	<script src="/register/js/jquery.min.js"></script>
	<script src="/register/js/jBox.all.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/register/css/jBox.all.min.css">
	<title>Inveltio</title>
</head>
<body>