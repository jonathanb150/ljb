<?php 
if(isset($_POST['session']) && isset($_POST['item']) && is_array(json_decode($_POST['session'], true))){
	session_start();
	$_SESSION['portfolio'] = json_decode($_POST['session'], true);
	$_SESSION['portfolio']['Name'] = $_POST['item'];
}
?>