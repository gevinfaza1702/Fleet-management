<?php
$plain_password = 'admin123';  // Password yang ingin di-hash
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Tampilkan hasil hashed password
echo "Plain Password: " . $plain_password . "<br>";
echo "Hashed Password: " . $hashed_password;
?>
