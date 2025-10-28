<?php
include('../config/db_connection.php');
include('../config/check_user.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id        = $_SESSION['user_id'];
    $service_id     = mysqli_real_escape_string($conn, $_POST['service_id']);
    $counselor_id   = mysqli_real_escape_string($conn, $_POST['counselor_id']);
    $booking_date   = mysqli_real_escape_string($conn, $_POST['booking_date']);
    $booking_time   = mysqli_real_escape_string($conn, $_POST['booking_time']);
    $durasi         = mysqli_real_escape_string($conn, $_POST['durasi']);
    $catatan_awal   = mysqli_real_escape_string($conn, $_POST['catatan_awal']);

    // Status awal booking
    $status = "Pending Payment";

    // Simpan data ke tabel bookings
    $query = "INSERT INTO bookings (user_id, service_id, counselor_id, booking_date, booking_time, durasi, catatan_awal_klien, status, created_at)
              VALUES ('$user_id', '$service_id', '$counselor_id', '$booking_date', '$booking_time', '$durasi', '$catatan_awal', '$status', NOW())";

    if (mysqli_query($conn, $query)) {
        // Redirect ke halaman daftar booking
        header("Location: booking_list_user.php?success=1");
        exit();
    } else {
        echo "Terjadi kesalahan saat memproses booking: " . mysqli_error($conn);
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
