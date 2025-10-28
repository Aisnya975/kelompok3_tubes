<?php
$base_path_for_links = '../'; 
/*
 * File: public/konselor/view.php
 * Halaman untuk klien melihat detail profil konselor.
 */
include_once '../../config/check_user.php';
require_login('klien'); // Hanya klien

include_once '../../config/db_config.php';

$konselor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($konselor_id <= 0) {
    header("Location: /public/counselors.php");
    exit();
}

// Ambil data konselor
$sql_data = "SELECT * FROM konselor WHERE id_konselor = ? AND status = 'Aktif'";
$stmt_data = $conn->prepare($sql_data);
$stmt_data->bind_param("i", $konselor_id);
$stmt_data->execute();
$result = $stmt_data->get_result();

if ($result->num_rows == 0) {
    header("Location: /public/counselors.php?error=notfound");
    exit();
}
$konselor = $result->fetch_assoc();
$stmt_data->close();
$conn->close();

include_once '../../templates/header.php';
?>

<div class="bg-white p-8 rounded-lg shadow-lg">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Foto dan Tombol Booking -->
        <div class="md:w-1/3 text-center">
            <img src="<?php echo htmlspecialchars($konselor['foto_profil'] ?? 'https://placehold.co/400x400/a855f7/FFFFFF?text=Foto'); ?>" 
                 alt="Foto <?php echo htmlspecialchars($konselor['nama_lengkap']); ?>" 
                 class="w-64 h-64 rounded-full object-cover mx-auto mb-6 border-8 border-violet-100 shadow-md">
            
            <a href="/public/booking/booking_form.php?counselor_id=<?php echo $konselor_id; ?>" 
               class="w-full bg-violet-600 text-white px-6 py-3 rounded-lg hover:bg-violet-700 text-lg font-semibold">
                Booking Sesi Sekarang
            </a>
        </div>

        <!-- Info Detail -->
        <div class="md:w-2/3">
            <h1 class="text-4xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($konselor['nama_lengkap']); ?></h1>
            <p class="text-2xl text-violet-600 font-medium mb-6"><?php echo htmlspecialchars($konselor['spesialisasi']); ?></p>
            
            <div class="border-t pt-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-3">Biografi</h3>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($konselor['biografi_singkat'] ?? 'Informasi biografi belum tersedia.')); ?>
                </p>
            </div>

            <!-- Di masa depan, Anda bisa tambahkan jadwal di sini -->
            <!-- <div class="border-t pt-6 mt-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-3">Jadwal Tersedia</h3>
                <p class="text-gray-600">Fitur jadwal akan datang.</p>
            </div> -->
        </div>
    </div>
</div>

<?php
include_once '../../templates/footer.php';
?>
