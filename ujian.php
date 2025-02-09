<?php
session_start();
include 'header.php';
include '../connect.php';

if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

$pengguna_id = $_SESSION['id_user'];

// Ambil daftar kuis yang tersedia
$query_kuis = "SELECT id, judul, deskripsi, tingkat FROM kuis";
$result_kuis = $conn->query($query_kuis);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f6f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 30px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .card-text {
            font-size: 0.95rem;
            color: #7f8c8d;
        }
        .btn-primary {
            background-color: #74b9ff;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0984e3;
        }
        .text-muted {
            font-size: 0.85rem;
        }
        h2 {
            color: #2d3436;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Pilih Ujian</h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($kuis = $result_kuis->fetch_assoc()) { ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($kuis['judul']); ?></h5>
                        <p class="card-text"><?= htmlspecialchars($kuis['deskripsi']); ?></p>
                        <p class="text-muted mt-auto">Tingkat: <?= htmlspecialchars($kuis['tingkat']); ?></p>
                        <a href="jawab_ujian.php?kuis_id=<?= $kuis['id']; ?>" class="btn btn-primary w-100 mt-3">Mulai Ujian</a>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if ($result_kuis->num_rows === 0): ?>
            <div class="col-12 text-center">
                <p class="text-muted">Tidak ada kuis tersedia saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
