<?php
$base_path_for_links = '../';
/*
 * File: public/clients/list.php
 * Halaman admin untuk mengelola klien.
 */
include_once '../../config/check_user.php';
require_admin(); // Hanya admin

include_once '../../config/db_config.php';
include_once '../../templates/header.php';

$result = $conn->query("SELECT * FROM klien ORDER BY created_at DESC");
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Kelola Klien</h1>
    <a href="create.php" class="bg-violet-600 text-white px-4 py-2 rounded-lg hover:bg-violet-700 font-semibold">
        + Tambah Klien
    </a>
</div>

<div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900"><?php echo $row['id_klien']; ?></td>
                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['email']); ?></td>
                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['nomor_telepon']); ?></td>
                <td class="px-6 py-4 text-sm font-medium">
                    <a href="edit.php?id=<?php echo $row['id_klien']; ?>" class="text-blue-600 hover:underline">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id_klien']; ?>" class="text-red-600 hover:underline ml-4" onclick="return confirm('Yakin hapus klien ini?');">Hapus</a>
                </td>
            </tr>
            <?php endwhile;
            else: ?>
            <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data klien.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include_once '../../templates/footer.php';
?>
