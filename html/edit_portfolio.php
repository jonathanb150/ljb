<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script type="text/javascript">
	$(document).ready(function() {
		$("input").keypress(function(key){
			if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
				return false;
			}
		});
	});
</script>
<?php
if (isset($_POST['cancel'])) {
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
else if (isset($_GET['item']) && isset($_POST['confirm']) && isset($_POST['selling-price']) && isset($_POST['target-price'])) {
	$target_price = mysqli_escape_string($db, $_POST['target-price']);
    $selling_price = mysqli_escape_string($db, $_POST['selling-price']);
	$portfolio_table = $_SESSION['user']."_portfolio";
	if (tableExists($db, $portfolio_table)) {
		$edit_portfolio = mysqli_query($db, "UPDATE `{$portfolio_table}` SET target_price = '{$target_price}', selling_price = '{$selling_price}' WHERE id = {$_GET['item']} AND status = 'open'") or die(mysqli_error($db));
		confirmQuery($edit_portfolio);
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
			"<form method='POST' action='/edit_portfolio.php?item={$_GET['item']}' class='main-form'>".
				"<h1><i class='fas fa-edit' style='margin-right: 10px;'></i>Edit Porfolio Item</h1>".
				"<table id='add-to-portfolio'>".
					"<thead>".
						"<th>Item</th>".
						"<th>Target<br>Price</th>".
						"<th>Selling<br>Price</th>".
					"</thead>".
					"<tbody>".
						"<tr>".
							"<td>{$row['item']}</td>".
							(isset($_POST['review']) ? "<td>{$_POST['target-price']}<input style='display: none;' name='target-price' value='{$_POST['target-price']}'></td>" : "<td><input type='text' name='target-price' value='".(number_format((float)$row['target_price'], 2, '.', ''))."'></td>").
							(isset($_POST['review']) ? "<td>{$_POST['selling-price']}<input style='display: none;' name='selling-price' value='{$_POST['selling-price']}'></td>" : "<td><input type='text' name='selling-price' value='".(round($row['selling_price'], 3))."'></td>").
						"</tr>".
					"</tbody>".
				"</table>".
				(isset($_POST['review']) ? "<button class='add-to' name='confirm' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-clipboard-check' style='margin-right: 7px;'></i>CONFIRM</button><button class='add-to' name='edit' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-edit' style='margin-right: 7px;'></i>EDIT</button><button class='add-to' name='cancel' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-ban' style='margin-right: 7px;'></i>CANCEL</button>" : "<button class='add-to' name='review' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fas fa-search' style='margin-right: 7px;'></i>REVIEW</button><button class='add-to' name='cancel' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-ban' style='margin-right: 7px;'></i>CANCEL</button>").
			"</form>";
		} else {
			echo "<meta http-equiv='refresh' content='0;url=index.php'>";
		}
	}
} 
else {
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
?>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>