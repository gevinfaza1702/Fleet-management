<?php
session_start();
include 'config/db.php';

// Ambil data statistik kendaraan
$total_kendaraan = $conn->query("SELECT COUNT(*) AS total FROM kendaraan")->fetch_assoc()['total'];
$kendaraan_aktif = $conn->query("SELECT COUNT(*) AS aktif FROM kendaraan WHERE status='Aktif'")->fetch_assoc()['aktif'];
$kendaraan_servis = $conn->query("SELECT COUNT(*) AS servis FROM kendaraan WHERE status='Servis'")->fetch_assoc()['servis'];
$kendaraan_nonaktif = $conn->query("SELECT COUNT(*) AS nonaktif FROM kendaraan WHERE status='Non-Aktif'")->fetch_assoc()['nonaktif'];

// Query untuk tabel rincian kendaraan
$kendaraan_query = "SELECT id_kendaraan, nomor_polisi, model, status FROM kendaraan ORDER BY id_kendaraan ASC";
$kendaraan_result = $conn->query($kendaraan_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analisis</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- External CSS -->
    <link rel="stylesheet" href="style.css">
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
            <a href="maintenance.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-tools me-2"></i>Pemeliharaan
            </a>
            <a href="reports.php" class="list-group-item list-group-item-action bg-dark text-white active">
                <i class="fas fa-chart-line me-2"></i>Laporan
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div id="page-content-wrapper">

        <div class="container-fluid px-4 mt-4">

            <!-- Title -->
            <h2 class="fw-bold text-center mb-4">📊 Laporan & Analisis</h2>

            <!-- Statistik Ringkas -->
            <div class="row text-center mb-5">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-truck fa-2x mb-2"></i>
                            <h5>Total Kendaraan</h5>
                            <h3><?php echo $total_kendaraan; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <h5>Kendaraan Aktif</h5>
                            <h3><?php echo $kendaraan_aktif; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-tools fa-2x mb-2"></i>
                            <h5>Dalam Servis</h5>
                            <h3><?php echo $kendaraan_servis; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-ban fa-2x mb-2"></i>
                            <h5>Non-Aktif</h5>
                            <h3><?php echo $kendaraan_nonaktif; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <!-- Bar Chart -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-center">📈 Distribusi Kendaraan</h5>
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-center">🍩 Persentase Kendaraan</h5>
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-center">📉 Tren Kendaraan Aktif per Bulan</h5>
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Kendaraan -->
            <div class="mt-5">
                <h4 class="mb-3">🚗 Rincian Kendaraan</h4>
                <div class="mb-3">
                    <label for="statusFilter" class="form-label">Filter Berdasarkan Status:</label>
                    <select id="statusFilter" class="form-select" onchange="filterTable()">
                        <option value="all">Semua</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Servis">Servis</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center" id="vehicleTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nomor Polisi</th>
                                <th>Model</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $kendaraan_result->fetch_assoc()): ?>
                            <tr data-status="<?php echo $row['status']; ?>">
                                <td><?php echo $row['id_kendaraan']; ?></td>
                                <td><?php echo $row['nomor_polisi']; ?></td>
                                <td><?php echo $row['model']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS & Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data dari PHP
const totalKendaraan = <?php echo $total_kendaraan; ?>;
const kendaraanAktif = <?php echo $kendaraan_aktif; ?>;
const kendaraanServis = <?php echo $kendaraan_servis; ?>;
const kendaraanNonAktif = <?php echo $kendaraan_nonaktif; ?>;

// Bar Chart
const ctxBar = document.getElementById('barChart').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: ['Aktif', 'Servis', 'Non-Aktif'],
        datasets: [{
            label: 'Jumlah Kendaraan',
            data: [kendaraanAktif, kendaraanServis, kendaraanNonAktif],
            backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
            borderColor: ['#1e7e34', '#d39e00', '#5a6268'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Pie Chart
const ctxPie = document.getElementById('pieChart').getContext('2d');
new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: ['Aktif', 'Servis', 'Non-Aktif'],
        datasets: [{
            data: [kendaraanAktif, kendaraanServis, kendaraanNonAktif],
            backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
        }]
    },
    options: {
        responsive: true
    }
});

// Line Chart (Simulasi Data Tren Kendaraan Aktif)
const ctxLine = document.getElementById('lineChart').getContext('2d');
new Chart(ctxLine, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
        datasets: [{
            label: 'Kendaraan Aktif',
            data: [5, 7, 9, 8, 10, 12, kendaraanAktif],
            borderColor: '#28a745',
            fill: false,
            tension: 0.3
        }]
    },
    options: {
        responsive: true
    }
});

// Filter Tabel Rincian Kendaraan
function filterTable() {
    const filter = document.getElementById("statusFilter").value;
    const rows = document.querySelectorAll("#vehicleTable tbody tr");

    rows.forEach(row => {
        const status = row.getAttribute("data-status");
        if (filter === "all" || status === filter) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

</body>
</html>
