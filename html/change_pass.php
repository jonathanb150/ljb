<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/login_head.php"); ?>
<?php 
if (isset($_POST["newPass"]) && $_POST["newPass"] != null) {
	//Getting POST value, escape hacks with mysqli_escape_string
	$newPass = mysqli_escape_string($db, $_POST["newPass"]);

	//Hash the new password
	$newPass = password_hash($newPass, PASSWORD_DEFAULT);

	//Find username
	$userFind = mysqli_query($db, "SELECT username, password FROM users");
	while ($row = mysqli_fetch_assoc($userFind)) {
		if ($row["username"] === $_SESSION["recoveryUser"]) {
			$changePass = mysqli_query($db, "UPDATE users SET password = '$newPass' WHERE username = '{$row["username"]}'");
			confirmQuery($changePass);
			$_SESSION["recoverSuccess"] = "You succesfully changed your password.";
			redirect("/login.php");
			break;
		}
	}
	unset($_POST["newPass"]);
}
else if (isset($_GET["recover"]) && isset($_SESSION["recoveryHash"])) {
	if ($_GET["recover"] === $_SESSION["recoveryHash"]) {
		unset($_GET["recover"]);
		unset($_SESSION["recoveryHash"]);
	} else {
		redirect("/login.php");
	} 
} else {
	redirect("/login.php");
}
?>
<div id="loginContainer" class="absoluteCenter">
	<a href="/login.php">
		<div id="loginLogo">
			<div id="logo-header"><span style="color: #7A7A7A;">L</span><span style="color: #5F5F5F;">J</span><span style="color: #4E4E4E;">B</span></div>
				<div id="logo-subheader">Finance</div>
		</div>
	</a>
	<form id="login" method="post" action="/change_pass.php">
		<i class="fas fa-key" style></i><input type="password" name="newPass" placeholder="New password">
		<div></div>
		<input type="submit" name="changepass" value="Confirm">
	</form>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_login.php"); ?>