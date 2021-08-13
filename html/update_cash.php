<?php require "/var/www/ljb.solutions/html/php_dependancies/db.php"; ?>
<?php require "/var/www/ljb.solutions/html/php_dependancies/db_inveltio.php"; ?>
<?php require "/var/www/ljb.solutions/html/php_dependancies/functions.php"; ?>
<?php
function updatePortfolioPercentages($db, $db2){
	$get_total_cash = mysqli_query($db2, "SELECT SUM(balance) FROM users");
	confirmQuery($get_total_cash);

	if($row = mysqli_fetch_assoc($get_total_cash)){
		$total_cash = (double) $row['SUM(balance)'];

		confirmQuery(mysqli_query($db, "UPDATE users SET balance = {$total_cash} WHERE admin = 1"));

		$get_users_balance = mysqli_query($db2, "SELECT balance, username FROM users");
		confirmQuery($get_users_balance);

		while($user_info = mysqli_fetch_assoc($get_users_balance)){
			$username = $user_info['username']; 
			$user_balance = (double) $user_info['balance'];
			$user_percentage = (double) (($user_balance*100)/$total_cash);

			if($user_percentage != null && !is_nan($user_percentage) && $user_percentage > 0){
				confirmQuery(mysqli_query($db2, "UPDATE users SET portfolio_percentage = {$user_percentage} WHERE username = '{$username}'"));
			}
		}
	}
}
function newClients($db, $db2){
	$get_new_clients = mysqli_query($db2, "SELECT username FROM users WHERE portfolio_percentage = 0");
	confirmQuery($get_new_clients);

	while($row = mysqli_fetch_assoc($get_new_clients)){
		$client_history_table = $row['username']."_cash_history";
		$client_table = $row['username']."_cash";

		if(tableExists($db2, $client_history_table) && tableExists($db2, $client_table)){
			$get_cash = mysqli_query($db2, "SELECT SUM(cash) FROM `{$client_history_table}` WHERE verified = 1 AND credited = 0");
			confirmQuery($get_cash);

			$get_cash = mysqli_fetch_all($get_cash);

			if(is_array($get_cash) && count($get_cash) == 1 && $get_cash[0][0] != null && $get_cash[0][0] > 0){
				confirmQuery(mysqli_query($db2, "UPDATE `{$client_history_table}` SET credited = 1 WHERE credited = 0 AND verified = 1"));
				confirmQuery(mysqli_query($db2, "INSERT INTO `{$client_table}` (date, cash) VALUES (NOW(), {$get_cash[0][0]})"));
				confirmQuery(mysqli_query($db2, "UPDATE users SET balance = {$get_cash[0][0]} WHERE username = '{$row['username']}'"));
			}
		}
	}
}

function updateClientHistories($db, $db2) {
	$total_balance = (double) getUserTotalBalance($db, "admin");

	if($total_balance != null && !is_nan($total_balance) && $total_balance > 0){
		$get_clients = mysqli_query($db2, "SELECT username, portfolio_percentage FROM users WHERE portfolio_percentage > 0");
		confirmQuery($get_clients);

		while($row = mysqli_fetch_assoc($get_clients)){
			$client_history_table = $row['username']."_cash_history";
			$client_table = $row['username']."_cash";
		
			if(tableExists($db2, $client_history_table) && tableExists($db2, $client_table)){
				$user_balance = (double) (($row['portfolio_percentage']*$total_balance)/100);
				$last_date = mysqli_query($db2, "SELECT date FROM `{$client_table}` ORDER BY date DESC");	
				confirmQuery($last_date);
				$last_date = mysqli_fetch_all($last_date);
				if(is_array($last_date) && (count($last_date) == 0 || (count($last_date) == 1 && $last_date[0][0] != date("Y-m-d")))){
					confirmQuery(mysqli_query($db2, "INSERT INTO `{$client_table}` (date, cash) VALUES (NOW(), {$user_balance})"));
				}
				$get_uncredited_cash = mysqli_query($db2, "SELECT SUM(cash) FROM `{$client_history_table}` WHERE verified = 1 AND credited = 0");
				confirmQuery($get_uncredited_cash);

				$get_uncredited_cash = mysqli_fetch_all($get_uncredited_cash);

				if(is_array($get_uncredited_cash) && count($get_uncredited_cash) == 1 && $get_uncredited_cash[0][0] != null && is_numeric($get_uncredited_cash[0][0])){
					confirmQuery(mysqli_query($db2, "UPDATE `{$client_history_table}` SET credited = 1 WHERE credited = 0 AND verified = 1"));
					confirmQuery(mysqli_query($db2, "UPDATE `{$client_table}` SET cash = (cash+{$get_uncredited_cash[0][0]}) WHERE date = '".date("Y-m-d")."'"));
					confirmQuery(mysqli_query($db2, "UPDATE users SET balance = (balance+{$get_uncredited_cash[0][0]}) WHERE username = '{$row['username']}'"));
				}
			}
		}
	}
}

newClients($db, $db2);
updatePortfolioPercentages($db, $db2);
updateClientHistories($db, $db2);
updatePortfolioPercentages($db, $db2);
?>