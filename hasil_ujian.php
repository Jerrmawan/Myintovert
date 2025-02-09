<?php
session_start();
include '../connect.php';
include 'header.php';

if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak!";
    exit;
}

if (!isset($_GET['kuis_id'])) {
    echo "Kuis tidak ditemukan.";
    exit;
}

$kuis_id = intval($_GET['kuis_id']);
$pengguna_id = $_SESSION['id_user'];

// Ambil hasil kuis
$query_hasil = "SELECT skor FROM hasil_kuis WHERE pengguna_id = ? AND kuis_id = ?";
$stmt_hasil = $conn->prepare($query_hasil);
$stmt_hasil->bind_param("ii", $pengguna_id, $kuis_id);
$stmt_hasil->execute();
$result_hasil = $stmt_hasil->get_result();
$hasil = $result_hasil->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 text-center">
    <h2>Hasil Ujian</h2>
    <p>Skor Anda: <?= $hasil['skor']; ?></p>
    <a href="ujian.php" class="btn btn-primary">Kembali ke Daftar Ujian</a>
</div>
</body>
</html>
