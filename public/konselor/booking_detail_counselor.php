<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

// 4. BARU panggil header.php KETIGA
$pageTitle = 'Detail Booking Klien';
include_once '../../templates/header.php'; 

// Ambil ID Konselor & Booking ID
$konselor_id = $_SESSION['counselor_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

if ($booking_id <= 0) {
     echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error: ID Booking tidak valid.</p></div></section>'; 
    include_once '../../templates/footer.php';
    exit();
}

// Ambil data booking, join dengan klien dan pembayaran
$sql = "SELECT b.*, 
               k.nama_lengkap AS klien_nama, k.email AS klien_email, k.nomor_telepon AS klien_telepon,
               p.payment_method, p.amount, p.bukti_pembayaran, p.status AS payment_status
        FROM bookings b
        JOIN klien k ON b.user_id = k.id_klien
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.booking_id = ? AND b.counselor_id = ?"; // Pastikan booking milik konselor ini

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing statement: " . $conn->error; 
    exit; 
}
$stmt->bind_param("ii", $booking_id, $konselor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
     echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error: Booking tidak ditemukan atau bukan milik Anda.</p></div></section>';
    $stmt->close(); 
    if(isset($conn)) $conn->close();
    include_once '../../templates/footer.php';
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

// Fungsi helper (duplikat dari file lain, idealnya dipindah ke file helper)
if (!function_exists('format_tanggal_indo_singkat')) {
    function format_tanggal_indo_singkat($datetime_str) {
         try {
             $waktu = new DateTime($datetime_str);
             if (class_exists('IntlDateFormatter')) {
                 $fmt = new IntlDateFormatter('id_ID', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Asia/Jakarta', IntlDateFormatter::GREGORIAN, 'eeee, dd MMM yyyy, HH:mm');
                 return $fmt->format($waktu);
             } else { return $waktu->format('D, d M Y, H:i'); }
        } catch (Exception $e) { return 'Invalid date'; }
    }
}
if (!function_exists('get_status_badge')) { 
    function get_status_badge($status) {
        $class = 'bg-gray-100 text-gray-600';
        if ($status == 'Verified') $class = 'bg-green-100 text-green-700';
        if ($status == 'Pending Payment') $class = 'bg-yellow-100 text-yellow-700';
        if ($status == 'Completed') $class = 'bg-blue-100 text-blue-700';
        if ($status == 'Canceled') $class = 'bg-red-100 text-red-700';
        return '<span class="font-semibold ' . $class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($status) . '</span>';
    }
}
function get_payment_status_badge($status) {
    $status = $status ?? 'Belum Bayar';
    $class = 'bg-gray-100 text-gray-600';
    if ($status == 'Diterima') $class = 'bg-green-100 text-green-700';
    if ($status == 'Menunggu Verifikasi') $class = 'bg-yellow-100 text-yellow-700';
    if ($status == 'Ditolak') $class = 'bg-red-100 text-red-700';
    return '<span class="font-semibold ' . $class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($status) . '</span>';
}

?>

<!-- Halaman Detail Booking (Konselor View) -->
<section id="counselor-booking-detail" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-3xl px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-4 text-center text-gradient-love">
                Detail Booking #<?php echo $booking['booking_id']; ?>
            </h1>
            
            <!-- Detail Klien -->
            <div class="border-b border-blush-pink/50 pb-6 mb-6">
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Informasi Klien</h2>
                <div class="space-y-3 text-gray-700">
                    <div class="flex justify-between">
                        <span class="font-semibold">Nama:</span>
                        <span><?php echo htmlspecialchars($booking['klien_nama']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Email:</span>
                        <span><?php echo htmlspecialchars($booking['klien_email']); ?></span>
                    </div>
                     <div class="flex justify-between">
                        <span class="font-semibold">Telepon:</span>
                        <span><?php echo htmlspecialchars($booking['klien_telepon'] ?? '-'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Detail Sesi -->
            <div class="border-b border-blush-pink/50 pb-6 mb-6">
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Detail Sesi</h2>
                <div class="space-y-3 text-gray-700">
                     <div class="flex justify-between">
                        <span class="font-semibold">Jadwal:</span>
                        <span class="text-right font-medium text-rose-accent"><?php echo format_tanggal_indo_singkat($booking['booking_date'] . ' ' . $booking['booking_time']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Status Booking:</span>
                        <span><?php echo get_status_badge($booking['status']); ?></span>
                    </div>
                     <div class="flex justify-between">
                        <span class="font-semibold">Status Pembayaran:</span>
                        <span><?php echo get_payment_status_badge($booking['payment_status']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Catatan Klien -->
            <div>
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Catatan dari Klien</h2>
                <p class="text-gray-600 italic bg-white/50 p-4 rounded-lg">
                    "<?php echo !empty($booking['notes']) ? nl2br(htmlspecialchars($booking['notes'])) : 'Tidak ada catatan khusus.'; ?>"
                </p>
            </div>

             <!-- Bukti Pembayaran (jika ada) -->
             <?php if(!empty($booking['bukti_pembayaran'])): ?>
             <div class="mt-6 border-t border-blush-pink/50 pt-6">
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Bukti Pembayaran</h2>
                <div class="flex justify-center">
                     <!-- Pastikan path absolut dari root web -->
                    <a href="/<?php echo htmlspecialchars($booking['bukti_pembayaran']); ?>" target="_blank" class="block border border-rose-accent rounded-lg overflow-hidden transition-transform hover:scale-105">
                         <img src="/<?php echo htmlspecialchars($booking['bukti_pembayaran']); ?>" alt="Bukti Pembayaran" class="max-w-xs max-h-48 object-contain">
                    </a>
                </div>
                 <p class="text-center text-xs text-gray-500 mt-2">(Klik gambar untuk memperbesar)</p>
             </div>
            <?php endif; ?>

            <!-- TODO: Tambahkan Aksi Konselor (Konfirmasi, Batalkan, Tandai Selesai) -->
             <!-- <div class="mt-8 pt-6 border-t border-blush-pink/50 text-center space-x-4">
                 <button class="btn-glow bg-green-500 text-white ...">Konfirmasi Booking</button>
                 <button class="btn-glow bg-red-500 text-white ...">Batalkan Booking</button>
            </div> -->

            <p class="text-center text-gray-600 mt-10">
                <a href="booking_list_counselor.php" class="hover:text-rose-accent hover:underline font-semibold">
                    &larr; Kembali ke Daftar Booking
                </a>
            </p>

        </div>
    </div>
</section>

<?php
if(isset($conn)) $conn->close();
include_once '../../templates/footer.php';
?>
