<?php
set_time_limit(0); 
ignore_user_abort(true);
require("/var/www/ljb.solutions/html/php_dependancies/db.php");
require "functions.php";

$csv = file_get_contents("nasdaq.csv");
$csv = csvToArray($csv);

for ($i=1; $i < count($csv); $i++) { 
    if (apiProcessYAHOO($csv[$i][0], "us_stock", $csv[$i][1], $db, $csv[$i][0], "yahoo", false)) {
        echo $csv[$i][0]." - Success<br>";
    } else {
        echo $csv[$i][0]." - Fail<br>";
    }

    sleep(5);
}