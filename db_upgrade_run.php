<?php
include "db.php";
$queries = [
    "ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `is_available` TINYINT(1) NOT NULL DEFAULT 1",
    "ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 10.00",
    "ALTER TABLE `restaurants` ADD COLUMN IF NOT EXISTS `is_online` TINYINT(1) NOT NULL DEFAULT 1",
    "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `delivery_fee` INT NOT NULL DEFAULT 99",
    "CREATE TABLE IF NOT EXISTS `settings` (`id` INT PRIMARY KEY AUTO_INCREMENT, `setting_key` VARCHAR(100) NOT NULL UNIQUE, `setting_value` VARCHAR(255) NOT NULL)",
    "INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES (\"delivery_fee\", \"99\")"
];
$out = "";
foreach($queries as $q) {
    if($conn->query($q)) { $out .= "<div style=\"color:green\">? OK: ".htmlspecialchars(substr($q,0,70))."</div>"; }
    else { $out .= "<div style=\"color:red\">? ERR: ".$conn->error."</div>"; }
}
echo "<!DOCTYPE html><html><body style=\"font-family:sans-serif;padding:30px\"><h2>DB Upgrade</h2>".$out."<p style=\"color:orange\"><b>Done! Delete db_upgrade_run.php now!</b></p></body></html>";
?>
