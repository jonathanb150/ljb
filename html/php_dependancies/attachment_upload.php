<?php
if(isset($_FILES) && is_array($_FILES) && count($_FILES) == 1 && isset($_POST["item"])){
	$allowedExts = array( 
		"doc", 
		"docx",
		"xls",
		"xlsx",
	); 
	$allowedMimeTypes = array( 
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.ms-excel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/octet-stream'
	);
	$extension = explode(".", $_FILES["file"]["name"])[count(explode(".", $_FILES["file"]["name"]))-1];

	if($_FILES["file"]["error"] != 0 || $_FILES["file"]["size"] <= 0){
		echo "There was a problem uploading your file.";
	}
	else if(!in_array($extension, $allowedExts) || !in_array($_FILES["file"]["type"], $allowedMimeTypes)){
		echo "You're only allowed to upload Word and Excel documents.";
	}
	else{
		session_start();
		$root = $_SERVER['DOCUMENT_ROOT']."/";
		if(!file_exists($root."attachments")){
			mkdir($root."attachments");
		}
		if(!file_exists($root."attachments/".$_SESSION["user"])){
			mkdir($root."attachments/".$_SESSION["user"]);
		}
		if(!file_exists($root."attachments/".$_SESSION["user"]."/".$_POST["item"])){
			mkdir($root."attachments/".$_SESSION["user"]."/".$_POST["item"]);
		}
		if(file_exists($root."attachments/".$_SESSION["user"]."/".$_POST["item"])){
			move_uploaded_file($_FILES["file"]["tmp_name"], $root."attachments/".$_SESSION["user"]."/".$_POST["item"]."/".$_FILES["file"]["name"]);
			if(file_exists($root."attachments/".$_SESSION["user"]."/".$_POST["item"]."/".$_FILES["file"]["name"])){
				echo "Success";
			}
			else{
				echo "Error.";
			}
		} else{
			echo "Error.";
		}
	}
}
else{
	echo "Please select a file.";
}
?>