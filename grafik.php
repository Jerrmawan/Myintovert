<?php
session_start();
include 'header.php';
include '../connect.php';

if (!isset($_SESSION['id_user']) || $_SESSION['peran'] !== 'siswa') {
    echo "Akses ditolak! Harap login sebagai siswa.";
    exit;
}

$pengguna_id = $_SESSION['id_user'];

// Ambil data kemajuan siswa
$query_kemajuan = "SELECT tingkat, persentase FROM kemajuan WHERE pengguna_id = ?";
$stmt_kemajuan = $conn->prepare($query_kemajuan);
$stmt_kemajuan->bind_param("i", $pengguna_id);
$stmt_kemajuan->execute();
$result_kemajuan = $stmt_kemajuan->get_result();

$tingkat = [];
$persentase = [];
$total_persentase = 0;
$count = 0;
while ($row = $result_kemajuan->fetch_assoc()) {
    $tingkat[] = $row['tingkat'];
    $persentase[] = $row['persentase'];
    $total_persentase += $row['persentase'];
    $count++;
}

// Hitung rata-rata persentase
$rata_rata_persentase = $count > 0 ? $total_persentase / $count : 0;

// Ambil data skor kuis siswa
$query_kuis = "SELECT k.judul, h.skor FROM hasil_kuis h
               JOIN kuis k ON h.kuis_id = k.id
               WHERE h.pengguna_id = ?";
$stmt_kuis = $conn->prepare($query_kuis);
$stmt_kuis->bind_param("i", $pengguna_id);
$stmt_kuis->execute();
$result_kuis = $stmt_kuis->get_result();

$judul_kuis = [];
$skor_kuis = [];
while ($row = $result_kuis->fetch_assoc()) {
    $judul_kuis[] = $row['judul'];
    $skor_kuis[] = $row['skor'];
}

// Tutup koneksi database
$stmt_kemajuan->close();
$stmt_kuis->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Peningkatan Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #eef5f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
        }
        .card-title {
            font-weight: 600;
            color: #44576d;
        }
        h2 {
            color: #374a60;
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Grafik Peningkatan Siswa</h2>
    
    <div class="row">
        <!-- Grafik Kemajuan -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h5 class="card-title text-center">Kemajuan Siswa</h5>
                <canvas id="grafikKemajuan"></canvas>
                <div id="kemajuanNoData" class="no-data d-none">Data tidak tersedia</div>
            </div>
        </div>

        <!-- Grafik Skor Kuis -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h5 class="card-title text-center">Skor Kuis</h5>
                <canvas id="grafikSkorKuis"></canvas>
                <div id="skorNoData" class="no-data d-none">Data tidak tersedia</div>
            </div>
        </div>
    </div>
</div>

<script>
// Data dari PHP
const tingkat = <?php echo json_encode($tingkat); ?>;
const persentase = <?php echo json_encode($persentase); ?>;
const rataRataPersentase = <?php echo $rata_rata_persentase; ?>;
const judulKuis = <?php echo json_encode($judul_kuis); ?>;
const skorKuis = <?php echo json_encode($skor_kuis); ?>;

// Fungsi untuk mengecek data dan menampilkan pesan jika kosong
function checkDataAndDisplayMessage(data, noDataElementId, chartElementId) {
    const noDataElement = document.getElementById(noDataElementId);
    const chartElement = document.getElementById(chartElementId);
    if (!data || data.length === 0) {
        noDataElement.classList.remove('d-none');
        chartElement.classList.add('d-none');
    } else {
        noDataElement.classList.add('d-none');
        chartElement.classList.remove('d-none');
    }
}

// Grafik Kemajuan
checkDataAndDisplayMessage(persentase, 'kemajuanNoData', 'grafikKemajuan');
if (persentase.length > 0) {
    const ctxKemajuan = document.getElementById('grafikKemajuan').getContext('2d');
    new Chart(ctxKemajuan, {
        type: 'line',
        data: {
            labels: tingkat,
            datasets: [{
                label: `Persentase Kemajuan (%) (Rata-rata: ${rataRataPersentase.toFixed(2)}%)`,
                data: persentase,
                borderColor: 'rgba(93, 164, 226, 1)',
                backgroundColor: 'rgba(93, 164, 226, 0.3)',
                borderWidth: 2,
                pointBackgroundColor: '#5DA4E2',
                pointHoverBackgroundColor: '#fff',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    max: 100,
                    beginAtZero: true,
                },
            },
        },
    });
}

// Grafik Skor Kuis
checkDataAndDisplayMessage(skorKuis, 'skorNoData', 'grafikSkorKuis');
if (skorKuis.length > 0) {
    const ctxSkorKuis = document.getElementById('grafikSkorKuis').getContext('2d');
    new Chart(ctxSkorKuis, {
        type: 'bar',
        data: {
            labels: judulKuis,
            datasets: [{
                label: 'Skor Kuis',
                data: skorKuis,
                backgroundColor: 'rgba(252, 192, 118, 0.6)',
                borderColor: 'rgba(252, 192, 118, 1)',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    max: 1,
                    beginAtZero: true,
                },
            },
        },
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
