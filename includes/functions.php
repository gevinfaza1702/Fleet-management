<?php
// Fungsi untuk mengenkripsi password saat registrasi
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk memverifikasi password saat login
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}
?>
