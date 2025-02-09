<?php
session_start();
include '../connect.php';

// Ambil id_ujian dan durasi dari POST
$id_ujian = isset($_POST['id_ujian']) ? intval($_POST['id_ujian']) : 0;
$durasi = isset($_POST['durasi']) ? intval($_POST['durasi']) : 0;

// Cek jika id_ujian valid
if ($id_ujian > 0) {
    // Ambil detail ujian
    $sql_ujian = "SELECT * FROM ujian WHERE id_ujian = ?";
    $stmt_ujian = $conn->prepare($sql_ujian);
    $stmt_ujian->bind_param("i", $id_ujian);
    $stmt_ujian->execute();
    $ujian_result = $stmt_ujian->get_result();
    $ujian = $ujian_result->fetch_assoc();

    // Ambil soal ujian
    $sql_soal = "SELECT * FROM soal_ujian WHERE id_ujian = ?";
    $stmt_soal = $conn->prepare($sql_soal);
    $stmt_soal->bind_param("i", $id_ujian);
    $stmt_soal->execute();
    $soal_result = $stmt_soal->get_result();

    // Simpan jawaban pengguna
    $jawaban = [];
    while ($soal = $soal_result->fetch_assoc()) {
        $id_soal = $soal['id_soal'];
        $jawaban[$id_soal] = isset($_POST["soal_$id_soal"]) ? intval($_POST["soal_$id_soal"]) : null; // Ambil ID pilihan jawaban
    }

    // Tampilkan konfirmasi
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Konfirmasi Ujian</title>
        <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f4f4f4;
                color: #333;
                font-family: Arial, sans-serif;
            }
            .container {
                max-width: 800px;
                margin-top: 20px;
            }
            .card {
                margin-bottom: 20px;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid #ddd;
            }
            .card-header {
                background-color: #007bff;
                color: white;
                padding: 15px;
                font-size: 1.25rem;
            }
            .btn-submit {
                background-color: #28a745;
                color: white;
                border: none;
            }
            .btn-submit:hover {
                background-color: #218838;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="text-center mb-4">Konfirmasi Ujian - <?php echo htmlspecialchars($ujian['judul_ujian']); ?></h1>

            <div class="card">
                <div class="card-header">Detail Jawaban Anda</div>
                <div class="card-body">
                    <form method="post" action="submit_ujian.php">
                        <input type="hidden" name="id_ujian" value="<?php echo $id_ujian; ?>">
                        <input type="hidden" name="durasi" value="<?php echo $durasi; ?>">
                        
                        <?php
                        // Tampilkan soal dan jawaban
                        while ($soal = $soal_result->fetch_assoc()) {
                            $id_soal = $soal['id_soal'];
                            echo "<p><strong>Soal:</strong> " . htmlspecialchars($soal['soal']) . "</p>";
                            echo "<p><strong>Jawaban Anda:</strong> " . htmlspecialchars($jawaban[$id_soal]) . "</p>";
                            echo "<input type='hidden' name='soal_$id_soal' value='" . htmlspecialchars($jawaban[$id_soal]) . "'>";
                        }
                        ?>

                        <div class="text-center">
                            <button type="submit" class="btn btn-submit">Kirim Jawaban</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="../assets/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} else {
    echo "<p>ID ujian tidak valid.</p>";
}

// Tutup koneksi
$conn->close();
?>
