<?php
include 'header.php';
// Mulai session
session_start();

// Koneksi ke database
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'e_learning';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Periksa apakah pengguna sudah login dan memiliki peran siswa
if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak. Hanya siswa yang dapat mengakses halaman ini.";
    exit;
}

// Periksa apakah ada permintaan untuk memperbarui status notifikasi
if (isset($_GET['id'])) {
    // Validasi ID notifikasi
    $notifikasi_id = intval($_GET['id']);

    // Update status notifikasi menjadi 'sudah_dibaca'
    $sql_update = "UPDATE notifikasi 
                   SET status = 'sudah_dibaca' 
                   WHERE id = ? AND target_peran = 'siswa' AND status = 'belum_dibaca'";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('i', $notifikasi_id);
    
    if ($stmt->execute()) {
        // Redirect kembali ke halaman notifikasi setelah pembaruan status
        header("Location: notifikasi.php");
        exit;
    } else {
        echo "Gagal memperbarui status notifikasi.";
    }
    $stmt->close();
}

// Fitur Hapus Semua Notifikasi
if (isset($_POST['hapus_semua'])) {
    // Cek jika ada notifikasi yang belum dibaca
    $status_check_sql = "SELECT COUNT(*) as count_belum_dibaca 
                         FROM notifikasi 
                         WHERE target_peran = 'siswa' AND status = 'belum_dibaca'";
    $status_check_result = $conn->query($status_check_sql);
    $status_check = $status_check_result->fetch_assoc();

    if ($status_check['count_belum_dibaca'] > 0) {
        $error_message = "Baca semua notifikasi terlebih dahulu sebelum menghapusnya.";
    } else {
        // Hapus semua notifikasi dari tampilan pengguna (tidak dari database)
        $delete_query = "UPDATE notifikasi 
                         SET status = 'terhapus' 
                         WHERE target_peran = 'siswa' AND status = 'sudah_dibaca'";
        $conn->query($delete_query);
    }
}

// Fitur Pilih Semua Baca
if (isset($_POST['pilih_semua_baca'])) {
    // Update status semua notifikasi menjadi 'sudah_dibaca'
    $sql_update_all = "UPDATE notifikasi 
                       SET status = 'sudah_dibaca' 
                       WHERE target_peran = 'siswa' AND status = 'belum_dibaca'";
    $conn->query($sql_update_all);
}

// Query untuk mengambil notifikasi berdasarkan peran siswa (tanpa target_id) dan menghindari yang status 'terhapus'
$sql = "SELECT id, pesan, jenis, status, tgl_dibuat 
        FROM notifikasi 
        WHERE target_peran = 'siswa' AND status != 'terhapus'
        ORDER BY tgl_dibuat DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .notification-card {
            margin-bottom: 1rem;
        }
        .notification-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-status {
            font-size: 0.8rem;
        }
        .notification-checked {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4 text-center">Notifikasi Siswa</h2>
        <div id="notification-list">
            <?php if (empty($notifications)): ?>
                <div class="alert alert-warning text-center">
                    Tidak ada notifikasi saat ini.
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <?php $is_checked = ($notification['status'] === 'sudah_dibaca') ? 'notification-checked' : ''; ?>
                    <div class="card notification-card <?= $is_checked ?>">
                        <div class="card-header">
                            <span><?= ucwords(str_replace('_', ' ', $notification['jenis'])) ?></span>
                            <span class="badge bg-<?= $notification['status'] === 'belum_dibaca' ? 'danger' : 'success' ?> badge-status">
                                <?= $notification['status'] === 'belum_dibaca' ? 'Belum Dibaca' : 'Sudah Dibaca' ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($notification['pesan']) ?></p>
                            <small class="text-muted">Dibuat pada: <?= date('d M Y, H:i', strtotime($notification['tgl_dibuat'])) ?></small>
                            <?php if ($notification['status'] === 'belum_dibaca'): ?>
                                <div class="mt-3">
                                    <a href="notifikasi.php?id=<?= $notification['id'] ?>" class="btn btn-primary btn-sm">
                                        Tandai Sudah Dibaca
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tombol Hapus Semua dan Pilih Semua Baca -->
        <div class="mt-4 text-center">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-warning"><?= $error_message ?></div>
            <?php endif; ?>
            <form method="POST" class="d-inline">
                <button type="submit" name="hapus_semua" class="btn btn-danger">Hapus Semua Notifikasi</button>
            </form>
            <form method="POST" class="d-inline">
                <button type="submit" name="pilih_semua_baca" class="btn btn-success">Pilih Semua Baca</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
