<?php
session_start();
include 'config/db.php'; // Koneksi ke database

// Query Ringkasan Data
$total_kendaraan = $conn->query("SELECT COUNT(*) AS total FROM kendaraan")->fetch_assoc()['total'];
$total_pengemudi = $conn->query("SELECT COUNT(*) AS total FROM pengemudi")->fetch_assoc()['total'];
$perjalanan_aktif = $conn->query("SELECT COUNT(*) AS total FROM perjalanan WHERE waktu_tiba IS NULL")->fetch_assoc()['total'];
$total_bbm = $conn->query("SELECT IFNULL(SUM(jumlah_bahan_bakar),0) AS total_bbm FROM konsumsi_bahan_bakar")->fetch_assoc()['total_bbm'];

// Hitung jumlah Overdue untuk notifikasi
$overdue_query = "SELECT COUNT(*) AS total_overdue 
                  FROM pemeliharaan 
                  WHERE tanggal_servis < CURDATE() AND status != 'Selesai'";
$overdue_result = $conn->query($overdue_query);
$overdue_count = $overdue_result->fetch_assoc()['total_overdue'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Fleet Management</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- External CSS -->
    <link rel="stylesheet" href="style.css">

    <style>
        /* Header Section */
        .header-section h2 {
            font-weight: 700;
            font-size: 2.5rem;
            color: #343a40;
        }

        .header-section p {
            font-size: 1.2rem;
            color: #6c757d;
        }

        /* Feature Cards */
        .feature-card, .summary-card {
            background-color: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover, .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .feature-card i, .summary-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 15px;
        }

        .feature-card h5, .summary-card h5 {
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Benefit List */
        .benefit-list {
            list-style: none;
            padding: 0;
        }

        .benefit-list li {
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            margin-bottom: 12px;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .benefit-list li i {
            color: #28a745;
            margin-right: 12px;
            font-size: 1.5rem;
        }

        /* Navbar */
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar */
        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            background-color: #343a40;
        }

        .list-group-item:hover {
            background-color: #495057;
        }

        /* Navbar Notifikasi */
        .nav-item .fa-bell {
            position: relative;
        }

        .nav-item .badge-notif {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 3px 6px;
            font-size: 0.7rem;
        }
    </style>
</head>

<body>

<!-- Wrapper -->
<div class="d-flex" id="wrapper">

    <!-- Sidebar -->
    <div class="bg-dark border-end" id="sidebar-wrapper">
        <div class="sidebar-heading text-white py-4 text-center fs-4">🚚 Fleet Management</div>
        <div class="list-group list-group-flush">
            <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white active">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
            <a href="monitoring.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-map-marker-alt me-2"></i>Monitoring
            </a>
            <a href="maintenance.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-tools me-2"></i>Pemeliharaan
            </a>
            <a href="reports.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-chart-line me-2"></i>Laporan
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div id="page-content-wrapper">

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>

                <h4 class="ms-4 mb-0">Dashboard Fleet Management</h4>

                <ul class="navbar-nav ms-auto">
                    <!-- Notifikasi -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php if($overdue_count > 0): ?>
                                <span class="badge-notif"><?php echo $overdue_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="width: 300px;">
                            <li class="dropdown-header">Notifikasi Overdue</li>
                            <?php
                            $notif_query = "SELECT k.nomor_polisi, p.tanggal_servis 
                                            FROM pemeliharaan p
                                            JOIN kendaraan k ON p.id_kendaraan = k.id_kendaraan
                                            WHERE p.tanggal_servis < CURDATE() AND p.status != 'Selesai'
                                            ORDER BY p.tanggal_servis ASC
                                            LIMIT 5";
                            $notif_result = $conn->query($notif_query);

                            if ($notif_result->num_rows > 0):
                                while ($notif = $notif_result->fetch_assoc()):
                            ?>
                                <li><a class="dropdown-item" href="#">
                                    🚗 <?php echo htmlspecialchars($notif['nomor_polisi']); ?> - Servis pada <?php echo htmlspecialchars($notif['tanggal_servis']); ?>
                                </a></li>
                            <?php endwhile; else: ?>
                                <li><a class="dropdown-item text-muted">Tidak ada notifikasi baru.</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Content -->
        <div class="container-fluid px-4 mt-4">

            <!-- Welcome Section -->
            <div class="text-center header-section mb-5">
                <h2 class="fw-bold">Selamat Datang di Fleet Management System 🚛</h2>
                <p>Pantau status armada kendaraan Anda secara real-time, kelola pemeliharaan, dan optimalkan operasi armada dengan efisien.</p>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="summary-card">
                        <i class="fas fa-truck"></i>
                        <h5>Total Kendaraan</h5>
                        <h3><?php echo $total_kendaraan; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <i class="fas fa-user"></i>
                        <h5>Total Pengemudi</h5>
                        <h3><?php echo $total_pengemudi; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <i class="fas fa-road"></i>
                        <h5>Perjalanan Aktif</h5>
                        <h3><?php echo $perjalanan_aktif; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <i class="fas fa-gas-pump"></i>
                        <h5>Konsumsi BBM</h5>
                        <h3><?php echo $total_bbm; ?> L</h3>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Manfaat Sistem -->
            <div class="my-5">
                <h3 class="mb-4"><i class="fas fa-lightbulb text-warning"></i> Manfaat & Fungsi Sistem Fleet Management</h3>
                <ul class="benefit-list">
                    <li><i class="fas fa-check-circle"></i> Memantau status armada secara real-time dan akurat.</li>
                    <li><i class="fas fa-check-circle"></i> Mengoptimalkan jadwal pemeliharaan kendaraan untuk mengurangi downtime.</li>
                    <li><i class="fas fa-check-circle"></i> Mempercepat pengambilan keputusan berbasis data statistik armada.</li>
                    <li><i class="fas fa-check-circle"></i> Meningkatkan efisiensi operasional armada dan mengurangi biaya.</li>
                    <li><i class="fas fa-check-circle"></i> Memberikan laporan lengkap terkait performa dan pemeliharaan kendaraan.</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS & External JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

</body>
</html>
