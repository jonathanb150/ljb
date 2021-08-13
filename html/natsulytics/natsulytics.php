<?php
if (
    isset($_SERVER["HTTP_USER_AGENT"]) && isset($_SERVER["REMOTE_ADDR"]) && isset($_SERVER["SCRIPT_NAME"]) &&
    filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP) &&
    !empty($_SERVER["HTTP_USER_AGENT"]) &&
    !empty($_SERVER["SCRIPT_NAME"])
) {
    require "db_connection.php";
    require "browser_detect.php";
    require "mobile_detect.php";
    require "geoip2.phar";
    if (!is_file(GEOIP_DB_PATH)) {
       exit;
    }
} else {
    exit;
}

use GeoIp2\Database\Reader;

$ip = $_SERVER["REMOTE_ADDR"];
$user_agent = $_SERVER["HTTP_USER_AGENT"];
$page = $_SERVER["SCRIPT_NAME"];

try {
    $geoip = new Reader(GEOIP_DB_PATH);
    $country = $geoip->country($ip)->country->name;
} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
}

$browser = get_browser_name($user_agent);

try {
    $mobile_detect = new Mobile_Detect;
    $phone = ($mobile_detect->isMobile() && !$mobile_detect->isTablet()) ? 1 : 0;
    $tablet = $mobile_detect->isTablet() ? 1 : 0;
    $ios = $mobile_detect->isiOS() ? 1 : 0;
    $android = $mobile_detect->isAndroidOS() ? 1 : 0;
} catch (Exception $e) {
}

try {
    $array = [$ip, $page, $browser, time(), (($browser == '[Bot] Other' || $browser == 'Other') ? $user_agent : NULL)];
    if (isset($country)) {
        $array[] = $country;
    }
    if (isset($phone)) {
        $array[] = $phone;
    }
    if (isset($tablet)) {
        $array[] = $tablet;
    }
    if (isset($ios)) {
        $array[] = $ios;
    }
    if (isset($android)) {
        $array[] = $android;
    }
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        $array[] = $_SERVER['HTTP_REFERER'];
    }
    $db_nl->query("INSERT INTO natsulytics (ip, page, browser, time, user_agent".(isset($country) ? ", country" : "").(isset($phone) ? ", phone" : "").(isset($tablet) ? ", tablet" : "").(isset($ios) ? ", ios" : "").(isset($android) ? ", android" : "").((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) ? ", referral" : "").") VALUES (?, ?, ?, ?, ?".(isset($country) ? ", ?" : "").(isset($phone) ? ", ?" : "").(isset($tablet) ? ", ?" : "").(isset($ios) ? ", ?" : "").(isset($android) ? ", ?" : "").((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) ? ", ?" : "").")", $array);
} catch (Exception $e) {
}