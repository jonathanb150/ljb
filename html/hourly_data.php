<?php 
if(isset($_POST['item'])) {
	require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php";
	require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php";
	$item = mysqli_escape_string($db, $_POST['item']);
	$query = mysqli_query($db, "SELECT hourly_data FROM items WHERE symbol = '{$item}'");
	$query = mysqli_fetch_all($query);

	if(is_array($query) && count($query) == 1) {
		if($query[0][0] == 0) {
			$hourly_table = $item."_1h";

			if(!tableExists($db, $hourly_table)) {
				mysqli_query($db, "CREATE TABLE IF NOT EXISTS `{$hourly_table}` (
					`id` int(255) NOT NULL AUTO_INCREMENT,
					`date` datetime NOT NULL,
					`open` varchar(255) NOT NULL,
					`high` varchar(255) NOT NULL,
					`low` varchar(255) NOT NULL,
					`close` varchar(255) NOT NULL,
					`volume` varchar(255) NOT NULL,
					PRIMARY KEY (`date`),
					KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;");

				mysqli_query($db, "UPDATE items SET hourly_data_table = '{$hourly_table}' WHERE symbol = '{$item}'");
			}

			if(tableExists($db, $hourly_table)) {
				if(isset($_POST['new_ticker'])) {
					mysqli_query($db, "UPDATE items SET hourly_data_ticker = '{$_POST['new_ticker']}' WHERE symbol = '{$item}'");
				}
				else {
					mysqli_query($db, "UPDATE items SET hourly_data_ticker = '{$item}' WHERE symbol = '{$item}'");
				}

				if(addHourlyData($db, (isset($_POST['new_ticker']) ? $_POST['new_ticker'] : $item), $item) == "1") {
					mysqli_query($db, "UPDATE items SET hourly_data = 1 WHERE symbol = '{$item}'");

					echo "Successfully activated hourly data for {$item}.";
				}
				else {
					echo "Unable to find the hourly data for this item in Yahoo.";
				}
			}
		}
		else {
			echo "Hourly data has already been activated for this item.";
		}
	}
	else {
		echo "Error activating hourly data for this item.";
	}

	die();
}
else if(isset($_POST['list_all'])) {
	require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php";

	$query = mysqli_query($db, "SELECT symbol FROM items WHERE hourly_data = 1");
	$query = mysqli_fetch_all($query);

	if(is_array($query) && count($query) > 0) {
		$list = "";

		for ($i=0; $i < count($query); $i++) { 
			$list .= "<li>".$query[$i][0]."</li>";
		}

		echo $list;
	}	
	else {
		echo "None yet...";
	}

	die();
}
else if(isset($_POST['delete'])) {
	require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/db.php";
	require $_SERVER['DOCUMENT_ROOT']."/php_dependancies/functions.php";

	$item = mysqli_escape_string($db, $_POST['delete']);
	$get_table = mysqli_query($db, "SELECT hourly_data_table FROM items WHERE symbol = '{$item}'");
	$get_table = mysqli_fetch_all($get_table);

	if(is_array($get_table) && count($get_table) == 1) {
		$table = $get_table[0][0];

		if(tableExists($db, $table)) {
			mysqli_query($db, "DROP TABLE `{$table}`");
		}
	}

	mysqli_query($db, "UPDATE items SET hourly_data = 0, hourly_data_table = null, hourly_data_ticker = null WHERE symbol = '{$item}'");

	echo "Deleted hourly data for {$item}.";

	die();
}
?>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<form class="analyze" onsubmit="return false;">
	<h1><i class="fas fa-search"></i>Search Item</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<div class='linearized_search_type' type='stock'>Stocks</div>
	<div class='linearized_search_type' type='index'>Indexes</div>
	<div class='linearized_search_type' type='currency'>Currencies</div>
	<div class='linearized_search_type' type='commodity'>Commodities</div>
	<div class='linearized_search_type' type='bond'>Bonds</div>
	<div class='linearized_search_type' type='us_fundamental'>Fundamentals</div>
	<div></div>
	<div id="search_results"></div>
	<button class="button" id="activate" style="margin-top: 10px;">Activate Hourly Data</button>
	<button class="button" id="list_activated" style="margin-top: 10px;">List All Activated</button>
	<button class="button" id="edit_ticker" style="margin-top: 10px;">Edit API Ticker</button>
	<button class="button" id="delete_hourly_data" style="margin-top: 10px;">Delete Hourly Data</button>
	<img src="/img/ajax-loader-3.svg" id="activate_loader" style="display: none; margin: 0px auto 30px auto">
	<p id="activate_result" style="display: none; font-size: 20px; margin: 30px auto 30px auto;"></p>
</form>
<script src="/js/item_search.js"></script>
<script type="text/javascript">
	itemSearch("statistical_backtesting", $("input[name='item_search']"), $("#search_results"));
	$(".linearized_search_type").click(function() {
		$(".linearized_search_type").removeClass("linearized_search_selected");
		$(this).addClass("linearized_search_selected");
		items = null;
		itemSearch($(this).attr("type"), $("input[name='item_search']"), $("#search_results"));
		$("#search_results").hide();
		$("#search_results").html("");
	});
</script>
<script type="text/javascript">
	$("#activate").click(function() {
		if($("input[name=selected_item]").length == 1) {
			$(".button").hide();
			$("#activate_loader").css("display", "block");
			$("#activate_result").hide();
			$.post("/hourly_data.php", { item: $("input[name=selected_item]").val() }, function(res) {
				$("#activate_result").show();
				if(res == "Unable to find the hourly data for this item in Yahoo.") {
					$("#activate_result").html(res);
					$("#activate_result").append("<br>Please enter the correct Yahoo API Ticker.<input type='text' placeholder='Ticker' id='yahoo_ticker'><button class='button' id='new_ticker'>Try Again</button>");
					$("#new_ticker").unbind("click");
					$("#new_ticker").click(function() {
						$(".button").hide();
						$("#activate_loader").css("display", "block");
						$("#activate_result").hide();
						$.post("/hourly_data.php", { item: $("input[name=selected_item]").val(), new_ticker: $("#yahoo_ticker").val().trim().toUpperCase() }, function(res) {
							$("#activate_result").show();
							$("#activate_result").html(res);
							$("#activate_loader").hide();
							$(".button").show();
						});
					});
				}
				else {
					$("#activate_result").html(res);
				}
				$("#activate_loader").hide();
				$(".button").show();
			});
		}
		else {
			alert("Please select an item.");
		}
	});

	$("#list_activated").click(function() {
		$(".button").hide();
		$("#activate_loader").css("display", "block");
		$("#activate_result").hide();
		$.post("/hourly_data.php", { list_all: true }, function(res) {
			$("#activate_result").show();
			$("#activate_result").html(res);
			$("#activate_loader").hide();
			$(".button").show();
		});
	});

	$("#edit_ticker").click(function() {
		if($("input[name=selected_item]").length == 1) {
			$(".button").hide();
			$("#activate_loader").css("display", "block");
			$("#activate_result").hide();
			$.post("/hourly_data.php", { delete: $("input[name=selected_item]").val() }, function(res) {
				$("#activate_result").show();
				$("#activate_loader").hide();
				$(".button").show();
				$("#activate_result").html("<br>Please enter the new Yahoo API Ticker.<input type='text' placeholder='Ticker' id='yahoo_ticker'><button class='button' id='new_ticker'>Submit</button>");
				$("#new_ticker").unbind("click");
				$("#new_ticker").click(function() {
					$(".button").hide();
					$("#activate_loader").css("display", "block");
					$("#activate_result").hide();
					$.post("/hourly_data.php", { item: $("input[name=selected_item]").val(), new_ticker: $("#yahoo_ticker").val().trim().toUpperCase() }, function(res) {
						$("#activate_result").show();
						$("#activate_result").html(res);
						$("#activate_loader").hide();
						$(".button").show();
					});
				});
			});
		}
		else {
			alert("Please select an item.");
		}
	});

	$("#delete_hourly_data").click(function() {
		if($("input[name=selected_item]").length == 1) {
			$(".button").hide();
			$("#activate_loader").css("display", "block");
			$("#activate_result").hide();
			$.post("/hourly_data.php", { delete: $("input[name=selected_item]").val() }, function(res) {
				$("#activate_result").show();
				$("#activate_result").html(res);
				$("#activate_loader").hide();
				$(".button").show();
			});
		}
		else {
			alert("Please select an item.");
		}
	});
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>