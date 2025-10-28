<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php (yang ada session_start()) KEDUA
$pageTitle = 'Edit Klien';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_admin(); // Hanya admin

// 4. Baru panggil config database
include_once '../../config/db_config.php';
$errors = [];
$klien_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($klien_id == 0) {
    header("Location: list.php?error=invalid_id");
    exit();
}

// Proses form UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email = $conn->real_escape_string($_POST['email']);
    $nomor_telepon = $conn->real_escape_string($_POST['nomor_telepon'] ?? '');
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir'] ?? '');
    $password_baru = $_POST['password_baru'];

    if (empty($nama_lengkap) || empty($email)) {
        $errors[] = "Nama Lengkap dan Email wajib diisi.";
    }

    // Cek email unik (pastikan bukan email milik user ini sendiri)
    $stmt_check = $conn->prepare("SELECT id_klien FROM klien WHERE email = ? AND id_klien != ?");
    $stmt_check->bind_param("si", $email, $klien_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $errors[] = "Email ini sudah terdaftar pada klien lain.";
    }
    $stmt_check->close();

    if (empty($errors)) {
        // Logika update
        $sql_parts = [
            "nama_lengkap = ?",
            "email = ?",
            "nomor_telepon = ?",
            "tanggal_lahir = " . ($tanggal_lahir ? "'$tanggal_lahir'" : "NULL")
        ];
        $params = ["sss", $nama_lengkap, $email, $nomor_telepon];
        
        if (!empty($password_baru)) {
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql_parts[] = "password = ?";
            $params[0] .= "s"; // Tambah 's' ke tipe data
            $params[] = $hashed_password;
        }
        
        $params[] = $klien_id; // Tambah ID di akhir untuk WHERE
        $sql_parts_string = implode(', ', $sql_parts);
        
        $sql_update = "UPDATE klien SET $sql_parts_string WHERE id_klien = ?";
        $params[0] .= "i"; // Tambah 'i' ke tipe data untuk WHERE
        
        $stmt_update = $conn->prepare($sql_update);
        call_user_func_array([$stmt_update, 'bind_param'], array_merge([$params[0]], array_slice($params, 1)));

        if ($stmt_update->execute()) {
            $stmt_update->close();
            $conn->close();
            header("Location: list.php?success=updated");
            exit();
        } else {
            $errors[] = "Gagal memperbarui klien: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

// Ambil data klien yang akan diedit (untuk mengisi form)
$sql_klien = "SELECT * FROM klien WHERE id_klien = ?";
$stmt_klien = $conn->prepare($sql_klien);
$stmt_klien->bind_param("i", $klien_id);
$stmt_klien->execute();
$klien_result = $stmt_klien->get_result();

if ($klien_result->num_rows == 0) {
    header("Location: list.php?error=not_found");
    exit();
}
$klien = $klien_result->fetch_assoc();
$stmt_klien->close();

?>

<!-- (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="edit-client" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">
        
        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">

            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Edit Klien #<?php echo $klien_id; ?>
            </h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="edit.php?id=<?php echo $klien_id; ?>" method="POST" class="space-y-6">
                <div>
                    <label for="nama_lengkap" class="block text-gray-700 mb-2 font-semibold">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                           value="<?php echo htmlspecialchars($klien['nama_lengkap']); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-gray-700 mb-2 font-semibold">Email</LabeL>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($klien['email']); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                 <div>
                    <label for="nomor_telepon" class="block text-gray-700 mb-2 font-semibold">Nomor Telepon</label>
                    <input type="tel" name="nomor_telepon" id="nomor_telepon" 
                           value="<?php echo htmlspecialchars($klien['nomor_telepon'] ?? ''); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="tanggal_lahir" class="block text-gray-700 mb-2 font-semibold">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
                           value="<?php echo htmlspecialchars($klien['tanggal_lahir'] ?? ''); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="password_baru" class="block text-gray-700 mb-2 font-semibold">Password Baru (Opsional)</label>
                    <input type="password" id="password_baru" name="password_baru" 
                           placeholder="Kosongkan jika tidak ingin diubah"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                
                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Perubahan
                    </button>
                </div>
                <p class="text-center text-gray-600 mt-4">
                    <a href="list.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Batal dan Kembali ke Daftar
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

