<?php session_start(); ?>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php"); ?>
<?php 
	unset($_SESSION["user"]);
	redirect("/login.php");
?>;