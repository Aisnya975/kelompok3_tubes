<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

// 4. BARU panggil header.php KETIGA
$pageTitle = 'Kelola Jadwal Tersedia';
include_once '../../templates/header.php'; 

// Ambil ID Konselor dari Sesi
$konselor_id = $_SESSION['counselor_id'];

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

// Ambil data jadwal tersedia untuk konselor ini
$sql = "SELECT * FROM jadwal_tersedia 
        WHERE id_konselor = ? AND waktu_mulai > NOW() 
        ORDER BY waktu_mulai ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
     echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error preparing statement: '.$conn->error.'</p></div></section>';
    if(isset($conn)) $conn->close(); 
    include_once '../../templates/footer.php'; 
    exit;
}
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$result = $stmt->get_result();

// Fungsi helper untuk format tanggal
if (!function_exists('format_jadwal_indo_singkat')) {
    function format_jadwal_indo_singkat($datetime_str) {
         try {
             $waktu = new DateTime($datetime_str);
             if (class_exists('IntlDateFormatter')) {
                 $fmt = new IntlDateFormatter(
                     'id_ID', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 
                     'Asia/Jakarta', IntlDateFormatter::GREGORIAN, 
                     'eeee, dd MMM yyyy, HH:mm' // Format lebih singkat
                 );
                 return $fmt->format($waktu);
             } else {
                 return $waktu->format('D, d M Y, H:i'); 
             }
        } catch (Exception $e) {
            return 'Invalid date'; 
        }
    }
}
?>

<!-- Halaman Kelola Jadwal -->
<section id="schedule-management" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-4xl px-6">

        <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 scroll-reveal">
            <h1 class="font-playfair text-4xl font-bold text-gray-800 text-gradient-love mb-4 md:mb-0">
                Kelola Jadwal Tersedia
            </h1>
             <!-- (PERBAIKAN LINK) Arahkan ke halaman add_schedule.php -->
            <a href="add_schedule.php" class="btn-glow bg-rose-accent text-white font-semibold py-3 px-6 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105 self-start md:self-center">
                + Tambah Jadwal Baru
            </a>
        </div>
         <!-- Notifikasi Sukses -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'added'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 scroll-reveal" role="alert">
                <p class="font-semibold">Jadwal baru berhasil ditambahkan.</p>
            </div>
        <?php endif; ?>


        <!-- Kontainer 'card-glass' untuk daftar jadwal -->
        <div class="card-glass p-6 md:p-8 shadow-soft-pink scroll-reveal">
            
            <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-6">Jadwal Mendatang</h2>

            <?php if ($result && $result->num_rows > 0): ?>
                <ul class="space-y-4">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <li class="bg-white/60 p-4 rounded-lg shadow-sm border border-blush-pink/50 flex flex-col sm:flex-row justify-between sm:items-center space-y-2 sm:space-y-0">
                            <span class="font-semibold text-gray-700">
                                <?php echo format_jadwal_indo_singkat($row['waktu_mulai']); ?> 
                                - <?php 
                                    try {
                                         echo (new DateTime($row['waktu_selesai']))->format('H:i'); 
                                    } catch (Exception $e) {
                                        echo '??:??'; 
                                    }
                                ?>
                            </span>
                             <div class="flex items-center space-x-4">
                                <span class="text-sm font-medium px-3 py-1 rounded-full <?php echo $row['status_ketersediaan'] == 'Tersedia' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                    <?php echo htmlspecialchars($row['status_ketersediaan']); ?>
                                </span>
                                 <!-- TODO: Tambahkan tombol edit/hapus jadwal -->
                                 <div class="flex space-x-2">
                                     <!-- <a href="edit_schedule.php?id=<?php //echo $row['id_jadwal']; ?>" class="text-xs text-blue-600 hover:underline">Edit</a> -->
                                     <a href="#" class="text-xs text-red-600 hover:underline cursor-not-allowed" onclick="alert('Fitur hapus jadwal segera hadir'); return false;">Hapus</a>
                                 </div> 
                             </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-center text-gray-600">Anda belum menambahkan jadwal tersedia untuk waktu mendatang.</p>
            <?php endif; ?>
        </div>
        
         <p class="text-center text-gray-600 mt-10">
            <a href="../dashboard.php" class="hover:text-rose-accent hover:underline font-semibold">
                &larr; Kembali ke Dashboard
            </a>
        </p>

    </div>
</section>

<?php
if(isset($stmt)) $stmt->close();
if(isset($conn)) $conn->close();
include_once '../../templates/footer.php';
?>

