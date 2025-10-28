<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. (PERBAIKAN) Panggil check_user.php PERTAMA (Ini akan memanggil session_start())
include_once '../../config/check_user.php';
// Lakukan pengecekan & redirect SEKARANG, sebelum HTML apa pun
require_admin(); // Hanya admin

// 3. (PERBAIKAN) Panggil config database KEDUA
include_once '../../config/db_config.php';

// 4. (PERBAIKAN) BARU panggil header.php KETIGA (Setelah pengecekan selesai)
$pageTitle = 'Kelola Konselor';
include_once '../../templates/header.php'; 

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');

// Logika ambil data konselor (dipindah setelah include DB)
$sql = "SELECT * FROM konselor ORDER BY created_at DESC";
$result = $conn->query($sql);

?>

<!-- 5. (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="admin-counselor-list" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-5xl px-6">

        <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 scroll-reveal">
            <h1 class="font-playfair text-4xl font-bold text-gray-800 text-gradient-love mb-4 md:mb-0">
                Kelola Konselor
            </h1>
            <a href="create.php" class="btn-glow bg-rose-accent text-white font-semibold py-3 px-6 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105 self-start md:self-center">
                + Tambah Konselor Baru
            </a>
        </div>

        <!-- Notifikasi -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 scroll-reveal" role="alert">
                <p class="font-semibold">
                    <?php 
                        if ($_GET['success'] == 'created') echo "Konselor baru berhasil ditambahkan.";
                        if ($_GET['success'] == 'updated') echo "Data konselor berhasil diperbarui.";
                        if ($_GET['success'] == 'deleted') echo "Konselor berhasil dihapus.";
                    ?>
                </p>
            </div>
        <?php endif; ?>
         <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 scroll-reveal" role="alert">
                 <p class="font-semibold">
                    <?php 
                        if ($_GET['error'] == 'delete_failed') echo "Gagal menghapus konselor.";
                        // Tambahkan pesan error lain jika perlu
                    ?>
                </p>
            </div>
        <?php endif; ?>


        <!-- Kontainer 'card-glass' untuk tabel -->
        <div class="card-glass p-4 md:p-8 shadow-soft-pink scroll-reveal">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-blush-pink/50">
                    <thead class="">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Spesialisasi</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Tgl Daftar</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-blush-pink/30">
                        <?php if ($result && $result->num_rows > 0): // Tambah pengecekan $result ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-blush-pink/20 transition-colors duration-200">
                                    <td class="py-3 px-4 text-gray-800 font-medium"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($row['spesialisasi'] ?? ''); ?></td>
                                    <td class="py-3 px-4">
                                        <?php
                                            $status_class = 'bg-gray-100 text-gray-600';
                                            if ($row['status'] == 'Aktif') $status_class = 'bg-green-100 text-green-700';
                                            if ($row['status'] == 'Menunggu Verifikasi') $status_class = 'bg-yellow-100 text-yellow-700';
                                            if ($row['status'] == 'Non-Aktif') $status_class = 'bg-red-100 text-red-700';
                                            echo '<span class="font-semibold ' . $status_class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($row['status']) . '</span>';
                                        ?>
                                    </td>
                                     <td class="py-3 px-4 text-gray-500 text-sm">
                                        <?php 
                                            // Tambah pengecekan jika created_at null
                                            if ($row['created_at']) {
                                                $tgl = new DateTime($row['created_at']);
                                                echo $tgl->format('d M Y'); 
                                            } else {
                                                echo '-';
                                            }
                                        ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium space-x-3 whitespace-nowrap">
                                        <a href="edit.php?id=<?php echo $row['id_konselor']; ?>" class="text-rose-accent hover:text-pink-600">Edit</a>
                                        <a href="delete.php?id=<?php echo $row['id_konselor']; ?>" 
                                           class="text-gray-500 hover:text-red-600"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus konselor ini? Tindakan ini tidak dapat diurungkan.');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                    Belum ada data konselor.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</section>

<?php
if ($conn) $conn->close(); // Tambah pengecekan $conn sebelum close
include_once '../../templates/footer.php';
?>

