<?php
session_start(); // Session shuru karein taake usay access kar sakein
session_unset(); // Saaray session variables (jaise rest_id) hata dein
session_destroy(); // Session ko mukammal khatam karein

// Ab wapis login page par bhej dein
header("Location: restaurent_login.php");
exit();
?>