<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php 
$get_unread_watchlist_notifications = mysqli_query($db, "SELECT * FROM `{$notifications_table}` WHERE status = 'unread' AND title LIKE '%Watchlist%' ORDER BY date DESC");
confirmQuery($get_unread_watchlist_notifications);
$get_read_watchlist_notifications = mysqli_query($db, "SELECT * FROM `{$notifications_table}` WHERE status = 'read' AND title LIKE '%Watchlist%' ORDER BY date DESC");
confirmQuery($get_read_watchlist_notifications);
$get_unread_portfolio_notifications = mysqli_query($db, "SELECT * FROM `{$notifications_table}` WHERE status = 'unread' AND title LIKE '%Portfolio%' ORDER BY date DESC");
confirmQuery($get_unread_portfolio_notifications);
$get_read_portfolio_notifications = mysqli_query($db, "SELECT * FROM `{$notifications_table}` WHERE status = 'read' AND title LIKE '%Portfolio%' ORDER BY date DESC");
confirmQuery($get_read_portfolio_notifications);
?>
<div class="super-container" style = "width: 48%;">
	<h1 class="super-container-header">Watchlist Notifications</h1>
	<div></div>
	<div class="general-container" style = "width: 100%; margin: 0;">
		<nav>
			<h1 class = "general-container-selected"><i class="fas fa-exclamation-circle"></i>Unread Messages (<span><?php echo mysqli_num_rows($get_unread_watchlist_notifications); ?></span>)</h1>
			<h1><i class="fas fa-envelope-open"></i>Read Messages (<span><?php echo mysqli_num_rows($get_read_watchlist_notifications); ?></span>)</h1>
		</nav>
		<div class="general-container-content" style = "width: 100%; max-height: 600px; overflow: auto">
			<button class="button expand_all">Expand All</button>
			<?php
			while($notification = mysqli_fetch_assoc($get_unread_watchlist_notifications)){
				echo
				'<div class="note_container" style= "margin: 10px auto; width: 80%">'.
				'<div class="note_header">';
				if(strpos($notification['notification'], "target") !== FALSE || strpos($notification['notification'], "financial") !== FALSE){
					echo '<div class="note_title notification_title" style="font-weight: 500"><a style="color: #414141" href="/analyze.php?item='.$notification['item'].'" target="_blank"><i class="far fa-chart-bar" style="margin-right: 5px; color: #414141;"></i>'.$notification["item"]."</a> ".$notification["title"].'<i class="fas fa-exclamation-circle" style="margin-left: 15px; color: #ec3737"></i></div>';
				}
				else{
					echo '<div class="note_title notification_title"><a style="color: #414141" href="/analyze.php?item='.$notification['item'].'" target="_blank"><i class="far fa-chart-bar" style="margin-right: 5px; color: #414141;"></i>'.$notification["item"]."</a> ".$notification["title"].'</div>';
				}
				echo 
				'<div class="note_date notification_date">'.unixTimeDifference($notification["date"])." ago".'</div>'.
				'<div class="note_expand"><i class="fas fa-plus"></i></div>'.
				'</div>'.
				'<div class="note_content notification_content">'.
				'<p>'.$notification["notification"].'</p>'.
				'<div><button onclick="readNotification('.$notification["id"].', this)" class = "button"><i class="fas fa-check"></i>Mark as read</button></div>'.
				'</div>'.
				'</div>';
			}
			if(mysqli_num_rows($get_unread_watchlist_notifications) == 0){
				echo "<p style='font-style:italic; margin: 20px 0 10px 0;'>There are no new notifications.</p>"; 
			}
			?>
		</div>
		<div class="general-container-content" style = "width: 100%; max-height: 600px; overflow: auto">
			<button class="button expand_all" style="display: inline-block">Expand All</button>
			<button class="button" onclick="deleteAll('Watchlist', this);" style="display: inline-block">Delete All</button>
			<?php
			while($notification = mysqli_fetch_assoc($get_read_watchlist_notifications)){
				echo
				'<div class="note_container" style= "margin: 10px auto; width: 80%">'.
				'<div class="note_header">'.
				'<div class="note_title notification_title"><a style="color: #414141" href="/analyze.php?item='.$notification['item'].'" target="_blank"><i class="far fa-chart-bar" style="margin-right: 5px; color: #414141;"></i>'.$notification["item"]."</a> ".$notification["title"].'</div>'.
				'<div class="note_date notification_date">'.unixTimeDifference($notification["date"])." ago".'</div>'.
				'<div class="note_expand"><i class="fas fa-plus"></i></div>'.
				'</div>'.
				'<div class="note_content notification_content">'.
				'<p>'.$notification["notification"].'</p>'.
				'<div><button onclick="deleteNotification('.$notification["id"].', this)" class = "button"><i class="fas fa-trash"></i>Delete</button></div>'.
				'</div>'.
				'</div>';
			}
			if(mysqli_num_rows($get_read_watchlist_notifications) == 0){
				echo "<p style='font-style:italic; margin: 20px 0 10px 0;'>There are no read notifications.</p>";
			}
			?>
		</div>
	</div>
</div>
<div class="super-container" style = "width: 48%;">
	<h1 class="super-container-header">Portfolio Notifications</h1>
	<div></div>
	<div class="general-container" style = "width: 100%; margin: 0;">
		<nav>
			<h1 class = "general-container-selected"><i class="fas fa-exclamation-circle"></i>Unread Messages (<span><?php echo mysqli_num_rows($get_unread_portfolio_notifications); ?></span>)</h1>
			<h1><i class="fas fa-envelope-open"></i>Read Messages (<span><?php echo mysqli_num_rows($get_read_portfolio_notifications); ?></span>)</h1>
		</nav>
		<div class="general-container-content" style = "width: 100%; max-height: 600px; overflow: auto">
			<button class="button expand_all">Expand All</button>
			<?php
			while($notification = mysqli_fetch_assoc($get_unread_portfolio_notifications)){
				echo
				'<div class="note_container" style= "margin: 10px auto; width: 80%">'.
				'<div class="note_header">';
				if(strpos($notification['notification'], "target") !== FALSE || strpos($notification['notification'], "financial") !== FALSE){
					echo '<div class="note_title notification_title" style="font-weight: 500"><a style="color: #414141" href="/analyze.php?item='.$notification['item'].'" target="_blank"><i class="far fa-chart-bar" style="margin-right: 5px; color: #414141;"></i>'.$notification["item"]."</a> ".$notification["title"].'<i class="fas fa-exclamation-circle" style="margin-left: 15px; color: #ec3737"></i></div>';
				}
				else{
					echo '<div class="note_title notification_title"><a style="color: #414141" href="/analyze.php?item='.$notification['item'].'" target="_blank"><i class="far fa-chart-bar" style="margin-right: 5px; color: #414141;"></i>'.$notification["item"]."</a> ".$notification["title"].'</div>';
				}
				echo
				'<div class="note_date notification_date">'.unixTimeDifference($notification["date"])." ago".'</div>'.
				'<div class="note_expand"><i class="fas fa-plus"></i></div>'.
				'</div>'.
				'<div class="note_content notification_content">'.
				'<p>'.$notification["notification"].'</p>'.
				'<div><button onclick="readNotification('.$notification["id"].', this)" class = "button"><i class="fas fa-check"></i>Mark as read</button></div>'.
				'</div>'.
				'</div>';
			}
			if(mysqli_num_rows($get_unread_portfolio_notifications) == 0){
				echo "<p style='font-style:italic; margin: 20px 0 10px 0;'>There are no new notifications.</p>"; 
			}
			?>
		</div>
		<div class="general-container-content" style = "width: 100%; max-height: 600px; overflow: auto">
			<button class="button expand_all" style="display: inline-block">Expand All</button>
			<button class="button" onclick="deleteAll('Portfolio', this);" style="display: inline-block">Delete All</button>
			<?php
			while($notification = mysqli_fetch_assoc($get_read_portfolio_notifications)){
				echo
				'<div class="note_container" style= "margin: 10px auto; width: 80%">'.
				'<div class="note_header">'.
				'<div class="note_title notification_title"><a style="color: #414141" href="/analyze.php?item='.$notification['item'].'" target="_blank"><i class="far fa-chart-bar" style="margin-right: 5px; color: #414141;"></i>'.$notification["item"]."</a> ".$notification["title"].'</div>'.
				'<div class="note_date notification_date">'.unixTimeDifference($notification["date"])." ago".'</div>'.
				'<div class="note_expand"><i class="fas fa-plus"></i></div>'.
				'</div>'.
				'<div class="note_content notification_content">'.
				$notification["notification"].
				'</div>'.
				'</div>';
			}
			if(mysqli_num_rows($get_read_portfolio_notifications) == 0){
				echo "<p style='font-style:italic; margin: 20px 0 10px 0;'>There are no read notifications.</p>";
			}
			?>
		</div>
	</div>
</div>
<div class="super-container" style = "width: 60%;">
	<h1 class="super-container-header">Settings</h1>
	<div></div>
	<div class="general-container" style = "margin-top: 0; width: 100%;">
		<nav>
			<h1 class="general-container-selected"><i class='fas fa-cog'></i>Watchlist Settings</h1>
			<h1><i class='fas fa-cog'></i>Portfolio Settings</h1>
		</nav>
		<div class="general-container-content" id = "watchlist-settings">
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item rises or drops 5% or more in one day</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item rises or drops 10% or more during the last 5 days</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item is close to Target Price (+-3%)</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when a potential buying point appears</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when company releases new financial information</div><div class='switch-container'><div></div></div>
			</div>
		</div>
		<div class="general-container-content" id = "portfolio-settings">
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item rises or drops 5% or more in one day</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item rises or drops 10% or more during the last 5 days</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item is close to Target Price (+-3%)</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item is close to Selling Price (+-3%)</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when company releases new financial information</div><div class='switch-container'><div></div></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(".expand_all").click(function(){
		if($(this).html() == "Expand All"){
			$(this).html("Close All");
			$(this).parent().find(".note_expand").each(function(){
				if ($(this).parent().next("div").css("display") == "none") {
					$(this).trigger("click");
				}
			});
		} else{
			$(this).html("Expand All");
			$(this).parent().find(".note_expand").each(function(){
				if ($(this).parent().next("div").css("display") == "block") {
					$(this).trigger("click");
				}
			});
		}
		
	});
	function readNotification(id, element){
		var delete_html = '<div class="note_container" style="margin: 10px auto; width: 80%;">'+($(element).parent().parent().parent().html()).replace("Mark as read", "Delete").replace("readNotification", "deleteNotification").replace("fa-check","fa-trash").replace("fa-minus", "fa-plus").replace('<div class="note_content notification_content" style="display: block;">', '<div class="note_content notification_content" style="display:none;">')+'</div>';
		var unread_count = parseInt($("#unread_notifications_count").html())-1;
		$(element).parent().parent().parent().parent().parent().find("nav h1:eq(0) span").html(parseInt($(element).parent().parent().parent().parent().parent().find("nav h1:eq(0) span").html())-1);
		$(element).parent().parent().parent().parent().parent().find("nav h1:eq(1) span").html(parseInt($(element).parent().parent().parent().parent().parent().find("nav h1:eq(1) span").html())+1);
		$("#unread_notifications_count").html(unread_count);
		$(element).parent().parent().parent().parent().parent().find(".general-container-content:eq(1)").children(".button:eq(1)").after(delete_html);
		$(element).parent().parent().parent().parent().parent().find(".general-container-content:eq(1)").children("p").hide();
		noteExpand();
		$(element).parent().parent().parent().fadeOut(250);
		$.post("/php_dependancies/notification_actions.php", {read: true, id: id}, function(data){});
	}
	function deleteNotification(id, element){
		$(element).parent().parent().parent().parent().parent().find("nav h1:eq(1) span").html(parseInt($(element).parent().parent().parent().parent().parent().find("nav h1:eq(1) span").html())-1);
		$(element).parent().parent().parent().fadeOut(250);
		$.post("/php_dependancies/notification_actions.php", {delete: true, id: id}, function(data){});
	}
	function deleteAll(type, element){
		$(element).parent().parent().find("nav h1:eq(1) span").html(0);
		$(element).parent().find(".note_container").fadeOut(250);
		if($(element).parent().children("p").length == 0){
			$(element).parent().append("<p style='font-style:italic; margin: 20px 0 10px 0;'>There are no new notifications.</p>");
		}
		else{
			$(element).parent().children("p").fadeIn(250);
		}
		$.post("/php_dependancies/notification_actions.php", {delete_all: type}, function(data){});
	}
	$(document).ready(function() {
		$(".switch-container").click(function() {
			$(this).find("div").toggleClass("switch-on");
			$(this).toggleClass("switch-container-on");
			var notification_name = $(this).parent().find(".switch-label").text();
			var notification_status = $(this).find("div").hasClass("switch-on") ? true : false;
			var notification_type = $(this).parent().parent().attr("id") == "portfolio-settings" ? "portfolio" : "watchlist";
			var array = {};
			array["notification_type"] = notification_type;
			array["notification_name"] = notification_name;
			array["notification_status"] = notification_status;
			$.post("/php_dependancies/edit_notification_settings.php", {notification: JSON.stringify(array)}, function(data){});

		});
		$.post("/php_dependancies/edit_notification_settings.php", {get_user_notifications: true}, function(data){
			var user_notifications = $.parseJSON(data);
			if('portfolio' in user_notifications){
				$("#portfolio-settings .switch-container").each(function() {
					var notification_name = $(this).parent().find(".switch-label").text();
					if(notification_name in user_notifications['portfolio']){
						if(user_notifications['portfolio'][notification_name]){
							$(this).find("div").toggleClass("switch-on");
							$(this).toggleClass("switch-container-on");
						}
					}
				});
			}
			if('watchlist' in user_notifications){
				$("#watchlist-settings .switch-container").each(function() {
					var notification_name = $(this).parent().find(".switch-label").text();
					if(notification_name in user_notifications['watchlist']){
						if(user_notifications['watchlist'][notification_name]){
							$(this).find("div").toggleClass("switch-on");
							$(this).toggleClass("switch-container-on");
						}
					}
				});
			}
		});
	});
</script>
<script type="text/javascript">
	function noteExpand(){
		$(".note_expand").unbind("click");
		$(".note_expand").click(function(){
			$(this).parent().next(".note_content").slideFadeToggle(150);
			if ($(this).html().indexOf("plus") > 0) {
				$(this).html($(this).html().replace("plus", "minus"));
			} else {
				$(this).html($(this).html().replace("minus", "plus"));
			}
		});
	}

	noteExpand();
</script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>