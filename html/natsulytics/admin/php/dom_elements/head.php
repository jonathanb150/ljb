<?php session_start(); ?>
<?php require("php/dependencies/db_connection.php"); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog==" crossorigin="anonymous" />
	<link rel="stylesheet" href="css/styles.css">
	<link rel="stylesheet" href="css/responsive.css">
	<title>Admin Panel</title>
</head>
<body>
<header>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<a class="navbar-brand" href="/admin/">Admin Panel</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
		<div class="navbar-nav">
		<a href="index.php" <?php if(isset($_SERVER['SCRIPT_NAME']) && stringInVariable("index.php", $_SERVER['SCRIPT_NAME'])) { echo 'class="nav-link active"'; } else { echo 'class="nav-link"'; } ?>><i class="fas fa-home"></i>Home</a>
		<a href="logs.php" <?php if(isset($_SERVER['SCRIPT_NAME']) && stringInVariable("logs.php", $_SERVER['SCRIPT_NAME'])) { echo 'class="nav-link active"'; } else { echo 'class="nav-link"'; } ?>><i class="fas fa-file-alt"></i>Logs<span style="margin-left: 0.3em" class="badge badge-light"><?php echo is_array(glob(LOGS_PATH."*")) ? count(glob(LOGS_PATH."*")) : "0"; ?></span></a>
		<a href="analytics.php" <?php if(isset($_SERVER['SCRIPT_NAME']) && stringInVariable("analytics.php", $_SERVER['SCRIPT_NAME'])) { echo 'class="nav-link active"'; } else { echo 'class="nav-link"'; } ?>><i class="fas fa-chart-area"></i>Web Analytics</a>
		</div>
	</div>
	</nav>
</header>