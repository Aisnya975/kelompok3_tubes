<?php
$base_path_for_links = './'; 
// Mulai sesi di paling atas
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// JIKA SUDAH LOGIN, TENDANG KE DASHBOARD
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: konselor/list.php"); // Dashboard Admin
    } else {
        header("Location: dashboard.php"); // Dashboard Klien/Konselor
    }
    exit;
}

include '../config/db_config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- (AKTIFKAN KEMBALI BACKDOOR) ---
    // Cek ini pertama kali SEBELUM ke database
    if ($email === 'admin@app.com' && $password === 'admin123') {
        // Langsung login tanpa cek database
        $_SESSION['admin_id'] = 1; // Kita anggap ID-nya 1
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = 'Admin (Forced)'; // Ganti nama agar tahu ini paksa
        $_SESSION['user_email'] = 'admin@app.com';
        
        // Arahkan admin ke daftar konselor
        header("Location: konselor/list.php"); 
        exit;
    }
    // --- AKHIR DARI BACKDOOR ---


    // 1. Coba cek di tabel KLIEN
    $sql_klien = "SELECT * FROM klien WHERE email = ?"; 
    if ($stmt_klien = $conn->prepare($sql_klien)) { 
        $stmt_klien->bind_param("s", $email);
        $stmt_klien->execute();
        $result_klien = $stmt_klien->get_result(); 

        if ($result_klien->num_rows == 1) { 
            $user = $result_klien->fetch_assoc(); 
            if (password_verify($password, $user['password'])) {
                // Login Klien Berhasil
                $_SESSION['user_id'] = $user['id_klien'];
                $_SESSION['user_type'] = 'klien';
                $_SESSION['user_name'] = $user['nama_lengkap'];
                $_SESSION['user_email'] = $user['email']; 
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            // 2. Jika tidak ada di KLIEN, cek di KONSELOR
            $sql_konselor = "SELECT * FROM konselor WHERE email = ?";
            if ($stmt_konselor = $conn->prepare($sql_konselor)) {
                $stmt_konselor->bind_param("s", $email);
                $stmt_konselor->execute();
                $result_konselor = $stmt_konselor->get_result();

                if ($result_konselor->num_rows == 1) {
                    $user_konselor = $result_konselor->fetch_assoc();
                    if (password_verify($password, $user_konselor['password'])) {
                        
                        if ($user_konselor['status'] == 'Aktif') {
                            // Login Konselor Berhasil
                            $_SESSION['counselor_id'] = $user_konselor['id_konselor']; 
                            $_SESSION['user_type'] = 'konselor';
                            $_SESSION['user_name'] = $user_konselor['nama_lengkap'];
                            $_SESSION['user_email'] = $user_konselor['email'];
                            header("Location: dashboard.php");
                            exit;
                        } elseif ($user_konselor['status'] == 'Menunggu Verifikasi') {
                            $error = "Akun Anda sedang menunggu verifikasi oleh Admin.";
                        } else {
                            $error = "Akun Anda telah dinonaktifkan.";
                        }
                    } else {
                        $error = "Password salah.";
                    }
                } else {
                    // 3. Jika tidak ada di KONSELOR, cek di ADMIN
                    $sql_admin = "SELECT * FROM admin WHERE email = ?";
                    if ($stmt_admin = $conn->prepare($sql_admin)) {
                        $stmt_admin->bind_param("s", $email);
                        $stmt_admin->execute();
                        $result_admin = $stmt_admin->get_result();

                        if ($result_admin->num_rows == 1) {
                            $user_admin = $result_admin->fetch_assoc();
                            
                            // Cek password_verify()
                            if (password_verify($password, $user_admin['password'])) {
                                // Login Admin Berhasil
                                $_SESSION['admin_id'] = $user_admin['id_admin']; 
                                $_SESSION['user_type'] = 'admin';
                                $_SESSION['user_name'] = $user_admin['nama_lengkap'];
                                $_SESSION['user_email'] = $user_admin['email'];
                                header("Location: konselor/list.php"); 
                                exit;
                            } else {
                                $error = "Password salah.";
                            }
                        } else {
                            // 4. Jika tidak ada di SEMUA tabel
                            $error = "Email tidak ditemukan.";
                        }
                        $stmt_admin->close();
                    } else {
                         $error = "Terjadi kesalahan saat memeriksa data admin.";
                    }
                }
                $stmt_konselor->close();
            } else {
                 $error = "Terjadi kesalahan saat memeriksa data konselor.";
            }
        }
        $stmt_klien->close(); 
    } else {
         $error = "Terjadi kesalahan saat memeriksa data klien.";
    }
}
// JANGAN tutup koneksi di sini

$pageTitle = 'Login';
include '../templates/header.php'; 
?>

<!-- Konten Halaman Login -->
<section id="login-page" class="relative py-32" style="min-height: 80vh;">
    <div class="container mx-auto max-w-lg px-6">
        
        <div class="card-glass p-8 md:p-12 shadow-soft-pink">
            
            <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-6 text-center text-gradient-love">
                Login
            </h2>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2 font-semibold">Email</label>
                    <input type="email" name="email" id="email" 
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" required>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 mb-2 font-semibold">Password</label>
                    <input type="password" name="password" id="password"
                           class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm" required>
                </div>

                <button type="submit" 
                        class="w-full btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                    Masuk
                </button>
                
                <p class="text-center text-gray-600 mt-6">
                    Belum punya akun? <a href="register.php" class="text-rose-accent hover:underline font-semibold">Daftar di sini</a>
                </p>
            </form>
            
        </div>
    </div>
</section>

<?php 
include '../templates/footer.php'; 
$conn->close();
?>

