<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script type="text/javascript">
	var selectedStock;
	var selectedPositionId;
</script>
<script type="text/javascript" src="/js/watchlist.js"></script>
<script src="https://cdn.quilljs.com/1.1.9/quill.js"></script>
<link href="https://cdn.quilljs.com/1.1.9/quill.snow.css" rel="stylesheet">
<div class="super-container" style = "width: 100%; margin: 0 auto 20px auto;">
	<h1 class="super-container-header">Account Balance</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%;'>
		<nav>
			<?php 
			if(tableExists($db, $_SESSION['user']."_portfolio")){
				$get_portfolio_items = mysqli_query($db, "SELECT `item` FROM `{$_SESSION['user']}_portfolio` WHERE status = 'open' GROUP BY `item` ORDER BY `item`");
				confirmQuery($get_portfolio_items);

				$get_portfolio_items = mysqli_fetch_all($get_portfolio_items);
			}
			?>
			<h1 class='general-container-selected'><i class="fas fa-dollar-sign"></i>Current Balance</h1>
			<h1><i class="fas fa-users"></i>Customers</h1>
			<h1><i class="fas fa-plus" ></i>Dividends</h1>
			<h1  <?php if(!isset($get_portfolio_items) || count($get_portfolio_items) == 0){ echo "style='display: none;'"; } ?>><i class="fas fa-minus"></i>Fees</h1>
		</nav>
		<div class='general-container-content'>
			<?php
			$account_balance = number_format(getUserTotalBalance($db, $_SESSION['user']), 2, ".", ",");
			$cash_balance = number_format(getUserBalance($db, $_SESSION['user']), 2, ".", ",");
			$invested_capital = number_format(getUserTotalBalance($db, $_SESSION['user'])-getUserBalance($db, $_SESSION['user']), 2, ".", ",");
			?>
			<div class="balance_container"><div>Total Balance</div><input class='accountBalance' type="text" readonly value="$ <?php echo $account_balance; ?>"></div
			><div class="balance_container"><div>Invested Capital</div><input class='accountBalance' type="text" readonly value="$ <?php echo $invested_capital; ?>"></div
			><div class="balance_container"><div>Cash</div><input class='accountBalance' type="text" readonly value="$ <?php echo $cash_balance; ?>"></div>
		</div>
		<div class="general-container-content">
			<?php 
				$get_clients = mysqli_query($db2, "SELECT balance, name, last_name, uid FROM users");
				confirmQuery($get_clients);
				$get_clients = mysqli_fetch_all($get_clients);
				echo
				'<table class="dataTable">
					<thead>
						<th>UID</th>
						<th>Name</th>
						<th>Cash</th>
					</thead>
					<tbody>';
				for ($i=0; $i < count($get_clients); $i++) { 
					echo "<tr><td>".$get_clients[$i][3]."</td>";
					echo "<td>".$get_clients[$i][1]." ".$get_clients[$i][2]."</td>";
					echo "<td><b>$ ".number_format($get_clients[$i][0], 2, ".",",")."</b></td></tr>";
				}
				echo
				'
					</tbody>
				</table>';
			?>
		</div>
		<div class='general-container-content'>
			<input type="number" step='any' placeholder='Amount...' style='margin: 30px;'>
			<?php
			echo "<select id='select_fee_dividend' style='display: block; margin: 0 auto 30px auto; padding: 5px; font-weight: 300; font-size: 20px; border: 1px solid #d6d8da;'>";
			echo "<option value='cash'>Cash</option>";
			if(isset($get_portfolio_items) && is_array($get_portfolio_items) && count($get_portfolio_items) > 0){
				for ($i=0; $i < count($get_portfolio_items); $i++) { 
					echo "<option value='".$get_portfolio_items[$i][0]."'>".$get_portfolio_items[$i][0]."</option>";
				}
			}
			echo "</select>";
			?>
			<button class='button' id='dividend_operation' style='margin-bottom: 20px;'>Submit</button>
			<h3>Dividends History</h3>
			<table class="dataTable">
				<thead>
					<th>Item</th>
					<th>Amount</th>
					<th>Date</th>
				</thead>
				<tbody>
					<?php 
					if(tableExists($db, $_SESSION['user']."_dividends")){
						$get_dividends_history = mysqli_query($db, "SELECT item, amount, date FROM `{$_SESSION['user']}_dividends` ORDER BY id DESC");
						confirmQuery($get_dividends_history);

						$get_dividends_history = mysqli_fetch_all($get_dividends_history);

						$total_dividends = 0;

						for ($i=0; $i < count($get_dividends_history); $i++) { 
							echo "<tr><td>{$get_dividends_history[$i][0]}</td><td>{$get_dividends_history[$i][1]}</td><td>{$get_dividends_history[$i][2]}</td></tr>";
							$total_dividends += (float) $get_dividends_history[$i][1];
						}
					}
					?>
				</tbody>
			</table>
			<?php 
				echo "<p>Total Dividends: <span style='font-weight: 700'>$".round($total_dividends, 2)."</span></p>";
			?>
		</div>
		<div class='general-container-content'>
			<input type="number" step='any' placeholder='Amount...' style='margin: 30px;'>
			<?php
			if(isset($get_portfolio_items) && is_array($get_portfolio_items) && count($get_portfolio_items) > 0){
				echo "<select id='select_fee_dividend' style='display: block; margin: 0 auto 30px auto; padding: 5px; font-weight: 300; font-size: 20px; border: 1px solid #d6d8da;'>";
				for ($i=0; $i < count($get_portfolio_items); $i++) { 
					echo "<option value='".$get_portfolio_items[$i][0]."'>".$get_portfolio_items[$i][0]."</option>";
				}
				echo "</select>";
			}
			?>
			<button class='button' id='fee_operation' style='margin-bottom: 20px;'>Submit</button>
			<h3>Fees History</h3>
			<table class="dataTable">
				<thead>
					<th>Item</th>
					<th>Amount</th>
					<th>Date</th>
				</thead>
				<tbody>
					<?php 
					if(tableExists($db, $_SESSION['user']."_fees")){
						$get_fees_history = mysqli_query($db, "SELECT item, amount, date FROM `{$_SESSION['user']}_fees` ORDER BY id DESC");
						confirmQuery($get_fees_history);

						$get_fees_history = mysqli_fetch_all($get_fees_history);

						$total_fees = 0;

						for ($i=0; $i < count($get_fees_history); $i++) { 
							echo "<tr><td>{$get_fees_history[$i][0]}</td><td>{$get_fees_history[$i][1]}</td><td>{$get_fees_history[$i][2]}</td></tr>";
							$total_fees += (float) $get_fees_history[$i][1];
						}
					}
					?>
				</tbody>
			</table>
			<?php 
				echo "<p>Total Fees: <span style='font-weight:700'>$".round($total_fees, 2)."</span></p>";
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
	$("#fee_operation, #dividend_operation").click(function(){
		var item = $(this).prev("select").find(":selected").text();
		var fee_dividend = $(this).parent().find("input").val();
		var amount_type = ($(this).attr("id") == "fee_operation" ? "fee" : "dividend");
		var this_button = $(this);

		$(this).after("<img src='/img/ajax-loader-3.svg'>");
		$(this).hide();

		if(fee_dividend != null && $.isNumeric(fee_dividend) && fee_dividend > 0){
			if(amount_type == "dividend"){
				$.post("/php_dependancies/fees_dividends.php", {item: item, dividend: fee_dividend}, function(data){
					$(this_button).show();
					$(this_button).next("img").remove();
					if(data != null && data == "1"){
						new jBox('Notice', {
				    	content: "Dividend added successfully.",
				    	color: "blue"
						});
					}
					else{
						alert("Error.");
					}
				});
			}
			else{
				$.post("/php_dependancies/fees_dividends.php", {item: item, fee: fee_dividend}, function(data){
					$(this_button).show();
					$(this_button).next("img").remove();
					if(data != null && data == "1"){
						new jBox('Notice', {
				    	content: "Fee added successfully.",
				    	color: "blue"
						});
					}
					else{
						alert("Error.");
					}
				});
			}
		}
		else{
			alert("Enter a valid amount.");
		}
	});
</script>
<div class="super-container" style = "width: 100%; margin: 0 auto;">
	<h1 class="super-container-header">Portfolio</h1>
	<div></div>
	<div class = "general-container" style = "margin-top: 0; width: 100%;" id = "portfolio_container">
		<nav>
			<h1 class = "general-container-selected">Short Term</h1>
			<h1>Long Term</h1>
		</nav>
		<div class="general-container-content">
			<img id='get_portfolio_loader_short' src="/img/ajax-loader-2.gif" style='margin: 15px 0;'>
		</div>
		<div class="general-container-content">
			<img id='get_portfolio_loader_long' src="/img/ajax-loader-2.gif" style='margin: 15px 0;'>
		</div>
	</div>
</div>
<div class="super-container" style = "width: 100%; margin: 0 auto;">
	<h1 class="super-container-header">Portfolio Stats</h1>
	<div></div>
	<div class = "general-container" style = "margin-top: 0; width: 100%;" id = "portfolio_stats_container">
		<nav style="display: none;">
			<h1 class = "general-container-selected">Short Term</h1>
			<h1>Long Term</h1>
		</nav>
		<div class="general-container-content">
			<img id='get_portfolio_loader_stats' src="/img/ajax-loader-2.gif" style='margin: 15px 0;'>
		</div>
	</div>
</div>
<script type="text/javascript">
	function refreshDataTable(table) {
		$(table).addClass("display");
		$(table).addClass("hover");
		$(table).css("width", "100%");
		$(table).wrap("<div style='text-align: center;' class='tableContainer'><div style='display: inline-block; width: 95%; margin: 0 auto;'></div></div>")
		$(table).each(function() {
			if ($(this).hasClass("dataTableDesc")) {
				$(this).DataTable({order: [[0, 'desc']]});
			} else if ($(this).hasClass("noSort")) {
				$(this).DataTable({"order": []});
			} else if ($(this).hasClass("smallTable")) {	
				$(this).DataTable({"lengthMenu": [5, 10]});
			} else {
				$(this).DataTable();
			}
		});
	}
	$.get("/php_dependancies/get_portfolio.php", {type: "Short Term"}, function(data) {
		$("#portfolio_container .general-container-content:eq(0)").append(data);
		refreshDataTable($("#portfolio_container .general-container-content:eq(0)").find('table').get(0));
		$("#get_portfolio_loader_short").remove();
	});
	$.get("/php_dependancies/get_portfolio.php", {type: "Long Term"}, function(data) {
		$("#portfolio_container .general-container-content:eq(1)").append(data);
		refreshDataTable($("#portfolio_container .general-container-content:eq(1)").find('table').get(0));
		$("#get_portfolio_loader_long").remove();
	});
	$.get("/php_dependancies/get_portfolio.php", function(data) {
		$("#portfolio_stats_container").append(data);
		$("#get_portfolio_loader_stats").remove();
	});
</script>
<div class="super-container" style = "width: 100%; margin: 0 auto;">
	<h1 class="super-container-header">Portfolio History</h1>
	<div></div>
	<div class = "general-container" style = "margin-top: 0; width: 100%;" id = "portfolio_history_container">
		<nav style="display:none">
			<h1 class = "general-container-selected"><i class='fas fa-clipboard-list'></i>Portfolio</h1>
		</nav>
		<img id='get_portfolio_history_loader' src="/img/ajax-loader-2.gif" style='margin: 15px 0;'>
	</div>
	<script type="text/javascript">
		$.get("/php_dependancies/get_portfolio_history.php", function(data) {
			if(data != null && data.length > 0){
				$("#portfolio_history_container").append(data);
				if ($("#portfolio_history").length == 1) {
					refreshDataTable($("#portfolio_history").get(0));
				}
			}
			else{
				$("#portfolio_history_container").append("<p style='font-style:italic; margin: 20px 0; font-size:16px; font-weight: 300'>Nothing here...</p>");
			}
			$("#get_portfolio_history_loader").remove();
		});
	</script>
</div>
<div class="super-container" id="notes_container" style='text-align: center; width: 100%; margin-top: 10px; display: none;'>
	<h1 class="super-container-header">Notes</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%;'>
		<nav>
			<h1 id='note_list_header' class='general-container-selected' onclick="getNotes('item', this)"><i class="fas fa-sticky-note"></i>Notes</h1>
			<h1 id='note_add_header'><i class="fas fa-plus"></i>Add</h1>
			<h1 style='display: none;' id='note_edit_header'><i class="fas fa-pen-square"></i>Edit</h1>
			<h1 id='attachments_header' onclick="getAttachments(this)"><i class="fas fa-file-alt"></i>Attachments</h1>
			<h1 id='tags_header' onclick="getTags(this)"><i class="fas fa-tags"></i>Tags</h1>
			<h1 id='position_note_header' onclick="getPositionNote(this)"><i class="fas fa-sticky-note"></i>Position Note</h1>
		</nav>
		<div class='general-container-content' style='width: 80%'>
		</div>
		<div class='general-container-content' style='width: 80%;'>
			<div class="add-note" style='height: 500px; margin-bottom: 15px; font-size: 18px;'></div>
			<input type="text" class="set_note_title" placeholder='Title...'>
			<button class="button" id='add_note'>Add Note</button>
		</div>
		<div class='general-container-content' style='width: 80%;'>
			<div class="note_editor" style='height: 500px; margin-bottom: 15px; font-size: 18px;'></div>
			<input type="text" class="set_note_title" placeholder='Title...'>
			<button class="button" id='edit_note'>Confirm</button>
		</div>
		<div class='general-container-content' style='width: 80%'>
			<input type="file" id="note_attachment">
			<button class="button" id='attachment_upload' style = "margin: 15px auto; display: block;">Upload</button>
		</div>
		<div class='general-container-content' style='width: 80%'>

		</div>
		<div class='general-container-content' style='width: 80%'>

		</div>
	</div>
</div>
<script type="text/javascript">
	function deleteAttachment(path){
		$.post("/php_dependancies/user_attachments.php", {delete_path: path}, function (data){
			$("#attachments_header").trigger("click");
		});
	}

	function getAttachments(element){
		var tabHeaderIndex = $(element).index();
		var tabContent = $(element).parent().parent().children("div:eq("+tabHeaderIndex+")");
		tabContent.find(".attachment_container").remove();
		tabContent.find("#no_attachments").remove();
		tabContent.append("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='attachments_loading'>");
		$.post("/php_dependancies/user_attachments.php", {item: selectedStock}, function(data) {
			if (data != null && data.length > 0) {
				var attachments = $.parseJSON(data);
				if (attachments.document_name != null && attachments.document_name.length > 0) {
					for (var i = 0; i < attachments.document_name.length; i++) {
						tabContent.append('<div class="attachment_container '+attachments.document_type[i]+'"><div class="attachment_type"><i class="fas fa-file-'+attachments.document_type[i]+'"></i></div><div class="attachment_name">'+attachments.document_name[i]+'</div><a target="_blank" href="'+attachments.path[i]+'"><div class="attachment_download"><i class="fas fa-download"></i></div></a><div class="attachment_delete" onclick="deleteAttachment(\''+attachments.path[i]+'\')"><i class="fas fa-trash"></i></div></div>');
					}
				} else {
					tabContent.append("<span id='no_attachments' style='font-style: italic;'>Nothing here...</span>");
				}
			} else {
				tabContent.append("<span id='no_attachments' style='font-style: italic;'>Nothing here...</span>");
			}
			$("#attachments_loading").remove();
		});		
	}

	function getTags(element){
		var tabHeaderIndex = $(element).index();
		var tabContent = $(element).parent().parent().children("div:eq("+tabHeaderIndex+")");
		tabContent.html("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='tags_loading'>");
		$.post("/php_dependancies/tags_actions.php", {get_item_tags: selectedPositionId}, function(data) {
			if (data != null && data.length > 0) {
				tabContent.html(data);
			} else {
				tabContent.html("<span id='no_attachments' style='font-style: italic;'>Nothing here...</span>");
			}
			$("#tags_loading").remove();
		});		
	}

	function getPositionNote(element){
		var tabHeaderIndex = $(element).index();
		var tabContent = $(element).parent().parent().children("div:eq("+tabHeaderIndex+")");
		tabContent.html("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='position_note_loading'>");
		$.post("/php_dependancies/get_position_note.php", {get_position_note: selectedPositionId}, function(data) {
			if (data != null && data.length > 0) {
				tabContent.html(data);
			} else {
				tabContent.html("<span id='no_attachments' style='font-style: italic;'>Nothing here...</span>");
			}
			$("#position_note_loading").remove();
		});		
	}


	$('#attachment_upload').on('click', function() {
		$(this).hide();
		$(this).after("<img id='attachment_loading' style = 'display: block; margin: 5px auto 0 auto;' src='/img/ajax-loader-2.gif'>");
		var file_data = $('#note_attachment').prop('files')[0];   
		var form_data = new FormData();                  
		form_data.append('file', file_data);
		form_data.append("item", $(this).attr("item"));              
		$.ajax({
        	url: "/php_dependancies/attachment_upload.php",
        	dataType: 'text',
        	cache: false,
        	contentType: false,
        	processData: false,
        	data: form_data,                         
        	type: 'post',
        	success: function(php_script_response){
        		if(php_script_response == "Success"){
        			$("#note_attachment").val("");
        			$("#attachments_header").trigger("click");
        		}
        		else{
        			alert(php_script_response);
        		}
        		$("#attachment_upload").show();
        		$("#attachment_loading").remove();
        	}
    	});
	});
	function editTargetPrice(id, item){
		var user_input = window.prompt("Please enter the desired target price. Enter -1 to disable.");
		user_input = user_input.trim();
		if(id != null && id > 0 && item != null && item.length > 0){
			if(!$.isNumeric(user_input)){
				alert("Your input must be a number.");
				return false;
			}
			else if(user_input == -1){
				user_input = '';
			}
			$.get("/watchlist_actions.php", {watchlist_id: id, watchlist_item: item, target_price: user_input}, function(data) {
				console.log(data);
				if(data == "1"){
					window.location.href = window.location.href;
				}
				else{
					alert("Error");
				}
			});
		}
		else{
			alert("Error");
		}
	}
</script>
<script>
	var editorCounter = 0;
	var toolbarOptions = [
	  ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
	  ['blockquote', 'code-block'],

	  [{ 'header': 1 }, { 'header': 2 }],               // custom button values
	  [{ 'list': 'ordered'}, { 'list': 'bullet' }],
	  [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
	  [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
	  [{ 'direction': 'rtl' }],                         // text direction

	  [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
	  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

	  [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
	  [{ 'align': [] }],

	  ['clean'],                                         // remove formatting button
	  ['link', 'image']
	];
	var noteAction = 0;
	function generateEditors() {
		$(".editor").each(function() {
			$(this).addClass("editor"+editorCounter);
			if ($(this).hasClass("note_content")) {
				new Quill((".editor"+editorCounter), {
					theme: 'snow',
					readOnly: true,
					modules: {
						toolbar: false
					}
				});
			} else {
				new Quill((".editor"+editorCounter), {
					theme: 'snow',
					modules: {
						toolbar: toolbarOptions
					},
					placeholder: 'Note...'
				});
			}
			editorCounter++;
		});
	}
	function notesToHTML(notes) {
			var HTMLNotes = "";
			for (var i = 0; i < notes.length; i++) {
				var title = notes[i]['title'].trim().length > 0 ? notes[i]['title'] : $(notes[i]['note']).text();
				if (title == null || title.trim().length <= 0) {
					title = "New Note";
				}
				HTMLNotes += '<div class="note_container" note_id="'+notes[i]['id']+'">';
				HTMLNotes += '<div class="note_header">';
				HTMLNotes += '<div class="note_title"><div>'+title+'</div></div>';
				HTMLNotes += '<div class="note_date">'+notes[i]['date']+'</div>';
				HTMLNotes += '<div class="note_delete" onclick="deleteNote('+notes[i]['id']+')" data-confirm="Do you really want to delete this note?"><i class="fas fa-trash" note_id="'+notes[i]['id']+'"></i></div>';
				HTMLNotes += '<div class="note_edit" onclick="editNote(this)"><i class="fas fa-pen-square" note_id="'+notes[i]['id']+'"></i></div>';			
				HTMLNotes += '<div class="note_expand" onclick="expandNote(this)"><i class="fas fa-plus"></i></div>';
				HTMLNotes += '</div>';
				HTMLNotes += '<div class="editor note_content">'+notes[i]['note']+'</div>';
				HTMLNotes += '</div>';
			}
			return HTMLNotes;
		}
	function getNotes(type, element) {
		if (noteAction == 0) {
			noteAction = 1;
			$(".set_note_title").val("");
			var tabHeaderIndex = $(element).index();
			var tabContent = $(element).parent().parent().children("div:eq("+tabHeaderIndex+")");
			tabContent.html("<img src='img/ajax-loader-2.gif' style='height: 50px; position: relative; top: 2px;' id='notes_loading'>");
			$.post("/php_dependancies/get_notes.php", {type: "item", item: selectedStock}, function(data) {
				if (data != null && data.length > 0) {
					var notes = $.parseJSON(data);
					if (notes.length > 0) {
						tabContent.html(notesToHTML(notes));
						generateEditors();
						new jBox('Confirm', {
						    confirmButton: 'Yes',
						    cancelButton: 'No'
						});
					} else {
						tabContent.html("<span style='font-style: italic;'>Nothing here...</span>");
					}
				} else {
					alert("There was an issue fetching your notes!");
				}
				$("#notes_loading").remove();
				noteAction = 0;
			});
		}
	}
	function expandNote(element) {
		$(element).parent().next(".note_content").slideFadeToggle(250);
		if ($(element).html().indexOf("plus") > 0) {
			$(element).html($(element).html().replace("plus", "minus"));
		} else {
			$(element).html($(element).html().replace("minus", "plus"));
		}
	}
	function editNote(element) {
		var noteContent = $(element).parent().next(".note_content").find(".ql-editor").html();
		var note_id = $(element).find("i").attr("note_id");
		$("#note_edit_header").show();
		$("#note_edit_header").trigger("click");
		$("#edit_note").attr("note_id", note_id);
		$(".note_editor .ql-editor").html(noteContent);
	}
	function deleteNote(note_id) {
		$.post("/php_dependancies/delete_item_note.php", {note_id: note_id}, function(data) {
			if (data == "success") {
				$(".note_container[note_id="+note_id+"]").slideFadeToggle(500, function() {
					$(".note_container[note_id="+note_id+"]").remove();
				});
			} else {
				alert("Error!");
				console.log(data);
			}
		});
	}
	function showNotes(item) {
		$("#notes_container .super-container-header").html(item + " Notes");
		$("#notes_container #attachment_upload").attr("item", item);
		$("#note_list_header").trigger("click");
		var myModal = new jBox('Modal', {
			width: '90%',
			height: '90%',
			isolateScroll: false
		}); 
		myModal.open();
		myModal.setContent($("#notes_container"));
		$("#notes_container").fadeIn(200);
	}
	$(document).ready(function() {
		//Generate editors
		new Quill((".note_editor"), {
		    theme: 'snow',
		    modules: {
		    	toolbar: toolbarOptions
		  	},
		  	placeholder: 'Note...'
		});
		new Quill((".add-note"), {
		    theme: 'snow',
		    modules: {
		    	toolbar: toolbarOptions
		  	},
		  	placeholder: 'Note...'
		});
		generateEditors();

		//Generate jBox
		new jBox('Confirm', {
		    confirmButton: 'Yes',
		    cancelButton: 'No'
		});

		//Add & Edit note
		$("#add_note").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='add_note_loading'>");
			var noteContent = $(".add-note .ql-editor").html();
			$.post("/php_dependancies/add_item_note.php", {item: selectedStock, note: noteContent, title: $(".add-note").next(".set_note_title").val().trim()}, function(data) {
				if (data == "success") {
					$(".add-note .ql-editor").html("");
					$("#note_list_header").trigger("click");
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#add_note_loading").remove();
				$("#add_note").show();
			});
		});
		$("#edit_note").click(function() {
			$(this).hide();
			$(this).after("<img src='img/ajax-loader-2.gif' style='height: 38px; position:relative; top: 1px;' id='edit_note_loading'>");
			$.post("/php_dependancies/edit_item_note.php", {note_id: $(this).attr("note_id"), note_content: $(".note_editor .ql-editor").html(), title: $(".note_editor").next(".set_note_title").val().trim()}, function(data) {
				if (data == "success") {
					$(".note_editor .ql-editor").html("");
					$("#note_list_header").trigger("click");
				} else {
					alert("Error!");
					console.log(data);
				}
				$("#edit_note_loading").remove();
				$("#edit_note").show();
				$("#note_edit_header").hide();
			});
		});
	});
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>