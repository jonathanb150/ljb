<?php
if(isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['item'])) {
	$script = shell_exec("python3.7 -W ignore {$_SERVER["DOCUMENT_ROOT"]}/algorithms/Others/statisticalAnalysis.py '{$_GET['item']}' '{$_GET['start_date']}' '{$_GET['end_date']}' 2>&1");
	if(file_exists($_SERVER["DOCUMENT_ROOT"]."/graphs/".$_GET['item']."stats.xlsx")) {
		echo "<script>window.location.replace('/graphs/{$_GET['item']}stats.xlsx')</script>";
	}
	else {
		echo "File generation failed.";
	}
}

//Redirect
function redirect($location) {
	header("Location: {$location}");
	exit;
}
?>