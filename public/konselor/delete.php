<?php
// 1. Tentukan base path DULU (Meskipun tidak dipakai di sini, jaga konsistensi)
$base_path_for_links = '../'; 

// 2. (PERBAIKAN) Panggil check_user.php PERTAMA (Ini akan memanggil session_start())
include_once '../../config/check_user.php';
// Lakukan pengecekan & redirect SEKARANG, sebelum output apa pun
require_admin(); // Hanya admin

// 3. (PERBAIKAN) Panggil config database KEDUA
include_once '../../config/db_config.php';

// --- Hapus 'include header.php' dari sini ---

$konselor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Tentukan URL redirect default
$redirect_url = 'list.php?error=invalid_id';

if ($konselor_id > 0) {
    // Siapkan statement DELETE
    $sql = "DELETE FROM konselor WHERE id_konselor = ?";
    if ($stmt = $conn->prepare($sql)) { // Tambah pengecekan prepare
        $stmt->bind_param("i", $konselor_id);
        
        if ($stmt->execute()) {
            // Berhasil dihapus
            $redirect_url = "list.php?success=deleted";
        } else {
            // Gagal dihapus
            $redirect_url = "list.php?error=delete_failed&msg=" . urlencode($stmt->error); // Tambah pesan error
        }
        $stmt->close();
    } else {
        // Gagal prepare statement
        $redirect_url = "list.php?error=delete_prepare_failed&msg=" . urlencode($conn->error); // Tambah pesan error
    }
}

// Tutup koneksi sebelum redirect dan exit
if ($conn) $conn->close();

// Lakukan redirect di akhir
header("Location: " . $redirect_url);
exit(); // Pastikan script berhenti di sini

// --- Hapus 'include footer.php' dari sini ---
?>
