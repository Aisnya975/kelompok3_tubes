<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php (yang ada session_start()) KEDUA
$pageTitle = 'Riwayat Booking';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_login('klien'); // Hanya klien

// 4. Baru panggil config database
include_once '../../config/db_config.php';

$klien_id = $_SESSION['user_id'];

// (PERBAIKAN BAHASA) Atur timezone default untuk PHP
date_default_timezone_set('Asia/Jakarta');
?>

<!-- 4. (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="booking-list" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-5xl px-6">

        <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love scroll-reveal">
            Riwayat Booking Saya
        </h1>

        <!-- Notifikasi Sukses -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'payment_uploaded'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 scroll-reveal" role="alert">
                <p class="font-semibold">Bukti pembayaran berhasil diupload. Booking Anda sedang menunggu verifikasi.</p>
            </div>
        <?php endif; ?>

        <!-- Kontainer 'card-glass' untuk tabel -->
        <div class="card-glass p-4 md:p-8 shadow-soft-pink scroll-reveal">
            
            <!-- Tambahkan overflow-x-auto agar tabel responsif di HP -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-blush-pink/50">
                    <thead class="">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">ID Booking</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Konselor</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Jadwal</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status Booking</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status Bayar</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 divide-y divide-blush-pink/30">
                        <?php
                        $sql = "SELECT b.*, k.nama_lengkap AS konselor_nama, p.status AS payment_status
                                FROM bookings b
                                JOIN konselor k ON b.counselor_id = k.id_konselor
                                LEFT JOIN payments p ON b.booking_id = p.booking_id
                                WHERE b.user_id = ?
                                ORDER BY b.booking_date DESC, b.booking_time DESC";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $klien_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0):
                            while($row = $result->fetch_assoc()):
                                $sesi_datetime = new DateTime($row['booking_date'] . ' ' . $row['booking_time']);
                        ?>
                        <tr class="hover:bg-blush-pink/20 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $row['booking_id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['konselor_nama']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php 
                                // (PERBAIKAN BAHASA) Format tanggal
                                $fmt = new IntlDateFormatter(
                                    'id_ID', 
                                    IntlDateFormatter::NONE, 
                                    IntlDateFormatter::NONE, 
                                    'Asia/Jakarta', 
                                    IntlDateFormatter::GREGORIAN, 
                                    'dd MMM yyyy, HH:mm' // Format: 27 Okt 2025, 14:00
                                );
                                echo $fmt->format($sesi_datetime); 
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php
                                $status_class = 'bg-gray-100 text-gray-600';
                                if ($row['status'] == 'Verified') $status_class = 'bg-green-100 text-green-700';
                                if ($row['status'] == 'Pending Payment') $status_class = 'bg-yellow-100 text-yellow-700';
                                if ($row['status'] == 'Canceled') $status_class = 'bg-red-100 text-red-700';
                                echo '<span class="font-semibold ' . $status_class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($row['status']) . '</span>';
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                 <?php
                                 $pay_status = $row['payment_status'] ?? 'Belum Bayar';
                                 $pay_class = 'bg-gray-100 text-gray-600';
                                 if ($pay_status == 'Diterima') $pay_class = 'bg-green-100 text-green-700';
                                 if ($pay_status == 'Menunggu Verifikasi') $pay_class = 'bg-yellow-100 text-yellow-700';
                                 if ($pay_status == 'Ditolak') $pay_class = 'bg-red-100 text-red-700';
                                 echo '<span class="font-semibold ' . $pay_class . ' px-3 py-1 rounded-full text-xs">' . htmlspecialchars($pay_status) . '</span>';
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="booking_detail.php?id=<?php echo $row['booking_id']; ?>" class="text-rose-accent hover:text-pink-600">Lihat Detail</a>
                                <?php if ($row['status'] == 'Pending Payment' && empty($row['payment_proof'])): ?>
                                     <a href="payment.php?booking_id=<?php echo $row['booking_id']; ?>" class="text-green-600 hover:text-green-800 ml-4">Bayar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <p class="font-semibold text-lg">Anda belum memiliki riwayat booking.</p>
                                <p class="mt-2">Mulai perjalanan Anda dengan <a href="../counselors.php" class="text-rose-accent hover:underline">mencari konselor</a>.</p>
                            </td>
                        </tr>
                        <?php
                        endif;
                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <p class="text-center text-gray-600 mt-8">
                <a href="../dashboard.php" class="hover:text-rose-accent hover:underline font-semibold">
                    &larr; Kembali ke Dashboard
                </a>
            </p>
        </div>
    </div>
</section>

<?php
include_once '../../templates/footer.php';
?>

