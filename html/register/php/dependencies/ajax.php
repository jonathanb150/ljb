<?php 
require "user.php";
//Register
if(isset($_POST['register'])) { 
	$register = new NewUser($_POST['register']);
	echo $register->error;
}
//Login
else if(isset($_POST['login'])) {
	$login = new User($_POST['login']);
	echo $login->error;
}
//Account setup
else if(isset($_POST['question']) && isset($_POST['answer']) && isset($_SESSION['account_setup']) && isset($_POST['inputs'])) {
	$account_setup = new AccountSetup($_POST['question'], $_POST['answer'], $_POST['inputs'], $_SESSION['account_setup'], null);
	echo $account_setup->error;
}
//Update Account Setup Status
else if(isset($_POST['status']) && isset($_SESSION['account_setup'])) {
	$account_setup = new AccountSetup(null, null, null, $_SESSION['account_setup'], $_POST['status']);
}
//Fetch Account Setup Status
else if(isset($_POST['get_status']) && isset($_SESSION['account_setup'])) {
	$account_setup = new AccountSetup(null, null, null, $_SESSION['account_setup'], null);
	echo $account_setup->settings_status;
}
?>