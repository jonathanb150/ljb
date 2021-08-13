<?php
session_start();
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php");
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db_inveltio.php");
require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php");
require($_SERVER['DOCUMENT_ROOT']."/natsulytics/natsulytics.php");

if (verifyLoggedIn()) {
	redirect("/index.php");
}
else if (verifyClientLoggedIn()) {
	redirect("/user/index.php");
}
?> 
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/x-icon" href="/img/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="/css/styles.css">
	<link href="https://fonts.googleapis.com/css?family=Heebo:100,300,400,500,700" rel="stylesheet">
	<script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
	<title>LJB Finance</title>
</head>
<script type="text/javascript">
	$(document).ready(function() {
		$("body").css("opacity", "1");
	});
</script>
<body id="loginBody" style='min-width: unset !important;'>