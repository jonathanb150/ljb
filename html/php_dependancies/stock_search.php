<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php"); ?>
<?php 
header('Content-Type: application/json');
$stocks = array();
$dbStocks = mysqli_query($db, "SELECT symbol, name, tableName FROM items WHERE type LIKE '%stock%'") or die(); 
while ($row = mysqli_fetch_assoc($dbStocks)) {
	$stocks[] = $row["symbol"] . " - " . $row["name"];
}
$stocks = json_encode($stocks);
echo $stocks;
?>