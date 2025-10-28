<?php
/*
 * File: config/check_user.php
 * Berisi fungsi-fungsi helper untuk otentikasi dan sesi.
 */

// (WAJIB) Mulai sesi HANYA JIKA BELUM DIMULAI
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * (PERBAIKAN) Fungsi is_logged_in() sekarang memeriksa SEMUA tipe user ID.
 * Mengecek apakah ada user (klien, konselor, ATAU admin) yang login.
 * @return bool
 */
function is_logged_in() {
    // Cek salah satu dari ID ini ada di sesi
    return isset($_SESSION['user_id']) || isset($_SESSION['counselor_id']) || isset($_SESSION['admin_id']);
}

/**
 * Mengarahkan user ke halaman login jika belum login.
 * Juga bisa mengecek role spesifik jika diperlukan.
 * @param string|null $required_type 'klien' atau 'konselor' atau 'admin' (opsional)
 */
function require_login($required_type = null) {
    global $base_path_for_links; // Ambil base path

    // Tentukan base path jika belum ada (untuk keamanan)
    // Gunakan path absolut dari root jika memungkinkan untuk lebih aman
    $login_url = '/public/login.php'; // Path absolut dari root
    $dashboard_url = '/public/dashboard.php'; // Path absolut dari root
    $admin_dashboard_url = '/public/konselor/list.php'; // Path absolut dari root

    // 1. Cek apakah login?
    if (!is_logged_in()) {
        header("Location: $login_url");
        exit();
    }

    // 2. Cek apakah role-nya sesuai?
    if ($required_type) {
        $user_type = $_SESSION['user_type'] ?? null; // 'klien', 'konselor', 'admin'
        
        if ($user_type != $required_type) {
            // Jika role tidak cocok, tendang ke dashboard mereka yang sesuai
             if ($user_type == 'admin') {
                 header("Location: $admin_dashboard_url?error=unauthorized");
             } else {
                 header("Location: $dashboard_url?error=unauthorized");
             }
            exit();
        }
    }
}

/**
 * Helper khusus untuk admin
 */
function require_admin() {
    require_login('admin'); // Memanggil fungsi di atas dengan role 'admin'
}

?>

