<?php include '../pengguna/header.php'; ?>
<?php
session_start();
include '../connect.php';

// Cek apakah pengguna telah login dengan memeriksa user_id dalam sesi
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];

    // Query untuk mendapatkan nama (username) dan role pengguna
    $query = "SELECT username AS nama, peran AS pengguna FROM pengguna WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->close();
}

// Ambil data progres kemajuan per tingkat
$levels = ['dasar', 'menengah', 'lanjutan'];
$progress_total = 0;
$level_count = 0;

foreach ($levels as $level) {
    $query = "SELECT persentase FROM kemajuan WHERE pengguna_id = ? AND tingkat = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $id_user, $level);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Cek apakah ada data progres untuk tingkat ini
    if ($row = $result->fetch_assoc()) {
        $progress_total += $row['persentase'];
        $level_count++;
    }
    $stmt->close();
}

// Hitung rata-rata progres jika ada data untuk semua tingkat
if ($level_count > 0) {
    $average_progress = $progress_total / $level_count;
} else {
    $average_progress = 0;  // Jika tidak ada data, set progres ke 0
}

// Ambil materi terkini
$materials_query = "SELECT * FROM materi ORDER BY tgl_dibuat DESC LIMIT 3";
$materials_result = $conn->query($materials_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - MY INTROVERT</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        .progress {
            height: 30px;
        }
        .progress-bar {
            background-color: #007bff; /* Color for progress bar */
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .hero {
            background-image: url('../assets/images/myintrovert.jpg');
            background-size: cover;
            background-position: center;
            margin-top: 0px;
            color: white;
            padding: 200px 0; /* Menambah padding atas dan bawah */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Menambah efek bayangan */
        }
        .hero h1 {
            font-size: 3rem;
        }
        .testimonial {
            background: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero text-center">
        <div class="container">
            <h1>Selamat Datang di MY INTROVERT</h1>
            <p class="lead">Tingkatkan kemampuan interpersonal dan keterampilan sosial Anda dengan belajar bersama kami.</p>
            <a href="#" class="btn btn-light btn-lg">Mulai Belajar</a>
        </div>
    </section>

    <!-- Progres Belajar -->
    <section class="container my-5">
        <h2 class="text-center">Progres Belajar Anda</h2>
        <div class="progress my-4">
            <div class="progress-bar" role="progressbar" style="width: <?php echo $average_progress; ?>%;" aria-valuenow="<?php echo $average_progress; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $average_progress; ?>% Selesai</div>
        </div>
    </section>
    
    <!-- Materi Terkini -->
    <section class="container my-5">
        <h2 class="text-center">Materi Terkini</h2>
        <div class="row">
            <?php
            // Query untuk mengambil 1 materi teratas dari setiap tingkat
            foreach ($levels as $level) {
                $materials_query = "SELECT * FROM materi WHERE tingkat = ? ORDER BY tgl_dibuat DESC LIMIT 1";
                $stmt = $conn->prepare($materials_query);
                $stmt->bind_param("s", $level);
                $stmt->execute();
                $materials_result = $stmt->get_result();
                $material = $materials_result->fetch_assoc(); // Ambil hanya satu materi

                if ($material): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php if ($material['jenis_file'] === 'pdf'): ?>
                                <a href="../uploads/article/<?php echo $material['lokasi_file']; ?>" target="_blank">
                                    <img src="../assets/images/pdf_icon.png" class="card-img-top" alt="<?php echo htmlspecialchars($material['judul']); ?>" style="object-fit: cover; height: 200px;">
                                </a>
                            <?php elseif ($material['jenis_file'] === 'video'): ?>
                                <video class="card-img-top" controls style="height: 200px; object-fit: cover;">
                                    <source src="../videos/<?php echo $material['lokasi_file']; ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: // Untuk tipe artikel ?>
                                <img src="../assets/images/article_icon.png" class="card-img-top" alt="<?php echo htmlspecialchars($material['judul']); ?>" style="object-fit: cover; height: 200px;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($material['judul']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($material['deskripsi']); ?></p>
                                <!-- Ubah tombol untuk tipe artikel -->
                                <a href="<?php echo $material['jenis_file'] === 'artikel' ? '../article/' . $material['lokasi_file'] : 'material_detail.php?id=' . $material['id']; ?>" class="btn btn-primary">
                                    <?php echo $material['jenis_file'] === 'artikel' ? 'Unduh Artikel' : 'Pelajari Sekarang'; ?>
                                </a>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="materi.php?level=<?php echo $level; ?>" class="btn btn-secondary">Lihat Semua <?php echo ucfirst($level); ?> Materials</a>
                        </div>
                    </div>
                <?php endif; // Tutup pengecekan apakah materi ada ?>
            <?php } ?>
        </div>
    </section>

    <!-- Testimoni/Quotes -->
    <section class="bg-primary py-5">
        <div class="container text-center">
            <h2 class="mb-4 text-white">Apa Kata Pengguna Kami</h2>
            <blockquote class="testimonial text-white">
                <p>"MY INTROVERT membantu saya untuk lebih percaya diri dalam berkomunikasi dengan orang lain. Ini benar-benar membantu!"</p>
                <footer class="blockquote-footer">Seorang Pengguna</footer>
            </blockquote>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; 2024 MY INTROVERT. All Rights Reserved.</p>
    </footer>

    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>
