<?php 
define("dbHost2", "localhost");
define("dbUser2", "ljb");
define("dbPass2", "GsnSdnrt^3475Sdnkfg#465");
define("dbName2", "inveltio");
$db2 = mysqli_connect(dbHost2, dbUser2, dbPass2, dbName2);

if (!$db2) {
	echo "We are experiencing connection issues. (" . "<b>" . mysqli_connect_errno() . ")</b>";
	die();
}

mysqli_set_charset($db2, "utf8mb4");
?>
