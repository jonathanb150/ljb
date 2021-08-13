<?php require "db.php"; ?>
<?php 
	session_start();
	if(isset($_SESSION['user']) && isset($_POST['questions_array']) && is_array(json_decode($_POST['questions_array'], true))){
		$questions_array = json_decode($_POST['questions_array'], true);
		for ($i=0; $i < count($questions_array); $i++) { 
			$questions_array[$i] = (float) $questions_array[$i];
		}

		$stocks = (float) 20;
		$cash = (float) 0;
		$bonds = (float) 0;
		$others = (float) 10;

		//QUESTION 1
		$stocks += 5-$questions_array[0];
		$bonds += $questions_array[0];

		//QUESTION 2
		$stocks += 5-$questions_array[1];
		$bonds += $questions_array[1]/2;
		$cash += $questions_array[1]/2;

		//QUESTION 3
		$stocks += 5-$questions_array[2];
		$bonds += $questions_array[2]/2;
		$cash += $questions_array[2]/2;

		//QUESTION 4 
		$stocks += $questions_array[3]-1;
		$cash += 5-($questions_array[3]-1);

		//QUESTION 5 
		$stocks += $questions_array[4]-1;
		$cash += 5-($questions_array[4]-1);

		//QUESTION 6
		$stocks += 5-$questions_array[5];
		$bonds += $questions_array[5]/2;
		$cash += $questions_array[5]/2;

		//QUESTION 7 
		$stocks += $questions_array[6]-1;
		$others += 5-($questions_array[6]-1);

		//QUESTION 8
		$stocks += 5-$questions_array[7];
		$bonds += $questions_array[7]/2;
		$cash += $questions_array[7]/2;

		//QUESTION 9
		$stocks += 5-$questions_array[8];
		$bonds += $questions_array[8]/2;
		$others += $questions_array[8]/2;

		//QUESTION 10
		$stocks += 5-$questions_array[9];
		$bonds += $questions_array[9]/2;
		$cash += $questions_array[9]/2;

		//QUESTION 11
		$stocks += 5-$questions_array[10];
		$bonds += $questions_array[10]/3;
		$cash += $questions_array[10]/3;
		$others += $questions_array[10]/3;

		//QUESTION 12 
		$stocks += $questions_array[11]-1;
		$cash += (5-($questions_array[11]-1))/2;
		$others += (5-($questions_array[11]-1))/2;

		//QUESTION 13 
		$others += $questions_array[12]-1;
		$stocks += 5-($questions_array[12]-1);

		//QUESTION 14 
		$cash += ($questions_array[13]-1)/2;
		$bonds += ($questions_array[13]-1)/2;
		$bonds += 5-($questions_array[13]-1);

		$stocks = (string) (round($stocks, 2)."%");
		$cash = (string) (round($cash, 2)."%");
		$bonds = (string) (round($bonds, 2)."%");
		$others = (string) (round($others, 2)."%");

		//RESULTS
		$result = [];
		$result['answers'] = $questions_array;
		$result['distribution'][] = ["stocks", "cash", "bonds", "others"];
		$result['distribution'][] = [$stocks, $cash, $bonds, $others];

		mysqli_query($db, "UPDATE users SET economy_health = '".json_encode($result, true)."' WHERE username = '{$_SESSION['user']}'") or die("Connection errors.");

		echo json_encode([$stocks, $cash, $bonds, $others], true);
	}
?>