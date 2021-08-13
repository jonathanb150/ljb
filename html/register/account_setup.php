<?php require 'php/dom_elements/head.php'; ?>
<?php 
if(!isset($_SESSION['account_setup']) || AccountSetup::hasCompletedSetup($_SESSION['account_setup'])) {
	redirect("/");
}
?>
<style type="text/css">
	body {
		background-color: #ffffff;
	    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 1600 800'%3E%3Cg fill-opacity='0.15'%3E%3Cpolygon fill='%23cecee7' points='1600 160 0 460 0 350 1600 50'/%3E%3Cpolygon fill='%239d9dcf' points='1600 260 0 560 0 450 1600 150'/%3E%3Cpolygon fill='%236c6cb6' points='1600 360 0 660 0 550 1600 250'/%3E%3Cpolygon fill='%233b3b9e' points='1600 460 0 760 0 650 1600 350'/%3E%3Cpolygon fill='%230a0a86' points='1600 800 0 800 0 750 1600 450'/%3E%3C/g%3E%3C/svg%3E");
	    background-size: cover;
	    background-repeat: no-repeat; 
	    background-attachment: fixed;
	    background-position: center;
		padding: 5%
	}
</style>
<link rel="stylesheet" type="text/css" href="/register/css/setup_process.css">
<script type="text/javascript" src="/register/js/setup_process.js"></script>
<?php require 'php/dom_elements/footer.php';?>