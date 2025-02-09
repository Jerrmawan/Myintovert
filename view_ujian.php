<?php
session_start();
include '../connect.php';
include 'header.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

// Ambil ID pengguna dari sesi
$id_user = $_SESSION['id_user'];

// Query untuk mengambil hasil kuis
$sql = "
    SELECT hk.skor, hk.tgl_dibuat, k.judul, k.tingkat
    FROM hasil_kuis hk
    JOIN kuis k ON hk.kuis_id = k.id
    WHERE hk.pengguna_id = ?
    ORDER BY hk.tgl_dibuat DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<style>
    body {
    font-family: 'Arial', sans-serif;
    background-color: #f7f9fc;
}

h1 {
    color: #333;
}

.card {
    background-color: #ffffff;
    border-radius: 10px;
}

.table th, .table td {
    vertical-align: middle;
}

</style>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-light">
    <div class="container">
        <div class="container mt-5">
            <h1 class="text-center mb-4">Hasil Ujian Anda</h1>
            <div class="card p-4 shadow">
                <?php if ($result->num_rows > 0): ?>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Judul Kuis</th>
                                <th>Tingkat</th>
                                <th>Skor</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['judul']); ?></td>
                                <td><?= ucfirst($row['tingkat']); ?></td>
                                <td><?= $row['skor']; ?></td>
                                <td><?= date("d-m-Y H:i", strtotime($row['tgl_dibuat'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">Belum ada hasil ujian.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p>&copy; 2024 MY INTROVERT</p>
</footer>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

