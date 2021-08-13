<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php"); ?>
<?php 
header('Content-Type: application/json');
if (isset($_GET['type'])) {
	if ($_GET['type'] != 'sector') {
		$counter = 0;
		$items = array();
		if($_GET['type'] == 'etf'){
			$query = mysqli_query($db, "SELECT symbol, name, tableName FROM items WHERE is_etf = 'yes'") or die(); 
		}
		else if($_GET['type'] == 'statistical_backtesting') {
			$query = mysqli_query($db, "SELECT symbol, name, tableName FROM items WHERE type LIKE '%stock%' OR type LIKE '%index%' OR type LIKE '%currency%' OR type LIKE '%commodity%' OR is_etf = 'yes'") or die(); 
		}
		else{
			$query = mysqli_query($db, "SELECT symbol, name, tableName FROM items WHERE type LIKE '%{$_GET['type']}%'") or die(); 
		}
		while ($row = mysqli_fetch_assoc($query)) {
			$items[$counter][0] = $row["symbol"];
			$items[$counter][1] = $row["name"];
			$items[$counter][2] = $row["tableName"];
			/*if(isset($row['type'])) {
				$items[$counter][3] = $row["type"];
			}*/
			$counter++;
		}
		$items = json_encode($items);
		echo $items; 
	} else {
		$counter = 0;
		$items = array();
		$query = mysqli_query($db, "SELECT DISTINCT (sector) FROM items WHERE sector != 'N/A'") or die(mysqli_error($db));
		while ($row = mysqli_fetch_assoc($query)) {
			$items[$counter] = $row["sector"];
			$counter++;
		}
		$items = json_encode($items);
		echo $items; 
	}
}
?>