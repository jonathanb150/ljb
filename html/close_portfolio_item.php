<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='selling-price']").keypress(function(key){
			if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
				return false;
			}
		});
		$("input[name='selling-price']").keyup(function(key){
			if ($.isNumeric($(this).val())) {
				var sellingPrice = parseFloat($(this).val());
				var buyingPrice = parseFloat($(this).attr('buying-price'));
				var allocatedCapital = parseFloat($(this).attr('allocated-capital'));
				var profitLossPercentage = parseFloat((sellingPrice)/buyingPrice);
				var profitLoss = parseFloat(allocatedCapital*profitLossPercentage);
				$("#profit_loss").html("<b>$ </b>"+profitLoss.toFixed(2)+"<input style='display: none;' name='profit-loss' value='"+profitLoss+"'>");
			}
		});
		if ($.isNumeric($("input[name='selling-price']").val())) {
			var sellingPrice = parseFloat($("input[name='selling-price']").val());
			var buyingPrice = parseFloat($("input[name='selling-price']").attr('buying-price'));
			var allocatedCapital = parseFloat($("input[name='selling-price']").attr('allocated-capital'));
			var profitLossPercentage = parseFloat((sellingPrice)/buyingPrice);
			var profitLoss = parseFloat(allocatedCapital*profitLossPercentage);
			$("#profit_loss").html("<b>$ </b>"+profitLoss.toFixed(2)+"<input style='display: none;' name='profit-loss' value='"+profitLoss+"'>");
		}
	});
</script>
<?php
if (isset($_POST['cancel'])) {
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
else if (isset($_GET['item']) && isset($_POST['confirm']) && isset($_POST['selling-price'])) {
    $selling_price = mysqli_escape_string($db, $_POST['selling-price']);
	$portfolio_table = $_SESSION['user']."_portfolio";
	if (tableExists($db, $portfolio_table)) {
		$profit_loss = (((double)$_POST['selling-price']*(double)$_POST['allocated-capital'])/(double)$_POST['buying-price']) - (double)$_POST['allocated-capital'];
		$get_clients = mysqli_query($db2, "SELECT balance, portfolio_percentage, uid FROM users WHERE portfolio_percentage > 0");
		confirmQuery($get_clients);

		while($row = mysqli_fetch_assoc($get_clients)){
			if(tableExists($db2, $row['uid']."_cash")){
				$client_percentage = (double) $row['portfolio_percentage'];
				$client_balance = (double) $row['balance'];

				$client_balance = $client_balance + (($client_percentage*$profit_loss)/100);

				confirmQuery(mysqli_query($db2, "UPDATE users SET balance = {$client_balance} WHERE uid = '{$row['uid']}'"));
				confirmQuery(mysqli_query($db2, "UPDATE `{$row['uid']}_cash` SET cash = (cash+".(($client_percentage*$profit_loss)/100).") WHERE date ='".date('Y-m-d')."'"));
			}
		}

		updatePortfolioPercentages($db, $db2);
		$edit_portfolio = mysqli_query($db, "UPDATE `{$portfolio_table}` SET sold_price = '{$selling_price}', status = 'closed', date_closed = NOW() WHERE id = {$_GET['item']} AND status = 'open'") or die(mysqli_error($db));
		confirmQuery($edit_portfolio);
		editPortfolio($db, $_SESSION['user']);
		echo "<meta http-equiv='refresh' content='0;url=index.php'>";
	}
}
else if (isset($_GET['item']) || (isset($_POST['review']) && isset($_GET['item']))) {
	$portfolio_table = $_SESSION["user"]."_portfolio";
	if (tableExists($db, $portfolio_table)) {
		$get_portfolio = mysqli_query($db, "SELECT * FROM `{$portfolio_table}` WHERE id = {$_GET['item']} AND status = 'open'");
		confirmQuery($get_portfolio);
		if ($row = mysqli_fetch_assoc($get_portfolio)) {
			echo
			"<form method='POST' action='/close_portfolio_item.php?item={$_GET['item']}' class='main-form'>".
				"<h1><i class='fas fa-money-check-alt' style='margin-right: 10px;'></i>Close Portfolio Item</h1>".
				"<table id='add-to-portfolio'>".
					"<thead>".
						"<th>Item</th>".
						"<th>Buying<br>Price</th>".
						"<th>Selling<br>Price</th>".
						"<th>Allocated<br>Capital</th>".
						"<th>Return</th>".
					"</thead>".
					"<tbody>".
						"<tr>".
							"<td>{$row['item']}</td>".
							"<td><input style='display: none;' name='buying-price' value='{$row['bought_price']}'><input style='display: none;' name='allocated-capital' value='{$row['allocated_capital']}'>{$row['bought_price']}</td>".
							((isset($_POST['review']) && isset($_POST['selling-price'])) ? "<td>{$_POST['selling-price']}<input style='display: none;' name='selling-price' value='{$_POST['selling-price']}'></td>" : "<td><input type='text' name='selling-price' autofocus allocated-capital='{$row['allocated_capital']}' buying-price='{$row['bought_price']}' autocomplete='off' value='".(number_format((float)getCurrentPrice($row['item']), 2, '.', ''))."'></td>").
							"<td><b>$</b> ".number_format((float)$row['allocated_capital'], 2, '.', ',')."</td>".
							((isset($_POST['review']) && isset($_POST['profit-loss'])) ? "<td><b>$</b> ".number_format((float)$_POST['profit-loss'], 2, '.', ',')."<input style='display: none;' name='profit-loss' value='{$_POST['profit-loss']}'></td>" : "<td id='profit_loss'></td>").
						"</tr>".
					"</tbody>".
				"</table>".
				(isset($_POST['review']) ? "<button class='add-to' name='confirm' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-clipboard-check' style='margin-right: 7px;'></i>CONFIRM</button><button class='add-to' name='edit' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-edit' style='margin-right: 7px;'></i>EDIT</button><button class='add-to' name='cancel' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-ban' style='margin-right: 7px;'></i>CANCEL</button>" : "<button class='add-to' name='review' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fas fa-search' style='margin-right: 7px;'></i>REVIEW</button><button class='add-to' name='cancel' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-ban' style='margin-right: 7px;'></i>CANCEL</button>").
			"</form>";
		}  else {
			echo "<meta http-equiv='refresh' content='0;url=index.php'>";
		}
	}
} 
else {
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
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
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>