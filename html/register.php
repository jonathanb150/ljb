<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/login_head.php"); ?>
<?php
require '/var/www/ljb.solutions/html/php_dependancies/PHPMailer/src/Exception.php';
require '/var/www/ljb.solutions/html/php_dependancies/PHPMailer/src/PHPMailer.php';
require '/var/www/ljb.solutions/html/php_dependancies/PHPMailer/src/SMTP.php';
?>
<?php 
//Setting registerError which will decide the result
$registerError = null;

if (isset($_POST["register"])) {
	//Getting POST values
	$user = mysqli_escape_string($db, $_POST["registerUser"]);
	$pass = mysqli_escape_string($db, $_POST["registerPass"]);
	$confirmPass = mysqli_escape_string($db, $_POST["registerConfirm"]);
	$email = mysqli_escape_string($db, $_POST["registerEmail"]);

	//Trim username and email, also lowercase username
	$user = strtolower(trim($user));
	$email = trim($email);

	//Registering if no errors
	if (strlen($user) < 1 || strlen($pass) < 1 || strlen($confirmPass) < 1 || strlen($email) < 1) {
		$registerError = "All fields must be filled.";
	} elseif (strlen($user) < 4 || strlen($pass) < 8) {
		$registerError = "Username and password must be at least 4 and 8 characters long respectively.";
	} elseif ($pass !== $confirmPass) {
		$registerError = "Passwords don't match.";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$registerError = "That is not a valid email address.";
	} elseif (strpos($user, " ") !== false) {
		$registerError = "Username can't contain spaces.";
	} elseif (strlen($user) > 50) {
		$registerError = "Username can't exceed 50 characters.";
	} else {
		//Checking if username already exists
		$userCheck = mysqli_query($db, "SELECT username, email FROM users");
		confirmQuery($userCheck);
		while ($row = mysqli_fetch_assoc($userCheck)) {
			if ($user === $row["username"]) {
				$registerError = 1;
				break;
			} else if ($email === $row["email"]) {
				$registerError = 2;
				break;
			} else {
				$registerError = null;
			}
		}
		if ($registerError == 1) {
			$registerError = "That username already exists.";
		} else if ($registerError == 2) {
			$registerError = "That email address has been used already.";
		} else {
			$pass = password_hash($pass, PASSWORD_DEFAULT);
			$ip = $_SERVER['REMOTE_ADDR'];
			$register = mysqli_query($db, "INSERT INTO users (username, password, email, ip) VALUES ('{$user}', '{$pass}', '{$email}', '{$ip}')");
			confirmQuery($register);
			$registerError = "Registered succesfully! We sent you an email, please activate your account.";
			$_SESSION["registerSuccess"] = $registerError;

			//Create user tables
			mysqli_query($db, "CREATE TABLE `{$user}_portfolio` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`item` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`bought_price` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`target_price` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`selling_price` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`allocated_capital` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`ljb_score` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`status` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
				`date_added` date NOT NULL,
				`sold_price` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
				`date_closed` date DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_notifications` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`item` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`notification` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`date` int(11) NOT NULL,
				`status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'unread',
				PRIMARY KEY (`id`),
				KEY `status` (`status`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_watchlist` (
				`watchlist_id` int(11) NOT NULL,
				`item` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`date_added` date NOT NULL,
				`target_price` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
				KEY `watchlist_id` (`watchlist_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_watchlists` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(64) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_arrays` (
				`identifier` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`array` longtext COLLATE utf8mb4_unicode_520_ci,
				PRIMARY KEY (`identifier`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "INSERT INTO `{$user}_arrays` (identifier, array) VALUES ('portfolio', '')") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_economy_notes` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`note` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`title` varchar(1000) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`date` date NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_graphs` (
				`identifier` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`graph` longtext COLLATE utf8mb4_unicode_520_ci,
				PRIMARY KEY (`identifier`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "INSERT INTO `{$user}_graphs` (identifier, graph) VALUES ('portfolio', '')") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_item_notes` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`item` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`note` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`title` varchar(1000) COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`date` date NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_portfolio_history` (
				`date` date NOT NULL,
				`total_balance` double NOT NULL,
				`invested_capital` double NOT NULL,
				`cash` double NOT NULL,
				PRIMARY KEY (`date`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));
			mysqli_query($db, "CREATE TABLE `{$user}_cash` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`date` date NOT NULL,
				`cash` double NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;") or die(mysqli_error($db));

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
    		$mail->Subject = "LJBFinance Registration";
    		$mail->Body = "<a target='_blank' href='https://ljb.solutions/login.php?activateAccount=true&user={$user}&activationCode={$pass}'>Click here</a> to activate your account.";
    		$mail->AddAddress($email);

    		$mail->Send();

    		redirect("/login.php");
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
	if ($registerError != null) {
		echo "<span style=\"font-family: 'Roboto', sans-serif; font-size: 16px; display: block; text-align: center; margin: 5px 37.5px; color: #E69E9E; background: #FFF0F0; padding: 5px 0; border: 1px solid #F5A9A9; font-weight: 300;\"> {$registerError} </span>";
	}
	?>
	<form id="login" method="post" action="/register.php">
		<i class="fa fa-user"></i><input type="text" name="registerUser" placeholder="Username" <?php if (isset($_POST["register"])) { echo "value='{$user}'"; } ?>>
		<div></div>
		<i class="fas fa-key"></i><input type="password" name="registerPass" placeholder="Password">
		<div></div>
		<i class="fas fa-key"></i><input type="password" name="registerConfirm" placeholder="Confirm password">
		<div></div>
		<i class="fas fa-envelope"></i><input type="text" name="registerEmail" placeholder="Email address" <?php if (isset($_POST["register"])) { echo "value='{$email}'"; } ?>>
		<div></div>
		<input type="submit" name="register" value="Register">
	</form>
	<a href="/login.php">Already have an account? Login now!</a>
	<a href="/recover.php">Forgot your password? Recover it here.</a>
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_login.php"); ?>