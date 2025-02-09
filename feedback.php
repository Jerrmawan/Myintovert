<?php
session_start();
include 'header.php';
include '../connect.php';

// Pastikan siswa telah login
if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

$pengguna_id = $_SESSION['id_user'];
$error = "";
$success = "";

// Tangani pengiriman umpan balik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = $_POST['pesan'] ?? '';

    // Validasi input
    if (empty($pesan)) {
        $error = "Pesan tidak boleh kosong.";
    } else {
        $query = "INSERT INTO umpan_balik (pengguna_id, pesan) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $pengguna_id, $pesan);

        if ($stmt->execute()) {
            $success = "Umpan balik berhasil dikirim!";
        } else {
            $error = "Gagal mengirim umpan balik. Silakan coba lagi.";
        }
    }
}

// Ambil umpan balik yang telah dikirim
$query_feedback = "
    SELECT uf.pesan, uf.tanggapan, uf.status, uf.tgl_dibuat
    FROM umpan_balik uf
    WHERE uf.pengguna_id = ?
    ORDER BY uf.tgl_dibuat DESC";
$stmt_feedback = $conn->prepare($query_feedback);
$stmt_feedback->bind_param("i", $pengguna_id);
$stmt_feedback->execute();
$result_feedback = $stmt_feedback->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umpan Balik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Umpan Balik</h1>

    <!-- Form Kirim Umpan Balik -->
    <div class="card mb-4">
        <div class="card-header">Kirim Umpan Balik</div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="pesan" class="form-label">Pesan</label>
                    <textarea name="pesan" id="pesan" class="form-control" rows="4" placeholder="Tulis umpan balik Anda..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Kirim</button>
            </form>
        </div>
    </div>

    <!-- Daftar Umpan Balik -->
    <div class="card">
        <div class="card-header">Umpan Balik yang Telah Dikirim</div>
        <div class="card-body">
            <?php if ($result_feedback->num_rows > 0): ?>
                <?php while ($feedback = $result_feedback->fetch_assoc()): ?>
                    <div class="border rounded p-3 mb-3">
                        <p><strong>Pesan:</strong> <?= htmlspecialchars($feedback['pesan']); ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($feedback['status'])); ?></p>
                        <?php if ($feedback['tanggapan']): ?>
                            <p><strong>Tanggapan:</strong> <?= htmlspecialchars($feedback['tanggapan']); ?></p>
                        <?php endif; ?>
                        <p class="text-muted small">Dikirim pada: <?= $feedback['tgl_dibuat']; ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">Belum ada umpan balik yang dikirim.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
