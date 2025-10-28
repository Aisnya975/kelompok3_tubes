<?php
// (WAJIB) Mulai sesi HANYA JIKA BELUM DIMULAI
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// (PERBAIKAN PATH - VERSI LEBIH SEDERHANA)
if (!isset($base_path_for_links)) {
    // Cek apakah file ini dipanggil dari folder 'public' atau subfolder di dalamnya
    $is_in_public_root = basename(realpath(dirname($_SERVER['SCRIPT_FILENAME']))) == 'public';
    // Perbaiki pengecekan subfolder agar lebih akurat
    $public_dir_realpath = realpath($_SERVER['DOCUMENT_ROOT'] . '/public');
    $script_dir_realpath = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
    $is_in_public_subfolder = $public_dir_realpath && $script_dir_realpath && strpos($script_dir_realpath, $public_dir_realpath) === 0 && $script_dir_realpath !== $public_dir_realpath;

    if ($is_in_public_subfolder) { 
         $base_path_for_links = '../'; // Ada di subfolder seperti /public/booking/
    } else {
         $base_path_for_links = './'; // Ada di /public/ atau mungkin di root (jika public adalah root)
         // Jika script ada di root, logika ini mungkin perlu penyesuaian lagi, tapi ./ biasanya aman.
    }
} 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Konseling Hati' : 'Konseling Hati'; ?></title>
    
    <!-- Impor Font dari Google Fonts (TIDAK DIUBAH) -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Impor Tailwind CSS (TIDAK DIUBAH) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Konfigurasi Kustom Tailwind (TIDAK DIUBAH) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'playfair': ['"Playfair Display"', 'serif'],
                        'inter': ['"Inter"', 'sans-serif'],
                    },
                    colors: {
                        'blush-pink': '#FFD5E5',
                        'pastel-cream': '#FFF7F3',
                        'lavender': '#EAD9FF',
                        'rose-accent': '#FF6B9A',
                        'navy-dark': '#121027',    
                        'neon-pink': '#FF4DA6',   
                    }
                }
            }
        }
    </script>
    
    <!-- (PERBAIKAN PATH CSS - Gunakan Path Absolut dari Root) -->
    <link rel="stylesheet" href="/assets/css/style.css"> 
    
</head>
<body class="text-gray-800 animate-bg"> <!-- Body TIDAK DIUBAH -->

    <!-- Efek Trail Sparkle (Mouse) (TIDAK DIUBAH) -->
    <div id="sparkle-trail" class="sparkle-trail"></div> 
    
    <!-- Partikel Hati Mengambang (Latar Belakang) (TIDAK DIUBAH) -->
    <div id="floating-particle-container" class="fixed top-0 left-0 w-full h-full pointer-events-none z-0"></div>

    <!-- Hujan Hati (Dekat Footer) (TIDAK DIUBAH) -->
    <div id="hearts-rain-container" class="fixed top-0 left-0 w-full h-full pointer-events-none overflow-hidden z-0"></div>
    
    <!-- Konten Utama (z-index 10-50) (TIDAK DIUBAH) -->
    <div class="relative z-10">

        <!-- Header (Sticky, Translucent) (Tampilan TIDAK DIUBAH) -->
        <header class="fixed top-0 left-0 w-full bg-pastel-cream/70 backdrop-blur-lg shadow-blush-pink/30 shadow-md z-50 transition-all duration-300">
            <nav class="container mx-auto max-w-6xl px-6 py-4 flex justify-between items-center">
                
                <!-- Logo/Judul Situs (Dinamis) -->
                <!-- (Path Sudah Benar) -->
                <a href="<?php echo $base_path_for_links; ?>index.php" class="font-playfair text-2xl font-bold text-rose-accent">
                    Konseling Hati
                </a>
                
                <!-- Navigasi Links (Logika Menu ASLI ANDA, Path Sudah Benar) -->
                <div class="hidden md:flex space-x-8 font-medium text-gray-700">
                    
                     <!-- (Path Sudah Benar) -->
                    <a href="<?php echo $base_path_for_links; ?>counselors.php" class="hover:text-rose-accent transition-colors">Konselor</a>
                    <a href="<?php echo $base_path_for_links; ?>materials/list.php" class="hover:text-rose-accent transition-colors">Materi</a>
                    
                    <?php 
                    // Logika ASLI ANDA untuk menu login/logout
                    $is_logged_in_klien_konselor = isset($_SESSION['user_id']) || isset($_SESSION['counselor_id']);
                    $is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'; 
                    ?>

                    <?php if ($is_logged_in_klien_konselor || $is_admin ): // Jika Klien/Konselor ATAU Admin login ?>
                        
                         <!-- (Path Sudah Benar) -->
                        <?php if ($is_admin): ?>
                            <a href="<?php echo $base_path_for_links; ?>konselor/list.php" class="hover:text-rose-accent transition-colors">Kelola Konselor</a>
                            <a href="<?php echo $base_path_for_links; ?>clients/list.php" class="hover:text-rose-accent transition-colors">Kelola Klien</a>
                        <?php else: ?>
                            <a href="<?php echo $base_path_for_links; ?>dashboard.php" class="hover:text-rose-accent transition-colors">Dashboard</a>
                        <?php endif; ?>
                        
                         <!-- (Path Sudah Benar) -->
                        <a href="<?php echo $base_path_for_links; ?>logout.php" class="bg-rose-accent text-white font-semibold py-2 px-5 rounded-full text-sm shadow-lg transition-transform duration-300 hover:scale-105">Logout</a>
                    
                    <?php else: ?>
                        <!-- Link Tamu -->
                         <!-- (Path Sudah Benar) -->
                         <a href="<?php echo $base_path_for_links; ?>index.php" class="hover:text-rose-accent transition-colors">Home</a>
                        <a href="<?php echo $base_path_for_links; ?>login.php" class="hover:text-rose-accent transition-colors">Login</a>
                        <a href="<?php echo $base_path_for_links; ?>register.php" class="bg-rose-accent text-white font-semibold py-2 px-5 rounded-full text-sm shadow-lg transition-transform duration-300 hover:scale-105">
                            Daftar
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Tombol Mobile Menu (Sederhana - TIDAK DIUBAH) -->
                <button class="md:hidden text-rose-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </nav>
        </header>

        <!-- Pastikan ada padding atas di konten utama agar tidak tertutup header -->

