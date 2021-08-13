<?php
if (isset($_POST['query']) && isset($_POST['startDate']) && isset($_POST['endDate'])) {
	$command = "python3.7 {$_SERVER['DOCUMENT_ROOT']}/algorithms/GlobalEconomy/linearizedPlot.py '{$_POST['query']}' '{$_POST['startDate']}' '{$_POST['endDate']}'";
	$exec = shell_exec($command);

	if (strpos($exec, "Plotly") !== FALSE) {
		echo $exec;
	} else {
		echo "fail";
	}
}	 
?>