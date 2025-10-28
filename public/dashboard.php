<?php
// 1. Tentukan base path DULU
$base_path_for_links = './'; 

// 2. Panggil header.php KEDUA 
$pageTitle = 'Dashboard';
include_once '../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php KETIGA
include_once '../config/check_user.php'; 
// Tidak perlu require_login() spesifik

$user_name = $_SESSION['user_name'] ?? 'Pengguna';
$role = $_SESSION['user_type'] ?? null; 

if (!$role) {
    header("Location: login.php?error=session_expired");
    exit;
}

?>

<!-- KONTEN HTML BARU DENGAN STYLE ROMANTIS -->
<main class="pt-32 pb-16 min-h-screen"> 
    <section id="dashboard-content" class="relative">
        <div class="container mx-auto max-w-4xl px-6">

            <div class="card-glass p-8 md:p-12 shadow-soft-pink">
                
                <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-6 text-center text-gradient-love scroll-reveal">
                    Selamat Datang, <?php echo htmlspecialchars($user_name); ?>!
                </h2>
                <p class="text-center text-lg text-gray-700 mb-10 scroll-reveal" style="transition-delay: 0.1s;">
                    Ini adalah ruang aman Anda untuk mengelola sesi dan perjalanan pemulihan Anda.
                </p>

                <!-- Konten Dashboard Berdasarkan Role -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <?php if ($role === 'klien'): ?>
                        <!-- Menu untuk Klien -->
                        <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.2s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Booking Saya</h3>
                            <p class="text-gray-600 mb-5">Lihat riwayat dan status sesi konseling Anda.</p>
                            <a href="booking/booking_list_user.php" class="font-semibold text-rose-accent hover:underline">Lihat Daftar Booking &rarr;</a>
                        </div>
                        
                        <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.3s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Cari Konselor</h3>
                            <p class="text-gray-600 mb-5">Temukan konselor yang tepat untuk Anda.</p>
                            <a href="counselors.php" class="font-semibold text-rose-accent hover:underline">Lihat Semua Konselor &rarr;</a>
                        </div>
                        
                        <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.4s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Profil Saya</h3>
                            <p class="text-gray-600 mb-5">Perbarui informasi pribadi dan foto profil Anda.</p>
                            <a href="<?php echo $base_path_for_links; ?>clients/edit_profile.php" class="font-semibold text-rose-accent hover:text-pink-600 transition-colors">
                                Edit Profil &rarr;
                            </a>
                        </div>
                    
                    <?php elseif ($role === 'konselor'): ?>
                        <!-- Menu untuk Konselor -->
                        <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.2s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Booking Masuk</h3>
                            <p class="text-gray-600 mb-5">Lihat daftar sesi yang telah dipesan oleh klien.</p>
                            <!-- (PERBAIKAN LINK) Arahkan ke halaman baru -->
                            <a href="konselor/booking_list_counselor.php" class="font-semibold text-rose-accent hover:underline">Lihat Booking Masuk &rarr;</a>
                        </div>
                        
                        <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.3s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Kelola Jadwal</h3>
                            <p class="text-gray-600 mb-5">Atur jadwal ketersediaan Anda untuk klien.</p>
                             <!-- Link ini sekarang benar -->
                            <a href="konselor/schedule_management.php" class="font-semibold text-rose-accent hover:underline">Kelola Jadwal Tersedia &rarr;</a>
                        </div>

                         <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.4s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Materi Pemulihan</h3>
                            <p class="text-gray-600 mb-5">Buat dan kelola artikel atau video materi pemulihan.</p>
                            <a href="materials/list.php" class="font-semibold text-rose-accent hover:underline">Kelola Materi &rarr;</a>
                        </div>

                        <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.5s;">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Profil Publik Saya</h3>
                            <p class="text-gray-600 mb-5">Perbarui biografi, spesialisasi, dan foto profil Anda.</p>
                            <a href="<?php echo $base_path_for_links; ?>konselor/edit.php?id=<?php echo $_SESSION['counselor_id'] ?? 0; ?>" class="font-semibold text-rose-accent hover:underline">Lihat & Edit Profil &rarr;</a>
                        </div>
                         <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal" style="transition-delay: 0.6s;"> <!-- Delay ditambah -->
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Lihat Profil Publik</h3>
                            <p class="text-gray-600 mb-5">Lihat bagaimana profil Anda tampil untuk klien.</p>
                            <a href="<?php echo $base_path_for_links; ?>counselor_profile.php?id=<?php echo $_SESSION['counselor_id'] ?? 0; ?>" class="font-semibold text-rose-accent hover:underline">Lihat Profil Publik &rarr;</a>
                        </div>

                    <?php elseif ($role === 'admin'): ?>
                         <!-- Menu untuk Admin -->
                         <p class="text-center md:col-span-2 text-gray-700">Selamat datang, Admin. Silakan gunakan menu di header untuk mengelola.</p>
                         <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Kelola Konselor</h3>
                             <a href="<?php echo $base_path_for_links; ?>konselor/list.php" class="font-semibold text-rose-accent hover:underline">Lihat Daftar Konselor &rarr;</a>
                         </div>
                          <div class="service-card bg-white/80 p-6 rounded-2xl shadow-lg shadow-blush-pink/50 scroll-reveal">
                            <h3 class="font-playfair text-2xl font-semibold mb-4 text-rose-accent">Kelola Klien</h3>
                             <a href="<?php echo $base_path_for_links; ?>clients/list.php" class="font-semibold text-rose-accent hover:underline">Lihat Daftar Klien &rarr;</a>
                         </div>

                    <?php else: ?>
                         <p class="text-center md:col-span-2 text-red-600">Terjadi kesalahan: Role pengguna tidak dikenali.</p>
                    <?php endif; ?>

                </div> <!-- end grid -->

            </div> <!-- end card-glass -->
        </div> <!-- end container -->
    </section>
</main>

<?php 
if (isset($conn)) $conn->close(); 
include '../templates/footer.php'; 
?>

