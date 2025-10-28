<?php
// 1. (PERBAIKAN PATH) Tentukan base path agar link di header tidak error
$base_path_for_links = '../';

// 2. (PERBAIKAN INCLUDE) Mulai sesi (ada di header) dan konek DB
// Kita hapus 'check_user.php' karena header sudah menangani sesi
include_once '../../config/db_config.php'; 
$pageTitle = 'Materi Pemulihan';
include_once '../../templates/header.php'; 

// Ambil user type dari sesi (jika sudah login)
$user_type = $_SESSION['user_type'] ?? null;

// Query database (kode Anda sudah bagus)
$sql = "SELECT m.*, k.nama_kategori 
        FROM materi_pemulihan m 
        LEFT JOIN kategori_materi k ON m.id_kategori = k.id_kategori
        ORDER BY m.created_at DESC";
$result = $conn->query($sql);
?>

<!-- 3. (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="materi-list" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-6xl px-6">

        <!-- Judul Halaman yang Sesuai Tema -->
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-12 scroll-reveal">
            <div>
                <h1 class="font-playfair text-5xl font-bold text-gray-800 mb-4 text-gradient-love">
                    Materi Pemulihan
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl">
                    Pengetahuan dan latihan untuk membantu Anda menyembuhkan luka hati.
                </p>
            </div>
            
            <!-- Tombol "Tambah Materi" yang Sesuai Tema -->
            <?php if ($user_type == 'konselor'): ?>
            <div class="mt-6 md:mt-0">
                <a href="create.php" class="btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                    + Tambah Materi
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Daftar Materi -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    
                    <!-- 4. (PERBAIKAN DESAIN) Gunakan 'card-glass' -->
                    <div class="card-glass p-6 md:p-8 flex flex-col scroll-reveal">
                        
                        <!-- Tipe Materi & Kategori (Sesuai Tema) -->
                        <div class="flex justify-between items-center mb-4">
                            <?php if (!empty($row['nama_kategori'])): ?>
                                <span class="inline-block bg-lavender/70 text-indigo-800 text-xs font-semibold px-3 py-1 rounded-full self-start">
                                    <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="inline-block bg-blush-pink/70 text-rose-accent/90 text-xs font-semibold px-3 py-1 rounded-full self-start">
                                <?php echo htmlspecialchars($row['tipe_materi']); ?>
                            </span>
                        </div>

                        <!-- Judul (Sesuai Tema) -->
                        <h3 class="font-playfair text-2xl font-bold text-gray-800 mb-3">
                            <?php echo htmlspecialchars($row['judul']); ?>
                        </h3>
                        
                        <!-- Konten -->
                        <div class="text-gray-600 mb-6 flex-grow">
                            <?php if ($row['tipe_materi'] == 'Video'): ?>
                                
                                <!-- 5. (PERBAIKAN VIDEO) Logika Regex baru untuk ID YouTube -->
                                <?php
                                $video_id = '';
                                $url = $row['konten'];
                                // Regex ini menangkap ID dari format youtube.com/watch?v=... DAN youtu.be/...
                                if (preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
                                    $video_id = $matches[2];
                                }
                                ?>
                                <?php if ($video_id): ?>
                                    <div class="aspect-w-16 aspect-h-9">
                                        <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>" 
                                                frameborder="0" 
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                allowfullscreen
                                                class="w-full h-full rounded-lg"></iframe>
                                    </div>
                                <?php else: ?>
                                    <p class="text-red-500">Link video tidak valid.</p>
                                <?php endif; ?>

                            <?php else: // Artikel atau Latihan ?>
                                <p><?php echo nl2br(htmlspecialchars(substr(strip_tags($row['konten']), 0, 150))); ?>...</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Tombol Aksi (Baca/Edit/Delete) -->
                        <div class="border-t border-blush-pink/50 mt-4 pt-4 flex justify-between items-center">
                            <!-- TODO: Buat halaman view.php -->
                            <a href="#" class="font-semibold text-rose-accent hover:text-pink-600 transition-colors">
                                Baca Selengkapnya &rarr;
                            </a>

                            <?php if ($user_type == 'konselor'): ?>
                            <div classs="space-x-3">
                                <a href="edit.php?id=<?php echo $row['id_materi']; ?>" class="text-gray-500 hover:text-rose-accent text-sm font-medium">Edit</a>
                                <a href="delete.php?id=<?php echo $row['id_materi']; ?>" 
                                   class="text-gray-500 hover:text-red-500 text-sm font-medium"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus materi ini?');">Hapus</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- 6. (PERBAIKAN DESAIN) Kartu jika materi kosong -->
                <div class="card-glass p-8 md:p-12 text-center md:col-span-2 lg:col-span-3">
                    <h2 class="font-playfair text-2xl font-bold text-gray-700 mb-4">
                        Belum Ada Materi
                    </h2>
                    <p class="text-gray-600">
                        Kami sedang menyiapkan materi pemulihan terbaik untuk Anda. Silakan kembali lagi nanti.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</section>

<?php
$conn->close();
include_once '../../templates/footer.php';
?>

