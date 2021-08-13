<?php require "db.php";?>
<?php 
if(isset($_POST['item']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
	$table_name = getTableName($db, $_POST['item']);

	if($_POST['start_date'] == -1 && $_POST['end_date'] == -1 && $table_name) {
		$query = mysqli_query($db, "SELECT close, date FROM `{$table_name}` ORDER BY date ASC") or die(mysqli_error($db));

		$dates = [];
		$values = [];

		while($row = mysqli_fetch_assoc($query)) {
			$dates[] = $row['date'];
			$values[] = $row['close'];
		}

		echo json_encode(array("dates"=>$dates, "values"=>$values), true);
	}
}

function getTableName($db, $item) {
	$query = mysqli_query($db, "SELECT tableName FROM items WHERE symbol = '{$item}' OR apiTicker = '{$item}' OR tableName = '{$item}'") or die(mysqli_error($db));

	if($row = mysqli_fetch_assoc($query)) {
		return $row['tableName'];
	}

	return false;
}
?>