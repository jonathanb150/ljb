<?php if (isset($_POST['startDate']) && isset($_POST['endDate']) && isset($_POST['country'])) {
	$command = "python3.7 -W ignore {$_SERVER['DOCUMENT_ROOT']}/algorithms/GlobalEconomy/fundamentalsAnalysis.py '{$_POST['country']}' '{$_POST['startDate']}' '{$_POST['endDate']}'";
	$exec = shell_exec($command);
	if (strpos($exec, "Plotly") !== FALSE && is_array(json_decode($exec, true))) {
		echo json_decode($exec, true)['graph'];
	} else {
		echo "fail";
	}
}	 
?>