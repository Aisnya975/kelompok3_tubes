<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php (yang ada session_start()) KEDUA
$pageTitle = 'Detail Booking';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_login('klien'); // Hanya klien

// 4. Baru panggil config database
include_once '../../config/db_config.php';

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

$klien_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    header("Location: booking_list_user.php?error=invalid_id");
    exit();
}

// Ambil semua data terkait booking ini
$sql = "SELECT b.*, k.nama_lengkap AS konselor_nama, k.spesialisasi, k.foto_profil,
               p.payment_method, p.amount, p.bukti_pembayaran, p.status AS payment_status
        FROM bookings b
        JOIN konselor k ON b.counselor_id = k.id_konselor
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.booking_id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $klien_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Bukan booking milik user ini atau tidak ditemukan
    header("Location: booking_list_user.php?error=not_found");
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

// Fungsi helper untuk format tanggal
function format_tanggal_indo($datetime_str) {
    $waktu = new DateTime($datetime_str);
    $fmt = new IntlDateFormatter(
        'id_ID',
        IntlDateFormatter::NONE,
        IntlDateFormatter::NONE,
        'Asia/Jakarta',
        IntlDateFormatter::GREGORIAN,
        'eeee, dd MMMM yyyy, HH:mm' // Format: Senin, 27 Oktober 2025, 14:00
    );
    return $fmt->format($waktu);
}

// Fungsi helper untuk status
function get_status_badge($status, $type = 'booking') {
    $class = 'bg-gray-100 text-gray-600';
    if ($type == 'booking') {
        if ($status == 'Verified') $class = 'bg-green-100 text-green-700';
        if ($status == 'Pending Payment') $class = 'bg-yellow-100 text-yellow-700';
        if ($status == 'Canceled') $class = 'bg-red-100 text-red-700';
    } else { // Payment
        $status = $status ?? 'Belum Bayar';
        if ($status == 'Diterima') $class = 'bg-green-100 text-green-700';
        if ($status == 'Menunggu Verifikasi') $class = 'bg-yellow-100 text-yellow-700';
        if ($status == 'Ditolak') $class = 'bg-red-100 text-red-700';
    }
    return '<span class="font-semibold ' . $class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($status) . '</span>';
}

?>

<!-- Halaman Detail Booking -->
<section id="booking-detail" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-3xl px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-4 text-center text-gradient-love">
                Detail Booking #<?php echo $booking['booking_id']; ?>
            </h1>
            
            <div class="border-b border-blush-pink/50 pb-6 mb-6">
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Detail Sesi</h2>
                <div class="space-y-3 text-gray-700">
                    <div class="flex justify-between">
                        <span class="font-semibold">Konselor:</span>
                        <span><?php echo htmlspecialchars($booking['konselor_nama']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Spesialisasi:</span>
                        <span><?php echo htmlspecialchars($booking['spesialisasi']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Jadwal:</span>
                        <span class="text-right font-medium text-rose-accent"><?php echo format_tanggal_indo($booking['booking_date'] . ' ' . $booking['booking_time']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Status Booking:</span>
                        <span><?php echo get_status_badge($booking['status'], 'booking'); ?></span>
                    </div>
                </div>
            </div>

            <div class="border-b border-blush-pink/50 pb-6 mb-6">
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Detail Pembayaran</h2>
                <div class="space-y-3 text-gray-700">
                    <div class="flex justify-between">
                        <span class="font-semibold">Jumlah:</span>
                        <span>Rp <?php echo number_format($booking['amount'] ?? 150000, 0, ',', '.'); ?>,-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Metode:</span>
                        <span><?php echo htmlspecialchars($booking['payment_method'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Status Bayar:</span>
                        <span><?php echo get_status_badge($booking['payment_status'], 'payment'); ?></span>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="font-playfair text-2xl font-semibold text-gray-800 mb-4">Catatan Anda</h2>
                <p class="text-gray-600 italic bg-white/50 p-4 rounded-lg">
                    "<?php echo !empty($booking['notes']) ? nl2br(htmlspecialchars($booking['notes'])) : 'Tidak ada catatan khusus.'; ?>"
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-8 pt-6 border-t border-blush-pink/50 text-center">
                <?php if ($booking['status'] == 'Pending Payment' && empty($booking['bukti_pembayaran'])): ?>
                    <a href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>"
                       class="w-full md:w-auto btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Lanjutkan Pembayaran
                    </a>
                <?php endif; ?>
                
                <p class="text-center text-gray-600 mt-6">
                    <a href="booking_list_user.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Kembali ke Riwayat Booking
                    </a>
                </p>
            </div>

        </div>
    </div>
</section>

<?php
include_once '../../templates/footer.php';
$conn->close();
?>

