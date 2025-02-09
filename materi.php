<?php
include 'header.php';
include '../connect.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    die(json_encode(['success' => false, 'message' => 'Pengguna belum login']));
}

$id_user = $_SESSION['id_user'];

// Fungsi untuk memperbarui tabel materi_selesai dan kemajuan
function updateProgress($conn, $id_user, $id_materi, $tingkat) {
    try {
        // Periksa apakah materi sudah selesai oleh pengguna
        $sql_check_selesai = "SELECT 1 FROM materi_selesai WHERE pengguna_id = ? AND materi_id = ?";
        $stmt = $conn->prepare($sql_check_selesai);
        $stmt->bind_param("ii", $id_user, $id_materi);
        $stmt->execute();
        $result = $stmt->get_result();

        // Jika belum selesai, tambahkan ke tabel materi_selesai
        if ($result->num_rows === 0) {
            $sql_insert_selesai = "INSERT INTO materi_selesai (pengguna_id, materi_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql_insert_selesai);
            $stmt->bind_param("ii", $id_user, $id_materi);
            $stmt->execute();
        }

        // Hitung jumlah materi selesai untuk tingkat tertentu
        $sql_count_selesai = "SELECT COUNT(*) AS total_selesai 
                              FROM materi_selesai 
                              JOIN materi ON materi_selesai.materi_id = materi.id 
                              WHERE materi_selesai.pengguna_id = ? AND materi.tingkat = ?";
        $stmt = $conn->prepare($sql_count_selesai);
        $stmt->bind_param("is", $id_user, $tingkat);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_selesai = $result->fetch_assoc()['total_selesai'] ?? 0;

        // Hitung total materi untuk tingkat tertentu
        $sql_count_total = "SELECT COUNT(*) AS total_materi FROM materi WHERE tingkat = ?";
        $stmt = $conn->prepare($sql_count_total);
        $stmt->bind_param("s", $tingkat);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_materi = $result->fetch_assoc()['total_materi'] ?? 0;

        // Hitung persentase kemajuan
        $persentase = ($total_materi > 0) ? floor(($total_selesai / $total_materi) * 100) : 0;

        // Perbarui tabel kemajuan
        $sql_update_kemajuan = "INSERT INTO kemajuan (pengguna_id, tingkat, persentase) VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE persentase = ?";
        $stmt = $conn->prepare($sql_update_kemajuan);
        $stmt->bind_param("isis", $id_user, $tingkat, $persentase, $persentase);
        $stmt->execute();

        return $persentase;
    } catch (Exception $e) {
        error_log("Error updating progress: " . $e->getMessage());
        return false;
    }
}


// Handle request dari AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['materialId'], $data['action'], $data['tingkat'])) {
        $id_materi = $data['materialId'];
        $tingkat = $data['tingkat'];

        // Perbarui kemajuan
        $newProgress = updateProgress($conn, $id_user, $id_materi, $tingkat);

        if ($newProgress !== false) {
            echo json_encode(['success' => true, 'newProgress' => $newProgress]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui kemajuan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Pembelajaran</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(166, 198, 247);
        }
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-video {
            background-color: #e3f2fd;
        }
        .card-article {
            background-color: #f8bbd0;
        }
        .card img {
            max-height: 200px;
            object-fit: cover;
        }
        .btn-download {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
        }
        .modal-content {
            border-radius: 15px;
        }
    </style>
    <script>
        function recordAction(materialId, action, tingkat) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ materialId, action, tingkat }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('kemajuan-' + tingkat).innerText = data.newProgress + '%';
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Materi Pembelajaran</h1>

        <?php
        $levels = ['dasar', 'menengah', 'lanjutan'];
        foreach ($levels as $level) {
            $sql = "SELECT * FROM materi WHERE tingkat = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $level);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0): ?>
                <h2 class="mt-4">Materi <?= ucfirst($level); ?></h2>
                <p>Kemajuan: <span id="kemajuan-<?= $level; ?>">
                    <?php
                    $sql_progress = "SELECT persentase FROM kemajuan WHERE pengguna_id = ? AND tingkat = ?";
                    $stmt_progress = $conn->prepare($sql_progress);
                    $stmt_progress->bind_param("is", $id_user, $level);
                    $stmt_progress->execute();
                    $result_progress = $stmt_progress->get_result();
                    $row_progress = $result_progress->fetch_assoc();
                    echo $row_progress['persentase'] ?? 0;
                    ?>%
                </span></p>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card <?= $row['jenis_file'] == 'artikel' ? 'card-article' : 'card-video'; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['judul']); ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($row['deskripsi']); ?></p>

                                    <?php if ($row['jenis_file'] == 'artikel'): ?>
                                        <a href="../uploads/<?= htmlspecialchars($row['lokasi_file']); ?>" 
                                           class="btn btn-download" 
                                           download 
                                           onclick="recordAction(<?= $row['id']; ?>, 'download', '<?= $level; ?>');">
                                            Unduh Artikel
                                        </a>
                                    <?php elseif ($row['jenis_file'] == 'video'): ?>
                                        <button type="button" 
                                                class="btn btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#videoModal<?= $row['id']; ?>"
                                                onclick="recordAction(<?= $row['id']; ?>, 'view', '<?= $level; ?>');">
                                            Tonton Video
                                        </button>

                                        <div class="modal fade" id="videoModal<?= $row['id']; ?>" tabindex="-1" aria-labelledby="videoModalLabel<?= $row['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="videoModalLabel<?= $row['id']; ?>">
                                                            <?= htmlspecialchars($row['judul']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <iframe width="100%" height="400" 
                                                                src="<?= htmlspecialchars($row['lokasi_file']); ?>" 
                                                                frameborder="0" 
                                                                allowfullscreen>
                                                        </iframe>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center">Belum ada materi <?= htmlspecialchars($level); ?> yang tersedia.</p>
            <?php endif;

            $stmt->close();
        }
        ?>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
