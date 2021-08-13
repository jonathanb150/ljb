<?php require "php_dependancies/db.php"; ?>
<?php require "php_dependancies/functions.php"; ?>
<?php
if(isset($_GET['item'])){
	$query = mysqli_query($db, "SELECT type FROM items WHERE symbol = '{$_GET['item']}'");
	confirmQuery($query);

	if($row = mysqli_fetch_assoc($query)){
		$type = "stocks";

		if($row['type'] == "index"){
			$type = "indexes";
		}
		else if($row['type'] == "commodity"){
			$type = "commodities";
		}
		else if($row['type'] == "currency" || $row['type'] == "crypto_currency"){
			$type = "currencies";
		}
		else if($row['type'] == "etf"){
			$type = "etfs";
		}

		redirect("/analyze_{$type}.php?item={$_GET['item']}");
	}
	else{
		redirect("/index.php");
	}
}
else{
	redirect("/index.php");
}
?>