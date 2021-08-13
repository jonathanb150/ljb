<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/admin_head.php"); ?>
<table class='dataTable dataTableDesc'>
	<thead>
		<th>Date</th>
		<th>Script</th>
		<th>Log</th>
	</thead> 
	<tbody>
		<?php 
			$query = mysqli_query($db, "SELECT log, date, type FROM logs ORDER by date DESC") or die(mysqli_error($db));
			$logs = mysqli_fetch_all($query);
			for ($i = 0; $i < sizeof($logs); $i++) { 
				echo "<tr>";
				echo "<td>{$logs[$i][1]}</td>";
				echo "<td>{$logs[$i][2]}</td>";
				echo "<td><textarea readonly style='width: 90%; max-width: 90%; max-height: 300px; min-height: 100px; text-align: center;'>".str_replace("<br>", "&#10;", $logs[$i][0])."</textarea></td>";
				echo "</tr>";
			}
		?>
	</tbody>
</table>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_main.php"); ?>