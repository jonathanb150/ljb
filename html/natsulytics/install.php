<?php require "db_connection.php"; ?>
<?php
$table_exists = $db_nl->query("SHOW TABLES LIKE `" . TABLE_NAME . "`", array());

if (is_array($table_exists) && count($table_exists) == 0) {
        var_dump($table_exists);
    if (
        $db_nl->query("CREATE TABLE `" . TABLE_NAME . "` (
        `id` int NOT NULL AUTO_INCREMENT,
        `ip` varchar(55) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        `country` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Other',
        `page` varchar(768) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        `referral` varchar(768) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        `phone` int DEFAULT NULL,
        `tablet` int DEFAULT NULL,
        `ios` int DEFAULT NULL,
        `android` int DEFAULT NULL,
        `browser` varchar(55) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Other',
        `user_agent` varchar(1024) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
        `time` int NOT NULL DEFAULT '0',
        PRIMARY KEY ( `id` ),
        INDEX(`time`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci", array())
    ) {
        echo "Installation success!";
    } else {
        echo "Installation error :(";
    }
} else {
    var_dump($table_exists);
    echo "Installation successs!";
}
?>