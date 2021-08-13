<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/login_head.php"); ?>
<?php
require '/var/www/ljb.solutions/html/php_dependancies/PHPMailer/src/Exception.php';
require '/var/www/ljb.solutions/html/php_dependancies/PHPMailer/src/PHPMailer.php';
require '/var/www/ljb.solutions/html/php_dependancies/PHPMailer/src/SMTP.php';
?>
<?php 
//Setting recoverError which will decide the result
$recoverError = null;

if (isset($_POST["recover"])) { //If user entered the form
	//Getting POST values
	$email = mysqli_escape_string($db, $_POST["recoverEmail"]);

	//Trim whitespaces
	$email = trim($email);

	//Checking to see if the username or password were left blank
	if (strlen($email) < 1) {
		$recoverError = "Please enter the email address.";
	} else {
		//Checking if email is correct
		$emailCheck = mysqli_query($db, "SELECT username, password, email FROM users");
		confirmQuery($emailCheck);
		if (mysqli_fetch_assoc($emailCheck)) {
			mysqli_data_seek($emailCheck, 0);
			while ($row = mysqli_fetch_assoc($emailCheck)) {
				if ($row["email"] === $email) {
					$_SESSION["recoveryHash"] = $row["password"];
					$_SESSION["recoveryUser"] = $row["username"];
					$mail = new PHPMailer\PHPMailer\PHPMailer();
					$mail->IsSMTP();

					$mail->SMTPDebug = 1;
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = 'ssl';
					$mail->Host = "smtp.gmail.com";
					$mail->Port = 465;
					$mail->IsHTML(true);
					$mail->Username = "ljbnotifications@gmail.com";
					$mail->Password = "gSkldnf#569784Sjfkd";
					$mail->SetFrom("ljbnotifications@gmail.com", "LJBFinance");
					$mail->Subject = "LJBFinance Recovery";
					$mail->Body = "<a target='_blank' href='https://ljb.solutions/change_pass.php?recover={$_SESSION["recoveryHash"]}'>Click here</a> to recover your account.";
					$mail->AddAddress($email);

					$mail->Send();

					$recoverError = "Recovery instructions sent!";
					break;
				} else {
					$recoverError = "Incorrect email address.";
				}
			}
		} else {
			$recoverError = "Incorrect email address.";
		}
	}
}
?>
<div id="loginContainer">
	<a href="/login.php">
		<div id="loginLogo">
			<div id="logo-header">LJB</div>
			<div id="logo-subheader">Finance</div>
		</div>
	</a>
	<?php 
	if ($recoverError != null) {
		echo "<span style=\"font-family: 'Roboto', sans-serif; font-size: 16px; display: block; text-align: center; margin: 5px 37.5px; color: #A8CEA6; background: #E0FFDE; padding: 5px 0; border: 1px solid #A8CEA6; font-weight: 300;\"> {$recoverError} </span>"; 
	} 
	?>
	<form id="login" method="post" action="/recover.php">
		<i class="fas fa-envelope"></i><input type="text" name="recoverEmail" placeholder="Email address">
		<div></div>
		<input type="submit" name="recover" value="Recover">
	</form>
	<a href="/login.php">Already have an account? Login now!</a>
	<a href="/register.php">Don't have an account? Register now!</a>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_login.php"); ?>