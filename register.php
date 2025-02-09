<?php
// Start session
session_start();

// Include database connection
include 'connect.php';

// Initialize error and success message variables
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_depan = trim($_POST['nama_depan']);
    $nama_belakang = trim($_POST['nama_belakang']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $nomor_telepon = trim($_POST['nomor_telepon']);
    $alamat = trim($_POST['alamat']);
    $tanggal_daftar = date("Y-m-d H:i:s"); // Format timestamp sesuai schema

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM pengguna WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email sudah terdaftar.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Default role for registered users is 'siswa'
            $role = 'siswa';
            $status = 'aktif';

            // Generate username
            $username = strtolower($nama_depan . "." . $nama_belakang);

            // Insert new user
            $sql = "INSERT INTO pengguna (username, email, password, nama_depan, nama_belakang, nomor_telepon, alamat, peran, status, tgl_dibuat) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $username, $email, $hashed_password, $nama_depan, $nama_belakang, $nomor_telepon, $alamat, $role, $status, $tanggal_daftar);

            if ($stmt->execute()) {
                $success_message = "Pendaftaran berhasil! Anda bisa <a href='login.php'>login</a>.";
            } else {
                $error_message = "Pendaftaran gagal. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .card {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }
        .card-header {
            background: #2575fc;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        .btn-primary {
            background-color: #2575fc;
            border-color: #2575fc;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #1a61cc;
            border-color: #1a61cc;
        }
        .mt-3 a {
            color: #2575fc;
            text-decoration: none;
            font-weight: bold;
        }
        .mt-3 a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Register
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?= $success_message; ?>
                            </div>
                        <?php endif; ?>
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="nama_depan" class="form-label">Nama Depan</label>
                                <input type="text" class="form-control" id="nama_depan" name="nama_depan" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama_belakang" class="form-label">Nama Belakang</label>
                                <input type="text" class="form-control" id="nama_belakang" name="nama_belakang" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon">
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                            <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
