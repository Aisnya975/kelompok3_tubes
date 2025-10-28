<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php KEDUA
$pageTitle = 'Edit Profil';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php KETIGA
include_once '../../config/check_user.php';
require_login('klien'); // Hanya KLIEN

// 4. Baru panggil config database
include_once '../../config/db_config.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$upload_dir = "../uploads/profile_pictures/"; // Folder penyimpanan

// Pastikan folder upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


// --- PROSES UPDATE FORM (METHOD POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email = $conn->real_escape_string($_POST['email']);
    $nomor_telepon = $conn->real_escape_string($_POST['nomor_telepon'] ?? '');
    $tanggal_lahir_input = $_POST['tanggal_lahir'] ?? '';
    // Handle tanggal lahir NULL
    $tanggal_lahir = !empty($tanggal_lahir_input) ? $conn->real_escape_string($tanggal_lahir_input) : NULL;
    
    // Password
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Path foto profil default (akan diupdate jika ada upload baru)
    $foto_profil_path = $_POST['foto_profil_lama'] ?? null; // Ambil path lama dari hidden input

    // Validasi dasar
     if (empty($nama_lengkap) || empty($email)) {
        $errors[] = "Nama Lengkap dan Email wajib diisi.";
    }
    
    // Cek jika password baru diisi dan cocok
    if (!empty($password_baru)) {
        if ($password_baru !== $konfirmasi_password) {
            $errors[] = "Konfirmasi password baru tidak cocok.";
        }
    }
    
    // Cek jika email sudah dipakai orang lain
    $cek_email_sql = "SELECT id_klien FROM klien WHERE email = ? AND id_klien != ?";
    $stmt_cek_email = $conn->prepare($cek_email_sql);
    $stmt_cek_email->bind_param("si", $email, $user_id);
    $stmt_cek_email->execute();
    if ($stmt_cek_email->get_result()->num_rows > 0) {
        $errors[] = "Email ini sudah digunakan oleh akun lain.";
    }
    $stmt_cek_email->close();

    // --- (TAMBAHAN) LOGIKA UPLOAD FOTO PROFIL BARU ---
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
        $fileName = $_FILES['foto_profil']['name'];
        $fileSize = $_FILES['foto_profil']['size'];
        $fileType = $_FILES['foto_profil']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Buat nama file unik
        $newFileName = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($fileName, PATHINFO_FILENAME));
        $newFileName = $newFileName . '_' . time() . '.' . $fileExtension;
        
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 2000000) { // Max 2MB
                $dest_path = $upload_dir . $newFileName;
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                     // Simpan path relatif dari folder public
                    $foto_profil_path = 'uploads/profile_pictures/' . $newFileName; 
                    // (Opsional) Hapus foto lama jika ada dan berhasil upload baru
                    $foto_lama = $_POST['foto_profil_lama'] ?? null;
                    if ($foto_lama && file_exists("../".$foto_lama) && strpos($foto_lama, 'default') === false) {
                        unlink("../".$foto_lama);
                    }
                } else {
                     $errors[] = 'Gagal memindahkan file yang diupload.';
                }
            } else {
                $errors[] = 'Ukuran file terlalu besar (maks 2MB).';
            }
        } else {
            $errors[] = 'Tipe file tidak diizinkan (hanya JPG, JPEG, PNG, GIF).';
        }
    } elseif (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] != UPLOAD_ERR_NO_FILE) {
        // Jika ada error upload selain "tidak ada file"
        $errors[] = 'Terjadi kesalahan saat mengupload file foto profil. Error code: ' . $_FILES['foto_profil']['error'];
    }
    // --- AKHIR LOGIKA UPLOAD ---


    if (empty($errors)) {
        // Bangun query UPDATE dinamis
        $sql_update_parts = [
            "nama_lengkap = ?",
            "email = ?",
            "nomor_telepon = ?",
            "tanggal_lahir = ?",
            "foto_profil = ?" // Tambahkan foto profil
        ];
        $params = [];
        $types = "sssss"; // 5 string (nama, email, telp, tgl, foto)
        
        $params[] = $nama_lengkap;
        $params[] = $email;
        $params[] = $nomor_telepon; // Sudah string kosong jika null
        $params[] = $tanggal_lahir; // Bisa NULL
        $params[] = $foto_profil_path; // Bisa NULL atau path string

        // Cek jika password baru diisi
        if (!empty($password_baru)) {
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql_update_parts[] = "password = ?";
            $types .= "s"; // Tambah 1 string
            $params[] = $hashed_password;
        }

        // Tambahkan ID klien di akhir untuk WHERE
        $types .= "i"; // Tambah 1 integer
        $params[] = $user_id; 

        $sql = "UPDATE klien SET " . implode(', ', $sql_update_parts) . " WHERE id_klien = ?";
        
        $stmt_update = $conn->prepare($sql);
        if ($stmt_update) {
            // Gunakan splat operator (...)
            $stmt_update->bind_param($types, ...$params); 

            if ($stmt_update->execute()) {
                $message = "Profil berhasil diperbarui.";
                // Perbarui data di sesi jika perlu (nama/email berubah)
                $_SESSION['user_name'] = $nama_lengkap;
                $_SESSION['user_email'] = $email;
                // Ambil data terbaru untuk ditampilkan (termasuk path foto baru)
                // $klien['foto_profil'] = $foto_profil_path; // Update data lokal
            } else {
                $error = "Gagal memperbarui profil: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error = "Gagal menyiapkan statement update: " . $conn->error;
        }
    }
}

// --- AMBIL DATA TERBARU UNTUK TAMPILKAN DI FORM ---
$sql_select = "SELECT * FROM klien WHERE id_klien = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $user_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows > 0) {
    $klien = $result_select->fetch_assoc();
} else {
    // Jika data tidak ditemukan (seharusnya tidak terjadi jika login benar)
    $error = "Error: Data klien tidak ditemukan.";
    $klien = []; // Beri nilai default agar form tidak error
}
$stmt_select->close();

?>

<!-- Halaman Form Edit Profil -->
<section id="edit-profile" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">
        
        <!-- Gunakan style 'card-glass' dari demo -->
        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-6 text-center text-gradient-love">
                Edit Profil Saya
            </h1>
            
            <!-- Tampilkan Pesan Sukses atau Error -->
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): // Ubah ke $errors ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                     <?php foreach ($errors as $err_msg): ?>
                         <p><?php echo $err_msg; ?></p>
                     <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Form Edit -->
            <!-- Tambahkan enctype="multipart/form-data" -->
            <form action="edit_profile.php" method="POST" class="space-y-6" enctype="multipart/form-data">
                
                 <!-- (TAMBAHAN) Tampilkan foto profil saat ini -->
                 <div class="text-center mb-6">
                     <img src="/<?php echo htmlspecialchars(!empty($klien['foto_profil']) ? $klien['foto_profil'] : 'public/assets/images/default_avatar.png'); ?>" 
                          alt="Foto Profil Saat Ini"
                          onerror="this.onerror=null; this.src='/public/assets/images/default_avatar.png';"
                          class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg inline-block">
                     <!-- Hidden input untuk menyimpan path foto lama -->
                     <input type="hidden" name="foto_profil_lama" value="<?php echo htmlspecialchars($klien['foto_profil'] ?? ''); ?>">
                 </div>

                 <!-- (TAMBAHAN) Input file untuk foto profil baru -->
                 <div>
                    <label for="foto_profil" class="block text-gray-700 mb-2 font-semibold">Ganti Foto Profil (Opsional)</label>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/jpeg, image/png, image/gif"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 text-gray-700
                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                  file:bg-blush-pink/60 file:text-rose-accent hover:file:bg-blush-pink">
                     <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, GIF. Maks 2MB.</p>
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-gray-700 mb-2 font-semibold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" 
                           value="<?php echo htmlspecialchars($klien['nama_lengkap'] ?? ''); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" required>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 mb-2 font-semibold">Email</label>
                    <input type="email" name="email" id="email" 
                           value="<?php echo htmlspecialchars($klien['email'] ?? ''); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" required>
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
                
                <div class="border-t border-blush-pink/50 pt-6">
                     <p class="font-semibold text-gray-700 mb-4">Ubah Password (Opsional)</p>
                     
                     <div>
                         <label for="password_baru" class="block text-gray-700 mb-2">Password Baru</label>
                         <input type="password" name="password_baru" id="password_baru" 
                                placeholder="Kosongkan jika tidak ingin diubah"
                                class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                    </div>
                     <div class="mt-4">
                         <label for="konfirmasi_password" class="block text-gray-700 mb-2">Konfirmasi Password Baru</label>
                         <input type="password" name="konfirmasi_password" id="konfirmasi_password" 
                                placeholder="Konfirmasi password baru Anda"
                                class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Perubahan
                    </button>
                </div>
                
                <p class="text-center text-gray-600 mt-4">
                    <a href="../dashboard.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Kembali ke Dashboard
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

