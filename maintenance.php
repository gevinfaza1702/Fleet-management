<?php
session_start();
include 'config/db.php'; // Koneksi ke database

// Ambil data pemeliharaan dari database
$query = "SELECT k.id_kendaraan, k.nomor_polisi, p.tanggal_servis, p.jenis_perawatan, p.status 
          FROM kendaraan k 
          JOIN pemeliharaan p ON k.id_kendaraan = p.id_kendaraan 
          ORDER BY p.tanggal_servis DESC";

$result = $conn->query($query);

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
    <title>Pemeliharaan & Notifikasi</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        /* Badge untuk status */
        .badge-warning { background-color: #f0ad4e; }
        .badge-danger { background-color: #d9534f; }
        .badge-success { background-color: #5cb85c; }
        .badge-secondary { background-color: #6c757d; }

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
            <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
            <a href="monitoring.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-map-marker-alt me-2"></i>Monitoring
            </a>
            <a href="maintenance.php" class="list-group-item list-group-item-action bg-dark text-white active">
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
                <h4 class="ms-4 mb-0">Pemeliharaan & Notifikasi</h4>

                <ul class="navbar-nav ms-auto">
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
                                while ($notif = $notif_result->fetch_assoc()): ?>
                                    <li><a class="dropdown-item" href="#">
                                        🚗 <?php echo htmlspecialchars($notif['nomor_polisi']); ?> - Servis pada <?php echo htmlspecialchars($notif['tanggal_servis']); ?>
                                    </a></li>
                            <?php endwhile; else: ?>
                                <li><a class="dropdown-item text-muted">Tidak ada notifikasi baru.</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Content -->
        <div class="container-fluid px-4 mt-4">

            <!-- Title -->
            <h2 class="fw-bold text-center mb-4"><i class="fas fa-tools"></i> Pemeliharaan & Notifikasi</h2>

            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan nomor polisi..." onkeyup="searchTable()">
            </div>

            <!-- Tabel Pemeliharaan -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center" id="maintenanceTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Kendaraan</th>
                            <th>Nomor Polisi</th>
                            <th>Tanggal Servis</th>
                            <th>Jenis Perawatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                $status = htmlspecialchars($row['status']);
                                $tanggal_servis = htmlspecialchars($row['tanggal_servis']);
                                $today = date('Y-m-d');

                                // Tentukan status Overdue jika diperlukan
                                if ($tanggal_servis < $today && $status != 'Selesai') {
                                    $status = 'Overdue';
                                }

                                $badgeClass = ($status == 'Dalam Proses') ? 'warning' :
                                              (($status == 'Overdue') ? 'danger' :
                                              (($status == 'Selesai') ? 'success' : 'secondary'));
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id_kendaraan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nomor_polisi']); ?></td>
                                    <td><?php echo $tanggal_servis; ?></td>
                                    <td><?php echo htmlspecialchars($row['jenis_perawatan']); ?></td>
                                    <td><span class="badge bg-<?php echo $badgeClass; ?>"><?php echo $status; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="markAsCompleted('<?php echo $row['id_kendaraan']; ?>')">
                                            <i class="fas fa-check"></i> Selesai
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada kendaraan dalam pemeliharaan saat ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Fungsi untuk pencarian di tabel
    function searchTable() {
        const input = document.getElementById("searchInput").value.toUpperCase();
        const table = document.getElementById("maintenanceTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            const td = tr[i].getElementsByTagName("td")[1]; // Kolom nomor polisi
            if (td) {
                const txtValue = td.textContent || td.innerText;
                tr[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
            }
        }
    }

    // Fungsi untuk menandai perawatan sebagai selesai (simulasi)
    function markAsCompleted(idKendaraan) {
        if (confirm("Apakah Anda yakin ingin menandai pemeliharaan ini sebagai selesai?")) {
            // Simulasi update status (di produksi, gunakan AJAX untuk update database)
            alert("Pemeliharaan untuk kendaraan ID " + idKendaraan + " telah ditandai sebagai selesai.");
        }
    }
</script>

</body>
</html>
