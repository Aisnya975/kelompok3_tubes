<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_login(); // Admin atau Konselor (cek ID di bawah)

// 3. Panggil config database KEDUA
include_once '../../config/db_config.php';

$konselor_id_url = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$message = '';
$upload_dir = "../uploads/profile_pictures/"; // Folder penyimpanan foto profil relatif dari /public/konselor/

// Pastikan folder upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Verifikasi: Apakah user admin ATAU konselor yang sesuai?
$is_admin = (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin');
$is_own_profile = (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'konselor' && isset($_SESSION['counselor_id']) && $_SESSION['counselor_id'] == $konselor_id_url);

if (!$is_admin && !$is_own_profile) {
    header("Location: " . ($base_path_for_links ?? './') . "dashboard.php?error=unauthorized");
    exit();
}

if ($konselor_id_url <= 0) {
     header("Location: " . ($is_admin ? 'list.php' : '../dashboard.php') . "?error=invalid_id"); 
    exit();
}

// --- LOGIKA PROSES FORM (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $konselor_id_form = isset($_POST['id_konselor']) ? intval($_POST['id_konselor']) : 0;
     if ($konselor_id_form !== $konselor_id_url) {
         $errors[] = "ID Konselor tidak cocok.";
     } else {
        $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
        $email = $conn->real_escape_string($_POST['email']);
        $spesialisasi = $conn->real_escape_string($_POST['spesialisasi'] ?? ''); 
        $biografi_singkat = $conn->real_escape_string($_POST['biografi_singkat'] ?? ''); 
        $status = $is_admin ? $conn->real_escape_string($_POST['status']) : null; 
        $password_baru = $_POST['password_baru']; 
        $foto_profil_path = null; // Path foto profil yang akan disimpan

        if (empty($nama_lengkap) || empty($email)) { 
            $errors[] = "Nama Lengkap dan Email wajib diisi.";
        }

        // Cek email unik
        $stmt_check_email = $conn->prepare("SELECT id_konselor FROM konselor WHERE email = ? AND id_konselor != ?");
        $stmt_check_email->bind_param("si", $email, $konselor_id_url);
        $stmt_check_email->execute();
        if ($stmt_check_email->get_result()->num_rows > 0) {
            $errors[] = "Email ini sudah digunakan oleh konselor lain.";
        }
        $stmt_check_email->close();

        // --- (TAMBAHAN) LOGIKA UPLOAD FOTO PROFIL ---
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto_profil']['tmp_name'];
            $fileName = $_FILES['foto_profil']['name'];
            $fileSize = $_FILES['foto_profil']['size'];
            $fileType = $_FILES['foto_profil']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitasi nama file & buat nama unik
            $newFileName = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($fileName, PATHINFO_FILENAME));
            $newFileName = $newFileName . '_' . time() . '.' . $fileExtension;
            
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Batasi ukuran file (misal: 2MB)
                if ($fileSize < 2000000) { 
                    $dest_path = $upload_dir . $newFileName;
                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        // Simpan path relatif dari FOLDER PUBLIC (penting untuk tag <img>)
                        $foto_profil_path = 'uploads/profile_pictures/' . $newFileName; 
                        
                        // Hapus foto lama jika ada (opsional)
                        $sql_old_photo = "SELECT foto_profil FROM konselor WHERE id_konselor = ?";
                        $stmt_old = $conn->prepare($sql_old_photo);
                        $stmt_old->bind_param("i", $konselor_id_url);
                        $stmt_old->execute();
                        $old_photo_res = $stmt_old->get_result()->fetch_assoc();
                        if ($old_photo_res && !empty($old_photo_res['foto_profil']) && file_exists('../' . $old_photo_res['foto_profil'])) {
                             unlink('../' . $old_photo_res['foto_profil']);
                        }
                        $stmt_old->close();

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
            $sql_update_parts = [
                "nama_lengkap = ?", "email = ?", "spesialisasi = ?", "biografi_singkat = ?"
            ];
            $params = [];
            $types = "ssss"; 
            
            $params[] = &$nama_lengkap; 
            $params[] = &$email;
            $params[] = &$spesialisasi;
            $params[] = &$biografi_singkat;

            if ($is_admin && $status !== null) {
                $sql_update_parts[] = "status = ?";
                $types .= "s";
                $params[] = &$status;
            }

            if (!empty($password_baru)) {
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql_update_parts[] = "password = ?";
                $types .= "s"; 
                $params[] = &$hashed_password;
            }
            
            // Tambahkan foto profil jika berhasil diupload
            if ($foto_profil_path !== null) {
                 $sql_update_parts[] = "foto_profil = ?";
                 $types .= "s";
                 $params[] = &$foto_profil_path;
            }


            $types .= "i"; 
            $params[] = &$konselor_id_url; // ID untuk WHERE

            $sql_update = "UPDATE konselor SET " . implode(', ', $sql_update_parts) . " WHERE id_konselor = ?";
            
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) { // Cek jika prepare berhasil
                $stmt_update->bind_param($types, ...$params); 
                if ($stmt_update->execute()) {
                    $stmt_update->close();
                    $redirect_page = $is_admin ? 'list.php' : '../dashboard.php';
                    header("Location: " . $redirect_page . "?success=updated");
                    exit(); 
                } else {
                    $errors[] = "Gagal memperbarui konselor: " . $stmt_update->error;
                    $stmt_update->close(); 
                }
            } else {
                 $errors[] = "Gagal menyiapkan update: " . $conn->error;
            }
        }
    } 
}
// --- AKHIR LOGIKA PROSES POST ---


// BARU panggil header.php SETELAH logika POST selesai
$pageTitle = 'Edit Konselor';
include_once '../../templates/header.php'; 

// --- AMBIL DATA KONSELOR UNTUK FORM (GET) ---
// (Kode ambil data $konselor tetap sama)
$sql_select = "SELECT * FROM konselor WHERE id_konselor = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $konselor_id_url);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows == 0) {
    echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error: Data konselor tidak ditemukan.</p></div></section>';
    $stmt_select->close();
    if(isset($conn)) $conn->close();
    include_once '../../templates/footer.php';
    exit();
}
$konselor = $result_select->fetch_assoc();
$stmt_select->close();

?>

<!-- Layout Form Edit -->
<section id="edit-counselor" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Edit Profil Konselor
            </h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Tambahkan enctype="multipart/form-data" -->
            <form action="edit.php?id=<?php echo $konselor_id_url; ?>" method="POST" class="space-y-6" enctype="multipart/form-data">
                 <input type="hidden" name="id_konselor" value="<?php echo $konselor_id_url; ?>">

                <div>
                    <label for="nama_lengkap" class="block text-gray-700 mb-2 font-semibold">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                           value="<?php echo htmlspecialchars($konselor['nama_lengkap']); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-gray-700 mb-2 font-semibold">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($konselor['email']); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="password_baru" class="block text-gray-700 mb-2 font-semibold">Password Baru (Opsional)</label>
                    <input type="password" id="password_baru" name="password_baru" 
                           placeholder="Kosongkan jika tidak ingin diubah"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="spesialisasi" class="block text-gray-700 mb-2 font-semibold">Spesialisasi</label>
                    <input type="text" id="spesialisasi" name="spesialisasi" 
                           value="<?php echo htmlspecialchars($konselor['spesialisasi'] ?? ''); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                 <div>
                    <label for="biografi_singkat" class="block text-gray-700 mb-2 font-semibold">Biografi Singkat</label>
                    <textarea id="biografi_singkat" name="biografi_singkat" rows="4" 
                              class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm"><?php echo htmlspecialchars($konselor['biografi_singkat'] ?? ''); ?></textarea>
                </div>
                
                 <?php if ($is_admin): ?>
                 <div>
                    <label for="status" class="block text-gray-700 mb-2 font-semibold">Status</label>
                    <select id="status" name="status" 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                        <option value="Menunggu Verifikasi" <?php echo ($konselor['status'] == 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="Aktif" <?php echo ($konselor['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Non-Aktif" <?php echo ($konselor['status'] == 'Non-Aktif') ? 'selected' : ''; ?>>Non-Aktif</option>
                    </select>
                </div>
                <?php endif; ?>

                 <!-- (TAMBAHAN) Input untuk upload foto profil -->
                 <div>
                    <label for="foto_profil" class="block text-gray-700 mb-2 font-semibold">Foto Profil (Opsional)</label>
                    <!-- Tampilkan foto saat ini -->
                     <?php if (!empty($konselor['foto_profil']) && file_exists('../'.$konselor['foto_profil'])): ?>
                         <img src="<?php echo $base_path_for_links; ?><?php echo htmlspecialchars($konselor['foto_profil']); ?>" alt="Foto profil saat ini" class="w-20 h-20 rounded-full object-cover mb-2 border border-blush-pink shadow-sm">
                     <?php else: ?>
                          <img src="<?php echo $base_path_for_links; ?>assets/images/default_avatar.png" alt="Foto profil default" class="w-20 h-20 rounded-full object-cover mb-2 border border-gray-300">
                     <?php endif; ?>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/jpeg, image/png, image/gif"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 text-gray-700
                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                  file:bg-blush-pink/60 file:text-rose-accent hover:file:bg-blush-pink">
                     <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, GIF. Maks 2MB. Upload file baru untuk mengganti foto saat ini.</p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Perubahan
                    </button>
                </div>
                
                <p class="text-center text-gray-600 mt-4">
                     <?php if ($is_admin): ?>
                        <a href="list.php" class="hover:text-rose-accent hover:underline font-semibold">
                            &larr; Kembali ke Daftar Konselor
                        </a>
                     <?php else: ?>
                         <a href="../dashboard.php" class="hover:text-rose-accent hover:underline font-semibold">
                            &larr; Kembali ke Dashboard
                        </a>
                     <?php endif; ?>
                </p>
            </form>
        </div>

    </div>
</section>

<?php
if(isset($conn)) $conn->close();
include_once '../../templates/footer.php';
?>

