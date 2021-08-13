<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php 
if(isset($_POST['LJBScore']) && isset($_POST['portfolio'])) {
	$_SESSION['portfolio'] = $_POST['portfolio'];

	if($_POST['LJBScore'] == "auto") {
		$last_ljbs = mysqli_query($db, "SELECT ljb_score FROM `{$_SESSION['user']}_portfolio` WHERE item = '{$_SESSION['portfolio']['Name']}' ORDER BY date_added DESC LIMIT 1");
		confirmQuery($last_ljbs);

		if($row = mysqli_fetch_assoc($last_ljbs)){
			if(!empty($row['ljb_score']) && is_numeric($row['ljb_score'])) {
				$_POST['LJBScore'] = $row['ljb_score'];
			}
			else{
				$_POST['LJBScore'] = "N/A";
			}
		}
	}
}
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("table input").keypress(function(key){
			if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
				return false;
			}
		});
		$("input[name='allocated-shares']").keyup(function() {
			var allocated_shares = parseFloat($(this).val());
			var buying_price = parseFloat($("input[name='buying-price']").val());

			if(allocated_shares != null && buying_price != null && $.isNumeric(allocated_shares) && $.isNumeric(buying_price) && allocated_shares > 0 && buying_price > 0){
				$("input[name='allocated-capital']").val(allocated_shares*buying_price);
			}
		});
	});
</script>
<?php
if (isset($_POST['cancel'])) {
	unset($_SESSION['portfolio']);
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
else if (isset($_POST['add']) && isset($_POST['allocated-capital']) && isset($_POST['buying-price']) && isset($_POST['selling-price']) && isset($_POST['target-price']) && isset($_SESSION['portfolio']) && isset($_POST['LJBScore'])
	&& (float)getUserBalance($db, $_SESSION['user']) >= (float)$_POST['allocated-capital']) {
	
	$item = mysqli_escape_string($db, $_SESSION['portfolio']['Name']);
	$buying_price = mysqli_escape_string($db, $_POST['buying-price']);
    $target_price = mysqli_escape_string($db, $_POST['target-price']);
    $selling_price = mysqli_escape_string($db, $_POST['selling-price']);
	$allocated_capital = mysqli_escape_string($db, $_POST['allocated-capital']);
	$ljb_score = mysqli_escape_string($db, $_POST['LJBScore']);
	$position_note = mysqli_escape_string($db, $_POST['position_note']);
	$portfolio_table = $_SESSION['user']."_portfolio";
	$duration = mysqli_escape_string($db, $_POST['duration']);

	if(!isset($_POST['tags'])){
		$_POST['tags'] = [];
	}

	$_POST['tags'] = json_encode($_POST['tags']);
	$_POST['tags'] = mysqli_escape_string($db, $_POST['tags']);

	$add_to_portfolio = mysqli_query($db, "INSERT INTO `{$portfolio_table}` (item, bought_price, target_price, selling_price, allocated_capital, ljb_score, date_added, tags, note, term) VALUES ('{$item}', '{$buying_price}', '{$target_price}', '{$selling_price}', '{$allocated_capital}', '{$ljb_score}', NOW(), '{$_POST['tags']}', '{$position_note}', '{$duration}')");
	confirmQuery($add_to_portfolio);
	editPortfolio($db, $_SESSION['user']);
	unset($_SESSION['portfolio']);
	deleteFromAllWatchlists($db, $_SESSION['user'], $item);
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
else if (isset($_SESSION['portfolio']) && isset($_POST['LJBScore'])) {
	if (isset($_POST['allocated-capital']) && (float)getUserBalance($db, $_SESSION['user']) < (float)$_POST['allocated-capital']) {
		echo '<div style="text-align: center;"><div style="display: inline-block; padding: 10px; font-weight: 700; font-size: 18px; background: #FF5C5C; margin-bottom: 20px; color: white;">Not enough cash! Please adjust the allocated capital.</div></div>';
	}
	echo
	"<form method='POST' action='/add_to_portfolio.php' class='main-form'>".
		"<h1><i class='fas fa-plus' style='margin-right: 10px;'></i>Add to Porfolio</h1>".
		"<table id='add-to-portfolio'>".
			"<thead>".
				"<th>Item</th>".
				"<th>Buying<br>Price</th>".
				"<th>Target<br>Price</th>".
				"<th>Selling<br>Price</th>".
				"<th>LJB Score</th>".
				"<th>Allocated<br>Shares</th>".
				"<th>Allocated<br>Capital</th>".
				"<th>Duration</th>".
			"</thead>".
			"<tbody>".
				"<tr>".
					"<td>{$_SESSION['portfolio']['Name']}</td>".
					(isset($_POST['review']) ? "<td>{$_POST['buying-price']}<input style='display: none;' name='buying-price' value='{$_POST['buying-price']}'></td>" : "<td><input type='text' name='buying-price' value='".(number_format((float)getCurrentPrice($_SESSION['portfolio']['Name']), 2, '.', ''))."'></td>").
					(isset($_POST['review']) ? "<td>{$_POST['target-price']}<input style='display: none;' name='target-price' value='{$_POST['target-price']}'></td>" : "<td><input type='text' name='target-price' value='".(round($_SESSION['portfolio'][0], 2))."'></td>").
					(isset($_POST['review']) ? "<td>{$_POST['selling-price']}<input style='display: none;' name='selling-price' value='{$_POST['selling-price']}'></td>" : "<td><input type='text' name='selling-price' value='".round(((float)getCurrentPrice($_SESSION['portfolio']['Name']))*0.85, 2)."'></td>").
					"<td><input style='display: none;' name='LJBScore' value='".($_POST['LJBScore']=='N/A' ? 'N/A' : round($_POST['LJBScore'], 2))."'>".($_POST['LJBScore']=='N/A' ? 'N/A' : round($_POST['LJBScore'], 2))."</td>".
					(isset($_POST['review']) ? "<td>".$_POST['allocated-shares']."<input style='display: none;' name='allocated-shares' value='{$_POST['allocated-shares']}'></td>" : "<td><input type='text' name='allocated-shares' autofocus></td>").
					(isset($_POST['review']) ? "<td><b>$</b> ".number_format((float)$_POST['allocated-capital'], 2, '.', ',')."<input style='display: none;' name='allocated-capital' value='{$_POST['allocated-capital']}'></td>" : "<td><input type='text' style='border: none; background: white; cursor: default;' name='allocated-capital' readonly></td>").
					(isset($_POST['review']) ? "<td><input type='hidden' value='{$_POST['duration']}' name='duration'>{$_POST['duration']}</td>" : "<td><select name='duration'><option value='Short Term'>Short Term</option><option value='Long Term'>Long Term</option></select></td>").
				"</tr>".
			"</tbody>".
		"</table>".
		(isset($_POST['review']) ? "<textarea readonly name='position_note' style='display: block; text-align: center; margin: 20px auto 0px auto; height: 200px; overflow: auto; padding: 10px; border: 1px solid #d8d8d8; width: 600px; max-height: 200px; max-width: 600px'>{$_POST['position_note']}</textarea>" : "<textarea name='position_note' style='display: block; text-align: center; margin: 20px auto 0px auto; height: 200px; overflow: auto; padding: 10px; border: 1px solid #d8d8d8; width: 600px; max-height: 200px; max-width: 600px' placeholder='Write any notes here.'>".(isset($_POST['position_note']) ? $_POST['position_note'] : "")."</textarea>").
		(isset($_POST['review']) ? "<button class='add-to' id='set_tags' style='margin-bottom: 0px;' onclick='return false;'><i class='fas fa-tags' style='margin-right: 7px;'></i>SET TAGS</button><button class='add-to' name='add' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-clipboard-check' style='margin-right: 7px;'></i>ADD TO PORTFOLIO</button><button class='add-to' name='edit' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-edit' style='margin-right: 7px;'></i>EDIT</button><button class='add-to' name='cancel' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-ban' style='margin-right: 7px;'></i>CANCEL</button>" : "<button class='add-to' name='review' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fas fa-search' style='margin-right: 7px;'></i>REVIEW</button><button class='add-to' name='cancel' id='add-to-portfolio' style='margin-bottom: 0px;'><i class='fas fa-ban' style='margin-right: 7px;'></i>CANCEL</button>");
		
		if(isset($_SESSION['portfolio']['Name'])){
			$get_tags = mysqli_query($db, "SELECT tags FROM items WHERE symbol = '{$_SESSION['portfolio']['Name']}'");
			confirmQuery($get_tags);

			if($row = mysqli_fetch_assoc($get_tags)){
				$tags_array = json_decode($row['tags']);

				for ($i=0; $i < count($tags_array); $i++) { 
					echo "<input type='hidden' name='tags[]' value='{$tags_array[$i]}'>";
				}
			}
		}			
		echo "</form>";
} 
else {
	unset($_SESSION['portfolio']);
	echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
?>
<div id="tags_super_container" class='super-container' style='display: none; width: 90%; margin: 20px auto; text-align: center'>
	<h1 class="super-container-header">Tags</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav style='display: none;'>
			<h1 class='general-container-selected'></h1>
		</nav>
		<div id="tags_container" style="width: 100%;" class='general-container-content'>
			<div>
				<h3>Tags</h3>
				<ul id="tags_list">
					<?php 
					$tags_table = $_SESSION['user']."_tags";

					if(tableExists($db, $tags_table)){
						$get_tags = mysqli_query($db, "SELECT tag FROM `{$tags_table}`");
						confirmQuery($get_tags);

						while($row = mysqli_fetch_assoc($get_tags)){
							echo "<li><span onclick='addTag(this, \"add\");'>{$row['tag']}</span><span onclick='removeTag(this,\"{$row['tag']}\")'>x</span></li>";
						}
					}
					?>
				</ul>
				<input type="text" class="input" style="font-size: 18px; margin: 15px auto 10px auto;">
				<button class="button" id="add_tag">Add Tag</button>
			</div>
			<div>
				<h3>Selected Tags</h3>
				<ul id="selected_tags">
					<?php 
						if(isset($_SESSION['portfolio']['Name'])){
							$get_tags = mysqli_query($db, "SELECT tags FROM items WHERE symbol = '{$_SESSION['portfolio']['Name']}'");
							confirmQuery($get_tags);

							if($row = mysqli_fetch_assoc($get_tags)){
								$tags_array = json_decode($row['tags']);

								for ($i=0; $i < count($tags_array); $i++) { 
									echo "<li><span onclick='addTag(this, \"remove\");'>{$tags_array[$i]}</span></li>";
								}
							}
						}
					?>
				</ul>
			</div>
			<div id="tags_match_table" style="width: 80%;"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var tags_modal;

	$("#add_tag").click(function() {
		var tag = $(this).prev("input").val().trim();

		if(tag.length > 0){
			$(this).parent().find("ul").prepend("<li><span onclick='addTag(this, \"add\");'>"+tag+"</span><span onclick='removeTag(this,\""+tag+"\")'>x</span></li>");

			$.post("/php_dependancies/tags_actions.php", {add_tag: tag}, function(data){});
		}
		else{
			alert("Please input the tag.");
		}
	});

	$("#set_tags").click(function() {
		tags_modal = new jBox('Modal', {
			content: $("#tags_super_container"),
			width: 1100
		});
		tags_modal.open();
	});

	$("li span").hover(function() {
		if($(this).parent().find("span").length == 2 && $(this).index() == 1){
			$(this).prev("span").css("background", "#c54242");
		}
	}, function() {
		if($(this).parent().find("span").length == 2 && $(this).index() == 1){
			$(this).prev("span").css("background", "gray");
		}
	});

	function removeTag(element, tag) {
		$(element).parent().fadeOut(250, function() {
			$(element).parent().remove();
		});
		$.post("/php_dependancies/tags_actions.php", {delete_tag: tag}, function(data){
			$("#tags_match_table").html(data);
			tags_modal.position();
		});
	}

	function addTag(element, action) {
		if(action == "add" && !verifySelectedTag($(element).text().trim())){
			$("#selected_tags").prepend("<li><span onclick='addTag(this, \"remove\")'>"+$(element).text().trim()+"</span></li>");
			$("form").append("<input name='tags[]' type='hidden' value='"+$(element).text().trim()+"'>");
		}
		else{
			if(!verifyTag($(element).text().trim())){
				$("#tags_list").prepend("<li><span onclick='addTag(this, \"add\")'>"+$(element).text().trim()+"</span><span onclick='removeTag(this,\'"+$(element).text().trim()+"\')'>x</span></li>");
			}
			$("form").find("input[value='"+$(element).text().trim()+"']").remove();
		}
		$(element).parent().remove();

		var current_tags = [];
		for (var i = 0; i < $("#selected_tags li").length; i++) {
			current_tags.push($("#selected_tags li:eq("+i+")").text().trim()); 
		}
		current_tags = JSON.stringify(current_tags);

		console.log(current_tags);
		$.post("/php_dependancies/tags_actions.php", {current_tags: current_tags}, function(data){
			console.log(data);
			$("#tags_match_table").html(data);
			tags_modal.position();
		});
	}

	var current_tags = [];
	for (var i = 0; i < $("#selected_tags li").length; i++) {
		current_tags.push($("#selected_tags li:eq("+i+")").text().trim()); 
	}
	current_tags = JSON.stringify(current_tags);

	$.post("/php_dependancies/tags_actions.php", {current_tags: current_tags}, function(data){
		$("#tags_match_table").html(data);
		if(tags_modal != null){
			tags_modal.position();
		}
	});

	function verifyTag(tag){
		for (var i = 0; i < $("#tags_list li").length; i++) {
			if($("#tags_list li:eq("+i+")").find("span:eq(0)").text().trim() == tag){
				return true;
			} 
		}

		return false;
	}

	function verifySelectedTag(tag){
		for (var i = 0; i < $("#selected_tags li").length; i++) {
			if($("#selected_tags li:eq("+i+")").find("span:eq(0)").text().trim() == tag){
				return true;
			} 
		}

		return false;
	}
</script>
<div class='super-container' style='width: 40%; margin-top: 20px;'>
	<h1 class="super-container-header">Account Balance</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav style='display: none;'>
			<h1 class='general-container-selected'><i class="fas fa-dollar-sign"></i>Current Balance</h1>
		</nav>
		<div class='general-container-content'>
			<?php
				$balance = number_format(getUserBalance($db, $_SESSION['user']), 2, ".", ",");
			?>
			<input class='accountBalance' type="text" readonly value="$ <?php echo $balance; ?>">
		</div>
	</div>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>