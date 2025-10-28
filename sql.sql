-- Buat database
CREATE DATABASE IF NOT EXISTS sistem_konseling CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistem_konseling;

-- Tabel Klien
CREATE TABLE klien (
  id_klien INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nomor_telepon VARCHAR(30),
  tanggal_lahir DATE,
  foto_profil VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
SELECT * FROM klien;

-- Tabel Konselor
CREATE TABLE konselor (
  id_konselor INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  spesialisasi VARCHAR(150),
  biografi_singkat TEXT,
  foto_profil VARCHAR(255),
  status ENUM('Menunggu Verifikasi','Aktif','Non-Aktif') DEFAULT 'Menunggu Verifikasi',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
SELECT * FROM konselor;

-- Tabel Jadwal Tersedia
CREATE TABLE jadwal_tersedia (
  id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
  id_konselor INT NOT NULL,
  waktu_mulai DATETIME NOT NULL,
  waktu_selesai DATETIME NOT NULL,
  status_ketersediaan ENUM('Tersedia','Dipesan') DEFAULT 'Tersedia',
  FOREIGN KEY (id_konselor) REFERENCES konselor(id_konselor) ON DELETE CASCADE
) ENGINE=InnoDB;
SELECT * FROM jadwal_tersedia;

-- Tabel Sesi Konseling
CREATE TABLE sesi_konseling (
  id_sesi INT AUTO_INCREMENT PRIMARY KEY,
  id_klien INT NOT NULL,
  id_konselor INT NOT NULL,
  jadwal_sesi DATETIME NOT NULL,
  durasi INT DEFAULT 60,
  status_sesi ENUM('Dijadwalkan','Selesai','Dibatalkan','Berlangsung') DEFAULT 'Dijadwalkan',
  link_meeting VARCHAR(255),
  catatan_awal_klien TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_klien) REFERENCES klien(id_klien) ON DELETE CASCADE,
  FOREIGN KEY (id_konselor) REFERENCES konselor(id_konselor) ON DELETE CASCADE
) ENGINE=InnoDB;
SELECT * FROM sesi_konseling;


-- Tabel Kategori Materi
CREATE TABLE kategori_materi (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(120) NOT NULL,
  deskripsi TEXT
) ENGINE=InnoDB;
SELECT * FROM kategori_materi;


-- Tabel Materi Pemulihan
CREATE TABLE materi_pemulihan (
  id_materi INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(255) NOT NULL,
  konten LONGTEXT,
  tipe_materi ENUM('Artikel','Video','Latihan') DEFAULT 'Artikel',
  id_kategori INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_kategori) REFERENCES kategori_materi(id_kategori) ON DELETE SET NULL
) ENGINE=InnoDB;
SELECT * FROM materi_pemulihan;

-- ==========================================================
-- URUTAN DIPERBAIKI: 'bookings' DIBUAT SEBELUM 'payments'
-- ==========================================================

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  counselor_id INT NOT NULL,
  service_id INT DEFAULT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  notes TEXT,
  payment_proof VARCHAR(255),
  status ENUM('Pending Payment', 'Verified', 'Completed', 'Canceled') DEFAULT 'Pending Payment',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES klien(id_klien) ON DELETE CASCADE,
  FOREIGN KEY (counselor_id) REFERENCES konselor(id_konselor) ON DELETE CASCADE
) ENGINE=InnoDB;

SELECT * FROM bookings;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  user_id INT NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  bukti_pembayaran VARCHAR(255),
  status ENUM('Menunggu Verifikasi','Diterima','Ditolak') DEFAULT 'Menunggu Verifikasi',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES klien(id_klien) ON DELETE CASCADE
) ENGINE=InnoDB;
SELECT * FROM payments;

-- ==========================================================
-- DATA INSERT
-- ==========================================================

INSERT INTO klien (nama_lengkap, email, password, nomor_telepon, tanggal_lahir)
VALUES ('Andi Setiawan', 'andi@example.com', '<HASHED_PASSWORD>', '08123456789', '2000-04-12');
INSERT INTO klien (nama_lengkap, email, password, nomor_telepon, tanggal_lahir)
VALUES
('Budi Santoso', 'budi@example.com', '<HASHED_PASSWORD>', '0812222222', '1995-03-15'),
('Citra Lestari', 'citra@example.com', '<HASHED_PASSWORD>', '0813333333', '1998-11-01'),
('Dewi Anggraini', 'dewi@example.com', '<HASHED_PASSWORD>', '0814444444', '2001-07-22');

SELECT * FROM klien;

INSERT INTO konselor (nama_lengkap, email, password, spesialisasi, biografi_singkat, status)
VALUES (
'Dra. Intan Cinta','intan@example.com','<HASHED_PASSWORD>','Pemulihan Pasca Putus Cinta','Konselor berpengalaman dalam membantu move on dan mengelola emosi.','Aktif'),
('Dr. Aggra Kurnia Idhan', 'aggra@example.com', '12345', 'Pemulihan Emosional', 'Ahli pemulihan emosional dan trauma.', 'Aktif'),
('Dr. Aisyah Azzahrah', 'aisyah@example.com', '12345', 'Manajemen Stres', 'Fokus pada pengelolaan stres dan kesejahteraan mental.', 'Aktif'),
('Dr. Tasya Triani', 'tasya@example.com', '12345', 'Motivasi dan Percaya Diri', 'Berpengalaman dalam membangun kepercayaan diri.', 'Aktif'),
('Dr. Aulia Hafizah', 'aulia@example.com', '12345', 'Konseling Pernikahan', 'Membantu pasangan dalam komunikasi harmonis.', 'Aktif'),
('Psikolog Judika, M.Psi', 'judika@example.com', '12345', 'Pemulihan Psikologis', 'Psikolog ahli trauma dan dukungan emosional.', 'Aktif');

INSERT INTO kategori_materi (nama_kategori, deskripsi)
VALUES
('Pemulihan Emosional', 'Materi terkait penyembuhan luka batin, trauma, dan putus cinta.'),
('Manajemen Stres', 'Teknik dan cara mengelola stres dan kecemasan sehari-hari.'),
('Pengembangan Diri', 'Artikel dan latihan untuk motivasi dan percaya diri.'),
('Hubungan & Pernikahan', 'Materi untuk membangun komunikasi sehat dalam hubungan.');

SELECT * FROM kategori_materi;

INSERT INTO materi_pemulihan (judul, konten, tipe_materi, id_kategori)
VALUES
('5 Langkah Move On Pasca Putus Cinta', 'Konten artikel lengkap tentang cara move on...', 'Artikel', 1),
('Teknik Pernapasan 4-7-8 untuk Tenang', 'Video panduan teknik pernapasan untuk meredakan stres.', 'Video', 2),
('Membangun Rutinitas Pagi Penuh Motivasi', 'Latihan harian untuk membangun percaya diri.', 'Latihan', 3),
('Cara Berkomunikasi Efektif dengan Pasangan', 'Konten artikel mengenai komunikasi dalam pernikahan.', 'Artikel', 4);

SELECT * FROM materi_pemulihan;

INSERT INTO jadwal_tersedia (id_konselor, waktu_mulai, waktu_selesai, status_ketersediaan)
VALUES
-- Slot untuk Konselor 1 (Intan)
(1, '2025-11-10 10:00:00', '2025-11-10 11:00:00', 'Tersedia'),
(1, '2025-11-10 11:00:00', '2025-11-10 12:00:00', 'Tersedia'),
-- Slot untuk Konselor 2 (Aggra)
(2, '2025-11-11 09:00:00', '2025-11-11 10:00:00', 'Tersedia'),
-- Slot untuk Konselor 3 (Aisyah)
(3, '2025-11-11 13:00:00', '2025-11-11 14:00:00', 'Tersedia'),
-- Slot untuk Konselor 4 (Tasya)
(4, '2025-11-12 10:00:00', '2025-11-12 11:00:00', 'Tersedia'),
-- Slot untuk Konselor 5 (Aulia)
(5, '2025-11-12 14:00:00', '2025-11-12 15:00:00', 'Tersedia'),
-- Slot untuk Konselor 6 (Judika)
(6, '2025-11-13 15:00:00', '2025-11-13 16:00:00', 'Tersedia');

SELECT * FROM jadwal_tersedia;

-- Klien 1 (Andi) booking Konselor 1 (Intan)
INSERT INTO bookings (user_id, counselor_id, booking_date, booking_time, notes, status)
VALUES
(1, 1, '2025-11-10', '10:00:00', 'Perlu konsultasi mendalam pasca putus cinta.', 'Verified');

-- Klien 2 (Budi) booking Konselor 5 (Aulia)
INSERT INTO bookings (user_id, counselor_id, booking_date, booking_time, notes, status)
VALUES
(2, 5, '2025-11-12', '14:00:00', 'Ingin diskusi soal komunikasi pernikahan.', 'Verified');

SELECT * FROM bookings;

-- Pembayaran untuk Booking 1 (Andi), status Diterima
INSERT INTO payments (booking_id, user_id, payment_method, amount, bukti_pembayaran, status)
VALUES
(1, 1, 'Transfer Bank', 150000.00, 'bukti_transfer_1.jpg', 'Diterima');

-- Pembayaran untuk Booking 2 (Budi), status Diterima
INSERT INTO payments (booking_id, user_id, payment_method, amount, bukti_pembayaran, status)
VALUES
(2, 2, 'E-Wallet', 150000.00, 'bukti_ewallet_2.jpg', 'Diterima');

SELECT * FROM payments;

-- Mengupdate jadwal Konselor 1 (ID Jadwal 1)
UPDATE jadwal_tersedia
SET status_ketersediaan = 'Dipesan'
WHERE id_konselor = 1 AND waktu_mulai = '2025-11-10 10:00:00';

-- Mengupdate jadwal Konselor 5 (ID Jadwal 6)
UPDATE jadwal_tersedia
SET status_ketersediaan = 'Dipesan'
WHERE id_konselor = 5 AND waktu_mulai = '2025-11-12 14:00:00';

SELECT * FROM jadwal_tersedia;

-- Sesi untuk Booking 1 (Andi & Intan)
INSERT INTO sesi_konseling (id_klien, id_konselor, jadwal_sesi, status_sesi, link_meeting, catatan_awal_klien)
VALUES
(1, 1, '2025-11-10 10:00:00', 'Dijadwalkan', 'https://meet.example.com/sesi-andi-intan', 'Perlu konsultasi mendalam pasca putus cinta.');

-- Sesi untuk Booking 2 (Budi & Aulia)
INSERT INTO sesi_konseling (id_klien, id_konselor, jadwal_sesi, status_sesi, link_meeting, catatan_awal_klien)
VALUES
(2, 5, '2025-11-12 14:00:00', 'Dijadwalkan', 'https://meet.example.com/sesi-budi-aulia', 'Ingin diskusi soal komunikasi pernikahan.');

SELECT * FROM sesi_konseling;

-- File: admin_setup.sql
-- Berisi perintah SQL untuk membuat tabel admin dan satu admin default.
-- JALANKAN INI SATU KALI DI DATABASE ANDA.

-- 1. Buat tabel admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admin (id_admin, nama_lengkap, email, password)
VALUES (1, 'Admin Utama', 'admin@app.com', '$2y$10$3.G/O4.wA0N4.a2mU/y.8ul5uKL.E.7G.3.M6.t4.9.g0.H/y.a2')
ON DUPLICATE KEY UPDATE
    nama_lengkap = 'Admin Utama',
    password = '$2y$10$3.G/O4.wA0N4.a2mU/y.8ul5uKL.E.7G.3.M6.t4.9.g0.H/y.a2';
    
    UPDATE admin
SET password = '$2y$10$gT8qN.3fL4X.kR/E.pA9l.wY3m/N.5zL8V.hQ7j/G.dF1o.U2e6' -- HASH BARU YANG BENAR (60 karakter)
WHERE email = 'admin@app.com';
SELECT email, password FROM admin WHERE email = 'admin@app.com';