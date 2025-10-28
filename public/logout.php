<?php
/*
 * File: public/logout.php
 * Menghancurkan sesi dan me-redirect ke halaman utama.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman index
header("Location: index.php");
exit;
?>
