<?php
session_start();
include 'header.php';
include '../connect.php';

// Pastikan siswa telah login
if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

// Ambil daftar diskusi dari database
$query_diskusi = "
    SELECT d.id, d.judul, LEFT(d.isi, 100) AS isi_ringkas, d.tgl_dibuat, p.username AS pembuat 
    FROM diskusi d
    LEFT JOIN pengguna p ON d.pembahas_id = p.id
    ORDER BY d.tgl_dibuat DESC";
$result_diskusi = $conn->query($query_diskusi);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Diskusi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Forum Diskusi</h2>
    <div class="row">
        <?php if ($result_diskusi->num_rows > 0): ?>
            <?php while ($diskusi = $result_diskusi->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($diskusi['judul']); ?></h5>
                            <p class="card-text"><?= htmlspecialchars($diskusi['isi_ringkas']); ?>...</p>
                            <p class="text-muted">Oleh: <?= htmlspecialchars($diskusi['pembuat']); ?> | <?= $diskusi['tgl_dibuat']; ?></p>
                            <a href="views_diskusi.php?id=<?= $diskusi['id']; ?>" class="btn btn-primary">Lihat Diskusi</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <h4 class="alert-heading">Belum Ada Diskusi Tersedia</h4>
                    <p>Saat ini belum ada diskusi yang tersedia di forum ini. Ayo jadi yang pertama memulai diskusi! Klik tombol di bawah ini untuk memulai diskusi baru.</p>
                    <a href="create_diskusi.php" class="btn btn-success">Mulai Diskusi Baru</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
