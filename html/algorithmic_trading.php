<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<form class="analyze" onsubmit="return false;" id="step_1">
	<h1><i class="fas fa-search"></i>Search Asset in Alpaca</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<div class="search_results_alpaca"></div>
	<button class="button" id="search_alpaca">Search</button>
</form>
<form class="analyze" onsubmit="return false;" id="step_2" style="display: none;">
	<h1><i class="fas fa-check-square"></i>Selected Alpaca Asset</h1>
	<div class="search_results_alpaca"></div>
	<h1><i class="fas fa-search"></i>Select Matching Asset in LJB</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<div id="search_results"></div>
	<h1><i class="fas fa-clock"></i>How often should the bot evaluate this asset?</h1>
	<div style="display: inline-block; min-width: 500px">
		<div class="switch-menu-entry">
			<div class="switch-label">Every two hours</div><div class="switch-container"><div></div></div>
		</div>
		<div class="switch-menu-entry">
			<div class="switch-label">Three times a day</div><div class="switch-container switch-container-on"><div class="switch-on"></div></div>
		</div>
	</div>
	<h1 style="margin-top: 15px;"><i class="fas fa-dollar-sign"></i>How much capital will you allocate on this asset?</h1>
	<span style="font-size: 20px; margin-right: 10px; line-height: 46px; vertical-align: bottom;">$</span><input class="input" id="capital" style="text-align: left;">
	<h1 style="margin-top: 15px;">Entry Points</h1>
	<span style="font-size: 20px; margin-right: 10px; line-height: 46px; vertical-align: bottom;">$</span><input class="input entry_point" style="text-align: left;">
	<i class="fas fa-plus" style="display: block; font-size: 24px; margin: 15px auto; cursor: pointer;" onclick="addEntryPoint(this);"></i>
	<button class="button" id="add_to_list" style="margin-top: 30px;">Add To List</button>
</form>
<img src="/img/ajax-loader-3.svg" id="ajax_loader" style="margin: 15px auto; display: none;">
<script src="/js/item_search.js"></script>
<script type="text/javascript">
	var alpaca_asset = null;

	itemSearch("statistical_backtesting", $("input[name='item_search']:eq(1)"), $("#search_results"));
	$("#search_alpaca").click(function() {
		$(this).hide();
		$("#ajax_loader").show();
		$(".search_results_alpaca").html("");
		$(".search_results_alpaca").hide();

		$.get("/php_dependancies/algorithmic_trading.php", { check_symbol: $("input[name=item_search]").val().toUpperCase() }, function(result){
			try{
				var result = JSON.parse(result);
			}
			catch(err) {}
			if(result['error'] == null && result['tradable'] != null){
				$(".search_results_alpaca").css("display", "inline-block");
				$(".search_results_alpaca").append('<div style="padding: 10px; font-weight: 500; cursor: pointer;"><div>'+result["symbol"]+' - <span style="color: '+(
					result['tradable'] ? '#52ff71' : '#ff5252')+';">'+(result['tradable'] ? '' : 'Not ')+'Tradable In Alpaca</span></div><div style="font-size: 12px; padding-top: 5px; font-style: italic;">Exchange: '+result["exchange"]+'</div></div>');
				$("#step_1 .search_results_alpaca div").unbind("click");
				$("#step_1 .search_results_alpaca div").click(function(){
					alpaca_asset = result["symbol"];
					$("#step_1").hide();
					$("#step_2").show();
				});
			}
			else{
				$(".search_results_alpaca").css("display", "inline-block");
				$(".search_results_alpaca").html("<p style='padding: 10px;'>"+$("input[name=item_search]").val().toUpperCase()+" was not found in Alpaca.</p>");
			}
			$("#search_alpaca").show();
			$("#ajax_loader").hide();
		});
	});

	$("#capital").keypress(function(key) {
		if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
			return false;	
		}
		else{
			return true;
		}
	});

	$(".switch-container").click(function() {
		$(".switch-container").find("div").removeClass("switch-on");
		$(".switch-container").removeClass("switch-container-on");
		$(this).find("div").toggleClass("switch-on");
		$(this).toggleClass("switch-container-on");
	});

	$("#add_to_list").click(function() {
		if($(".selected").length < 1){
			alert("Please select a matching LJB asset.");
		}
		else if($("#capital").val().length < 1 || !$.isNumeric(parseFloat($("#capital").val())) || parseFloat($("#capital").val()) < 1){
			alert("Please enter a valid capital to allocate.");
		}
		else{
			var entry_points = [];
			var ljb_asset = $(".selected").text().split(" - ")[0];
			var interval = $(".switch-container-on").prev("div").text();
			var capital = parseFloat($("#capital").val()).toFixed(2);

			for (var i = 0; i < $(".entry_point").length; i++) {
				if($(".entry_point:eq("+i+")").val().length < 1 || !$.isNumeric(parseFloat($(".entry_point:eq("+i+")").val())) || parseFloat($(".entry_point:eq("+i+")").val()) < 1){
					alert("Make sure to set at least one entry point, and that they're all valid numbers.");
					entry_points = [];
					break;
				}
				else{
					entry_points.push(parseFloat($(".entry_point:eq("+i+")").val()));
				}
			}

			if(entry_points.length > 0) {
				$("#ajax_loader").show();
				$("#add_to_list").hide();

				$.post("/php_dependancies/algorithmic_trading.php", { alpaca_asset: alpaca_asset, ljb_asset: ljb_asset, interval: interval, capital: capital, entry_points: JSON.stringify(entry_points) }, function(response) {
					if(response == "success"){
						location.reload();
					}
					else{
						alert("There was an error adding this asset.");
					}
					$("#ajax_loader").hide();
					$("#ajax_loader").show();
				});
			}
		}
	});

	function removeEntryPoint(element){
		$(element).prev("input").remove();
		$(element).prev("span").remove();
		$(element).prev("div").remove();
		$(element).remove();
	}

	function addEntryPoint(element){
		$(element).before('<div style="margin: 10px auto;"></div><span style="margin-left: 31px; font-size: 20px; margin-right: 10px; line-height: 46px; vertical-align: bottom;">$</span><input class="input entry_point" style="text-align: left;"><i class="fas fa-trash" onclick="removeEntryPoint(this)" style="font-size: 20px; margin-left: 10px; line-height: 46px; vertical-align: bottom; cursor: pointer;"></i>');
	}
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>
