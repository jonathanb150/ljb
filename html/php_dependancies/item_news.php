<?php
if (isset($_GET['item']) && isset($_GET['yahoo'])) {
	$xml = file_get_contents("https://feeds.finance.yahoo.com/rss/2.0/headline?s={$_GET['item']}&region=US&lang=en-US");
	$return = parseXML($xml);

	if(!empty($return)){
		echo $return;
	} 
}
else if(isset($_GET['item']) && isset($_GET['google'])){
	$xml = file_get_contents("https://news.google.com/rss/search?q={$_GET['item']}");
	$return = parseXML($xml);

	if(!empty($return)){
		echo $return;
	}
}
else if (!isset($_GET['item']) && isset($_GET['marketwatch'])) {
	$xml = file_get_contents("http://feeds.marketwatch.com/marketwatch/{$_GET['marketwatch']}/");
	$return = parseXML($xml);

	if(!empty($return)){
		echo $return;
	}
}

function parseXML($xml){
	$result = "";

	if ($xml != null) {
		$xml = (array)simplexml_load_string($xml);
		$xml = (array)$xml['channel'];
		if ($xml != null && is_array($xml) && count($xml) > 0 && isset($xml['item'])) {
			foreach ($xml['item'] as $news) {
				$news = (array)$news;
				if ($news != null && is_array($news) && count($news) > 0 && isset($news['link']) && isset($news['title']) && isset($news['description'])) {
					$result .= "<div class='news_container'>";
					$result .= "<div class='news_title'>{$news['title']}</div>";
					if(is_array(explode("<div class=\"feedflare\">",$news['description'])) && count(explode("<div class=\"feedflare\">",$news['description'])) > 0){
						$result .= "<div class='news_description'>".explode("<div class=\"feedflare\">",$news['description'])[0]."</div>";
					}
					else{
						$result .= "<div class='news_description'>".$news['description']."</div>";
					}
					$result .= "<a href='{$news['link']}' target='_blank' style='background: #d5d5d5; padding: 5px; color: white; border-radius: 1px; font-weight: 700; transition: 0.2s all;'>Go to Article</a>";
					$result .= "</div>";
				}
			}
		}
	}

	return $result;
}
?>