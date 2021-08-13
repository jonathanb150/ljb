<?php 
//Functions
//Get Remaining Storage of Biggest Drive on Bytes
function remainingStorage() {
    $available_storage = trim(shell_exec("df"));
    $available_storage = explode(" ", $available_storage);
    $array = [];

    for ($i=0; $i < count($available_storage); $i++) { 
        $aux = explode(" ", $available_storage[$i]);
        $aux = trim($aux[0]);

        if(is_numeric($aux) && (int) $aux > 0) {
            $array[] = (int) $aux;
        }
    }

    $max = max($array);
    $max_index = array_search($max, $array);

    if(isset($array[$max_index+2])) {
        return $array[$max_index+2]*1024;
    }

    return 0;
}

function stringInVariable($a, $b) {
	if (strpos($b, $a) !== FALSE) {
		return true;
    }
    return false;
}

function displayAllErrors() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function hideAllErrors() {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

function redirect($url, $permanent = false) {
    header('Location: '.$url);
    exit();
}

function recursiveRemoveDirectoryContents($directory) {
    foreach(glob("{$directory}/*") as $file)
    {
        if(is_dir($file)) { 
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
        clearstatcache();
    }
    clearstatcache();
}

//Compares current time with provided unix timestampt. Returns 1-60 minutes, 1-24 hours or 1+ days
function unixTimeDifference($past) {
    $difference = time() - $past;
    if ($difference <= 60) {
        return '1 minute';
    } else if ($difference > 60 && $difference < 3600) {
        return (int)($difference / 60).((int)($difference / 60) > 1 ? ' minutes' : ' minute');
    } else if ($difference >= 3600 && $difference < 86400) {
        return (int)($difference / 3600).((int)($difference / 3600) > 1 ? ' hours' : ' hour');
    } else if ($difference >= 86400) {
        return (int)($difference / 86400).((int)($difference / 86400) > 1 ? ' days' : ' day');
    }
}
?>