<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php (yang ada session_start()) KEDUA
$pageTitle = 'Buat Materi Baru';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_login('konselor'); // Hanya konselor

// 4. Baru panggil config database
include_once '../../config/db_config.php';

$errors = [];

// Proses form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $conn->real_escape_string($_POST['judul']);
    $konten = $conn->real_escape_string($_POST['konten']);
    $tipe_materi = $conn->real_escape_string($_POST['tipe_materi']);
    // (Perbaikan) Handle jika kategori tidak dipilih (NULL)
    $id_kategori = !empty($_POST['id_kategori']) ? intval($_POST['id_kategori']) : NULL;
    
    if (empty($judul) || empty($konten) || empty($tipe_materi)) {
        $errors[] = "Judul, Konten, dan Tipe Materi harus diisi.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO materi_pemulihan (judul, konten, tipe_materi, id_kategori) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // 'i' untuk integer (id_kategori), 's' untuk string
        // Tipe bind param harus 'sssi'
        $stmt->bind_param("sssi", $judul, $konten, $tipe_materi, $id_kategori);
        
        if ($stmt->execute()) {
            header("Location: list.php?success=created");
            exit();
        } else {
            $errors[] = "Gagal menyimpan materi: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil kategori
$kategori_result = $conn->query("SELECT * FROM kategori_materi ORDER BY nama_kategori");

?>

<!-- 5. (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="create-material" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">

        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">
            
            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Buat Materi Baru
            </h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="create.php" method="POST" class="space-y-6">
                <div>
                    <label for="judul" class="block text-gray-700 mb-2 font-semibold">Judul</label>
                    <input type="text" id="judul" name="judul" required 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                
                <div>
                    <label for="tipe_materi" class="block text-gray-700 mb-2 font-semibold">Tipe Materi</label>
                    <select id="tipe_materi" name="tipe_materi" required 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                        <option value="Artikel">Artikel</option>
                        <option value="Video">Video</option>
                        <option value="Latihan">Latihan</option>
                    </select>
                </div>

                <div>
                    <label for="id_kategori" class="block text-gray-700 mb-2 font-semibold">Kategori</label>
                    <select id="id_kategori" name="id_kategori" 
                            class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                        <option value="">-- Pilih Kategori (Opsional) --</option>
                        <?php while($kat = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo $kat['id_kategori']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label for="konten" class="block text-gray-700 mb-2 font-semibold">Konten</label>
                    <textarea id="konten" name="konten" rows="10" required 
                              class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm"></textarea>
                    <p class="text-xs text-gray-500 mt-2">Jika tipe 'Video', masukkan URL (misal: Youtube). Jika 'Artikel' atau 'Latihan', tulis kontennya.</p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Materi
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
$conn->close();
include_once '../../templates/footer.php';
?>

