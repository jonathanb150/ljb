<?php require "db.php"; ?>
<?php require "functions.php"; ?>
<?php
session_start();
$tags_table = $_SESSION['user']."_tags";
$portfolio_table = $_SESSION['user']."_portfolio";

if(tableExists($db, $tags_table)){
	if(isset($_POST['add_tag'])){
		$tag = mysqli_escape_string($db, $_POST['add_tag']);
		confirmQuery(mysqli_query($db, "INSERT INTO `{$tags_table}` (tag) VALUES ('{$tag}')"));
	}
	else if(isset($_POST['delete_tag'])){
		$tag = mysqli_escape_string($db, $_POST['delete_tag']);
		confirmQuery(mysqli_query($db, "DELETE FROM `{$tags_table}` WHERE tag = '{$tag}'"));
	}
	else if(isset($_POST['item']) && isset($_POST['current_tags'])){
		confirmQuery(mysqli_query($db, "UPDATE items SET tags = '{$_POST['current_tags']}' WHERE symbol = '{$_POST['item']}'"));
	}
}
if(isset($_POST['current_tags']) && tableExists($db, $portfolio_table)){
	$post_tags = json_decode($_POST['current_tags']);
	
	if(is_array($post_tags)){
		$get_portfolio = mysqli_query($db, "SELECT item, bought_price, sold_price, tags, note FROM `{$portfolio_table}` WHERE status = 'closed'");
		confirmQuery($get_portfolio);

		$get_portfolio = mysqli_fetch_all($get_portfolio);
		$tags_match = [];

		for ($i=0; $i < count($get_portfolio); $i++) { 
			$tags = json_decode($get_portfolio[$i][3]);
			$count_tags = 0;

			for ($j=0; $j < count($post_tags); $j++) { 
				if(in_array($post_tags[$j], $tags)){
					$count_tags++;
				}
			}

			$tags_match[] = (float) (($count_tags * 100)/count($post_tags));
		}

		echo "<table style='width: 100%'><thead><th>Item</th><th>Bought Price</th><th>Sold Price</th><th>Profit/Loss</th><th>Tags Match</th></thead><tbody>";

		for ($i=0; $i < count($tags_match); $i++) { 
			$aux = 0;

			for ($j=$i+1; $j < count($tags_match); $j++) { 
				if($tags_match[$i] < $tags_match[$j]){
					$aux = $j;
				}
			}

			if(($i+1) < count($tags_match)){
				$pos = $tags_match[$i];
				$pos_portfolio = $get_portfolio[$i];
				$tags_match[$i] = $tags_match[$aux];
				$get_portfolio[$i] = $get_portfolio[$aux];
				$tags_match[$aux] = $pos;
				$get_portfolio[$aux] = $pos_portfolio;
			}
		}

		for ($i=0; $i < count($tags_match); $i++) { 
			echo "<tr>";
			echo "<td>".$get_portfolio[$i][0]."</td><td>".$get_portfolio[$i][1]."</td><td>".$get_portfolio[$i][2]."</td><td>".round((float) ((($get_portfolio[$i][2]*100)/$get_portfolio[$i][1])-100),2)."%</td><td>".round($tags_match[$i],2)."%</td>";
			echo "</tr>";

			if($get_portfolio[$i][4] != null && !empty($get_portfolio[$i][4])){
				echo "<tr>";
				echo "<td colspan='5'>".$get_portfolio[$i][4]."</td>";
				echo "</tr>";
			}
		}

		echo "</tbody></table>";
	}
}

if(isset($_POST['check_tags'])){
	$get_item_tags = mysqli_query($db, "SELECT symbol, name, tags FROM items");
	confirmQuery($get_item_tags);

	$array = [];
	$current_tags = json_decode($_POST['check_tags']);

	while($row = mysqli_fetch_assoc($get_item_tags)){
		$tags = json_decode($row['tags']); 
		$counter = 0;

		for ($i=0; $i < count($current_tags); $i++) { 
			if(in_array($current_tags[$i], $tags)){
				$counter++;
			}
		}

		if($counter > 0){
			$match_percentage = round((($counter*100)/count($current_tags)),2);
			$array[][] = array($row['symbol'], $row['name'], $match_percentage);
		}
	}

	echo json_encode($array);
}

if(isset($_POST['get_item_tags']) && isset($_SESSION['user'])){
	$query = mysqli_query($db, "SELECT tags FROM `{$_SESSION['user']}_portfolio` WHERE id = '{$_POST['get_item_tags']}'");
	confirmQuery($query);

	if($row = mysqli_fetch_assoc($query)){
		$tags = json_decode($row['tags']);
		if(is_array($tags) && count($tags) > 0){
			echo "<div id='tags_container'><ul id='tags_list'>";
			for ($i=0; $i < count($tags); $i++) { 
				echo "<li><span>".$tags[$i]."</span></li>";
			}
			echo "</ul></div>";
		}
	}
}
?>