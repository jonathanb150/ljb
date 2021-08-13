<?php require "db.php"; ?>
<?php require "functions.php"; ?>
<?php session_start(); ?>
<?php 
$get_watchlist_items = mysqli_query($db, "SELECT item, min_expected, max_expected FROM `".$_SESSION['user']."_watchlist`");
confirmQuery($get_watchlist_items);

$watchlist_reminder_good = [];
$watchlist_reminder_bad = [];
while ($row = mysqli_fetch_assoc($get_watchlist_items)) {
	if(!empty($row['min_expected']) && is_numeric($row['min_expected']) && !empty($row['max_expected']) && is_numeric($row['max_expected'])){
		$current_price = (float) getCurrentPrice($row['item']);

		if((((float) $row['max_expected'] - $current_price) > ($current_price - (float) $row['min_expected']))){
			if (hasGoodFundamentals($db, $row['item'])) {
				$watchlist_reminder_good[] = [$row['item'], round(((float) ($current_price-$row['min_expected'])/(float) ($row['max_expected']-$current_price)),2)];
			} else {
				$watchlist_reminder_bad[] = [$row['item'], round(((float) ($current_price-$row['min_expected'])/(float) ($row['max_expected']-$current_price)),2)];
			}
			
		}
	}
}

$get_portfolio_items = mysqli_query($db, "SELECT item, date_added FROM `".$_SESSION['user']."_portfolio` WHERE status = 'open'");
confirmQuery($get_portfolio_items);

$portfolio_reminder = [];

while($row = mysqli_fetch_assoc($get_portfolio_items)){
	$week_day = date("l", strtotime($row['date_added']));

	if(date('l') == $week_day && !in_array($row['item'], $portfolio_reminder)){
		$portfolio_reminder[] = $row['item'];
	}
}

	$portfolio_content = "<h3 style='text-align: center;'>Check the following positions</h3><ul>";
	for ($i = 0; $i < count($portfolio_reminder); $i++) {
		if($portfolio_reminder[$i] != null){
			$portfolio_content .= "<li style='list-style-type: none; text-align: center;'>".$portfolio_reminder[$i]."</li>";
		}
	}
	if(count($portfolio_reminder) == 0){
		$portfolio_content .= "<li style='list-style-type: none; text-align: center; font-style: italic'>Nothing here...</li>";
	}
	$portfolio_content .= "</ul>";

	$watchlist_content = "<h3 style='text-align: center;'>Good Fundamentals & Good Risk/Reward Ratio</h3><ul>";
	for ($i = 0; $i < count($watchlist_reminder_good); $i++) {
		if($watchlist_reminder_good[$i][0] != null){
			$watchlist_content .= "<li style='list-style-type: none; text-align: center;'>".$watchlist_reminder_good[$i][0]." (".$watchlist_reminder_good[$i][1]."/1)</li>";
		}
	}
	if(count($watchlist_reminder_good) == 0){
		$watchlist_content .= "<li style='list-style-type: none; text-align: center; font-style: italic'>Nothing here...</li>";
	}
	$watchlist_content .= "</ul>";

	$watchlist_content .= "<h3 style='margin-top: 10px; text-align: center;'>Good Risk/Reward Ratio</h3><ul>";
	for ($i = 0; $i < count($watchlist_reminder_bad); $i++) {
		if($watchlist_reminder_bad[$i][0] != null){
			$watchlist_content .= "<li style='list-style-type: none; text-align: center;'>".$watchlist_reminder_bad[$i][0]." (".$watchlist_reminder_bad[$i][1]."/1)</li>";
		}
	}
	if(count($watchlist_reminder_bad) == 0){
		$watchlist_content .= "<li style='list-style-type: none; text-align: center; font-style: italic'>Nothing here...</li>";
	}
	$watchlist_content .= "</ul>";

	echo json_encode([$portfolio_content, $watchlist_content]);
?>