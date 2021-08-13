<?php 
require 'db.php';
require 'functions.php';

//RETURNS ALL BEAR AND BULL MARKETS FOUND IN THE SPECIFIED DATE RANGE
/*
USAGE:
	ACCEPTS 
		REQUIRED INPUTS: item, start_date, end_date (Date range must be at least 1 year to work), find_markets (can be equal to anything).
		OPTIONAL INPUTS: min_duration (will only return bear and bull markets with an equal or greater duration, 2/3 for bear markets, default: 90 days), min_change (will only return bear and bull markets with an equal or greater change percentage, default: 20)
	RETURNS 
		OUTPUTS empty array IF NOTHING IS FOUND.
*/
if (isset($_GET['find_markets']) && isset($_GET['item']) && isset($_GET['start_date']) && isset($_GET['end_date'])) {
	$bears = [];
	$bears[] = ["Start Date", "End Date", "Duration", "Change"];
	$bulls = [];
	$bulls[] = ["Start Date", "End Date", "Duration", "Change"];
	$min_duration = 60;
	$min_change = 18;

	if(isset($_GET['min_duration'])) {
		$min_duration = (int) $_GET['min_duration'];
	}
	if(isset($_GET['min_change'])) {
		$min_change = (int) $_GET['min_change'];
	}

	$query = mysqli_query($db, "SELECT tableName FROM items WHERE symbol = '{$_GET['item']}' OR apiTicker = '{$_GET['item']}'");
	confirmQuery($query);
	
	if ($row = mysqli_fetch_assoc($query)) {
		$query = mysqli_query($db, "SELECT close, date FROM `{$row['tableName']}` WHERE date >= '{$_GET['start_date']}' AND date <= '{$_GET['end_date']}'");
		confirmQuery($query);
		$query = mysqli_fetch_all($query);
		
		if (count($query) >= 200) {
			$array = arrayToDouble($query);

			$bear = 0;
			$bear_price_start = $array[50];
			$bear_date_start = $query[50][1];
			$bear_exceptions = 0;
			
			$bull = 0;
			$bull_price_start = $array[50];
			$bull_date_start = $query[50][1];
			$bull_exceptions = 0;
			
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

			$avg_up = (int) (array_sum($avg_up)/count($avg_up));
			$avg_down = (int) (array_sum($avg_down)/count($avg_down));

			//Calculate Bulls & Bears
			$bull = 0;
			$bear = 0;

			for ($i = 50; $i < count($array); $i++) { 
				if ($array[$i] >= SMA50($array, $i)) {
					$bull++;
				} 
				else if($bull_exceptions < 15) {
					$bull++;
					$bull_exceptions++;
				} else {
					$change = ($array[$i]*100/$bull_price_start)-100;

					if ($bull >= $min_duration && $change >= $min_change) {
						$duration = (int) ((strtotime($query[$i][1])-strtotime($bull_date_start))/86400);
						$bulls[] = [$bull_date_start, $query[$i][1], $duration." days", round($change, 2)."%"];
					}

					$bull_date_start = $query[$i][1];
					$bull_price_start = $array[$i];
					$bull = 0;
					$bull_exceptions = 0;
				}
			}

			for ($i = 50; $i < count($array); $i++) { 
				if ($array[$i] <= SMA50($array, $i)) {
					$bear++;
				} 
				else if($bear_exceptions < 15) {
					$bear++;
					$bear_exceptions++;
				} else {
					$change = ($bear_price_start*100/$array[$i])-100;

					if ($bear >= $min_duration*0.67 && $change >= $min_change) {
						$duration = (int) ((strtotime($query[$i][1])-strtotime($bear_date_start))/86400);
						$bears[] = [$bear_date_start, $query[$i][1], $duration." days", "-".round($change, 2)."%"];
					}

					$bear_date_start = $query[$i][1];
					$bear_price_start = $array[$i];
					$bear = 0;
					$bear_exceptions = 0;
				}
			}
		}
	}
	echo json_encode(array("bull_markets"=>$bulls, "bear_markets"=>$bears, "avg_bull_duration"=>$avg_up, "avg_bear_duration"=>$avg_down), true);
}

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