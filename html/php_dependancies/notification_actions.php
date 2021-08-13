<?php require "db.php"; ?>
<?php 
session_start();
//READ NOTIFICATIONS
$notifications_table = $_SESSION["user"]."_notifications";
if(isset($_POST["read"]) && isset($_POST["id"]) && is_numeric($_POST["id"]) && $_POST["id"] > 0){
	mysqli_query($db, "UPDATE `{$notifications_table}` SET status = 'read' WHERE id = {$_POST["id"]}") or die("Connection error.");
}
//DELETE NOTIFICATIONS
else if(isset($_POST["delete"]) && isset($_POST["id"]) && is_numeric($_POST["id"]) && $_POST["id"] > 0){
	mysqli_query($db, "DELETE FROM `{$notifications_table}` WHERE id = {$_POST["id"]}") or die("Connection error.");
}
//DELETE ALL READ NOTIFICATIONS
else if(isset($_POST['delete_all']) && !empty($_POST['delete_all'])){
	mysqli_query($db, "DELETE FROM `{$notifications_table}` WHERE title LIKE '%{$_POST['delete_all']}%' AND status = 'read'") or die("Connection error.");
}
?>