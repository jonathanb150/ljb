<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<div class="super-container" style='width: 90%;'>
	<h1 class="super-container-header">Entry Points Calculator</h1>
	<div></div>
	<div class="general-container" style='margin: 0 auto; width: 100%;'>
		<nav style='display:none'>
			<h1 class='general-container-selected'></h1>
		</nav>
		<div id="entry_points"class='general-container-content' style='width: 100%;'>
			<label style="font-weight: 500;margin: 0 auto 10px auto;display: none; font-size: 24px;">Current Price</label>
			<input type="text" class="input" value="0" id="current_price" readonly style='display: none;'>
			<label style="font-weight: 500;margin: 0px auto 10px auto;display: block; font-size: 24px;">Total Capital</label>
			<input type="text" class="input" value="0" id="total_capital">
			<table>
				<thead>
					<th>Positions</th>
					<th>%</th>
					<th>$</th>
					<th>Entry Price</th>
					<th>Profit</th>
					<th>Loss</th>
					<th>Risk/Reward</th>
				</thead>
				<tbody>
					<tr>
						<td>Entry 1</td>
						<td><input type="text" value="0" id="entry_1_percent"></td>
						<td><input type="text" value="0" id="entry_1_capital"></td>
						<td><input type="text" value="0" id="entry_1_price"></td>
						<td id="profit_1">0</td>
						<td id="loss_1">0</td>
						<td id="risk_reward_1">0</td>
					</tr>
					<tr>
						<td>Entry 2</td>
						<td><input type="text" value="0" id="entry_2_percent"></td>
						<td><input type="text" value="0" id="entry_2_capital"></td>
						<td><input type="text" value="0" id="entry_2_price"></td>
						<td id="profit_2">0</td>
						<td id="loss_2">0</td>
						<td id="risk_reward_2">0</td>
					</tr>	
					<tr>
						<td>Entry 3</td>
						<td><input type="text" value="0" id="entry_3_percent"></td>
						<td><input type="text" value="0" id="entry_3_capital"></td>
						<td><input type="text" value="0" id="entry_3_price"></td>
						<td id="profit_3">0</td>
						<td id="loss_3">0</td>
						<td id="risk_reward_3">0</td>
					</tr>	
				</tbody>
			</table>
			<ul>
				<li><label>Best Case</label> <input type="text" id="best_case" class="input" value="0" style="margin-bottom: 20px"></li>
				<li><label>Worst Case</label> <input type="text" id="worst_case" class="input" value="0"></li>
			</ul>
			<ul>
				<li><label style="display: block">Total Return</label><input id="best_return_capital" type="text" class="input" value="$0" readonly style="display: inline-block; margin: 0 10px 10px 10px"><input id="best_return_percent" type="text" class="input" value="0%" readonly style="display: inline-block; margin: 0 10px 10px 10px"></li>
				<li><label style="display: block">Total Return</label><input id="worst_return_capital" type="text" class="input" value="$0" readonly style="display: inline-block; margin: 0 10px 10px 10px"><input id="worst_return_percent" type="text" class="input" value="0%" readonly style="display: inline-block; margin: 0 10px 10px 10px"></li>
			</ul>
			<ul>
				<li><label style="display: block">Risk/Reward</label><input id="risk_reward_total" type="text" class="input" value="N/A" readonly style="display: inline-block; margin: 0 10px 10px 10px"></li>
			</ul>
		</div>
	</div>
</div>
<script src="/js/entry_calculator.js"></script>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>
