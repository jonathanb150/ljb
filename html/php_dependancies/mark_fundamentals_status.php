<?php require "db.php"; ?>
<?php require "functions.php"; ?>
<?php 
	if(isset($_POST['item']) && isset($_POST['status']) && ($_POST['status'] == 0 || $_POST['status'] == 1)){
		$fundamentals_status = mysqli_query($db, "SELECT fundamentals_status FROM items WHERE symbol = '{$_POST['item']}' OR apiTicker = '{$_POST['item']}'");
		confirmQuery($fundamentals_status);

		if($row = mysqli_fetch_assoc($fundamentals_status)) {
			$new_fundamentals_status = [];

			if($row["fundamentals_status"] == null) {
				$new_fundamentals_status[0]["date"] = date("Y-m-d", time());
				$new_fundamentals_status[0]["value"] = (int) $_POST['status'];
			}
			else {
				$new_fundamentals_status = json_decode($row["fundamentals_status"], true);
				$new_fundamentals_status[] = array("date"=>date("Y-m-d", time()), "value"=>((int) $_POST['status']));
			}
		}

		$new_fundamentals_status = json_encode($new_fundamentals_status, true);

		confirmQuery(mysqli_query($db, "UPDATE items SET fundamentals_status = '{$new_fundamentals_status}' WHERE symbol = '{$_POST['item']}' OR apiTicker = '{$_POST['item']}'"));
	}
?>