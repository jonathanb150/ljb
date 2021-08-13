<?php 
if(isset($_POST["query"])){
	$script = shell_exec("python3.7 -W ignore {$_SERVER["DOCUMENT_ROOT"]}".$_POST["query"]." 2>&1");
	die($script);
	if ($script != null && is_array(json_decode(stripslashes($script), true))) {
		echo $script;
	}
}
?>