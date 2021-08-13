<?php 
require 'db.php';
require 'functions.php';

$bears = [];
$bulls = [];
if (isset($_GET['item']) && isset($_GET['start_date']) && isset($_GET['end_date'])) {
	$query = mysqli_query($db, "SELECT tableName FROM items WHERE symbol = '{$_GET['item']}' OR apiTicker LIKE '%{$_GET['item']}%'");
	confirmQuery($query);
	
	if ($row = mysqli_fetch_assoc($query)) {
		$query = mysqli_query($db, "SELECT close, date FROM `{$row['tableName']}` WHERE date >= '{$_GET['start_date']}' AND date <= '{$_GET['end_date']}'");
		confirmQuery($query);
		$query = mysqli_fetch_all($query);
		
		if (count($query) >= 300) {
			$array = arrayToDouble($query);

			$bear = 0;
			$bear_start = $query[50][1];
			
			$bull = 0;
			$bull_start = $query[50][1];
			
			//Calculate AVG UPs & DOWNs
			$avg_up = [];
			$avg_down = [];
			for ($i = 50; $i < count($array); $i++) { 
				if ($array[$i] >= SMA50($array, $i)) {
					$bull++;
				} else {
					if ($bull > 0) {
						$avg_up[] = $bull;
					}
					$bull = 0;
				}
			}

			for ($i = 50; $i < count($array); $i++) { 
				if ($array[$i] <= SMA50($array, $i)) {
					$bear++;
				} else {
					if ($bear > 0) {
						$avg_down[] = $bear;
					}
					$bear = 0;
				}
			}

			//Calculate Bulls & Bears
			$bull = 0;
			$bear = 0;
			$avg_up = (int) (array_sum($avg_up)/count($avg_up)*1.25);
			$avg_down = (int) (array_sum($avg_down)/count($avg_down)*1.25);

			for ($i = 50; $i < count($array); $i++) { 
				if ($array[$i] >= SMA50($array, $i)) {
					$bull++;
				} else {
					if ($bull >= $avg_up) {
						$bulls[] = [$bull_start, $query[$i][1]];
					}
					$bull_start = $query[$i][1];
					$bull = 0;
				}
			}

			for ($i = 50; $i < count($array); $i++) { 
				if ($array[$i] <= SMA50($array, $i)) {
					$bear++;
				} else {
					if ($bear >= $avg_down) {
						$bears[] = [$bear_start, $query[$i][1]];
					}
					$bear_start = $query[$i][1];
					$bear = 0;
				}
			}
		}
	}
}

echo json_encode([$bears, $bulls]);

function arrayToDouble($array) {
	$double_array = [];
	for ($i=0; $i < count($array); $i++) { 
		$double_array[] = (double)$array[$i][0];
	}

	return $double_array;
}

function SMA50($array, $index) {
	$sma = 0;
	for ($i=$index-50; $i < $index; $i++) { 
		if (isset($array[$i])) {
			$sma += $array[$i];
		} else {
			return -1;
		}
	}
	return $sma/50;
}

?>