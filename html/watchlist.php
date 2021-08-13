<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php
$watchlists_table = $_SESSION["user"]."_watchlists";
if(isset($_POST['add_watchlist'])){
	if(isset($_POST['watchlist_name']) && strlen($_POST['watchlist_name']) > 0 && tableExists($db, $watchlists_table)){
		$watchlist_name = mysqli_escape_string($db, trim($_POST['watchlist_name']));
		confirmQuery(mysqli_query($db, "INSERT INTO `{$watchlists_table}` (name) VALUES ('{$watchlist_name}')"));
	}
}
?>
<script type="text/javascript">
	var selectedStock;
</script>
<script type="text/javascript" src="/js/watchlist.js"></script>
<script src="https://cdn.quilljs.com/1.1.9/quill.js"></script>
<link href="https://cdn.quilljs.com/1.1.9/quill.snow.css" rel="stylesheet">
<div class = "super-container" style="min-width: 90%">
	<h1 class = "super-container-header">Watchlist</h1>
	<div></div>
	<div class="general-container" style = "margin-top: 0">
		<nav>
			<?php 
			if (tableExists($db, $watchlists_table)){
				$get_watchlist_names = mysqli_query($db, "SELECT name FROM {$watchlists_table}");
				confirmQuery($get_watchlist_names);
				$select_watchlist_names = mysqli_fetch_all($get_watchlist_names);
				for ($i=0; $i < count($select_watchlist_names); $i++) { 
					if($i == 0){
						echo "<h1 class='general-container-selected'><i class='far fa-eye'></i>{$select_watchlist_names[$i][0]}</h1>";
					}
					else{
						echo "<h1><i class='far fa-eye'></i>{$select_watchlist_names[$i][0]}</h1>";
					}
				}
			}
			?>
			<h1><i class='fas fa-plus'></i>Add Watchlist</h1>
		</nav>
		<?php
		$current_watchlist_id = 0;
		$watchlist_table = $_SESSION["user"]."_watchlist";
		$portfolio_table = $_SESSION["user"]."_portfolio";
		$graphs_table = $_SESSION["user"]."_graphs";
		$arrays_table = $_SESSION["user"]."_arrays";
		if (tableExists($db, $watchlist_table) && tableExists($db, $watchlists_table)) {
			$get_watchlists = mysqli_query($db, "SELECT * FROM {$watchlists_table}");
			confirmQuery($get_watchlists);
			while($select_watchlist = mysqli_fetch_assoc($get_watchlists)){
				$current_watchlist_id = $select_watchlist['id'];
				$get_watchlist = mysqli_query($db, "SELECT * FROM {$watchlist_table} WHERE watchlist_id = {$select_watchlist['id']}");
				confirmQuery($get_watchlist);
				if (mysqli_num_rows($get_watchlist) > 0) {
					echo
					"<div class='general-container-content'><table id='watchlist' class='dataTable'>".
					"<thead>".
					"<th>Item</th>".
					"<th>Name</th>".
					"<th>Current<br>Price</th>".
					"<th>Added</th>".
					"<th>Target Price</th>".
					"<th>Min/Max Expected Price</th>".
					"<th>Delete</th>".
					"<th>Notes</th>".
					"</thead><tbody>";
					while ($row = mysqli_fetch_assoc($get_watchlist)) {
						$get_price = mysqli_query($db, "SELECT tableName, name FROM items WHERE symbol = '{$row['item']}'");
						confirmQuery($get_price);
						if ($row2 = mysqli_fetch_assoc($get_price)) {
							$get_price = mysqli_query($db, 'SELECT close FROM `'.$row2['tableName'].'` ORDER BY date DESC LIMIT 1') or die(mysqli_error($db));
							confirmQuery($get_price);
							if ($row3 = mysqli_fetch_assoc($get_price)) {
								$price = number_format((float)$row3['close'], 2, '.', '');
								echo 
								"<tr>".
								"<td><a href='/analyze.php?item={$row['item']}' target='_blank'><i class='far fa-chart-bar' style='margin-right: 5px; color: #414141;'></i>{$row['item']}</a></td>".
								"<td>{$row2['name']}</td>".
								"<td>{$price}</td>";
								echo "<td>{$row['date_added']}</td>";
								echo empty($row['target_price']) ? "<td><i style = 'margin-left: 5px' onclick = 'editTargetPrice({$row['watchlist_id']}, \"{$row['item']}\")' class='far fa-edit''></i></td>" : "<td>{$row['target_price']}<i style = 'margin-left: 5px' onclick = 'editTargetPrice({$row['watchlist_id']}, \"{$row['item']}\")' class='far fa-edit'></i></td>";
								echo "<td>".(empty($row['min_expected']) ? "Unset" : $row['min_expected'])."/".(empty($row['max_expected']) ? "Unset" : $row['max_expected'])."<i style = 'margin-left: 5px' onclick = 'editExpectedPrices({$row['watchlist_id']}, \"{$row['item']}\")' class='far fa-edit'></i></td>";
								echo 
								"<td value='{$row2['tableName']}'><a href='/php_dependancies/delete_watchlist_item.php?symbol={$row['item']}&id={$current_watchlist_id}'><i class='fas fa-trash-alt'></i></a></td>".
								"<td onclick=\"selectedStock = '{$row['item']}'; showNotes(selectedStock);\" class='item_notes'><i class='fas fa-plus'></i></td>".
								"</tr>";
							}
						} 
					}
					echo "</tbody></table>";
					echo "<button onclick = 'renameWatchlist({$current_watchlist_id})' class = 'button rename_watchlist' style = 'display: inline-block; margin: 20px'><i class='fas fa-edit'></i> Rename</button>";
					echo "<button onclick = 'deleteWatchlist({$current_watchlist_id})' class = 'button delete_watchlist' style = 'display: inline-block; margin: 20px' data-confirm = 'Do you really wish to delete this watchlist?'><i class='fas fa-trash-alt'></i> Delete</button></div>";
				} else {
					echo "<div class='general-container-content'>Nothing here...<br>";
					echo "<button onclick = 'renameWatchlist({$current_watchlist_id})' class = 'button rename_watchlist' style = 'display: inline-block; margin: 20px'><i class='fas fa-edit'></i> Rename</button>";
					echo "<button onclick = 'deleteWatchlist({$current_watchlist_id})' class = 'button delete_watchlist' style = 'display: inline-block; margin: 20px' data-confirm = 'Do you really wish to delete this watchlist?'><i class='fas fa-trash-alt'></i> Delete</button>";
					echo "</div>";
				}
			}
		} else {
			echo "<div class='general-container-content'>Nothing here...</div>";
		}
		?>
		<div class = 'general-container-content'>
			<form action = '/watchlist.php' method = 'POST'>
				<input type="text" class="standard_input" name="watchlist_name" placeholder="Watchlist Name...">
				<button name="add_watchlist" class="button">Submit</button>
			</form>
		</div>
	</div>
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
	function editExpectedPrices(id, item){
		var min_expected = window.prompt("Please enter the minimum expected price. Leave empty if you do not wish to change it. Enter -1 to unset.");
		min_expected = min_expected.trim();
		var max_expected = window.prompt("Please enter the maximum expected price. Leave empty if you do not wish to change it. Enter -1 to unset.");
		max_expected = max_expected.trim();
		if(id != null && id > 0 && item != null && item.length > 0){
			if((min_expected.length != 0 && !$.isNumeric(min_expected)) || (max_expected.length != 0 && !$.isNumeric(max_expected))){
				alert("You can only enter numbers. Please try again.");
				return false;
			}
			if(min_expected.length == 0){
				min_expected = -2;
			}
			else if(min_expected == -1){
				min_expected = '';
			}
			if(max_expected.length == 0){
				max_expected = -2;
			}
			else if(max_expected == -1){
				max_expected = '';
			}
			$.get("/watchlist_actions.php", {watchlist_id: id, watchlist_item: item, min_expected: min_expected, max_expected: max_expected}, function(data) {
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
	function renameWatchlist(id){
		var user_input = window.prompt("To what do you wish to rename this watchlist?");
		user_input = user_input.trim();
		if(user_input != null && user_input.length > 0 && id != null && id > 0){
			$.get("/watchlist_actions.php", {watchlist_id: id, rename_to: user_input}, function(data) {
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
	function deleteWatchlist(id){
		var watchlist_id = id;
		if(watchlist_id != null && watchlist_id > 0){
			$.get("/watchlist_actions.php", {watchlist_id: watchlist_id, delete: true}, function(data) {
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