<?php
session_start();
include 'header.php';
include '../connect.php';

// Pastikan siswa telah login
if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

$diskusi_id = $_GET['id'] ?? 0;

// Ambil detail diskusi
$query_diskusi = "
    SELECT d.judul, d.isi, d.tgl_dibuat, p.username AS pembuat 
    FROM diskusi d
    LEFT JOIN pengguna p ON d.pembahas_id = p.id
    WHERE d.id = ?";
$stmt = $conn->prepare($query_diskusi);
$stmt->bind_param("i", $diskusi_id);
$stmt->execute();
$diskusi = $stmt->get_result()->fetch_assoc();

if (!$diskusi) {
    echo "Diskusi tidak ditemukan.";
    exit;
}

// Ambil komentar pada diskusi ini
$query_komentar = "
    SELECT k.isi, k.balasan_komentar, k.tgl_dibuat, u.username AS pengomentar 
    FROM komentar k
    LEFT JOIN pengguna u ON k.pengguna_id = u.id
    WHERE k.diskusi_id = ?
    ORDER BY k.tgl_dibuat ASC";
$stmt_komentar = $conn->prepare($query_komentar);
$stmt_komentar->bind_param("i", $diskusi_id);
$stmt_komentar->execute();
$result_komentar = $stmt_komentar->get_result();

$error = ""; // Variabel untuk menampilkan pesan kesalahan

// Tambah komentar jika metode adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengguna_id = $_SESSION['id_user'];
    $isi = $_POST['isi'] ?? '';

    // Validasi input
    if (empty($isi)) {
        $error = "Komentar tidak boleh kosong.";
    } else {
        // Simpan komentar ke database
        $query = "INSERT INTO komentar (diskusi_id, pengguna_id, isi, balasan_komentar) VALUES (?, ?, ?, '')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $diskusi_id, $pengguna_id, $isi);

        if ($stmt->execute()) {
            // Redirect setelah komentar berhasil ditambahkan
            header("Location: views_diskusi.php?id=$diskusi_id");
            exit;
        } else {
            $error = "Gagal menyimpan komentar. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($diskusi['judul']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .introvert-friendly {
            background-color: #e9f5fb;
            border: 2px solid #6cb2eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="introvert-friendly">
        <h2><?= htmlspecialchars($diskusi['judul']); ?></h2>
        <p class="text-muted">Oleh: <?= htmlspecialchars($diskusi['pembuat']); ?> | <?= $diskusi['tgl_dibuat']; ?></p>
        <div>
            <p><?= htmlspecialchars($diskusi['isi']); ?></p>
        </div>
    </div>

    <h4>Komentar:</h4>
    <div>
        <?php while ($komentar = $result_komentar->fetch_assoc()) { ?>
            <div class="introvert-friendly">
                <p><strong><?= htmlspecialchars($komentar['pengomentar']); ?>:</strong></p>
                <p><?= htmlspecialchars($komentar['isi']); ?></p>
                <?php if (!empty($komentar['balasan_komentar'])): ?>
                    <div class="mt-3 p-3 border-start">
                        <p><strong>Balasan:</strong> <?= htmlspecialchars($komentar['balasan_komentar']); ?></p>
                    </div>
                <?php endif; ?>
                <p class="text-muted small">Dikirim pada: <?= $komentar['tgl_dibuat']; ?></p>
            </div>
        <?php } ?>
    </div>

    <h4>Tambahkan Komentar:</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="" method="post">
        <input type="hidden" name="diskusi_id" value="<?= $diskusi_id; ?>">
        <div class="mb-3">
            <textarea name="isi" class="form-control" rows="4" placeholder="Tulis komentar Anda..." required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Kirim Komentar</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
