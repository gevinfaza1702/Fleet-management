<?php
session_start();
include 'config/db.php'; // Koneksi ke database

// Query Kendaraan, Perjalanan Aktif, dan Konsumsi Bahan Bakar
$monitoring_query = "SELECT k.id_kendaraan, k.nomor_polisi, k.model, k.status, 
                            p.lokasi_awal, p.lokasi_tujuan, pg.nama AS pengemudi, 
                            kb.jumlah_bahan_bakar, kb.jarak_tempuh
                     FROM kendaraan k
                     LEFT JOIN perjalanan p ON k.id_kendaraan = p.id_kendaraan AND p.waktu_tiba IS NULL
                     LEFT JOIN pengemudi pg ON p.id_pengemudi = pg.id_pengemudi
                     LEFT JOIN konsumsi_bahan_bakar kb ON k.id_kendaraan = kb.id_kendaraan";
$monitoring_result = $conn->query($monitoring_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Armada</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        /* Map Modal */
        #map {
            width: 100%;
            height: 400px;
        }

        .modal-header {
            background-color: #343a40;
            color: white;
        }

        .btn-lacak {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
        }

        .btn-lacak:hover {
            background-color: #0056b3;
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
            <a href="monitoring.php" class="list-group-item list-group-item-action bg-dark text-white active">
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

        <div class="container-fluid px-4 mt-4">

            <!-- Title -->
            <h2 class="fw-bold text-center mb-4">📍 Monitoring Armada</h2>

            <!-- Tabel Monitoring -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Nomor Polisi</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Asal Pengiriman</th> <!-- Kolom Baru -->
                            <th>Lokasi Tujuan</th>
                            <th>Pengemudi</th>
                            <th>Konsumsi BBM (L)</th>
                            <th>Jarak Tempuh (Km)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $monitoring_result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nomor_polisi']); ?></td>
                                <td><?php echo htmlspecialchars($row['model']); ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    $badge_class = ($status == 'Aktif') ? 'success' : (($status == 'Servis') ? 'warning' : 'secondary');
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['lokasi_awal'] ?? 'Belum ditentukan'); ?></td> <!-- Asal Pengiriman -->
                                <td><?php echo htmlspecialchars($row['lokasi_tujuan'] ?? 'Tidak ada perjalanan'); ?></td>
                                <td><?php echo htmlspecialchars($row['pengemudi'] ?? 'Belum ditetapkan'); ?></td>
                                <td><?php echo htmlspecialchars($row['jumlah_bahan_bakar'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($row['jarak_tempuh'] ?? '0'); ?></td>
                                <td>
                                    <!-- Tombol Lacak -->
                                    <button class="btn-lacak" onclick="showMap('<?php echo $row['nomor_polisi']; ?>')">
                                        <i class="fas fa-map-marker-alt"></i> Lacak
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Map -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📍 Lokasi Armada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    let map;
    let marker;

    // Fungsi untuk menampilkan modal map dan lacak kendaraan
    function showMap(nomorPolisi) {
        // Simulasi koordinat kendaraan (bisa diambil dari DB)
        const locations = {
            'B 1234 XYZ': { lat: -6.200000, lng: 106.816666 },
            'D 5678 ABC': { lat: -6.914744, lng: 107.609810 },
            'F 9876 DEF': { lat: -7.250445, lng: 112.768845 }
        };

        const coords = locations[nomorPolisi] || { lat: -6.200000, lng: 106.816666 }; // Default ke Jakarta

        // Tampilkan modal
        const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
        mapModal.show();

        // Inisialisasi Leaflet Map
        setTimeout(() => {
            if (map) {
                map.remove();
            }

            map = L.map('map').setView([coords.lat, coords.lng], 13);

            // Tambahkan Layer dari OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Tambahkan Marker
            marker = L.marker([coords.lat, coords.lng]).addTo(map)
                .bindPopup(`<b>${nomorPolisi}</b><br>Lokasi Armada.`)
                .openPopup();
        }, 500); // Delay untuk memastikan modal sudah terbuka
    }
</script>

</body>
</html>
