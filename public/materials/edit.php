<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. (PERBAIKAN URUTAN) Panggil check_user.php PERTAMA 
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor (atau admin?)

// 3. (PERBAIKAN URUTAN) Panggil config database KEDUA
include_once '../../config/db_config.php';

$materi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$message = '';

// Jika ID tidak valid, redirect SEKARANG
if ($materi_id <= 0) {
    header("Location: list.php?error=invalid_id");
    exit();
}

// 4. (PERBAIKAN URUTAN) Pindahkan logika proses POST ke ATAS SINI
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $conn->real_escape_string($_POST['judul']);
    // (PERUBAHAN) Ambil 'konten_url' sekarang
    $konten_url = $conn->real_escape_string($_POST['konten_url']); 
    $tipe_materi = $conn->real_escape_string($_POST['tipe_materi']);
    $id_kategori = !empty($_POST['id_kategori']) ? intval($_POST['id_kategori']) : NULL;
    
    // Validasi: Judul, URL, Tipe harus diisi
    if (empty($judul) || empty($konten_url) || empty($tipe_materi)) {
        $errors[] = "Judul, Link Konten, dan Tipe Materi harus diisi.";
    } 
    // Validasi: Cek apakah URL valid (opsional tapi bagus)
    elseif (!filter_var($konten_url, FILTER_VALIDATE_URL)) {
         $errors[] = "Format Link Konten tidak valid.";
    }


    if (empty($errors)) {
        // (PERUBAHAN) Update kolom 'konten' dengan $konten_url
        $sql_update = "UPDATE materi_pemulihan SET judul = ?, konten = ?, tipe_materi = ?, id_kategori = ? WHERE id_materi = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            // Tipe bind param: sssi (judul, konten_url, tipe) + i (id_kategori) + i (id_materi)
             $stmt_update->bind_param("sssii", $judul, $konten_url, $tipe_materi, $id_kategori, $materi_id);
            
            if ($stmt_update->execute()) {
                 $stmt_update->close();
                 header("Location: list.php?success=updated"); 
                 exit(); 
            } else {
                $errors[] = "Gagal memperbarui materi: " . $stmt_update->error;
            }
             $stmt_update->close(); 
        } else {
             $errors[] = "Gagal menyiapkan statement update: " . $conn->error;
        }
    }
}
// --- AKHIR LOGIKA PROSES POST ---


// 5. BARU panggil header.php SETELAH logika POST selesai
$pageTitle = 'Edit Materi';
include_once '../../templates/header.php'; 

// --- AMBIL DATA MATERI UNTUK FORM (GET) ---
$sql_select = "SELECT * FROM materi_pemulihan WHERE id_materi = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $materi_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows == 0) {
    echo '<section class="py-24 pt-32 bg-pastel-cream min-h-screen"><div class="container mx-auto max-w-lg px-6 py-20"><p class="text-red-600 text-center font-semibold">Error: Materi tidak ditemukan.</p></div></section>';
    $stmt_select->close();
    if(isset($conn)) $conn->close();
    include_once '../../templates/footer.php';
    exit();
}

$materi = $result_select->fetch_assoc();
$stmt_select->close();

// Ambil kategori untuk dropdown
$kategori_result = $conn->query("SELECT * FROM kategori_materi ORDER BY nama_kategori");
?>

<!-- 6. Layout Form Edit -->
<section id="edit-material" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Edit Materi #<?php echo $materi_id; ?>
            </h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
             <?php if (!empty($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                   <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>
            
            <form action="edit.php?id=<?php echo $materi_id; ?>" method="POST" class="space-y-6">
                
                <div>
                    <label for="judul" class="block text-gray-700 mb-2 font-semibold">Judul Materi</label>
                    <input type="text" id="judul" name="judul" required 
                           value="<?php echo htmlspecialchars($materi['judul']); ?>"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                
                <div>
                    <label for="tipe_materi" class="block text-gray-700 mb-2 font-semibold">Tipe Materi</label>
                    <select id="tipe_materi" name="tipe_materi" required 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                        <option value="Artikel" <?php echo ($materi['tipe_materi'] == 'Artikel') ? 'selected' : ''; ?>>Artikel Eksternal</option>
                        <option value="Video" <?php echo ($materi['tipe_materi'] == 'Video') ? 'selected' : ''; ?>>Video Eksternal (misal: YouTube)</option>
                        <option value="Latihan" <?php echo ($materi['tipe_materi'] == 'Latihan') ? 'selected' : ''; ?>>Latihan/Sumber Eksternal</option>
                    </select>
                </div>

                <div>
                    <label for="id_kategori" class="block text-gray-700 mb-2 font-semibold">Kategori</label>
                    <select id="id_kategori" name="id_kategori" 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                        <option value="">-- Pilih Kategori (Opsional) --</option>
                        <?php if($kategori_result) : // Cek jika query kategori berhasil ?>
                            <?php while($kat = $kategori_result->fetch_assoc()): ?>
                                <option value="<?php echo $kat['id_kategori']; ?>" <?php echo ($materi['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- (PERUBAHAN) Ganti textarea menjadi input URL -->
                <div>
                    <label for="konten_url" class="block text-gray-700 mb-2 font-semibold">Link Konten Eksternal</label>
                    <input type="url" id="konten_url" name="konten_url" required 
                           value="<?php echo htmlspecialchars($materi['konten']); ?>"
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                    <p class="text-xs text-gray-500 mt-2">Masukkan URL lengkap (termasuk https://) ke video, artikel, atau sumber lainnya.</p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Perubahan
                    </button>
                </div>
                
                <p class="text-center text-gray-600 mt-4">
                    <a href="list.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Batal dan Kembali ke Daftar Materi
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

