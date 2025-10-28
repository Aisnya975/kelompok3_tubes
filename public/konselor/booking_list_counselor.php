<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

// 4. BARU panggil header.php KETIGA
$pageTitle = 'Daftar Booking Masuk';
include_once '../../templates/header.php'; 

// Ambil ID Konselor dari Sesi
$konselor_id = $_SESSION['counselor_id'];

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

// Ambil data booking untuk konselor ini, join dengan klien
$sql = "SELECT b.*, k.nama_lengkap AS klien_nama, k.email AS klien_email
        FROM bookings b
        JOIN klien k ON b.user_id = k.id_klien
        WHERE b.counselor_id = ?
        ORDER BY b.booking_date ASC, b.booking_time ASC"; // Urutkan dari yang paling dekat
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing statement: " . $conn->error; 
    exit; 
}
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$result = $stmt->get_result();

// Fungsi helper untuk format tanggal
if (!function_exists('format_tanggal_indo_singkat')) { // Cegah redeclare
    function format_tanggal_indo_singkat($datetime_str) {
         try {
             $waktu = new DateTime($datetime_str);
             if (class_exists('IntlDateFormatter')) {
                 $fmt = new IntlDateFormatter(
                     'id_ID', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 
                     'Asia/Jakarta', IntlDateFormatter::GREGORIAN, 
                     'eeee, dd MMM yyyy, HH:mm'
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
// Fungsi helper untuk status badge
if (!function_exists('get_status_badge')) { // Cegah redeclare
    function get_status_badge($status) {
        $class = 'bg-gray-100 text-gray-600';
        if ($status == 'Verified') $class = 'bg-green-100 text-green-700';
        if ($status == 'Pending Payment') $class = 'bg-yellow-100 text-yellow-700';
        if ($status == 'Completed') $class = 'bg-blue-100 text-blue-700';
        if ($status == 'Canceled') $class = 'bg-red-100 text-red-700';
        return '<span class="font-semibold ' . $class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($status) . '</span>';
    }
}
?>

<!-- Halaman Daftar Booking Konselor -->
<section id="counselor-booking-list" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-5xl px-6">

        <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love scroll-reveal">
            Daftar Booking Masuk
        </h1>

        <!-- Kontainer 'card-glass' untuk tabel -->
        <div class="card-glass p-4 md:p-8 shadow-soft-pink scroll-reveal">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-blush-pink/50">
                    <thead class="">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">ID Booking</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Nama Klien</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Jadwal Sesi</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Catatan Klien</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-blush-pink/30">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-blush-pink/20 transition-colors duration-200">
                                    <td class="py-3 px-4 text-gray-800 font-medium">#<?php echo $row['booking_id']; ?></td>
                                    <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($row['klien_nama']); ?></td>
                                    <td class="py-3 px-4 text-gray-700">
                                        <?php echo format_tanggal_indo_singkat($row['booking_date'] . ' ' . $row['booking_time']); ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php echo get_status_badge($row['status']); ?>
                                    </td>
                                     <td class="py-3 px-4 text-gray-600 text-sm italic">
                                        <?php echo !empty($row['notes']) ? htmlspecialchars(substr($row['notes'], 0, 50)) . '...' : '-'; ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium space-x-3 whitespace-nowrap">
                                        <!-- (PERBAIKAN LINK) Arahkan ke halaman detail baru -->
                                        <a href="booking_detail_counselor.php?id=<?php echo $row['booking_id']; ?>" class="text-rose-accent hover:text-pink-600">Lihat Detail</a>
                                        <!-- TODO: Tambah aksi konfirmasi/batalkan -->
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                    Belum ada booking yang masuk untuk Anda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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

