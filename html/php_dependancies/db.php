<?php 
define("dbHost", "localhost");
define("dbUser", "ljb");
define("dbPass", "GsnSdnrt^3475Sdnkfg#465");
define("dbName", "ljb");
$db = mysqli_connect(dbHost, dbUser, dbPass, dbName);

if (!$db) {
	echo "We are experiencing connection issues. (" . "<b>" . mysqli_connect_errno() . ")</b>";
	die();
}

mysqli_set_charset($db, "utf8mb4");
?>
