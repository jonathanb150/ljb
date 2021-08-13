<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php  
$resultMessage = null;
if (isset($_POST['add_item'])) {
	if (strlen($_POST['api_ticker']) > 0 && strlen($_POST['item_symbol']) > 0 && strlen($_POST['item_label']) > 0) {
		$country = $_POST['type'] != "currency" && $_POST['type'] != "crypto_currency" && $_POST['type'] != "index" && $_POST['type'] != "commodity" && $_POST['type'] != "etf" ? $_POST['country']."_" : "";
		switch ($_POST['source']){
			case "yahoo":
				if(apiProcessYAHOO($_POST['item_symbol'], $country.$_POST['type'], $_POST['item_label'], $db, $_POST['api_ticker'], $_POST['source'], $_POST['is_etf'])){
					$resultMessage = "Item added succesfully.";
				}
				else{
					$resultMessage = "Error fetching the item from the API.";
				}
				break;
			case "stooq":
				if(apiProcessSTOOQ(apiRequest(strtolower($_POST['api_ticker']), "https://stooq.com/q/d/l/?s="), $_POST['item_symbol'], $country.$_POST['type'], $_POST['item_label'], $db, $_POST['api_ticker'], $_POST['source'], $_POST['is_etf'])){
					$resultMessage = "Item added succesfully.";
				}
				else{
					$resultMessage = "Error fetching the item from the API.";
				}
				break;
			case "fred":
				if(apiProcessFRED(apiRequest($_POST['api_ticker'], "https://fred.stlouisfed.org/graph/fredgraph.csv?bgcolor=%23e1e9f0&chart_type=line&drp=0&fo=open%20sans&graph_bgcolor=%23ffffff&height=450&mode=fred&recession_bars=on&txtcolor=%23444444&ts=12&tts=12&width=1168&nt=0&thu=0&trc=0&show_legend=yes&show_axis_titles=yes&show_tooltip=yes&scale=left&cosd=1900-01-01&coed=3000-01-01&line_color=%234572a7&link_values=false&line_style=solid&mark_type=none&mw=3&lw=2&ost=-99999&oet=99999&mma=0&fml=a&fq=Daily&fam=avg&fgst=lin&fgsnd=2009-06-01&line_index=1&transformation=lin&vintage_date=2018-12-07&revision_date=2018-12-07&nd=1982-01-04&id="), $_POST['item_symbol'], $country.$_POST['type'], $_POST['item_label'], $db, $_POST['api_ticker'], $_POST['source'], $_POST['is_etf'])){
					$resultMessage = "Item added succesfully.";
				}
				else{
					$resultMessage = "Error fetching the item from the API.";
				}
				break;
			default:
				$resultMessage = "Invalid source."; 
		}
	}
	else{
		$resultMessage = "Please fill all input fields."; 
	}
}
//Upload CSV file
if (isset($_POST['upload_csv'])) {
	if (!isset($_POST['stock_symbol']) || strlen($_POST['stock_symbol']) <= 0) {
		$resultMessage = "Please select a symbol.";
	}
	else if (!isset($_POST['stock_label']) || strlen($_POST['stock_label']) <= 0) {
		$resultMessage = "Please select a name.";
	}
	else if (!isset($_POST['type'])) {
		$resultMessage = "Please select the type.";
	}
	else if (isset($_FILES["csv_file"]) && isset($_FILES["csv_file"]["error"]) && $_FILES["csv_file"]["error"] == 4) {
		$resultMessage = "Please select the CSV file to upload.";
	} 
	else if (isset($_FILES["csv_file"]) && sizeof($_FILES["csv_file"]) == 5 && $_FILES["csv_file"]["error"] == 0 && tableStringValueExists($db, "items", "symbol", strtoupper(trim($_POST['stock_symbol'])))) {
		$resultMessage = "The database already contains that item.";
	} 
	else if (isset($_FILES["csv_file"]) && sizeof($_FILES["csv_file"]) == 5 && $_FILES["csv_file"]["error"] == 0) {
		$target_dir = $_SERVER['DOCUMENT_ROOT']."/csv_uploads/";
		$target_file = $target_dir . basename($_FILES["csv_file"]["name"]);
		$file_ext = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		if ($file_ext == "csv") {
			if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
				//Create Table
				$dbName = mysqli_escape_string($db, trim(strtoupper($_POST['stock_symbol'])."_1d"));
				$dbName = str_replace("-", "_", $dbName);
				$createDB = mysqli_query($db, "CREATE TABLE IF NOT EXISTS `{$dbName}` (
				  `id` int(255) NOT NULL AUTO_INCREMENT,
				  `date` date NOT NULL,
				  `open` varchar(255) NOT NULL,
				  `high` varchar(255) NOT NULL,
				  `low` varchar(255) NOT NULL,
				  `close` varchar(255) NOT NULL,
				  `volume` varchar(255) NOT NULL,
				  PRIMARY KEY (`date`),
				  KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
				confirmQuery($createDB);
				//Add to "Stocks" general table if it's not there
				if (!tableStringValueExists($db, "items", "tableName", $dbName)) {
					$stockName = mysqli_escape_string($db, trim($_POST['stock_label']));
					$stockSymbol = mysqli_escape_string($db, trim(strtoupper($_POST['stock_symbol'])));
					$addStock = mysqli_query($db, "INSERT into items (name, symbol, yahooSymbol, tableName, dateCreated, type) VALUES ('{$stockName}', '{$stockSymbol}', '{$stockSymbol}', '{$dbName}', NOW(), '{$_POST['type']}')"); 
					confirmQuery($addStock);
				} 
				//Create fundamentals table
				$fundamentalsTable = mysqli_escape_string($db, trim(strtoupper($_POST['stock_symbol'])."_f"));
				$createFundamentalsTable = mysqli_query($db, "CREATE TABLE IF NOT EXISTS `{$fundamentalsTable}` (
					`type` varchar(255) NOT NULL,
					`date` date NOT NULL,
					`value` varchar(255) NOT NULL
					) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
				confirmQuery($createFundamentalsTable);
				//Add values
				$csv_file = fopen($target_file, "r");
				$csv_content = fread($csv_file, filesize($target_file));
				$row = 0;
				if (($handle = fopen($target_file, "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						if ($row >= 1) {
							$num = count($data);
							$row++;
							$info = array();
							for ($c = 0; $c < $num; $c++) {
								$info[$c] = $data[$c];
							}
							$date = $info[0];
							$open = $info[1];
							$high = $info[2];
							$low = $info[3];
							$close = $info[4];
							$volume = $info[6];
							$addData = mysqli_query($db, "INSERT into `{$dbName}` (date, open, high, low, close, volume) VALUES ('{$date}', '{$open}', '{$high}', '{$low}', '{$close}', '{$volume}')");
							confirmQuery($addData);
						} else {
							$row++;
						}
					}
					fclose($handle);
				}
				fclose($csv_file);
				unlink($target_file);
				$resultMessage = "The file ". basename( $_FILES["csv_file"]["name"]). " has been uploaded to the database.\n {$row} queries were executed.";
			} else {
				$resultMessage = "Sorry, there was an error uploading your file.";
			}
		} else {
			$resultMessage = "You can only upload CSV files.";
		}
	}
}
else if (isset($_POST['upload_csv_fred'])) {
	if (!isset($_POST['fred_symbol']) || strlen($_POST['fred_symbol']) <= 0) {
		$resultMessage = "Please select a symbol.";
	}
	else if (!isset($_POST['fred_label']) || strlen($_POST['fred_label']) <= 0) {
		$resultMessage = "Please select a name.";
	}
	else if (!isset($_POST['fred_type'])) {
		$resultMessage = "Please select the type.";
	}
	else if (isset($_FILES["csv_file_fred"]) && isset($_FILES["csv_file_fred"]["error"]) && $_FILES["csv_file_fred"]["error"] == 4) {
		$resultMessage = "Please select the CSV file to upload.";
	} 
	else if (isset($_FILES["csv_file_fred"]) && sizeof($_FILES["csv_file_fred"]) == 5 && $_FILES["csv_file_fred"]["error"] == 0 && tableStringValueExists($db, "items", "symbol", strtoupper(trim($_POST['fred_symbol'])))) {
		$resultMessage = "The database already contains that item.";
	} 
	else if (isset($_FILES["csv_file_fred"]) && sizeof($_FILES["csv_file_fred"]) == 5 && $_FILES["csv_file_fred"]["error"] == 0) {
		$target_dir = $_SERVER['DOCUMENT_ROOT']."/csv_uploads/";
		$target_file = $target_dir . basename($_FILES["csv_file_fred"]["name"]);
		$file_ext = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		if ($file_ext == "csv") {
			if (move_uploaded_file($_FILES["csv_file_fred"]["tmp_name"], $target_file)) {
				//Create Table
				$dbName = mysqli_escape_string($db, trim(strtoupper($_POST['fred_symbol'])."_1d"));
				$dbName = str_replace("-", "_", $dbName);
				$createDB = mysqli_query($db, "CREATE TABLE IF NOT EXISTS `{$dbName}` (
				  `id` int(255) NOT NULL AUTO_INCREMENT,
				  `date` date NOT NULL,
				  `value` varchar(255) NOT NULL,
				  PRIMARY KEY (`date`),
				  KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
				confirmQuery($createDB);
				//Add to "Stocks" general table if it's not there
				if (!tableStringValueExists($db, "items", "tableName", $dbName)) {
					$stockName = mysqli_escape_string($db, trim($_POST['fred_label']));
					$stockSymbol = mysqli_escape_string($db, trim(strtoupper($_POST['fred_symbol'])));
					$addStock = mysqli_query($db, "INSERT into items (name, symbol, yahooSymbol, tableName, dateCreated, type) VALUES ('{$stockName}', '{$stockSymbol}', '{$stockSymbol}', '{$dbName}', NOW(), '{$_POST['fred_type']}')");
					confirmQuery($addStock);
				} 
				//Add values
				$csv_file = fopen($target_file, "r");
				$csv_content = fread($csv_file, filesize($target_file));
				$row = 0;
				if (($handle = fopen($target_file, "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						if ($row >= 1) {
							$num = count($data);
							$row++;
							$info = array();
							for ($c = 0; $c < $num; $c++) {
								$info[$c] = $data[$c];
							}
							$date = $info[0];
							$value = $info[1];
							if (is_numeric($value)) {
								$addData = mysqli_query($db, "INSERT into `{$dbName}` (date, value) VALUES ('{$date}', '{$value}')");
								confirmQuery($addData);
							}
						} else {
							$row++;
						}
					}
					fclose($handle);
				}
				fclose($csv_file);
				unlink($target_file);
				$resultMessage = "The file ". basename( $_FILES["csv_file_fred"]["name"]). " has been uploaded to the database.\n {$row} queries were executed.";
			} else {
				$resultMessage = "Sorry, there was an error uploading your file.";
			}
		} else {
			$resultMessage = "You can only upload CSV files.";
		}
	}
}
else if (isset($_POST['edit'])) {
	if (isset($_POST['edit_name']) && isset($_POST['selected_edit']) && strlen(trim($_POST['edit_name'])) > 0 && strlen(trim($_POST['selected_edit'])) > 0) {
		$original = mysqli_escape_string($db, $_POST['selected_edit']);
		$name = mysqli_escape_string($db, $_POST['edit_name']);
		$query = mysqli_query($db, "UPDATE items SET name = '{$name}' WHERE symbol = '{$original}'");
		confirmQuery($query);
		$resultMessage = "Successfully changed!";
	} else {
		$resultMessage = "Please fill all the inputs.";
	}
} 
else if (isset($_POST['delete-yes']) && isset($_POST['selected_delete'])) {
	$query = mysqli_query($db, "SELECT tableName, symbol FROM items WHERE symbol = '{$_POST['selected_delete']}'");
	confirmQuery($query);
	if (mysqli_num_rows($query) > 0) {
		if ($row = mysqli_fetch_assoc($query)) {
			$price_table = $row['tableName'];
			$fundamental_table = $row['symbol'].'_f';
			if (tableExists($db, $price_table)) {
				mysqli_query($db, "DROP TABLE `{$price_table}`") or die(mysqli_error($db));
			}
			if (tableExists($db, $fundamental_table)) {
				mysqli_query($db, "DROP TABLE `{$fundamental_table}`") or die(mysqli_error($db));
			}
			mysqli_query($db, "DELETE FROM items WHERE tableName = '{$price_table}'") or die(mysqli_error($db));
			$resultMessage = "Successfully deleted.";
		}
	}
}
else if (isset($_POST['good_fundamentals'])) {
	confirmQuery(mysqli_query($db, "UPDATE items SET fundamentals_status = 1 WHERE symbol = '{$_POST['selected_fundamental']}'"));
}
else if (isset($_POST['bad_fundamentals'])) {
	confirmQuery(mysqli_query($db, "UPDATE items SET fundamentals_status = 0 WHERE symbol = '{$_POST['selected_fundamental']}'"));
}
?>
<script type="text/javascript" src="/js/db_operations.js"></script>
<form method="POST" action="/db_operations.php" class='db-operations-form'>
	<h1><i class="fas fa-edit" style="margin-right: 10px;"></i>Edit Item</h1>
	<select name='selected_edit'>
		<?php  
			$dbStocks = mysqli_query($db, "SELECT symbol, name, tableName FROM items ORDER BY symbol ASC");
			confirmQuery($dbStocks);
			while ($row = mysqli_fetch_assoc($dbStocks)) {
				echo "<option value='{$row['symbol']}' ". ((isset($_POST["selectedStock"]) && $_POST["selectedStock"] == $row['symbol']) ? "selected" : "") .">". ($row['symbol'] . " - " . $row['name']) ."</option>";
			}
		?>
	<select>
	<input style='margin: 10px auto;' type="text" name="edit_name" placeholder="Name" autocomplete="off" value="<?php mysqli_data_seek($dbStocks, 0);  if ($row = mysqli_fetch_assoc($dbStocks)) {echo $row['name'];} ?>">
	<input type="submit" name="edit" value="EDIT">
</form>
<form method="POST" action="/db_operations.php" class='db-operations-form'>
	<h1><i class="fas fa-trash-alt" style="margin-right: 10px;"></i>Delete Item</h1>
	<select name='selected_delete'>
		<?php  
			mysqli_data_seek($dbStocks, 0);
			while ($row = mysqli_fetch_assoc($dbStocks)) {
				echo "<option value='{$row['symbol']}' ". ((isset($_POST["selectedStock"]) && $_POST["selectedStock"] == $row['symbol']) ? "selected" : "") .">". ($row['symbol'] . " - " . $row['name']) ."</option>";
			}
		?>
	<select>
	<input type="button" name="delete" value="DELETE">
</form>
<form method="POST" action="/db_operations.php" class='db-operations-form'>
	<h1><i class="fas fa-trash-alt" style="margin-right: 10px;"></i>Mark Fundamentals Status</h1>
	<select name='selected_fundamental'>
		<?php  
			mysqli_data_seek($dbStocks, 0);
			while ($row = mysqli_fetch_assoc($dbStocks)) {
				echo "<option value='{$row['symbol']}' ". ((isset($_POST["selectedStock"]) && $_POST["selectedStock"] == $row['symbol']) ? "selected" : "") .">". ($row['symbol'] . " - " . $row['name']) ."</option>";
			}
		?>
	<select>
	<input type="submit" name="good_fundamentals" value="Good">
	<input type="submit" name="bad_fundamentals" value="Bad">
</form>
<form method="POST" action="/db_operations.php" class='db-operations-form'>
	<h1><i class="fas fa-upload" style="margin-right: 10px;"></i>Add Item</h1>
	<label style="font-size: 20px; display: block;">Select Country</label>
	<select name='country' style="display: block; margin: 10px auto 0 auto;">
		<option value="us" <?php if(isset($_POST["country"]) && $_POST["country"] == "us"){ echo "selected";} ?>>US</option>
		<option value="uk" <?php if(isset($_POST["country"]) && $_POST["country"] == "uk"){ echo "selected";} ?>>UK</option>
		<option value="hk" <?php if(isset($_POST["country"]) && $_POST["country"] == "hk"){ echo "selected";} ?>>HK</option>
		<option value="cad" <?php if(isset($_POST["country"]) && $_POST["country"] == "cad"){ echo "selected";} ?>>CAD</option>
		<option value="chf" <?php if(isset($_POST["country"]) && $_POST["country"] == "chf"){ echo "selected";} ?>>CHF</option>
		<option value="eu" <?php if(isset($_POST["country"]) && $_POST["country"] == "eu"){ echo "selected";} ?>>EU</option>
	</select>
	<label style="font-size: 20px; display: block; margin-top: 10px;">Select API</label>
	<select name='source' style="display: block; margin: 10px auto 0 auto;">
		<option value="yahoo" <?php if(isset($_POST["source"]) && $_POST["source"] == "yahoo"){ echo "selected";} ?>>Yahoo</option>
		<option value="stooq" <?php if(isset($_POST["source"]) && $_POST["source"] == "stooq"){ echo "selected";} ?>>Stooq</option>
		<option value="fred" <?php if(isset($_POST["source"]) && $_POST["source"] == "fred"){ echo "selected";} ?>>FRED</option>
	</select>
	<label style="font-size: 20px; display: block; margin-top: 10px;">Select Type</label>
	<select <?php if((isset($_POST["source"]) && $_POST["source"] == "yahoo") || !isset($_POST["source"])) { echo "name='type'"; } else { echo "name='type2'"; }?> id="select_yahoo" style="display: <?php if((isset($_POST["source"]) && $_POST["source"] == "yahoo") || !isset($_POST["source"])) { echo "block"; } else { echo "none"; }?>; margin: 10px auto 0 auto;">
		<option value="stock" <?php if(isset($_POST["type"]) && $_POST["type"] == "stock"){ echo "selected";} ?>>Stock</option>
		<option value="index" <?php if(isset($_POST["type"]) && $_POST["type"] == "index"){ echo "selected";} ?>>Index</option>
		<option value="currency" <?php if(isset($_POST["type"]) && $_POST["type"] == "currency"){ echo "selected";} ?>>Currency</option>
		<option value="crypto_currency" <?php if(isset($_POST["type"]) && $_POST["type"] == "crypto_currency"){ echo "selected";} ?>>Crypto Currency</option>
		<option value="commodity" <?php if(isset($_POST["type"]) && $_POST["type"] == "commodity"){ echo "selected";} ?>>Commodity</option>
	</select>
	<select <?php if(isset($_POST["source"]) && $_POST["source"] == "stooq") { echo "name='type'"; } else { echo "name='type2'"; }?> id="select_stooq" style="display: <?php if(isset($_POST["source"]) && $_POST["source"] == "stooq") { echo "block"; } else { echo "none"; }?>; margin: 10px auto 0 auto;">
		<option value="stock" <?php if(isset($_POST["type"]) && $_POST["type"] == "stock"){ echo "selected";} ?>>Stock</option>
		<option value="index" <?php if(isset($_POST["type"]) && $_POST["type"] == "index"){ echo "selected";} ?>>Index</option>
		<option value="currency" <?php if(isset($_POST["type"]) && $_POST["type"] == "currency"){ echo "selected";} ?>>Currency</option>
		<option value="crypto_currency" <?php if(isset($_POST["type"]) && $_POST["type"] == "crypto_currency"){ echo "selected";} ?>>Crypto Currency</option>
		<option value="commodity" <?php if(isset($_POST["type"]) && $_POST["type"] == "commodity"){ echo "selected";} ?>>Commodity</option>
		<option value="fundamental" <?php if(isset($_POST["type"]) && $_POST["type"] == "fundamental"){ echo "selected";} ?>>Fundamental</option>
		
	</select>
	<select <?php if(isset($_POST["source"]) && $_POST["source"] == "fred") { echo "name='type'"; } else { echo "name='type2'"; }?> id="select_fred" style="display: <?php if(isset($_POST["source"]) && $_POST["source"] == "fred") { echo "block"; } else { echo "none"; }?>; margin: 10px auto 0 auto;">
		<option value="bond" <?php if(isset($_POST["type"]) && $_POST["type"] == "bond"){ echo "selected";} ?>>Bond</option>
		<option value="fundamental" <?php if(isset($_POST["type"]) && $_POST["type"] == "fundamental"){ echo "selected";} ?>>Fundamental</option>
	</select>
	<label style="font-size: 20px; display: block; margin-top: 10px;">Is ETF?</label>
	<select name="is_etf">
		<option value="no" <?php if(isset($_POST["is_etf"]) && $_POST["is_etf"] == "no"){ echo "selected";} ?>>No</option>
		<option value="yes" <?php if(isset($_POST["is_etf"]) && $_POST["is_etf"] == "yes"){ echo "selected";} ?>>Yes</option>
	</select>
	<input type="text" name="item_symbol" placeholder="Symbol" autocomplete="off">
	<input type="text" name="item_label" placeholder="Name" autocomplete="off">
	<input type="text" name="api_ticker" placeholder="API Ticker" autocomplete="off">
	<input type="submit" name="add_item" value="SUBMIT">
</form>
<script type="text/javascript">
$("select[name='source']").change(function() {
	if($(this).val() == "fred"){
		$("#select_yahoo").attr("name", "type2");
		$("#select_yahoo").hide();
		$("#select_stooq").attr("name", "type2");
		$("#select_stooq").hide();
		$("#select_fred").attr("name", "type");
		$("#select_fred").show();
	}
	else if($(this).val() == "stooq"){
		$("#select_yahoo").attr("name", "type2");
		$("#select_yahoo").hide();
		$("#select_fred").attr("name", "type2");
		$("#select_fred").hide();
		$("#select_stooq").attr("name", "type");
		$("#select_stooq").show();
	}
	else{
		$("#select_fred").attr("name", "type2");
		$("#select_fred").hide();
		$("#select_stooq").attr("name", "type2");
		$("#select_stooq").hide();
		$("#select_yahoo").attr("name", "type");
		$("#select_yahoo").show();
	}
});
</script>
<div class="info-header">
	<h1><i class="fas fa-question-circle"></i>Statistics</h1>
	<?php  
	$num_stocks = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE type LIKE '%stock%'"));
	$num_indexes = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE type LIKE '%index%'"));
	$num_currencies = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE type = 'currency'"));
	$num_crypto_currencies = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE type = 'crypto_currency'"));
	$num_bonds = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE type LIKE '%bond%'"));
	$num_commodites = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE type LIKE '%commodity%'"));
	$num_etfs = mysqli_num_rows(mysqli_query($db, "SELECT symbol FROM items WHERE is_etf = 'yes'"));
	echo "<li>Stocks: <span style='font-weight: 400;'>{$num_stocks}</span></li>";
	echo "<li>Indexes: <span style='font-weight: 400;'>{$num_indexes}</span></li>";
	echo "<li>ETFs: <span style='font-weight: 400;'>{$num_etfs}</span></li>";
	echo "<li>Currencies: <span style='font-weight: 400;'>{$num_currencies}</span></li>";
	echo "<li>Cryptocurrencies: <span style='font-weight: 400;'>{$num_crypto_currencies}</span></li>";
	echo "<li>Commodities: <span style='font-weight: 400;'>{$num_commodites}</span></li>";
	echo "<li>Bonds: <span style='font-weight: 400;'>{$num_bonds}</span></li>";
	?>
</div>
<div></div>
<div id="resultMessage" style='display: <?php if ($resultMessage == null) { echo 'none'; } else { echo 'inline-block'; }?>;'>
	<?php echo $resultMessage; ?>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>