<?php
// (LOGIKA PHP LAMA TETAP ADA)
require '../config/db_config.php';
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // 'klien' or 'konselor'
    
    // Validasi sederhana
    if ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal harus 6 karakter.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Tentukan tabel
        if ($role === 'klien') {
            $stmt = $conn->prepare("INSERT INTO klien (nama_lengkap, email, password) VALUES (?, ?, ?)");
        } else { // 'konselor'
            $stmt = $conn->prepare("INSERT INTO konselor (nama_lengkap, email, password, status) VALUES (?, ?, ?, 'Menunggu Verifikasi')");
        }
        
        $stmt->bind_param("sss", $nama_lengkap, $email, $hashed_password);
        
        try {
            if ($stmt->execute()) {
                $success_message = "Registrasi berhasil! Silakan login.";
                if ($role === 'konselor') {
                    $success_message = "Registrasi konselor berhasil. Akun Anda akan diverifikasi oleh admin sebelum bisa login.";
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Error duplikat email
                $error_message = "Email sudah terdaftar. Silakan gunakan email lain.";
            } else {
                $error_message = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
        
        $stmt->close();
    }
    $conn->close();
}

$pageTitle = 'Registrasi';
include '../templates/header.php'; 
?>

<!-- KONTEN HTML BARU DENGAN STYLE ROMANTIS -->
<main class="pt-32 pb-16 min-h-screen"> <!-- Beri padding atas untuk header sticky -->
    <section id="register-form" class="relative">
        <div class="container mx-auto max-w-lg px-6">
            
            <!-- Menggunakan Card Glass dari style baru -->
            <div class="card-glass p-8 md:p-12 shadow-soft-pink">
                
                <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-8 text-center text-gradient-love">
                    Buat Akun Baru
                </h2>

                <!-- Tampilkan Error jika ada -->
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Tampilkan Sukses jika ada -->
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form Registrasi (Style diperbarui) -->
                <form action="register.php" method="POST" class="space-y-6">
                    
                    <div>
                        <label for="nama_lengkap" class="block text-gray-700 font-medium mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" 
                               class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" 
                               required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" name="email" id="email" 
                               class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" 
                               required>
                    </div>

                    <div>
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password (min. 6 karakter)</label>
                        <input type="password" name="password" id="password"
                               class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" 
                               required>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" 
                               required>
                    </div>

                    <div>
                        <label for="role" class="block text-gray-700 font-medium mb-2">Saya ingin mendaftar sebagai:</label>
                        <select name="role" id="role" 
                                class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm appearance-none">
                            <option value="klien" selected>Klien (Mencari Konseling)</option>
                            <option value="konselor">Konselor (Memberi Konseling)</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="w-full btn-glow bg-rose-accent text-white font-semibold py-4 px-8 rounded-full text-lg shadow-lg shadow-rose-accent/40 transition-all duration-300 hover:scale-105 hover:bg-pink-600">
                        Daftar
                    </button>
                    
                    <p class="text-center text-gray-600 pt-4">
                        Sudah punya akun? 
                        <a href="<?php echo $base_path_for_links; ?>login.php" class="text-rose-accent hover:underline font-semibold">
                            Login di sini
                        </a>
                    </p>
                </form>
                
            </div>
        </div>
    </section>
</main>

<?php include '../templates/footer.php'; ?>

