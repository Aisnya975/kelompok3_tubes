<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_admin(); // Hanya admin

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

$errors = [];
$upload_dir = "../uploads/profile_pictures/"; // Folder penyimpanan foto profil

// Pastikan folder upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Proses form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // Password sementara
    $spesialisasi = $conn->real_escape_string($_POST['spesialisasi'] ?? '');
    $biografi_singkat = $conn->real_escape_string($_POST['biografi_singkat'] ?? '');
    $status = $conn->real_escape_string($_POST['status']);
    $foto_profil_path = null;
    
    if (empty($nama_lengkap) || empty($email) || empty($password) || empty($status)) {
        $errors[] = "Nama, Email, Password, dan Status wajib diisi.";
    }

    // Cek email unik
    $stmt_check = $conn->prepare("SELECT id_konselor FROM konselor WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $errors[] = "Email ini sudah terdaftar.";
    }
    $stmt_check->close();

    // --- (TAMBAHAN) LOGIKA UPLOAD FOTO PROFIL ---
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
        $fileName = $_FILES['foto_profil']['name'];
        $fileSize = $_FILES['foto_profil']['size'];
        $fileType = $_FILES['foto_profil']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($fileName, PATHINFO_FILENAME));
        $newFileName = $newFileName . '_' . time() . '.' . $fileExtension;
        
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 2000000) { 
                $dest_path = $upload_dir . $newFileName;
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $foto_profil_path = 'uploads/profile_pictures/' . $newFileName; 
                } else {
                     $errors[] = 'Gagal memindahkan file yang diupload.';
                }
            } else {
                $errors[] = 'Ukuran file terlalu besar (maks 2MB).';
            }
        } else {
            $errors[] = 'Tipe file tidak diizinkan (hanya JPG, JPEG, PNG, GIF).';
        }
    }
    // --- AKHIR LOGIKA UPLOAD ---

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql_insert = "INSERT INTO konselor (nama_lengkap, email, password, spesialisasi, biografi_singkat, status, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        // Tipe bind param: ssssss (6 string) + s (1 string foto)
        $stmt_insert->bind_param("sssssss", $nama_lengkap, $email, $hashed_password, $spesialisasi, $biografi_singkat, $status, $foto_profil_path);
        
        if ($stmt_insert->execute()) {
            $stmt_insert->close();
            // $conn->close(); // Jangan tutup koneksi sebelum redirect
            header("Location: list.php?success=created");
            exit();
        } else {
            $errors[] = "Gagal membuat konselor: " . $stmt_insert->error;
            $stmt_insert->close();
        }
    }
}

// BARU panggil header.php SETELAH logika POST
$pageTitle = 'Tambah Konselor Baru';
include_once '../../templates/header.php';
?>

<!-- Layout Form Create -->
<section id="create-counselor" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Tambah Konselor Baru
            </h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Tambahkan enctype="multipart/form-data" -->
            <form action="create.php" method="POST" class="space-y-6" enctype="multipart/form-data">
                <div>
                    <label for="nama_lengkap" class="block text-gray-700 mb-2 font-semibold">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                           value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-gray-700 mb-2 font-semibold">Email</label>
                    <input type="email" id="email" name="email" required 
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="password" class="block text-gray-700 mb-2 font-semibold">Password Sementara</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="spesialisasi" class="block text-gray-700 mb-2 font-semibold">Spesialisasi</label>
                    <input type="text" id="spesialisasi" name="spesialisasi" 
                            value="<?php echo isset($_POST['spesialisasi']) ? htmlspecialchars($_POST['spesialisasi']) : ''; ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                 <div>
                    <label for="biografi_singkat" class="block text-gray-700 mb-2 font-semibold">Biografi Singkat</label>
                    <textarea id="biografi_singkat" name="biografi_singkat" rows="4" 
                              class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm"><?php echo isset($_POST['biografi_singkat']) ? htmlspecialchars($_POST['biografi_singkat']) : ''; ?></textarea>
                </div>
                 <div>
                    <label for="status" class="block text-gray-700 mb-2 font-semibold">Status Awal</label>
                    <select id="status" name="status" 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                        <option value="Menunggu Verifikasi" selected>Menunggu Verifikasi</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                    </select>
                </div>

                 <!-- (TAMBAHAN) Input untuk upload foto profil -->
                 <div>
                    <label for="foto_profil" class="block text-gray-700 mb-2 font-semibold">Foto Profil (Opsional)</label>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/jpeg, image/png, image/gif"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 text-gray-700
                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                  file:bg-blush-pink/60 file:text-rose-accent hover:file:bg-blush-pink">
                     <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, GIF. Maks 2MB.</p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Konselor Baru
                    </button>
                </div>
                
                <p class="text-center text-gray-600 mt-4">
                    <a href="list.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Batal dan Kembali ke Daftar Konselor
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

