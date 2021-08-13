<?php
//General utility
function redirect($location) {
	header("Location: {$location}");
	exit;
} 
function checkArray($array) {
	if(is_array($array) && count($array) > 0) {
		return true;
	}

	return false;
}
function stringInVariable($a, $b) {
	if (strpos($b, $a) !== FALSE) {
		return true;
    }
    return false;
} 

//User input verification
function verifyEmail($mail) {
	if(!filter_var($mail, FILTER_VALIDATE_EMAIL) || strlen($mail) > 255) {
		return "Provided email is invalid.";
	}

	return true;
}
function verifyPassword($pw, $min_length, $max_length) {
	if(strlen($pw) < $min_length || strlen($pw) > $max_length) {
		return "Password must be {$min_length} to {$max_length} characters long.";
	}
	else if(ctype_digit($pw) ||	ctype_alpha($pw)) {
		return "Password must contain letters and numbers.";
	}

	return true;
}
function verifyName($name) {
	if(empty($name) || !ctype_alpha($name) || strlen($name) < 2 || strlen($name) > 255) {
		return false;
	}

	return true;
}
function verifyDOB($dob) {
	if(strtotime($dob) === FALSE) {
		return "Please select your date of birth.";
	}
	else if(time()-strtotime($dob) < (86400*365*18)) {
		return "You must be over 18 years old.";
	}

	return true;
}
function verifyAddress($address) {
	$space_count = substr_count($address, " ");

	if($space_count < 3 || strlen($address) > 500) {
		return "Please provide a valid address.";
	}

	return true;
}
function verifyUSZipCode($code) {
	if(!is_numeric($code) || $code < 00501 || $code > 99950) {
		return "Please provide a valid ZIP Code.";
	}

	return true;
}
?>