<?php require "php_dependancies/db.php"; ?>
<?php 
if(isset($_GET['item']) && isset($_GET['start_date']) && isset($_GET['end_date']) && getItemTable($db, $_GET['item']) !== false) {

	$item_table = getItemTable($db, $_GET['item']);
	$fp = fopen('graphs/data.csv', 'w');
	fputcsv($fp, getColumnNames($db, $item_table));
	$query = mysqli_query($db, "SELECT * FROM `{$item_table}` WHERE date >= '{$_GET['start_date']}' AND date <= '{$_GET['end_date']}' ORDER BY date ASC") or die("Incorrect date syntax.");

	while ($row = mysqli_fetch_assoc($query)) { 
		if(isset($row['id'])) {
			$data_temp = array_values($row);
			$data = [];

			for ($i=1; $i < count($data_temp); $i++) { 
				$data[] = $data_temp[$i]; 
			}
		}
		else {
			$data = array_values($row);
		}

		fputcsv($fp, $data);
	}
	fclose($fp);

	if(tableExists($db, $_GET['item']."_f")) {
		$fp = fopen('graphs/fundamentals.csv', 'w');
		fputcsv($fp, getColumnNames($db, $_GET['item']."_f"));
		$query = mysqli_query($db, "SELECT * FROM `".$_GET['item']."_f"."` WHERE date >= '{$_GET['start_date']}' AND date <= '{$_GET['end_date']}' ORDER BY date ASC") or die("Incorrect date syntax.");

		while ($row = mysqli_fetch_assoc($query)) { 
			if(isset($row['id'])) {
				$data_temp = array_values($row);
				$data = [];

				for ($i=1; $i < count($data_temp); $i++) { 
					$data[] = $data_temp[$i]; 
				}
			}
			else {
				$data = array_values($row);
			}

			fputcsv($fp, $data);
		}
		fclose($fp);

		echo "<script>window.open('/graphs/data.csv', '_black');</script>";
		echo "<script>window.open('/graphs/fundamentals.csv', '_black');</script>";
	}
	else {
		echo "<script>window.open('/graphs/data.csv', '_black');</script>";
	}
	
}
else if(!isset($_GET['item']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])) {
	die("Incorrect or missing args. Please provide item, start_date, end_date (Date format = YYYY-MM-DD)");
}
else if(getItemTable($db, $_GET['item']) === false) {
	die("Invalid item.");
}

function getItemTable($db, $symbol){
	$query = mysqli_query($db, "SELECT tableName FROM items WHERE symbol = '{$symbol}' or apiTicker = '{$symbol}' or tableName = '{$symbol}'") or die("Error.");
	if($row = mysqli_fetch_assoc($query)){
		return $row["tableName"];
	}
	return false;
}

function getColumnNames($db, $table_name) {
	$query = mysqli_query($db, "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema='ljb' AND table_name='{$table_name}'") or die("Connection errors.");

	$query = mysqli_fetch_all($query);
	$array = [];

	for ($i=0; $i < count($query); $i++) { 
		if($query[$i][0] != "id") {
			$array[] = $query[$i][0];
		}
	}

	return $array;
}

function tableExists($db, $table) {
	if ($result = $db->query("SHOW TABLES LIKE '".$table."'")) {
		if($result->num_rows == 1) {
			return true;
		}
	}
	return false;
}

//Redirect
function redirect($location) {
	header("Location: {$location}");
	exit;
}
?>