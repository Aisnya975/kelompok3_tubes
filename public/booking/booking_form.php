<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php (yang ada session_start()) PERTAMA
include_once '../../config/check_user.php';
require_login('klien'); // Wajib login klien

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

$klien_id = $_SESSION['user_id'];
$konselor_id = isset($_GET['id_konselor']) ? intval($_GET['id_konselor']) : 0;

$message = '';
$error = '';

// 4. (PERBAIKAN) SEMUA LOGIKA PROSES FORM (POST) PINDAH KE ATAS SINI
//    SEBELUM 'header.php' DIPANGGIL
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $konselor_id_form = intval($_POST['id_konselor']);
    $jadwal_id = intval($_POST['id_jadwal']); 
    $catatan_awal = $conn->real_escape_string($_POST['catatan_awal']);
    
    // 1. Ambil detail jadwal yang dipilih
    $sql_jadwal = "SELECT * FROM jadwal_tersedia WHERE id_jadwal = ? AND id_konselor = ? AND status_ketersediaan = 'Tersedia'";
    $stmt_jadwal = $conn->prepare($sql_jadwal);
    $stmt_jadwal->bind_param("ii", $jadwal_id, $konselor_id_form);
    $stmt_jadwal->execute();
    $result_jadwal = $stmt_jadwal->get_result();

    if ($result_jadwal->num_rows > 0) {
        $jadwal = $result_jadwal->fetch_assoc();
        $waktu_mulai = $jadwal['waktu_mulai'];
        
        $conn->begin_transaction();
        
        try {
            // 2. Buat entri di 'bookings'
            $sql_booking = "INSERT INTO bookings (user_id, counselor_id, booking_date, booking_time, notes, status) 
                            VALUES (?, ?, DATE(?), TIME(?), ?, 'Pending Payment')";
            $stmt_booking = $conn->prepare($sql_booking);
            $stmt_booking->bind_param("iisss", $klien_id, $konselor_id_form, $waktu_mulai, $waktu_mulai, $catatan_awal);
            $stmt_booking->execute();
            
            $booking_id = $conn->insert_id;

            // 3. Update 'jadwal_tersedia'
            $sql_update_jadwal = "UPDATE jadwal_tersedia SET status_ketersediaan = 'Dipesan' WHERE id_jadwal = ?";
            $stmt_update_jadwal = $conn->prepare($sql_update_jadwal);
            $stmt_update_jadwal->bind_param("i", $jadwal_id);
            $stmt_update_jadwal->execute();

            $conn->commit();
            
            // (INI YANG GAGAL TADI) Sekarang bisa redirect karena HTML belum dikirim
            header("Location: payment.php?booking_id=" . $booking_id);
            exit; // Penting untuk stop eksekusi script setelah redirect

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error = "Terjadi kesalahan database: " . $exception->getMessage();
        }

    } else {
        $error = "Jadwal yang Anda pilih tidak tersedia atau tidak valid.";
    }
}
// --- AKHIR DARI LOGIKA PROSES FORM (POST) ---


// 5. SETELAH SEMUA LOGIKA, BARU PANGGIL HEADER (HTML)
$pageTitle = 'Form Booking';
include_once '../../templates/header.php'; 


// --- Logika untuk TAMPILKAN FORM (GET) ---
// (Pindahkan query GET ke bawah sini)
if ($konselor_id == 0) {
    echo "<p>Error: ID Konselor tidak valid.</p>";
    include_once '../../templates/footer.php';
    exit;
}

$sql_konselor = "SELECT nama_lengkap, spesialisasi FROM konselor WHERE id_konselor = ?";
$stmt_konselor = $conn->prepare($sql_konselor);
$stmt_konselor->bind_param("i", $konselor_id);
$stmt_konselor->execute();
$result_konselor = $stmt_konselor->get_result();
$konselor = $result_konselor->fetch_assoc();

if (!$konselor) {
    echo "<p>Error: Konselor tidak ditemukan.</p>";
    include_once '../../templates/footer.php';
    exit;
}

$sql_jadwal_list = "SELECT * FROM jadwal_tersedia 
                    WHERE id_konselor = ? AND status_ketersediaan = 'Tersedia' AND waktu_mulai > NOW()
                    ORDER BY waktu_mulai ASC";
$stmt_jadwal_list = $conn->prepare($sql_jadwal_list);
$stmt_jadwal_list->bind_param("i", $konselor_id);
$stmt_jadwal_list->execute();
$result_jadwal_list = $stmt_jadwal_list->get_result();
?>

<!-- Halaman Form Booking (HTML Mulai Di Sini) -->
<section id="booking-form" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">
        
        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-2 text-center text-gradient-love">
                Booking Sesi
            </h1>
            <p class="text-lg text-gray-600 text-center mb-6">
                dengan <?php echo htmlspecialchars($konselor['nama_lengkap']); ?>
            </p>

            <!-- Tampilkan Pesan Error (jika ada, dari proses POST di atas) -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Booking -->
            <form action="booking_form.php?id_konselor=<?php echo $konselor_id; ?>" method="POST" class="space-y-6">
                <!-- Hidden input untuk id konselor -->
                <input type="hidden" name="id_konselor" value="<?php echo $konselor_id; ?>">
                
                <div>
                    <label for="id_jadwal" class="block text-gray-700 mb-2 font-semibold">Pilih Jadwal Tersedia</label>
                    <select name="id_jadwal" id="id_jadwal" 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" required>
                        <option value="">-- Pilih jadwal --</option>
                        <?php if ($result_jadwal_list->num_rows > 0): ?>
                            <?php while($jadwal = $result_jadwal_list->fetch_assoc()): ?>
                                <option value="<?php echo $jadwal['id_jadwal']; ?>">
                                    <?php 
                                        $waktu = new DateTime($jadwal['waktu_mulai']);
                                        $fmt = new IntlDateFormatter(
                                            'id_ID',
                                            IntlDateFormatter::NONE,
                                            IntlDateFormatter::NONE,
                                            'Asia/Jakarta',
                                            IntlDateFormatter::GREGORIAN,
                                            'eeee, dd MMMM yyyy \'pukul\' HH:mm'
                                        );
                                        echo $fmt->format($waktu); 
                                    ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>Saat ini tidak ada jadwal tersedia untuk konselor ini.</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="catatan_awal" class="block text-gray-700 mb-2 font-semibold">Catatan Awal (Opsional)</label>
                    <textarea name="catatan_awal" id="catatan_awal" rows="4"
                              placeholder="Ceritakan singkat apa yang ingin Anda diskusikan..."
                              class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm"></textarea>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105"
                            <?php if ($result_jadwal_list->num_rows == 0) echo 'disabled'; ?>>
                        Lanjut ke Pembayaran
                    </button>
                    <?php if ($result_jadwal_list->num_rows == 0): ?>
                        <p class="text-center text-red-500 mt-2">Tidak bisa booking karena jadwal tidak tersedia.</p>
                    <?php endif; ?>
                </div>
                
                <p class="text-center text-gray-600 mt-4">
                    <a href="../counselor_profile.php?id=<?php echo $konselor_id; ?>" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Kembali ke Profil Konselor
                    </a>
                </p>
            </form>
            
        </div>
    </div>
</section>

<?php
$conn->close();
include_once '../../templates/footer.php';
?>

