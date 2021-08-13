<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php
$error = "";
if (isset($_POST["sort"])) {
	if (!isset($_POST["start_date"]) || strlen($_POST["start_date"]) <= 0) {
		$error = "Please fill all the inputs";
	} else if (!isset($_POST["end_date"]) || strlen($_POST["end_date"]) <= 0) {
		$error = "Please fill all the inputs";
	} else if (!isset($_POST["num_stocks"]) || strlen($_POST["num_stocks"]) <= 0) {
		$error = "Please fill all the inputs";
	}
}
?>
<form class='sort' method="POST" action="/sort_stocks.php#sort_results" onsubmit="return submitSort();">
	<label>Analyze</label><input type="text" name="num_stocks" autocomplete="off" maxlength="3" <?php if(isset($_POST['num_stocks']) && !empty($_POST['num_stocks'])){echo "value='{$_POST['num_stocks']}'";} ?>><label>biggest stocks by</label>
	<select name='change_in_2'>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'MarketCap'){echo "selected";} ?> value="MarketCap">Market Cap</option>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'profit'){echo "selected";} ?> value="profit">Profit</option>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'revenue'){echo "selected";} ?> value="revenue">Revenue</option>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'eps'){echo "selected";} ?> value="eps">EPS</option>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'equity'){echo "selected";} ?> value="equity">Equity</option>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'cash'){echo "selected";} ?> value="cash">Cash</option>
		<option <?php if(isset($_POST['change_in_2']) && $_POST['change_in_2'] == 'debt'){echo "selected";} ?> value="debt">Debt</option>
	</select>
	<label>that belong to the</label>
	<select name='sector'>
		<?php 
		$sector = mysqli_query($db, "SELECT DISTINCT (sector) FROM items WHERE sector != 'N/A'");
		confirmQuery($sector);
		echo isset($_POST['sector']) && $_POST['sector'] == 'all' ? "<option selected value='all'>All</option>" : "<option value='all'>All</option>";
		while ($row = mysqli_fetch_assoc($sector)) {
			echo isset($_POST['sector']) && $_POST['sector'] == $row['sector'] ? "<option selected value='{$row['sector']}'>{$row['sector']}</option>" : "<option value='{$row['sector']}'>{$row['sector']}</option>";
		}
		?>
	</select>
	<label>sector</label>
	<div></div>
	<label>Sort by change in</label>
	<select name='change_in'>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'MarketCap'){echo "selected";} ?> value="MarketCap">Market Cap</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'price'){echo "selected";} ?> value="price">Price</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'profit'){echo "selected";} ?> value="profit">Profit</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'revenue'){echo "selected";} ?> value="revenue">Revenue</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'eps'){echo "selected";} ?> value="eps">EPS</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'equity'){echo "selected";} ?> value="equity">Equity</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'cash'){echo "selected";} ?> value="cash">Cash</option>
		<option <?php if(isset($_POST['change_in']) && $_POST['change_in'] == 'debt'){echo "selected";} ?> value="debt">Debt</option>
	</select>
	<label>from</label>
	<input type="date" name="start_date" <?php if(isset($_POST['start_date']) && !empty($_POST['start_date'])){echo "value='{$_POST['start_date']}'";} ?>>
	<label>to</label>
	<input type="date" name="end_date" <?php if(isset($_POST['end_date']) && !empty($_POST['end_date'])){echo "value='{$_POST['end_date']}'";} ?>>
	<input type="submit" name="sort" value="Sort" class="button">
	<img id="sort_loading" src="/img/ajax-loader-2.gif" style="display: none; margin: 12px auto 0 auto">
</form>
<?php 
if(!empty($error)){
	echo "<div id='sort_error' style='margin: 0 auto;'><div class='super-container'><h1 class='super-container-header'>Error</h1><div></div><div class='general-container' style='margin: 0 auto; width: 100%;'><nav><h1 class='general-container-selected' style='display: none;'></h1></nav><div class='general-container-content'>{$error}</div></div></div></div>";
	echo "<script src='/js/sort_stocks.js'></script>";
	require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php");
	die();
}
?>
<div id="sort_results" class="super-container" style='width: 90%; margin: 30px auto 0 auto; display: <?php echo empty($error) && isset($_POST['sort']) ? "block" : "none"; ?>'>
	<h1 class="super-container-header">Sorting Results</h1>
	<div></div>
	<div class="general-container" style='margin: 0 auto; width: 100%;'>
		<nav style="display: none;">
			<h1 class='general-container-selected'></h1>
		</nav>
		<div class="general-container-content" style="width: 100%"></div>
	</div>
</div>
<script src="/js/sort_stocks.js"></script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>