<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<?php
if (isset($_POST['balanceChange'])) {
	if (isset($_POST['balanceAmountAdd']) && is_numeric($_POST['balanceAmountAdd'])) {
		$balanceAdd = (float)trim($_POST['balanceAmountAdd']);
		if ($balanceAdd != null && is_numeric($balanceAdd)) {
			addUserBalance($db, $_SESSION['user'], $balanceAdd);
		}
	} else if (isset($_POST['balanceAmountSubstract']) && is_numeric($_POST['balanceAmountSubstract'])) {
		$balanceSubstract = -1 * (float)trim($_POST['balanceAmountSubstract']);
		if ($balanceSubstract != null && is_numeric($balanceSubstract)) {
			addUserBalance($db, $_SESSION['user'], $balanceSubstract);
		}
	}
	$investments = getOpenInvestments($db, $_SESSION['user']);
	$currentBalance = (float)getUserBalance($db, $_SESSION['user']) + (float)getInvestedCash($db, $_SESSION['user']);
	$portfolioDistribution = shell_exec('sudo python3.5 '.$_SERVER['DOCUMENT_ROOT'].'/algorithms/Portfolio/portfolioAnalysis.py \''.implode(" ", $investments['item']).'\' \''.implode(" ", $investments['capital']).'\' \''.$currentBalance.'\' false false false false');
	if (!empty($portfolioDistribution) && is_array(json_decode($portfolioDistribution, true))) {
		$portfolioDistribution = json_decode($portfolioDistribution, true);
		$graphs_table = $_SESSION['user']."_graphs";
		$arrays_table = $_SESSION['user']."_arrays";
		$graph = array();
		$graph['graph1'] = $portfolioDistribution['graph1'];
		$graph['graph2'] = $portfolioDistribution['graph2'];
		$graph = json_encode($graph, true); 
		$graph = mysqli_escape_string($db, $graph);
		$array = array();
		$array['currentDist'] = $portfolioDistribution['currentDist']; 
		$array['recommendedDist'] = $portfolioDistribution['recommendedDist'];
		$array['standardDist'] = $portfolioDistribution['standardDist']; 
		$array = json_encode($array, true); 
		$array = mysqli_escape_string($db, $array);
		mysqli_query($db, "UPDATE {$graphs_table} SET graph = '{$graph}' WHERE identifier = 'portfolio'") or die('Error');
		mysqli_query($db, "UPDATE {$arrays_table} SET array = '{$array}' WHERE identifier = 'portfolio'") or die('Error');
	}
} 
?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".switch-container").click(function() {
			$(this).find("div").toggleClass("switch-on");
			$(this).toggleClass("switch-container-on");
		});
	});
</script>
<div class='super-container' style='width: 40%;'>
	<h1 class="super-container-header">Notification Settings</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 class='general-container-selected'><i class='fas fa-clipboard-list'></i>Portfolio Settings</h1>
			<h1><i class='far fa-eye'></i>Watchlist Settings</h1>
		</nav>
		<div class='general-container-content'>
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
		<div class='general-container-content'>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item rises or drops 5% or more in one day</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item rises or drops 10% or more during the last 5 days</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when item is close to True Price (+-3%)</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when a potential buying point appears</div><div class='switch-container'><div></div></div>
			</div>
			<div class='switch-menu-entry'>
				<div class='switch-label'>Notify me when company releases new financial information</div><div class='switch-container'><div></div></div>
			</div>
		</div>
	</div>
</div>
<div class='super-container' style='width: 40%;'>
	<h1 class="super-container-header">Account Balance</h1>
	<div></div>
	<div class="general-container" style='margin: 0; width: 100%; max-width: 100%;'>
		<nav>
			<h1 class='general-container-selected'><i class="fas fa-dollar-sign"></i>Current Balance</h1>
			<h1><i class="fas fa-plus"></i>Add Balance</h1>
			<h1><i class="fas fa-minus"></i>Substract Balance</h1>
		</nav>
		<div class='general-container-content'>
			<?php
				$balance = number_format(getUserBalance($db, $_SESSION['user']), 2, ".", ",");
			?>
			<input class='accountBalance' type="text" readonly value="$ <?php echo $balance; ?>">
		</div>
		<div class='general-container-content'>
			<form method='POST'>
				<input type="number" step='any' name="balanceAmountAdd" placeholder='Amount...'>
				<button class='button' name='balanceChange' style='margin-bottom: 20px;' data-confirm="Are you sure you wish to add specified amount to your balance?">Submit</button>
			</form>
		</div>
		<div class='general-container-content'>
			<form method='POST'>
				<input type="number" step='any' name="balanceAmountSubstract" placeholder='Amount...'>
				<button class='button' name='balanceChange' style='margin-bottom: 20px;'>Submit</button>
			</form>
		</div>
	</div>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>