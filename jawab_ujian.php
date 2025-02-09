<?php
include 'header.php';
include '../connect.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

$pengguna_id = intval($_SESSION['id_user']);

if (isset($_GET['kuis_id'])) {
    $kuis_id = intval($_GET['kuis_id']);

    // Periksa apakah siswa sudah menyelesaikan ujian ini
    $query_cek_hasil = "SELECT id FROM hasil_kuis WHERE pengguna_id = ? AND kuis_id = ?";
    $stmt_cek_hasil = $conn->prepare($query_cek_hasil);
    $stmt_cek_hasil->bind_param("ii", $pengguna_id, $kuis_id);
    $stmt_cek_hasil->execute();
    $result_cek_hasil = $stmt_cek_hasil->get_result();

    if ($result_cek_hasil->num_rows > 0) {
        echo "<script>alert('Anda sudah menyelesaikan ujian ini. Anda harus melanjutkan ke ujian berikutnya.');</script>";
        echo "<script>window.location.href = 'ujian.php';</script>"; // Ganti dengan halaman yang sesuai
        exit;
    }
} else {
    echo "Kuis tidak ditemukan.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kuis_id = intval($_POST['kuis_id']);
    $jawaban = $_POST['jawaban'];
    $skor = 0;

    // Ambil tingkat kuis
    $query_tingkat = "SELECT tingkat FROM kuis WHERE id = ?";
    $stmt_tingkat = $conn->prepare($query_tingkat);
    $stmt_tingkat->bind_param("i", $kuis_id);
    $stmt_tingkat->execute();
    $result_tingkat = $stmt_tingkat->get_result();
    $data_tingkat = $result_tingkat->fetch_assoc();
    $tingkat = $data_tingkat['tingkat'];

    // Simpan hasil kuis baru
    $query_hasil = "INSERT INTO hasil_kuis (pengguna_id, kuis_id, skor) VALUES (?, ?, 0)";
    $stmt_hasil = $conn->prepare($query_hasil);
    $stmt_hasil->bind_param("ii", $pengguna_id, $kuis_id);
    $stmt_hasil->execute();
    $hasil_kuis_id = $stmt_hasil->insert_id;

    foreach ($jawaban as $pertanyaan_id => $opsi_dipilih) {
        $query_pertanyaan = "SELECT opsi_benar FROM pertanyaan_kuis WHERE id = ?";
        $stmt_pertanyaan = $conn->prepare($query_pertanyaan);
        $stmt_pertanyaan->bind_param("i", $pertanyaan_id);
        $stmt_pertanyaan->execute();
        $result_pertanyaan = $stmt_pertanyaan->get_result();
        $data = $result_pertanyaan->fetch_assoc();

        $benar = $data['opsi_benar'] === $opsi_dipilih;
        if ($benar) {
            $skor++;
        }

        $query_jawaban = "INSERT INTO jawaban_kuis (hasil_kuis_id, pertanyaan_id, opsi_dipilih, benar) VALUES (?, ?, ?, ?)";
        $stmt_jawaban = $conn->prepare($query_jawaban);
        $stmt_jawaban->bind_param("iisi", $hasil_kuis_id, $pertanyaan_id, $opsi_dipilih, $benar);
        $stmt_jawaban->execute();
    }

    // Update skor hasil kuis
    $query_update_hasil = "UPDATE hasil_kuis SET skor = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update_hasil);
    $stmt_update->bind_param("ii", $skor, $hasil_kuis_id);
    $stmt_update->execute();

    // Hitung dan simpan kemajuan
    $query_total_kuis = "SELECT COUNT(*) AS total FROM kuis WHERE tingkat = ?";
    $stmt_total_kuis = $conn->prepare($query_total_kuis);
    $stmt_total_kuis->bind_param("s", $tingkat);
    $stmt_total_kuis->execute();
    $result_total_kuis = $stmt_total_kuis->get_result();
    $total_kuis = $result_total_kuis->fetch_assoc()['total'];

    $query_selesai = "SELECT COUNT(*) AS selesai FROM hasil_kuis 
                      INNER JOIN kuis ON hasil_kuis.kuis_id = kuis.id 
                      WHERE hasil_kuis.pengguna_id = ? AND kuis.tingkat = ?";
    $stmt_selesai = $conn->prepare($query_selesai);
    $stmt_selesai->bind_param("is", $pengguna_id, $tingkat);
    $stmt_selesai->execute();
    $result_selesai = $stmt_selesai->get_result();
    $kuis_selesai = $result_selesai->fetch_assoc()['selesai'];

    $persentase = ($kuis_selesai / $total_kuis) * 100;

    // Periksa apakah ada kemajuan untuk tingkat ini
    $query_cek_kemajuan = "SELECT id FROM kemajuan WHERE pengguna_id = ? AND tingkat = ?";
    $stmt_cek_kemajuan = $conn->prepare($query_cek_kemajuan);
    $stmt_cek_kemajuan->bind_param("is", $pengguna_id, $tingkat);
    $stmt_cek_kemajuan->execute();
    $result_kemajuan = $stmt_cek_kemajuan->get_result();

    if ($result_kemajuan->num_rows > 0) {
        // Update persentase kemajuan
        $query_update_kemajuan = "UPDATE kemajuan SET persentase = ? WHERE pengguna_id = ? AND tingkat = ?";
        $stmt_update_kemajuan = $conn->prepare($query_update_kemajuan);
        $stmt_update_kemajuan->bind_param("iis", $persentase, $pengguna_id, $tingkat);
        $stmt_update_kemajuan->execute();
    } else {
        // Tambahkan kemajuan baru
        $query_insert_kemajuan = "INSERT INTO kemajuan (pengguna_id, tingkat, persentase) VALUES (?, ?, ?)";
        $stmt_insert_kemajuan = $conn->prepare($query_insert_kemajuan);
        $stmt_insert_kemajuan->bind_param("isi", $pengguna_id, $tingkat, $persentase);
        $stmt_insert_kemajuan->execute();
    }

    echo "<script>alert('Ujian selesai! Skor Anda: $skor');</script>";
    echo "<script>window.location.href = 'hasil_ujian.php?kuis_id=$kuis_id';</script>";
    exit;
}

if (!isset($_GET['kuis_id'])) {
    echo "Kuis tidak ditemukan.";
    exit;
}

$kuis_id = intval($_GET['kuis_id']);

$query_kuis = "SELECT judul, deskripsi FROM kuis WHERE id = ?";
$stmt_kuis = $conn->prepare($query_kuis);
$stmt_kuis->bind_param("i", $kuis_id);
$stmt_kuis->execute();
$result_kuis = $stmt_kuis->get_result();
$kuis = $result_kuis->fetch_assoc();

$query_pertanyaan = "SELECT id, pertanyaan, opsi1, opsi2, opsi3, opsi4 FROM pertanyaan_kuis WHERE kuis_id = ?";
$stmt_pertanyaan = $conn->prepare($query_pertanyaan);
$stmt_pertanyaan->bind_param("i", $kuis_id);
$stmt_pertanyaan->execute();
$result_pertanyaan = $stmt_pertanyaan->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kuis['judul']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4 class="text-center"><?= htmlspecialchars($kuis['judul']); ?></h4>
            <p class="text-center mb-0"><?= htmlspecialchars($kuis['deskripsi']); ?></p>
        </div>
        <div class="card-body">
            <form action="" method="POST" id="form-quiz">
                <input type="hidden" name="kuis_id" value="<?= $kuis_id; ?>">
                <?php while ($pertanyaan = $result_pertanyaan->fetch_assoc()) { ?>
                    <div class="mb-4">
                        <h5><?= htmlspecialchars($pertanyaan['pertanyaan']); ?></h5>
                        <?php for ($i = 1; $i <= 4; $i++) { 
                            $opsi = 'opsi' . $i; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jawaban[<?= $pertanyaan['id']; ?>]" 
                                       value="opsi<?= $i; ?>" id="jawaban_<?= $pertanyaan['id']; ?>_<?= $i; ?>" required
                                       aria-label="<?= htmlspecialchars($pertanyaan[$opsi]); ?>">
                                <label class="form-check-label" for="jawaban_<?= $pertanyaan['id']; ?>_<?= $i; ?>">
                                    <?= htmlspecialchars($pertanyaan[$opsi]); ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-success">Kirim Jawaban</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Tambahkan validasi tambahan jika diperlukan
    const form = document.getElementById('form-quiz');
    form.addEventListener('submit', function (event) {
        const inputs = form.querySelectorAll('input[type="radio"]:checked');
        const totalQuestions = <?= $result_pertanyaan->num_rows; ?>;
        if (inputs.length < totalQuestions) {
            alert('Harap jawab semua pertanyaan sebelum mengirimkan!');
            event.preventDefault();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
