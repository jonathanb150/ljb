<?php 
session_start();

if(isset($_SESSION["user"]) && isset($_POST["item"])){
	$root = $_SERVER['DOCUMENT_ROOT']."/attachments/".$_SESSION["user"]."/".$_POST["item"];

	if(file_exists($root)){
		$get_attachments = glob($root."/*");
		$array = [];

		for ($i=0; $i < count($get_attachments); $i++) { 
			$document_name = explode("/", $get_attachments[$i]);
			$array["path"][] = "/attachments/".$_SESSION["user"]."/".$_POST["item"]."/".end($document_name);
			$array["document_name"][] = end($document_name);
			$array["document_type"][] = strpos($get_attachments[$i], "doc") ? "word" : "excel";
 		}

		$array = json_encode($array, true);
		echo $array;
	}
}
else if(isset($_POST["delete_path"])){
	$file = $_SERVER['DOCUMENT_ROOT'].$_POST["delete_path"];

	if(file_exists($file)){
		unlink($file);
	}
}
?>