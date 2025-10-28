<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

// 4. Proses Form (POST) SEBELUM header.php
$errors = [];
$message = '';
$konselor_id = $_SESSION['counselor_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan validasi input waktu
    $waktu_mulai_input = $_POST['waktu_mulai'] ?? null;
    $waktu_selesai_input = $_POST['waktu_selesai'] ?? null;

    if (empty($waktu_mulai_input) || empty($waktu_selesai_input)) {
        $errors[] = "Waktu mulai dan waktu selesai harus diisi.";
    } else {
        try {
            $waktu_mulai = new DateTime($waktu_mulai_input);
            $waktu_selesai = new DateTime($waktu_selesai_input);

            // Validasi: Waktu selesai harus setelah waktu mulai
            if ($waktu_selesai <= $waktu_mulai) {
                $errors[] = "Waktu selesai harus setelah waktu mulai.";
            }
            // Validasi: Waktu mulai harus di masa depan
            if ($waktu_mulai <= new DateTime()) {
                 $errors[] = "Waktu mulai harus di masa depan.";
            }
            // Validasi: Cek tumpang tindih dengan jadwal lain konselor ini (opsional tapi bagus)
            // $sql_check_overlap = "SELECT id_jadwal FROM jadwal_tersedia 
            //                       WHERE id_konselor = ? 
            //                       AND NOT (waktu_selesai <= ? OR waktu_mulai >= ?)";
            // $stmt_check = $conn->prepare($sql_check_overlap);
            // $stmt_check->bind_param("iss", $konselor_id, $waktu_mulai->format('Y-m-d H:i:s'), $waktu_selesai->format('Y-m-d H:i:s'));
            // $stmt_check->execute();
            // if ($stmt_check->get_result()->num_rows > 0) {
            //     $errors[] = "Jadwal yang Anda masukkan tumpang tindih dengan jadwal lain.";
            // }
            // $stmt_check->close();


        } catch (Exception $e) {
            $errors[] = "Format waktu tidak valid.";
        }
    }

    if (empty($errors)) {
        // Format waktu untuk database MySQL (YYYY-MM-DD HH:MM:SS)
        $waktu_mulai_db = $waktu_mulai->format('Y-m-d H:i:s');
        $waktu_selesai_db = $waktu_selesai->format('Y-m-d H:i:s');

        $sql = "INSERT INTO jadwal_tersedia (id_konselor, waktu_mulai, waktu_selesai, status_ketersediaan) 
                VALUES (?, ?, ?, 'Tersedia')";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iss", $konselor_id, $waktu_mulai_db, $waktu_selesai_db);
            if ($stmt->execute()) {
                // Berhasil, redirect kembali ke halaman manajemen jadwal
                 header("Location: schedule_management.php?success=added");
                 exit();
            } else {
                $errors[] = "Gagal menyimpan jadwal ke database: " . $stmt->error;
            }
            $stmt->close();
        } else {
             $errors[] = "Gagal menyiapkan statement database: " . $conn->error;
        }
    }
}

// 5. BARU panggil header.php KETIGA
$pageTitle = 'Tambah Jadwal Baru';
include_once '../../templates/header.php'; 
?>

<!-- Halaman Form Tambah Jadwal -->
<section id="add-schedule" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-lg px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Tambah Jadwal Tersedia Baru
            </h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="add_schedule.php" method="POST" class="space-y-6">
                
                <div>
                    <label for="waktu_mulai" class="block text-gray-700 mb-2 font-semibold">Waktu Mulai Sesi</label>
                    <input type="datetime-local" id="waktu_mulai" name="waktu_mulai" required 
                           value="<?php echo isset($_POST['waktu_mulai']) ? htmlspecialchars($_POST['waktu_mulai']) : ''; ?>"
                           min="<?php echo date('Y-m-d\TH:i'); // Minimal waktu saat ini ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                     <p class="text-xs text-gray-500 mt-1">Pilih tanggal dan jam mulai sesi.</p>
                </div>

                 <div>
                    <label for="waktu_selesai" class="block text-gray-700 mb-2 font-semibold">Waktu Selesai Sesi</label>
                    <input type="datetime-local" id="waktu_selesai" name="waktu_selesai" required 
                            value="<?php echo isset($_POST['waktu_selesai']) ? htmlspecialchars($_POST['waktu_selesai']) : ''; ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                      <p class="text-xs text-gray-500 mt-1">Pilih tanggal dan jam selesai sesi.</p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Jadwal
                    </button>
                </div>
                
                <p class="text-center text-gray-600 mt-4">
                    <a href="schedule_management.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Batal dan Kembali ke Kelola Jadwal
                    </a>
                </p>
            </form>
        </div>

    </div>
</section>

<?php
if(isset($conn)) $conn->close();
include_once '../../templates/footer.php';
?>
