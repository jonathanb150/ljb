$(document).ready(function() {
	//Functions
	function displayLoader(button) {
		$(button).hide();
		$(button).after("<img src='/img/ajax-loader-2.gif' height='37' style='margin-bottom: 7px;'>"); 
		$("#resultMessage").hide();
	}
	function hideLoader(button) {
		$(button).show();
		$(button).next("img").hide(); 
		$("#resultMessage").css("display", "inline-block");
	}

	//Algorithms
	$(".db-operations-form input[name='delete']").click(function() {
		var selected = $(".db-operations-form select[name='selected_delete']").find(":selected").text().trim();
		$(this).hide();
		$(this).prev("select").hide();
		$(this).after("<input type='submit' name='delete-yes' value='YES' style='width: 44px; display: inline-block; margin-right: 15px;'><input type='button' name='delete-no' value='NO' style='width: 44px; display: inline-block;'>");
		$(this).after("<div style='margin: 15px 15px 0 15px; max-width: 260px; font-size: 16px;'>Are you sure you wish to delete <b>"+ selected +"</b>?</div>");
		$(".db-operations-form input[name='delete-no']").click(function() {
			$(this).prev("input").prev("div").remove();
			$(this).prev("input").remove();
			$(this).remove();
			$(".db-operations-form input[name='delete']").show();
			$(".db-operations-form select[name='selected_delete']").show();
		});
	});

	$(".db-operations-form select[name='selected_edit']").change(function() {
		$(".db-operations-form input[name='edit_name']").val($(".db-operations-form select[name='selected_edit'] option:selected").text().split("- ")[1]);
	});

	var count = 0;
	$(".db-operations-form input[name='update_fundamentals']").click(function() {
		$(".db-operations-form input[name='update_fundamentals']").hide();
		$(".db-operations-form input[name='update_fundamentals']").after("<img src='/img/ajax-loader-2.gif' height='37' style='margin-bottom: 7px;'>"); 
		interval = setInterval(function () {
			if (count < 1) {
				count++;
				$.post("/php_dependancies/update_fundamentals.php", {update: true}, function(result) {
					$("#resultMessage").css("display", "inline-block");
					if (result != null && result.length > 0) {
						$("#resultMessage").append(result);
					} else {
						$("#resultMessage").append("An unknown error has ocurred.");
					}
					/*if (!result.includes("updated successfully")) {
						$(".db-operations-form input[name='update_fundamentals']").show();
						$(".db-operations-form input[name='update_fundamentals']").next("img").hide(); 
						clearInterval(interval);		
					}*/
					count--;
				});
			}
		}, 100);
	});
});