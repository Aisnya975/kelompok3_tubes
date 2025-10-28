<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../';

// 2. Panggil header.php (yang ada session_start()) KEDUA
$pageTitle = 'Pembayaran Booking';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_login('klien'); // Hanya klien

// 4. Baru panggil config database
include_once '../../config/db_config.php';

$klien_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Cek kepemilikan booking
if ($booking_id <= 0) {
    header("Location: ../dashboard.php");
    exit();
}

$sql_check = "SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $booking_id, $klien_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows == 0) {
    // Bukan booking milik user ini
    header("Location: booking_list_user.php?error=unauthorized");
    exit();
}
$booking = $result_check->fetch_assoc();
$stmt_check->close();
// Kita biarkan koneksi $conn terbuka untuk form

?>

<!-- 5. (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="payment-page" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-4xl px-6">

        <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love scroll-reveal">
            Konfirmasi Pembayaran
        </h1>
        <p class="text-xl text-gray-600 mb-8 text-center scroll-reveal" style="transition-delay: 0.1s;">
            Booking ID Anda: <strong class="text-rose-accent">#<?php echo $booking_id; ?></strong>
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Kolom Instruksi Pembayaran -->
            <div class="card-glass p-8 shadow-soft-pink scroll-reveal" style="transition-delay: 0.2s;">
                <h2 class="font-playfair text-2xl font-semibold mb-4 text-gray-800">Instruksi Pembayaran</h2>
                <p class="text-gray-700 mb-2">Silakan transfer sejumlah:</p>
                <!-- Asumsi harga flat, Anda bisa ambil dari DB jika dinamis -->
                <p class="text-4xl font-bold text-gradient-love mb-4">Rp 150.000,-</p>
                <p class="text-gray-700 mb-2 font-semibold">Ke rekening berikut:</p>
                <ul class="space-y-2 text-gray-700">
                    <li><strong>Bank BCA:</strong> 1234567890 (a.n. Konseling Hati)</li>
                    <li><strong>OVO/GoPay:</strong> 081234567890 (a.n. Konseling Hati)</li>
                </ul>
                <p class="text-sm text-gray-500 mt-6">
                    Setelah melakukan transfer, silakan upload bukti pembayaran Anda di formulir di samping.
                </p>
            </div>

            <!-- Kolom Form Upload -->
            <div class="card-glass p-8 shadow-soft-pink scroll-reveal" style="transition-delay: 0.3s;">
                <h2 class="font-playfair text-2xl font-semibold mb-6 text-gray-800">Upload Bukti Pembayaran</h2>
                
                <form action="payment_process.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $klien_id; ?>">
                    <input type="hidden" name="amount" value="150000.00"> <!-- Asumsi harga flat -->

                    <div>
                        <label for="payment_method" class="block text-gray-700 mb-2 font-semibold">Metode Pembayaran Anda</label>
                        <select id="payment_method" name="payment_method" required 
                                class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                            <option value="Bank Transfer BCA">Bank Transfer BCA</option>
                            <option value="OVO">OVO</option>
                            <option value="GoPay">GoPay</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label for="bukti_pembayaran" class="block text-gray-700 mb-2 font-semibold">File Bukti Pembayaran</label>
                        <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required 
                               class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 text-gray-700
                                      file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                      file:bg-blush-pink/60 file:text-rose-accent hover:file:bg-blush-pink">
                        <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, PDF. Max 2MB.</p>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                            Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center text-gray-600 mt-10">
            <a href="booking_list_user.php" class="hover:text-rose-accent hover:underline font-semibold">
                &larr; Kembali ke Riwayat Booking
            </a>
        </p>

    </div>
</section>

<?php
$conn->close();
include_once '../../templates/footer.php';
?>

