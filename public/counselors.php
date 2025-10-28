<?php 
require '../config/db_config.php';
$pageTitle = 'Daftar Konselor';
include '../templates/header.php'; 

// Ambil data konselor yang statusnya 'Aktif'
$sql = "SELECT id_konselor, nama_lengkap, spesialisasi, foto_profil, biografi_singkat 
        FROM konselor 
        WHERE status = 'Aktif'";
$result = $conn->query($sql);
?>

<!-- KONTEN HTML BARU DENGAN STYLE ROMANTIS -->
<main class="pt-32 pb-16 min-h-screen"> <!-- Beri padding atas untuk header sticky -->
    <section id="counselor-list" class="relative">
        <div class="container mx-auto max-w-6xl px-6">

            <div class="text-center mb-16">
                <h2 class="font-playfair text-5xl font-bold text-gray-800 mb-4 text-gradient-love scroll-reveal">
                    Temui Konselor Kami
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto scroll-reveal" style="transition-delay: 0.1s;">
                    Profesional berlisensi yang siap membantu Anda dalam perjalanan pemulihan hati.
                </p>
            </div>

            <!-- Grid untuk Daftar Konselor -->
            <div class="grid md:grid-cols-3 gap-8">

                <?php if ($result && $result->num_rows > 0): ?>
                    <?php 
                    $delay = 0.1; // Inisialisasi delay animasi
                    while($row = $result->fetch_assoc()): 
                        $delay += 0.1; // Tambah delay untuk setiap kartu
                        
                        // Fallback untuk foto profil
                        $foto_profil_url = (!empty($row['foto_profil']) && file_exists($row['foto_profil'])) 
                                           ? htmlspecialchars($row['foto_profil']) 
                                           : 'https://placehold.co/400x400/FFD5E5/333?text=' . htmlspecialchars(substr($row['nama_lengkap'], 0, 1));
                    ?>
                        <!-- Kartu Konselor (Menggunakan style service-card) -->
                        <div class="service-card bg-white p-8 rounded-2xl shadow-lg shadow-blush-pink/50 text-center flex flex-col justify-between scroll-reveal" 
                             style="transition-delay: <?php echo $delay; ?>s;">
                            
                            <div>
                                <img src="<?php echo $foto_profil_url; ?>" alt="Foto Profil <?php echo htmlspecialchars($row['nama_lengkap']); ?>"
                                     class="w-32 h-32 rounded-full mx-auto mb-6 object-cover border-4 border-blush-pink shadow-md">
                                
                                <h3 class="font-playfair text-2xl font-semibold mb-2 text-gray-800">
                                    <?php echo htmlspecialchars($row['nama_lengkap']); ?>
                                </h3>
                                
                                <p class="text-rose-accent font-medium text-lg mb-4">
                                    <?php echo htmlspecialchars($row['spesialisasi'] ?? 'Konselor Profesional'); ?>
                                </p>
                                
                                <p class="text-gray-600 text-sm mb-6">
                                    <?php echo htmlspecialchars(substr($row['biografi_singkat'] ?? 'Berdedikasi untuk membantu Anda pulih.', 0, 100)) . '...'; ?>
                                </p>
                            </div>

                            <a href="counselor_profile.php?id=<?php echo $row['id_konselor']; ?>" 
                               class="btn-glow bg-rose-accent text-white font-semibold py-2 px-6 rounded-full text-base shadow-lg shadow-rose-accent/40 transition-all duration-300 hover:scale-105 hover:bg-pink-600 mt-4 inline-block">
                                Lihat Profil & Booking
                            </a>
                        </div>
                    
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Jika tidak ada konselor -->
                    <div class="md:col-span-3 text-center card-glass p-12">
                        <h3 class="font-playfair text-2xl font-semibold mb-4 text-gray-700">Belum Ada Konselor</h3>
                        <p class="text-gray-600">Saat ini belum ada konselor yang tersedia. Silakan cek kembali nanti.</p>
                    </div>
                <?php endif; ?>

            </div> <!-- end grid -->

        </div> <!-- end container -->
    </section>
</main>

<?php 
$conn->close();
include '../templates/footer.php'; 
?>

