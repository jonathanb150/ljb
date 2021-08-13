<?php require "/var/www/ljb.solutions/html/php_dependancies/db.php"; ?>
<?php require "/var/www/ljb.solutions/html/php_dependancies/db_inveltio.php"; ?>
<?php require "/var/www/ljb.solutions/html/php_dependancies/functions.php"; ?>
<?php 
	session_start();
	if(isset($_SESSION['user']) && isset($_POST['fee']) && isset($_POST['item']) && tableExists($db, $_SESSION['user']."_fees")){
		$cash_balance = (double) getUserBalance($db, $_SESSION['user']);
		if (is_numeric($_POST['fee']) && $_POST['fee'] > 0 && $_POST['fee'] <= $cash_balance) {
			$get_clients = mysqli_query($db2, "SELECT balance, portfolio_percentage, uid FROM users WHERE portfolio_percentage > 0");
			confirmQuery($get_clients);

			$fee = (double) $_POST['fee'];

			while($row = mysqli_fetch_assoc($get_clients)){
				if(tableExists($db2, $row['uid']."_cash")){
					$client_percentage = (double) $row['portfolio_percentage'];
					$client_balance = (double) $row['balance'];

					$client_balance = $client_balance - (($client_percentage*$fee)/100);

					confirmQuery(mysqli_query($db2, "UPDATE users SET balance = {$client_balance} WHERE uid = '{$row['uid']}'"));
					confirmQuery(mysqli_query($db2, "UPDATE `{$row['uid']}_cash` SET cash = (cash-".(($client_percentage*$fee)/100).") WHERE date ='".date('Y-m-d')."'"));
				}
			}
			confirmQuery(mysqli_query($db, "INSERT INTO `{$_SESSION['user']}_fees` (item, amount, date) VALUES ('".$_POST['item']."', {$fee}, NOW())"));

			updatePortfolioPercentages($db, $db2);
			editPortfolio($db, $_SESSION['user']);
			echo "1";
		}
	}
	else if(isset($_SESSION['user']) && isset($_POST['dividend']) && isset($_POST['item']) && tableExists($db, $_SESSION['user']."_dividends")){
		if (is_numeric($_POST['dividend']) && $_POST['dividend'] > 0) {
			$get_clients = mysqli_query($db2, "SELECT balance, portfolio_percentage, uid FROM users WHERE portfolio_percentage > 0");
			confirmQuery($get_clients);

			$dividend = (double) $_POST['dividend'];

			while($row = mysqli_fetch_assoc($get_clients)){
				if(tableExists($db2, $row['uid']."_cash")){
					$client_percentage = (double) $row['portfolio_percentage'];
					$client_balance = (double) $row['balance'];

					$client_balance = $client_balance + (($client_percentage*$dividend)/100);

					confirmQuery(mysqli_query($db2, "UPDATE users SET balance = {$client_balance} WHERE uid = '{$row['uid']}'"));
					confirmQuery(mysqli_query($db2, "UPDATE `{$row['uid']}_cash` SET cash = (cash+".(($client_percentage*$dividend)/100).") WHERE date ='".date('Y-m-d')."'"));
				}
			}
			confirmQuery(mysqli_query($db, "INSERT INTO `{$_SESSION['user']}_dividends` (item, amount, date) VALUES ('".$_POST['item']."', {$dividend}, NOW())"));

			updatePortfolioPercentages($db, $db2);
			editPortfolio($db, $_SESSION['user']);
			echo "1";
		}
	}

	function updatePortfolioPercentages($db, $db2){
		$get_total_cash = mysqli_query($db2, "SELECT SUM(balance) FROM users");
		confirmQuery($get_total_cash);

		if($row = mysqli_fetch_assoc($get_total_cash)){
			$total_cash = (double) $row['SUM(balance)'];

			confirmQuery(mysqli_query($db, "UPDATE users SET balance = {$total_cash} WHERE admin = 1"));

			$get_users_balance = mysqli_query($db2, "SELECT balance, uid FROM users");
			confirmQuery($get_users_balance);

			while($user_info = mysqli_fetch_assoc($get_users_balance)){
				$uid = $user_info['uid']; 
				$user_balance = (double) $user_info['balance'];
				$user_percentage = (double) (($user_balance*100)/$total_cash);

				if($user_percentage != null && !is_nan($user_percentage) && $user_percentage > 0){
					confirmQuery(mysqli_query($db2, "UPDATE users SET portfolio_percentage = {$user_percentage} WHERE uid = '{$uid}'"));
				}
			}
		}
	}
?>