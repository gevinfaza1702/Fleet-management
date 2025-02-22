<?php
include 'config/db.php';

if(isset($_GET['id'])) {
    $id_notifikasi = $_GET['id'];
    $conn->query("UPDATE notifikasi SET status = 'Dibaca' WHERE id_notifikasi = $id_notifikasi");
    header("Location: maintenance.php");
}
?>
