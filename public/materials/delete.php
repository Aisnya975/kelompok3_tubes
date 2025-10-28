<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php (yang ada session_start()) KEDUA
// Meskipun tidak ada output HTML, ini penting untuk memuat sesi
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor

// 4. Baru panggil config database
include_once '../../config/db_config.php';

$materi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// (PERBAIKAN LOGIKA) Tentukan URL redirect default
$redirect_url = 'list.php?error=invalid_id';

if ($materi_id > 0) {
    // Siapkan statement DELETE
    $sql = "DELETE FROM materi_pemulihan WHERE id_materi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $materi_id);
    
    if ($stmt->execute()) {
        // Berhasil dihapus
        $redirect_url = "list.php?success=deleted";
    } else {
        // Gagal dihapus
        $redirect_url = "list.php?error=delete_failed";
    }
    // (PERBAIKAN) Tutup statement setelah selesai digunakan
    $stmt->close();
}

// (PERBAIKAN) Tutup koneksi sebelum redirect dan exit
$conn->close();

// (PERBAIKAN) Lakukan redirect di akhir
header("Location: " . $redirect_url);
exit();

?>
