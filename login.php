<?php 
// Mulai sesi secara aman
session_start();
session_regenerate_id(true);

// Hubungkan ke database
include 'connect.php';

// Inisialisasi variabel pesan error
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bersihkan input untuk mencegah XSS
    $email_or_username = filter_var($_POST['email_or_username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Validasi format input
    if (empty($email_or_username) || empty($password)) {
        $error_message = "Email/Username dan password tidak boleh kosong.";
    } else {
        // Cek di tabel admin dulu menggunakan username
        $sql_admin = "SELECT * FROM admin WHERE username = ?";
        $stmt_admin = $conn->prepare($sql_admin);
        // print_r($stmt_admin);
        if ($stmt_admin === false) {
            error_log('Query preparation failed: ' . $conn->error);
            $error_message = "Terjadi kesalahan. Silakan coba lagi.";
        } else {
            $stmt_admin->bind_param("s", $email_or_username);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            // Cek apakah admin ada
            if ($result_admin->num_rows > 0) {
                $admin = $result_admin->fetch_assoc();

                // Cek password admin menggunakan MD5
                if (md5($password) === $admin['password']) {
                    $_SESSION['id_user'] = $admin['id'];
                    $_SESSION['peran'] = 'admin';

                    // Arahkan ke dashboard admin
                    header('Location: admin/index.php');
                    exit();
                } else {
                    $error_message = "Password salah untuk admin.";
                }
            } else {
                // Jika bukan admin, cek di tabel users (untuk siswa dan pembahas) menggunakan username atau email
                $sql_user = "SELECT * FROM pengguna WHERE username = ? OR email = ?";
                $stmt_user = $conn->prepare($sql_user);
                
                if ($stmt_user === false) {
                    error_log('Query preparation failed: ' . $conn->error);
                    $error_message = "Terjadi kesalahan. Silakan coba lagi.";
                } else {
                    $stmt_user->bind_param("ss", $email_or_username, $email_or_username);
                    $stmt_user->execute();
                    $result_user = $stmt_user->get_result();

                    if ($result_user->num_rows > 0) {
                        $user = $result_user->fetch_assoc();

                        // Cek password menggunakan password_verify
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['id_user'] = $user['id'];
                            $_SESSION['peran'] = $user['peran'];
echo $user['peran'];
                            // Arahkan ke dashboard berdasarkan peran
                            switch ($user['peran']) {
                                case 'pembahas':
                                    echo "pembahas";
                                    header('Location: pembahas/index.php');
                                    exit();
                                    break;
                                case 'siswa':
                                    header('Location: pengguna/kuis.php');
                                    exit();
                                    break;
                                
                                default:
                                    $error_message = "peran tidak dikenal.";
                                    break;
                            }
                        } else {
                            $error_message = "Password salah untuk pengguna.";
                        }
                    } else {
                        $error_message = "Email atau username belum terdaftar. Silakan <a href='register.php'>daftar di sini</a>.";
                    }
                }
                $stmt_user->close();
            }
            $stmt_admin->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5, #9face6);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: #6c63ff;
            color: #fff;
            border-radius: 15px 15px 0 0;
        }
        .btn-primary {
            background-color: #6c63ff;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #574b90;
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        .container img {
            display: block;
            margin: 0 auto 20px;
            max-width: 120px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                    <img src="assets/images/intro.jpg" alt="Logo">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email_or_username" class="form-label">Email atau Username</label>
                                <input type="text" class="form-control" id="email_or_username" name="email_or_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                            <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
