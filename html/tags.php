<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<div id="tags_container" style="width: 50%; margin: 0 auto; padding-top: 10px; background: #f1f1f1">
	<div>
		<h3>Tags</h3>
		<ul id="tags_list">
			<?php 
			$tags_table = $_SESSION['user']."_tags";

			if(tableExists($db, $tags_table)){
				$get_tags = mysqli_query($db, "SELECT tag FROM `{$tags_table}`");
				confirmQuery($get_tags);

				while($row = mysqli_fetch_assoc($get_tags)){
					echo "<li><span onclick='addTag(this, \"add\");'>{$row['tag']}</span></li>";
				}
			}
			?>
		</ul>
	</div>
	<div>
		<h3>Selected Tags</h3>
		<ul id="selected_tags">
			
		</ul>
	</div>
</div>
<div id="match_results">
</div>
<script type="text/javascript">
	function addTag(element, action) {
		if(action == "add"){
			$("#selected_tags").prepend("<li><span onclick='addTag(this, \"remove\")'>"+$(element).text().trim()+"</span></li>");
		}
		else{
			$("#tags_list").prepend("<li><span onclick='addTag(this, \"add\")'>"+$(element).text().trim()+"</span></li>");
		}
		$(element).parent().remove();

		$("#match_results").html("<img src='/img/ajax-loader-3.svg'>");

		$.post("/php_dependancies/tags_actions.php", {check_tags: getSelectedTags()}, function(data){
			$("#match_results").html("");
			data = $.parseJSON(data);

			if(data != null && data.length > 0){
				console.log(data);
				for (var i = 0; i < data.length; i++) {
					console.log(data[i]);
					$("#match_results").append("<div><div>"+data[i][0][0]+" - "+data[i][0][1]+"</div><div>"+data[i][0][2]+"%</div></div>");
				}
			}
		});
	}

	function getSelectedTags(){
		var current_tags = [];
		for (var i = 0; i < $("#selected_tags li").length; i++) {
			current_tags.push($("#selected_tags li:eq("+i+")").text().trim()); 
		}
		current_tags = JSON.stringify(current_tags);

		return current_tags;
	}
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>