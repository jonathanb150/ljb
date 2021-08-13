<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/login_head.php"); ?>
<?php 
//Setting loginError which will decide the result
$loginError = null;
if (isset($_POST["login"])) { //If user entered the form
	//Getting POST values
	$user = mysqli_escape_string($db, $_POST["loginUser"]);
	$pass = mysqli_escape_string($db, $_POST["loginPass"]);

	//Trim whitespaces and lowercase username
	$user = trim(strtolower($user));

	//Checking to see if the username or password were left blank
	if (strlen($user) < 1 || strlen($pass) < 1) {
		$loginError = "All fields must be filled.";
	} else {
		if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
			//Checking if username and password are indeed correct
			$loginCheck = mysqli_query($db2, "SELECT email, password, activated, balance FROM users");
			confirmQuery($loginCheck);
			if (mysqli_fetch_assoc($loginCheck)) {
				mysqli_data_seek($loginCheck, 0);
				while ($row = mysqli_fetch_assoc($loginCheck)) {
					if ($row["email"] === $user && password_verify($pass, $row["password"])) {
						if ($row["activated"] == 1 && $row['balance'] > 0) {
							$updateTime = mysqli_query($db2, "UPDATE users SET last_login = ".time()." WHERE email='{$user}'");
							confirmQuery($updateTime);
							$updateIP = mysqli_query($db2, "UPDATE users SET last_ip = '{$_SERVER['REMOTE_ADDR']}' WHERE email='{$user}'");
							confirmQuery($updateIP);
							$loginError = "Logged in succesfully!";
							$_SESSION["user"] = $user;
							if (isset($_GET['redirect'])) {
								redirect("/{$_GET['redirect']}");
							} else {
								redirect("/user/index.php");
							}
							break;
						}
						else if($row["activated"] == 1 && $row['balance'] == 0){
							$updateTime = mysqli_query($db2, "UPDATE users SET last_login = ".time()." WHERE email='{$user}'");
							confirmQuery($updateTime);
							$updateIP = mysqli_query($db2, "UPDATE users SET last_ip = '{$_SERVER['REMOTE_ADDR']}' WHERE email='{$user}'");
							confirmQuery($updateIP);
							$loginError = "Your account doesn't possess funds. Please add some before trying to log in.";
							break;
						}
						else {
							$loginError = "Your account is not activated. Please confirm your email address.";
							break;
						}
					} else {
						$loginError = "Wrong email or password.";
					}
				}
			} else {
				$loginError = "Wrong email or password.";
			}
		}
		else {
			//Checking if username and password are indeed correct
			$loginCheck = mysqli_query($db, "SELECT username, password, activated FROM users");
			confirmQuery($loginCheck);
			if (mysqli_fetch_assoc($loginCheck)) {
				mysqli_data_seek($loginCheck, 0);
				while ($row = mysqli_fetch_assoc($loginCheck)) {
					if ($row["username"] === $user && password_verify($pass, $row["password"])) {
						if ($row["activated"] == 1) {
							if (isAdmin($db, $row["username"])) {
								$updateTime = mysqli_query($db, "UPDATE users SET last = NOW() WHERE username='{$user}'");
								confirmQuery($updateTime);
								$updateIP = mysqli_query($db, "UPDATE users SET ip = '{$_SERVER['REMOTE_ADDR']}' WHERE username='{$user}'");
								confirmQuery($updateIP);
								$loginError = "Logged in succesfully!";
								$_SESSION["user"] = $user;
								if (isset($_GET['redirect'])) {
									redirect("/{$_GET['redirect']}");
								} else {
									redirect("/index.php");
								}
								break;
							} else {
								$loginError = "Sorry, account access is currently restricted.";
							}
						} else {
							$loginError = "Your account is not activated. Please confirm your email address.";
							break;
						}
					} else {
						$loginError = "Wrong username or password.";
					}
				}
			} else {
				$loginError = "Wrong username or password.";
			}	
		}
	}
}
?>
<?php  
//Activate account
$accountActivated = null;
if (isset($_GET["activateAccount"]) && isset($_GET["user"]) && isset($_GET["activationCode"])) {
	$user = trim(strtolower($_GET["user"]));
	$user = mysqli_escape_string($db, $user);
	$activate = mysqli_query($db, "SELECT password FROM users WHERE username = '{$user}'");
	confirmQuery($activate);
	while ($row = mysqli_fetch_assoc($activate)) {
		if ($row["password"] === $_GET["activationCode"]) {
			$query = mysqli_query($db, "UPDATE users SET activated = 1 WHERE username = '{$user}'");
			confirmQuery($query);
			$accountActivated = "Your account was activated succesfully! You can login now.";
		};
	}
}
?>
<div id="loginContainer">
	<a href="/login.php<?php if(isset($_GET['redirect'])) {echo "?redirect={$_GET['redirect']}";} ?>">
		<li class="loginLogo"><img src="/register/media/logo.svg"><span>Inveltio</span></li>
	</a>
	<?php 
	if (isset($_SESSION["registerSuccess"]) && $_SESSION["registerSuccess"] !== null) {
		echo "<span style=\"font-family: 'Roboto Mono', sans-serif; font-size: 16px; display: block; text-align: center; margin: 5px 37.5px 30px 37.5px; color: #A8CEA6; background: #E0FFDE; padding: 5px; border: 1px solid #A8CEA6; font-weight: 300;\"> {$_SESSION["registerSuccess"]} </span>";
		$_SESSION["registerSuccess"] = null;
	} elseif ($loginError != null) {
		echo "<span style=\"font-family: 'Roboto Mono', sans-serif; font-size: 16px; display: block; text-align: center; margin: 5px 37.5px 30px 37.5px; color: #E69E9E; background: #FFF0F0; padding: 5px; border: 1px solid #F5A9A9; font-weight: 300;\"> {$loginError} </span>";
	} elseif (isset($_SESSION["recoverSuccess"]) && $_SESSION["recoverSuccess"] !== null) {
		echo "<span style=\"font-family: 'Roboto Mono', sans-serif; font-size: 16px; display: block; text-align: center; margin: 5px 37.5px 30px 37.5px; color: #A8CEA6; background: #E0FFDE; padding: 5px; border: 1px solid #A8CEA6; font-weight: 300;\"> {$_SESSION["recoverSuccess"]} </span>";
		$_SESSION["recoverSuccess"] = null;
	} elseif ($accountActivated != null) {
		echo "<span style=\"font-family: 'Roboto Mono', sans-serif; font-size: 16px; display: block; text-align: center; margin: 5px 37.5px 30px 37.5px; color: #A8CEA6; background: #E0FFDE; padding: 5px; border: 1px solid #A8CEA6; font-weight: 300;\"> {$accountActivated} </span>";
	}
	?>
	<form id="login" method="post" action="/login.php<?php if(isset($_GET['redirect'])) {echo "?redirect={$_GET['redirect']}";} ?>">
		<img src="/register/media/email.svg"><input type="text" name="loginUser" placeholder="Email" autocomplete="off">
		<div style="margin: 15px 0;"></div>
		<img src="/register/media/lock.svg"><input type="password" name="loginPass" placeholder="Password" autocomplete="off">
		<div></div>
		<button class='btn_1' name="login">SIGN IN</button>
	</form>
	<!--<a href="/register.php">Don't have an account? Register now!</a>
	<a href="/recover.php">Forgot your password? Recover it here.</a>-->
</div>
<?php require($_SERVER['DOCUMENT_ROOT']."/php_dependancies/footer_login.php"); ?>