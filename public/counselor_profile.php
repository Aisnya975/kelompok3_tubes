<?php
// 1. Tentukan base path DULU
$base_path_for_links = './'; // Ada di /public/

// 2. (PERBAIKAN URUTAN) Panggil check_user.php PERTAMA (Ini akan memanggil session_start())
include_once '../config/check_user.php';
// Tidak perlu require_login() di sini, halaman ini publik
// Pengecekan login hanya untuk tombol booking

// 3. (PERBAIKAN URUTAN) Panggil config database KEDUA
include_once '../config/db_config.php';

// 4. (PERBAIKAN URUTAN) BARU panggil header.php KETIGA
$pageTitle = 'Profil Konselor'; // Judul akan diset ulang di bawah
include_once '../templates/header.php'; 

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

// Ambil ID Konselor dari URL
$konselor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($konselor_id == 0) {
    echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error: ID Konselor tidak valid.</p></div></section>'; 
    // Jangan panggil footer dua kali, cukup exit
    exit;
}

// Ambil data konselor
$sql_konselor = "SELECT * FROM konselor WHERE id_konselor = ? AND status = 'Aktif'";
$stmt_konselor = $conn->prepare($sql_konselor);
// Tambahkan pengecekan jika prepare gagal
if (!$stmt_konselor) {
     echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error preparing statement: '.$conn->error.'</p></div></section>';
    if(isset($conn)) $conn->close(); // Tutup koneksi jika ada
    include_once '../templates/footer.php'; // Panggil footer sebelum exit
    exit;
}
$stmt_konselor->bind_param("i", $konselor_id);
$stmt_konselor->execute();
$result_konselor = $stmt_konselor->get_result();

if ($result_konselor->num_rows == 0) {
     echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error: Konselor tidak ditemukan atau tidak aktif.</p></div></section>';
    $stmt_konselor->close(); // Tutup statement
    if(isset($conn)) $conn->close();
    include_once '../templates/footer.php';
    exit;
}
$konselor = $result_konselor->fetch_assoc();
$stmt_konselor->close();

// Set ulang Page Title dengan nama konselor
$pageTitle = 'Profil: ' . htmlspecialchars($konselor['nama_lengkap']); 
// Output ulang title menggunakan JavaScript karena header sudah dipanggil
echo "<script>document.title = '" . htmlspecialchars($pageTitle) . " - Konseling Hati';</script>";

// Ambil jadwal tersedia untuk konselor ini
$sql_jadwal = "SELECT * FROM jadwal_tersedia 
               WHERE id_konselor = ? AND status_ketersediaan = 'Tersedia' AND waktu_mulai > NOW()
               ORDER BY waktu_mulai ASC
               LIMIT 10"; 
$stmt_jadwal = $conn->prepare($sql_jadwal);
// Tambahkan pengecekan jika prepare gagal
if (!$stmt_jadwal) {
     echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error preparing statement jadwal: '.$conn->error.'</p></div></section>';
    if(isset($conn)) $conn->close(); 
    include_once '../templates/footer.php'; 
    exit;
}
$stmt_jadwal->bind_param("i", $konselor_id);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();

// Fungsi helper untuk format tanggal
if (!function_exists('format_jadwal_indo')) { // Cegah redeclare jika dipanggil lagi
    function format_jadwal_indo($datetime_str) {
        try {
             $waktu = new DateTime($datetime_str);
             // Cek IntlDateFormatter ada atau tidak
             if (class_exists('IntlDateFormatter')) {
                 $fmt = new IntlDateFormatter(
                     'id_ID', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 
                     'Asia/Jakarta', IntlDateFormatter::GREGORIAN, 
                     'eeee, dd MMM yyyy (HH:mm)'
                 );
                 return $fmt->format($waktu);
             } else {
                 // Fallback jika intl extension tidak ada
                 return $waktu->format('D, d M Y (H:i)'); 
             }
        } catch (Exception $e) {
            return 'Invalid date'; // Handle jika format tanggal salah
        }
    }
}
?>

<!-- Halaman Profil Konselor -->
<section id="counselor-profile" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-4xl px-6">
        
        <!-- Kartu Profil Utama -->
        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal mb-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                
                <!-- Foto Profil -->
                <div class="flex-shrink-0">
                    <img src="<?php echo $base_path_for_links; ?><?php echo !empty($konselor['foto_profil']) ? htmlspecialchars($konselor['foto_profil']) : 'assets/images/default_avatar.png'; ?>" 
                         alt="Foto <?php echo htmlspecialchars($konselor['nama_lengkap']); ?>"
                         onerror="this.onerror=null; this.src='<?php echo $base_path_for_links; ?>assets/images/default_avatar.png';"
                         class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-lg">
                </div>
                
                <!-- Info Teks -->
                <div class="flex-grow text-center md:text-left">
                    <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-2 text-gradient-love">
                        <?php echo htmlspecialchars($konselor['nama_lengkap']); ?>
                    </h1>
                    <p class="font-semibold text-rose-accent text-lg mb-4">
                        <?php echo htmlspecialchars($konselor['spesialisasi'] ?? 'Spesialisasi belum ditentukan'); ?>
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        <?php echo nl2br(htmlspecialchars($konselor['biografi_singkat'] ?? 'Biografi belum tersedia.')); ?>
                    </p>

                    <!-- Tombol Aksi Booking -->
                    <?php 
                    $user_type = $_SESSION['user_type'] ?? null;
                    if ($user_type == 'klien'): 
                    ?>
                        <a href="<?php echo $base_path_for_links; ?>booking/booking_form.php?id_konselor=<?php echo $konselor['id_konselor']; ?>"
                           class="btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                            Booking Sesi Sekarang
                        </a>
                    <?php elseif ($user_type == null): // Jika Tamu ?>
                        <a href="<?php echo $base_path_for_links; ?>login.php?redirect=<?php echo urlencode('counselor_profile.php?id='.$konselor['id_konselor']); ?>"
                           class="btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                            Login untuk Booking
                        </a>
                    <?php else: // Jika Konselor atau Admin ?>
                        <p class="text-gray-500 italic">(Anda tidak dapat booking sebagai <?php echo htmlspecialchars($user_type); ?>)</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Kartu Jadwal Tersedia -->
        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            <h2 class="font-playfair text-3xl font-bold text-gray-800 mb-6 text-center">
                Jadwal Tersedia (Terdekat)
            </h2>
            
            <?php if ($result_jadwal && $result_jadwal->num_rows > 0): ?>
                <ul class="space-y-4 max-w-lg mx-auto">
                    <?php while($jadwal = $result_jadwal->fetch_assoc()): ?>
                        <li class="bg-white/60 p-4 rounded-lg shadow-sm border border-blush-pink/50 text-center">
                            <span class="font-semibold text-gray-700 text-lg">
                                <?php echo format_jadwal_indo($jadwal['waktu_mulai']); ?>
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <?php if ($user_type == 'klien'): // Hanya tampilkan link ini untuk klien ?>
                <p class="text-center text-gray-600 mt-6">
                    Klik tombol "Booking Sesi" di atas untuk melihat semua jadwal & memilih.
                </p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center text-gray-600 text-lg">
                    Saat ini konselor belum memiliki jadwal yang tersedia.
                </p>
            <?php endif; ?>
            
        </div>

         <p class="text-center text-gray-600 mt-10">
            <a href="<?php echo $base_path_for_links; ?>counselors.php" class="hover:text-rose-accent hover:underline font-semibold">
                &larr; Kembali ke Daftar Konselor
            </a>
        </p>

    </div>
</section>

<?php
if(isset($stmt_jadwal)) $stmt_jadwal->close();
if(isset($conn)) $conn->close();
include_once '../templates/footer.php';
?>

