<?php
// 1. (WAJIB) Mulai sesi DULU
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Tentukan base path
$base_path_for_links = '../'; 

// 3. Panggil check_user.php (yang ada require_login())
include_once '../../config/check_user.php';
require_login('klien'); // Hanya klien

// 4. Baru panggil config database
include_once '../../config/db_config.php';

// Folder untuk menyimpan file upload
$upload_dir = "../uploads/payments/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Proses form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form
    $booking_id = intval($_POST['booking_id']);
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    
    // Pastikan user_id dari sesi sama dengan dari form (keamanan)
    if ($user_id != $_SESSION['user_id']) {
        die("Error: Operasi tidak diizinkan.");
    }

    // --- Logika File Upload ---
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        
        $file = $_FILES['bukti_pembayaran'];
        $file_name = basename($file['name']);
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Buat nama file unik: bookingID_userID_timestamp.ext
        $new_file_name = $booking_id . "_" . $user_id . "_" . time() . "." . $file_ext;
        $target_path = $upload_dir . $new_file_name;
        
        // Validasi file (ext dan size)
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_ext, $allowed_exts)) {
            header("Location: payment.php?booking_id=$booking_id&error=file_type");
            exit();
        }
        if ($file_size > $max_size) {
            header("Location: payment.php?booking_id=$booking_id&error=file_size");
            exit();
        }

        // Pindahkan file
        if (move_uploaded_file($file_tmp, $target_path)) {
            
            // File berhasil diupload, simpan data ke DB
            $db_file_path = $target_path; // Simpan path relatif

            // 1. Masukkan ke tabel 'payments'
            $sql_payment = "INSERT INTO payments (booking_id, user_id, payment_method, amount, bukti_pembayaran, status) 
                            VALUES (?, ?, ?, ?, ?, 'Menunggu Verifikasi')";
            $stmt_payment = $conn->prepare($sql_payment);
            $stmt_payment->bind_param("iisds", $booking_id, $user_id, $payment_method, $amount, $db_file_path);
            
            if ($stmt_payment->execute()) {
                // 2. Update status di tabel 'bookings'
                $sql_booking = "UPDATE bookings SET payment_proof = ?, status = 'Verified' WHERE booking_id = ? AND user_id = ?";
                // (Catatan: Anda mungkin ingin status 'Pending Verification' dulu, tapi skema Anda hanya punya 'Verified')
                // (Kita ganti ke 'Verified' agar sesuai skema, admin bisa cek nanti)
                $stmt_booking = $conn->prepare($sql_booking);
                $stmt_booking->bind_param("sii", $db_file_path, $booking_id, $user_id);
                $stmt_booking->execute();
                
                $stmt_payment->close();
                $stmt_booking->close();
                $conn->close();

                // Redirect ke daftar booking dengan pesan sukses
                header("Location: booking_list_user.php?success=payment_uploaded");
                exit();
                
            } else {
                $conn->close();
                header("Location: payment.php?booking_id=$booking_id&error=db_payment");
                exit();
            }

        } else {
            // Gagal memindahkan file
            $conn->close();
            header("Location: payment.php?booking_id=$booking_id&error=file_upload");
            exit();
        }

    } else {
        // Error pada file upload
        $conn->close();
        header("Location: payment.php?booking_id=$booking_id&error=no_file");
        exit();
    }
} else {
    // Bukan POST request
    $conn->close();
    header("Location: ../dashboard.php");
    exit();
}
?>