<?php 
function arrayToTable($array) {
	$response = null;
	if (!is_array($array)) {
		$array = json_decode($array, true);
	}
	if (is_array($array)) {
		$response .= "<table><thead>";
		for ($a = 0; $a < 1; $a++) { 
			if (is_array($array[$a])) {
				for ($b = 0; $b < sizeof($array[$a]); $b++) { 
					$response .= "<th>".array_values($array[$a])[$b]."</th>";
				}	
			}
		}
		$response .= "</thead><tbody>";
		for ($a = 1; $a < sizeof($array); $a++) { 
			if (is_array($array[$a])) {
				$response .= "<tr>";
				for ($b = 0; $b < sizeof($array[$a]); $b++) { 
					$arrayValue = is_numeric(array_values($array[$a])[$b]) ? number_format(array_values($array[$a])[$b], 3) : array_values($array[$a])[$b];
					$response .= "<td>".$arrayValue."</td>";
				}	
				$response .= "</tr>";
			}
		}
		$response .= "</tbody></table>";
		return $response;
	}
	return $response;
}

if (isset($_POST['stocks']) && isset($_POST['startDate']) && isset($_POST['endDate']) && isset($_POST['sector'])) {
	$stocks = trim($_POST['stocks']);
	$LJBCalcCommand = "python3.7 -W ignore {$_SERVER['DOCUMENT_ROOT']}/algorithms/LongTerm/LJBCalculations/LJBcalculationsAvg.py '{$stocks}' {$_POST['sector']} '{$_POST['startDate']}' '{$_POST['endDate']}'";
	$exec = shell_exec($LJBCalcCommand);
	$exec = json_decode($exec, true);
	if ($exec != null && is_array($exec)) {
		$exec['html'] = arrayToTable($exec['table']);
		echo json_encode($exec, true);
	} else {
		echo "fail";
	}
}	 
?>