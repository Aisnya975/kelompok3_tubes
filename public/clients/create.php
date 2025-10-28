<?php
// 1. Tentukan base path DULU
$base_path_for_links = '../'; 

// 2. Panggil header.php (yang ada session_start()) KEDUA
$pageTitle = 'Tambah Klien Baru';
include_once '../../templates/header.php'; // INI MEMULAI SESI

// 3. Panggil check_user.php (yang ada require_login()) KETIGA
include_once '../../config/check_user.php';
require_admin(); // Hanya admin

// 4. Baru panggil config database
include_once '../../config/db_config.php';
$errors = [];

// Logika insert mirip dengan register.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Validasi
    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        $errors[] = "Nama Lengkap, Email, dan Password wajib diisi.";
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = "Password dan Konfirmasi Password tidak cocok.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password minimal harus 6 karakter.";
    }

    // Cek email unik
    $stmt_check = $conn->prepare("SELECT id_klien FROM klien WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $errors[] = "Email ini sudah terdaftar sebagai klien.";
    }
    $stmt_check->close();

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql_insert = "INSERT INTO klien (nama_lengkap, email, password) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sss", $nama_lengkap, $email, $hashed_password);
        
        if ($stmt_insert->execute()) {
            $stmt_insert->close();
            $conn->close();
            header("Location: list.php?success=created");
            exit();
        } else {
            $errors[] = "Gagal membuat klien: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}
?>

<!-- (PERBAIKAN DESAIN) Ganti layout dengan tema baru -->
<section id="create-client" class="py-24 pt-32 bg-pastel-cream min-h-screen">
    <div class="container mx-auto max-w-2xl px-6">
        
        <div class="card-glass p-8 md:p-12 shadow-soft-pink scroll-reveal">

            <h1 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                Tambah Klien Baru
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
                    <label for="nama_lengkap" class="block text-gray-700 mb-2 font-semibold">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-gray-700 mb-2 font-semibold">Email</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="password" class="block text-gray-700 mb-2 font-semibold">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>
                <div>
                    <label for="konfirmasi_password" class="block text-gray-700 mb-2 font-semibold">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                        Simpan Klien
                    </button>
                </div>
                <p class="text-center text-gray-600 mt-4">
                    <a href="list.php" class="hover:text-rose-accent hover:underline font-semibold">
                        &larr; Batal dan Kembali ke Daftar
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

