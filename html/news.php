<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<div id="general_news" class="super-container" style='width: 90%; margin: 0 auto 20px auto;'>
	<h1 class="super-container-header">General News</h1>
	<div></div>
	<div class="general-container" style='margin: 0 auto; width: 100%;'>
		<nav>
			<h1 class='general-container-selected'>Top Stories</h1>
			<h1>MarketPulse</h1>
			<h1>Breaking News</h1>
			<h1>Research</h1>
		</nav>
		<div class="general-container-content" style="width: 100%; max-height: 500px; overflow: auto; ">
			<img src="/img/ajax-loader-3.svg" id='top_stories_loader' style='display: none;'>
			<div id="top_stories"></div>
		</div>
		<div class="general-container-content" style="width: 100%; max-height: 500px; overflow: auto; ">
			<img src="/img/ajax-loader-3.svg" id='marketpulse_loader' style='display: none;'>
			<div id="marketpulse"></div>
		</div>
		<div class="general-container-content" style="width: 100%; max-height: 500px; overflow: auto; ">
			<img src="/img/ajax-loader-3.svg" id='breaking_news_loader' style='display: none;'>
			<div id="breaking_news"></div>
		</div>
		<div class="general-container-content" style="width: 100%; max-height: 500px; overflow: auto; ">
			<img src="/img/ajax-loader-3.svg" id='research_loader' style='display: none;'>
			<div id="research"></div>
		</div>
	</div>
</div>
<form class="analyze" onsubmit="event.preventDefault();" method="POST">
	<h1><i class="fas fa-search"></i>Search Item</h1>
	<input type='text' name='item_search' placeholder='Search...'autocomplete='off'>
	<div id="search_results">
	</div>
	<input style="margin: 0 auto 30px auto" type="submit" class="button" value="Fetch Latest News">
</form>
<div id="news" class="super-container" style='width: 90%; margin: 0 auto; display: none'>
	<h1 class="super-container-header"></h1>
	<div></div>
	<div class="general-container" style='margin: 0 auto; width: 100%;'>
		<nav>
			<h1 class='general-container-selected'>Yahoo</h1>
			<h1>Google</h1>
		</nav>
		<div class='general-container-content' style='width: 100%'>
			<img src="/img/ajax-loader-3.svg" id='yahoo_loader' style='display: none;'>
			<div id="yahoo_news"></div>
		</div>
		<div class='general-container-content' style='width: 100%'>
			<img src="/img/ajax-loader-3.svg" id='google_loader' style='display: none;'>
			<div id="google_news"></div>
		</div>
	</div>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>
<script src="/js/item_search.js"></script>
<script type="text/javascript">
	itemSearch("", $("input[name='item_search']"), $("#search_results"));
</script>
<script type="text/javascript">
	$.get('/php_dependancies/item_news.php', {marketwatch: "topstories"}, function(data) {
		$('#top_stories_loader').hide();
		$('#top_stories').html(data);
		$('#top_stories .news_description').each(function() {
			if ($(this).html().length > 300) {
				$(this).html($(this).html().substring(0, 300)+"...");
			}
		});
	});
	$.get('/php_dependancies/item_news.php', {marketwatch: "marketpulse"}, function(data) {
		$('#marketpulse_loader').hide();
		$('#marketpulse').html(data);
		$('#marketpulse .news_description').each(function() {
			if ($(this).html().length > 300) {
				$(this).html($(this).html().substring(0, 300)+"...");
			}
		});
	});
	$.get('/php_dependancies/item_news.php', {marketwatch: "bulletins"}, function(data) {
		$('#breaking_news_loader').hide();
		$('#breaking_news').html(data);
		$('#breaking_news .news_description').each(function() {
			if ($(this).html().length > 300) {
				$(this).html($(this).html().substring(0, 300)+"...");
			}
		});
	});
	$.get('/php_dependancies/item_news.php', {marketwatch: "newslettersandresearch"}, function(data) {
		$('#research_loader').hide();
		$('#research').html(data);
		$('#research .news_description').each(function() {
			if ($(this).html().length > 300) {
				$(this).html($(this).html().substring(0, 300)+"...");
			}
		});
	});
	$(".analyze .button").click(function() {
		if ($('.selected').length == 1) {
			$('#yahoo_loader').show();
			$('.super-container').show();
			$('#news').show();
			$('#yahoo_news').html("");
			var item = $('.selected').html().split(' - ')[0].trim();
			var name = $('.selected').html().split(' - ')[1].trim();
			$('.super-container-header').html(name+' News');
			$.get('/php_dependancies/item_news.php', {item: item, yahoo:true}, function(data) {
				$('#yahoo_loader').hide();
				$('#yahoo_news').html(data);
				$('#yahoo_news').prepend("<h3>Found "+$("#yahoo_news .news_container").length+" News</h3>");
				$('#yahoo_news .news_description').each(function() {
					if ($(this).html().length > 600) {
						$(this).html($(this).html().substring(0, 600)+"...");
					}
				});
			});
			$.get('/php_dependancies/item_news.php', {item: item, google:true}, function(data) {
				$('#google_loader').hide();
				$('#google_news').html(data);
				$('#google_news').prepend("<h3>Found "+$("#google_news .news_container").length+" News</h3>");
				$('#google_news .news_description').each(function() {
					if ($(this).html().length > 600) {
						$(this).html($(this).html().substring(0, 600)+"...");
					}
				});
			});
		} else {
			alert('Please select an item.');
		}
	});
</script>