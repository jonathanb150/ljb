<?php  
if (isset($_GET['item']) && isset($_GET['end_date']) && isset($_GET['start_date'])) {
	$item = trim(strtoupper($_GET['item']));
	$peRatio = shell_exec("python3.7 ".$_SERVER['DOCUMENT_ROOT']."/algorithms/peRatio.py '{$item}' '{$_GET['end_date']}'");
	$peRatioHistorical = shell_exec("python3.7 ".$_SERVER['DOCUMENT_ROOT']."/algorithms/LongTerm/peRatioHistorical.py '{$item}'");
	$peRatio = json_decode($peRatio, true);
	$peRatioHistorical = json_decode($peRatioHistorical, true);
	if (is_array($peRatio) && is_array($peRatioHistorical)) {
		$peRatio = array_merge($peRatio, $peRatioHistorical);
		echo json_encode($peRatio, true);
	}
}
?>