<?php
$host = "localhost";      // Server database (localhost di XAMPP)
$user = "root";           // Default user MySQL
$pass = "";               // Kosongkan jika belum ada password di MySQL
$db_name = "fleet-management";  // Nama database yang dibuat

$conn = new mysqli($host, $user, $pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
} else {
    // echo "Koneksi berhasil"; // Uncomment untuk cek koneksi
}
?>
