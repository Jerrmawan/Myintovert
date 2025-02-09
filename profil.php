<?php
session_start();
// Pastikan pengguna login
if (!isset($_SESSION['id_user']) || $_SESSION['peran'] != 'siswa') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['id_user'];

function getProfilSiswa()
{
    $host = 'localhost';
    $db = 'e_learning';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
            return json_encode(['error' => 'Pengguna tidak terautentikasi atau tidak memiliki akses.']);
        }

        $id_user = $_SESSION['id_user'];
        $stmt = $pdo->prepare("SELECT username, email, nama_depan, nama_belakang, nomor_telepon, alamat, bio, foto_profil 
                               FROM pengguna 
                               WHERE id = :id AND peran = 'siswa'");
        $stmt->bindParam(':id', $id_user, PDO::PARAM_INT);
        $stmt->execute();

        $profil = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($profil ?: ['error' => 'Profil tidak ditemukan.']);
    } catch (PDOException $e) {
        return json_encode(['error' => 'Kesalahan database: ' . $e->getMessage()]);
    }
}

// Menangani permintaan profil siswa
if (isset($_GET['load_profil'])) {
    header('Content-Type: application/json');
    echo getProfilSiswa();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9;
        }

        /* .header {
            background: #4caf50;
            color: white;
            padding: 1rem;
            text-align: center;
            font-size: 1.5rem;
        } */

        .container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profil-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .profil-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #4caf50;
        }

        .profil-info {
            flex-grow: 1;
        }

        .profil-info h2 {
            margin: 0;
            color: #333;
        }

        .profil-info p {
            margin: 0.5rem 0;
            color: #666;
        }

        .edit-button {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
            transition: background 0.3s;
        }

        .edit-button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    <div class="container" id="profil-container">
        <!-- Profil siswa akan dimuat di sini -->
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const profilContainer = document.getElementById("profil-container");

            // Memuat data profil siswa
            fetch("<?php echo $_SERVER['PHP_SELF']; ?>?load_profil=1")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        profilContainer.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }

                    const fotoProfil = data.foto_profil || "https://via.placeholder.com/120";
                    profilContainer.innerHTML = `
                        <div class="profil-container">
                            <img src="${fotoProfil}" alt="Foto Profil">
                            <div class="profil-info">
                                <h2>${data.nama_depan} ${data.nama_belakang}</h2>
                                <p><strong>Username:</strong> ${data.username}</p>
                                <p><strong>Email:</strong> ${data.email}</p>
                                <p><strong>Telepon:</strong> ${data.nomor_telepon || "Tidak tersedia"}</p>
                                <p><strong>Alamat:</strong> ${data.alamat || "Tidak tersedia"}</p>
                                <p><strong>Bio:</strong> ${data.bio || "Tidak tersedia"}</p>
                                <a href="edit_profil.php" class="edit-button">Edit Profil</a>
                                <a href="view_ujian.php" class="edit-button">Lihat Hasil Ujian</a>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error("Error fetching profile:", error);
                    profilContainer.innerHTML = `<p>Gagal memuat profil.</p>`;
                });
        });
    </script>
</body>
</html>
