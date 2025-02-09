<?php
session_start();
require 'header.php'; // Pastikan file ini berisi koneksi database
require '../connect.php'; // Pastikan file ini berisi koneksi database

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php"); // Sesuaikan dengan halaman login Anda
    exit();
}

// Ambil data pengguna berdasarkan session ID
$id_user = $_SESSION['id_user'];
$query = "SELECT * FROM pengguna WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error prepare: ' . $conn->error); // Gunakan $conn, bukan $connect
}
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Proses ketika pengguna mengirimkan formulir pengaturan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['username'];
    $email = $_POST['email'];

    // Proses upload foto profil jika ada file yang diunggah
    if (!empty($_FILES['foto_profil']['name'])) {
        $target_dir = "../pengguna/uploads/"; // Pastikan folder 'uploads' ada di project Anda
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada
        }
        $target_file = $target_dir . basename($_FILES["foto_profil"]["name"]);
        if (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
            $foto_profil = $target_file;
        } else {
            die("Error: Gagal mengunggah file.");
        }
    } else {
        $foto_profil = $user['foto_profil']; // Gunakan foto profil lama jika tidak ada yang diunggah
    }

    // Jika password diubah, proses perubahan
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $query = "UPDATE pengguna SET nama_depan = ?, email = ?, foto_profil = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nama, $email, $foto_profil, $password, $id_user);
    } else {
        $query = "UPDATE pengguna SET nama_depan = ?, email = ?, foto_profil = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $nama, $email, $foto_profil, $id_user);
    }

    if ($stmt->execute()) {
        // Berhasil mengupdate profil
        $_SESSION['username'] = $nama; // Perbarui nama di session
        echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='profil.php';</script>";
    } else {
        echo "Gagal memperbarui profil: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Pengaturan Profil</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= $user['username']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $user['email']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="foto_profil" class="form-label">Foto Profil</label>
                <input type="file" class="form-control" id="foto_profil" name="foto_profil">
                <?php if ($user['foto_profil']): ?>
                    <img src="<?= $user['foto_profil']; ?>" alt="Foto Profil" width="150" class="mt-3">
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi (Opsional)</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah kata sandi">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
