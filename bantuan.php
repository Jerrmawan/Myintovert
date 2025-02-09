<?php include 'header.php';?>
<?php
session_start();
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "e_learning");
if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

$pengguna_id = intval($_SESSION['id_user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengguna_id = $_SESSION['id_user']; // Ambil ID pengguna dari sesi
    $pesan = htmlspecialchars($_POST['pesan']); // Pesan permintaan bantuan
    $pembahas_id = null; // Awalnya belum ditentukan pembahas

    $stmt = $conn->prepare("INSERT INTO permintaan_bantuan (pengguna_id, pembahas_id, pesan, status) VALUES (?, ?, ?, 'buka')");
    $stmt->bind_param("iis", $pengguna_id, $pembahas_id, $pesan);
    if ($stmt->execute()) {
        echo "<script>alert('Permintaan bantuan berhasil dikirim!');</script>";
    } else {
        echo "<script>alert('Gagal mengirim permintaan bantuan!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan - MY INTROVERT</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Arial', sans-serif;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-submit {
            width: 100%;
            background-color: #4CAF50;
            color: white;
        }
        .faq-item {
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .contact-form input, .contact-form textarea {
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Bantuan -->
    <div class="container my-5">
        <h1 class="text-center">Bantuan</h1>
        
        <!-- FAQ -->
        <section>
            <h2 class="my-4">FAQ - Pertanyaan Umum</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Bagaimana cara memulai belajar?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Untuk memulai belajar, Anda dapat mengunjungi halaman <a href="#">Materi</a> dan memilih kategori yang sesuai dengan level Anda. Ikuti panduan di dalamnya untuk meningkatkan keterampilan interpersonal Anda.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Bagaimana cara mengubah pengaturan akun saya?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Untuk mengubah pengaturan akun, kunjungi halaman <a href="#">Pengaturan</a> di menu navigasi. Anda dapat mengubah preferensi bahasa, tema, dan pengaturan lainnya dari halaman tersebut.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Bagaimana cara menghubungi dukungan?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi kami melalui formulir kontak di bawah atau kirim email ke <a href="mailto:myintrovert29@gmail.com">myintrovert29@gmail.com</a>.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Panduan Pengguna -->
        <section class="my-5">
            <h2 class="mb-4">Panduan Pengguna Interaktif</h2>
            <p>Untuk panduan lengkap mengenai penggunaan platform kami, Anda dapat mengikuti tutorial interaktif di bawah ini:</p>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tutorial Video</h5>
                    <p class="card-text">Tonton video panduan singkat kami untuk memahami fitur-fitur utama.</p>
                    <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="btn btn-primary" target="_blank">Tonton Video</a>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Panduan PDF</h5>
                    <p class="card-text">Unduh panduan pengguna lengkap dalam format PDF untuk referensi offline.</p>
                    <a href="artikel.php" class="btn btn-primary">Unduh Panduan</a>
                </div>
            </div>
        </section>

        <!-- Formulir Kontak -->
        <section class="my-5">
            <h2>Formulir Kontak</h2>
            <form method="POST">
            <div class="mb-3">
                <label for="pesan" class="form-label">Pesan Bantuan</label>
                <textarea class="form-control" id="pesan" name="pesan" rows="5" placeholder="Tuliskan pesan Anda..." required></textarea>
            </div>
            <button type="submit" class="btn btn-submit">Kirim Permintaan</button>
        </form>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; 2024 MY INTROVERT. All Rights Reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>
