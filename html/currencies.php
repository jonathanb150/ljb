<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<script src="https://cdn.quilljs.com/1.1.9/quill.js"></script>
<link href="https://cdn.quilljs.com/1.1.9/quill.snow.css" rel="stylesheet">
<script src="/js/jBox.min.js"></script>
<link href="/js/jBox.css" rel="stylesheet">
<script src="/js/jBox.Confirm.min.js"></script>
<link href="/js/jBox.Confirm.css" rel="stylesheet">

<?php
$error = "";
if(isset($_POST["analyze"]) && isset($_POST["selected_item"])){
	$item_symbol = mysqli_escape_string($db, trim($_POST["selected_item"]));
	$item_table = mysqli_query($db, "SELECT tableName FROM items WHERE symbol = '{$item_symbol}'");
	confirmQuery($item_table);
	if(mysqli_num_rows($item_table) == 1){
		$item_table = mysqli_fetch_assoc($item_table)["tableName"];
	}
	if (!isset($_POST["start_date"]) || strlen($_POST["start_date"]) <= 0) {
		$error = "Please select a start date.";
	} else if (!isset($_POST["end_date"]) || strlen($_POST["end_date"]) <= 0) {
		$error = "Please select an end date.";
	}
}
else if(isset($_POST["analyze"]) && !isset($_POST["selected_item"])){
	$error="Please select an item to analyze.";
}
?>

<form class="analyze" action="#analysis_results" method="POST">
	<h1><i class="fas fa-search"></i>Search Stock</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<div id="search_results"></div>
	<h1><i class="fas fa-table"></i>Select Date Range</h1>
	<div class='date_container'>Start Date<input type="date" name="start_date" <?php if (isset($_POST["start_date"]) && strlen($_POST["start_date"]) > 0) {echo "value='{$_POST["start_date"]}'";} else { echo "value='".date("Y-m-d", time() - 31557600*4)."'";} ?>></div>
	<div class='date_container'>End Date<input type="date" name="end_date" <?php if (isset($_POST["end_date"]) && strlen($_POST["end_date"]) > 0) {echo "value='{$_POST["end_date"]}'";} else { echo "value='".date("Y-m-d", time())."'";} ?>></div>
	<input style="margin-top:0" type="submit" name="analyze" class="button" value="Analyze">
	<?php 
	if(empty($error) && isset($_POST["selected_item"]) && isset($_POST["start_date"]) && isset($_POST["end_date"])){
		echo "<input type='hidden' name='selected_item' value='{$_POST['selected_item']}'>";
		echo "<input type='hidden' name='selected_table' value='".$item_table."'>";
		echo "<input type='hidden' name='selected_start_date' value='".date("Y-m-d", (strtotime($_POST['start_date']) - 31557600))."'>";
		echo "<input type='hidden' name='selected_end_date' value='{$_POST['end_date']}'>";
	}
	?>
	
</form>
<?php 
if(!empty($error)){
	echo "<div class='super-container'><h1 class='super-container-header'>Error</h1><div></div><div class='general-container' style='margin: 0 auto; width: 100%;'><nav><h1 class='general-container-selected' style='display: none;'></h1></nav><div class='general-container-content'>{$error}</div></div></div>";
	require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php");
	die();
}
?>
<div id="analysis_results" class="super-container" style='width: 90%; margin: 30px auto 0 auto; display: <?php echo isset($_POST["selected_item"]) ? "block" : "none"; ?>'>
	<?php 
	if(isset($_POST["selected_item"])){
		$header = (onWatchlist($db, $_SESSION['user'], $_POST["selected_item"]) ? "<i class='far fa-eye' style='margin-right: 10px;'></i>" : "").(onPortfolio($db, $_SESSION['user'], $_POST["selected_item"]) ? "<i class='fas fa-briefcase' style='margin-right: 10px;'></i>" : "")."{$_POST["selected_item"]} Analysis";
	}
	?>
	<h1 class="super-container-header"><?php echo $header; ?></h1>
	<div></div>
	<div class="general-container" style='margin: 0 auto; width: 100%;'>
		<nav>
			<h1 class='general-container-selected'><i class="fas fa-chart-area"></i>Overview</h1>
			<h1><i class="fas fa-folder-plus"></i>Actions</h1>
			<h1 id='note_list_header' onclick="getNotes('economy', this)"><i class="fas fa-sticky-note"></i>Notes</h1>
			<h1 id='note_add_header'><i class="fas fa-plus"></i>Add Note</h1>
			<h1 style='display: none;' id='note_edit_header'><i class="fas fa-pen-square"></i>Edit</h1>
			<h1 id='attachments_header' onclick="getAttachments(this)"><i class="fas fa-file-alt"></i>Attachments</h1>
		</nav>
		<div id="overview" class='general-container-content' style='width: 100%;'><img id="overview_loader" src="/img/ajax-loader-2.gif"></div>
		<div id="actions" class='general-container-content' style='width: 100%;'>
			<?php 
			if(empty($error) && isset($_POST['selected_item'])){
				echo "<button class='add-to' id='add_watchlist'><i class='far fa-eye' style='margin-right: 5px;'></i>ADD TO WATCHLIST</button>";
				echo "<button id='add_portfolio' class='add-to'><i class='fas fa-clipboard-list' style='margin-right: 5px;'></i>ADD TO PORTFOLIO</button>";
			}
			?>
		</div>
		<div class='general-container-content' style='width: 80%'>
			<?php
				$item_notes_table = $_SESSION["user"] . "_item_notes";
				$notes = mysqli_query($db, "SELECT title, note, date, id FROM `{$item_notes_table}` WHERE item = '{$selectedStock}' ORDER BY id ASC");
				if ($notes != null && mysqli_num_rows($notes) > 0) {
					while ($row = mysqli_fetch_assoc($notes)) {
						$note_date = $row['date'];
						$note_content = $row['note'];
						$note_title = strlen($row['title']) > 0 ? $row['title'] : strip_tags($row['note']);
						if ($note_title == null || strlen($note_title) <= 0) {
							$note_title = "New Note";
						}
						echo '<div class="note_container" note_id="'.$row['id'].'">
								<div class="note_header">
									<div class="note_title"><div>'.$note_title.'</div></div>
									<div class="note_date">'.$note_date.'</div>
									<div class="note_delete" onclick="deleteNote('.$row['id'].')" data-confirm="Do you really want to delete this note?"><i class="fas fa-trash" note_id="'.$row['id'].'"></i></div>
									<div class="note_edit" onclick="editNote(this)"><i class="fas fa-pen-square"  note_id="'.$row['id'].'"></i></div>				
									<div class="note_expand" onclick="expandNote(this)"><i class="fas fa-plus"></i></div>
								</div>
								<div class="editor note_content">'.$note_content.'</div>
							</div>';
					}	
				} else {
					echo "<span style='font-style: italic;'>Nothing here...</span>";
				}
			?>
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
			<button class="button" id='attachment_upload' style = "margin: 15px auto; display: block;" <?php if(isset($_POST['selected_item'])){echo "item = '{$_POST['selected_item']}'";} ?>>Upload</button>
		</div>
	</div>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>
<script src="/js/item_search.js"></script>
<script type="text/javascript">
	itemSearch("currency", $("input[name='item_search']"), $("#search_results"));
	//PYTHON
	if($("input[name='selected_item']").length == 1){
		var technical_query = "/algorithms/LongTerm/TechnicalAnalysisIndex.py '"+$("input[name='selected_table']").val()+"' '"+$("input[name='selected_start_date']").val()+"' '"+$("input[name='selected_end_date']").val()+"'";

		var overview_counter = 0;
		$.post("/php_dependancies/analysis_scripts.php", {query: technical_query}, function(data){
			console.log(data);
			var data = $.parseJSON(data);
		});
	}
</script>
<script type="text/javascript">
	//NOTES
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
			var selectedStock = "<?php echo $stock; ?>";
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
		var selectedStock = "<?php echo $stock; ?>";
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
</script>
<script type="text/javascript">
	//ATTACHMENTS
	function deleteAttachment(path){
		$.post("/php_dependancies/user_attachments.php", {delete_path: path}, function (data){
			$("#attachments_header").trigger("click");
		});
	}

	function getAttachments(element){
		var selectedStock = $("input[name='selected_item']").val();
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
        			alert("Error");
        		}
        		$("#attachment_upload").show();
        		$("#attachment_loading").remove();
        	}
    	});
	});
</script>
<script type="text/javascript">
	//WATCHLIST ADD
	var watchlist_request = false;

	$('#add_watchlist').click(function(){
		if (!watchlist_request) {
			watchlist_request = true;
			var myModalLoading = new jBox('Modal', {
				content: '<img src="/img/ajax-loader-2.gif">'
			}); 
			myModalLoading.open();
			$.get('/watchlist_actions.php', {get_watchlists: true, symbol: $("input[name='selected_table']").val()}, function(data) {
				myModalLoading.close();
				if (data != "fail") {
					var myModal = new jBox('Modal', {
						width: $(window).innerWidth()
					}); 
					myModal.open();
					myModal.setContent(data);

				} else {
					alert('Error!');
					console.log(data);
				}
				watchlist_request = false;
			});
		}
	});
</script>