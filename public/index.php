<?php 
$base_path_for_links = './'; 
$pageTitle = 'Home';
include '../templates/header.php'; 
?>

<!-- 2. Hero Section (Konten Baru) -->
<!-- Kita kembalikan ke tema gelap agar teks putih kontras dan indah -->
<section id="hero" class="relative h-screen flex items-center justify-center text-center text-white px-6 overflow-hidden">
    
    <!-- Background Image (Gambar asli Unsplash) -->
    <div class="absolute inset-0 z-0">
        <!-- 
          PERUBAHAN: 
          1. Opacity gambar diubah ke 50% (lebih gelap)
          2. Gambar Unsplash yang indah dipertahankan
        -->
        <img src="https://images.unsplash.com/photo-1519680387612-1687C35f438b?q=80&w=1920&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
             alt="Lampu-lampu bokeh romantis" 
             class="w-full h-full object-cover opacity-50"> <!-- Opacity diubah ke 50% -->
        
        <!-- 
          PERUBAHAN: 
          1. Overlay diubah dari 'blush-pink' ke 'navy-dark' agar gelap
        -->
        <div class="absolute inset-0 bg-gradient-to-t from-navy-dark/70 via-navy-dark/30 to-transparent"></div>
    </div>
    
    <div class="relative z-10 flex flex-col items-center">
        <!-- Teks (kembali putih dan terlihat) -->
        <h1 class="font-playfair text-6xl md:text-8xl font-bold text-shadow-lg mb-4 text-white">
            <span class="floating-letter" style="animation-delay: 0.1s;">S</span>
            <span class="floating-letter" style="animation-delay: 0.2s;">e</span>
            <span class="floating-letter" style="animation-delay: 0.3s;">l</span>
            <span class="floating-letter" style="animation-delay: 0.4s;">a</span>
            <span class="floating-letter" style="animation-delay: 0.5s;">m</span>
            <span class="floating-letter" style="animation-delay: 0.6s;">a</span>
            <span class="floating-letter" style="animation-delay: 0.7s;">t</span>
            <span class="floating-letter" style="animation-delay: 0.8s;">&nbsp;</span>
            <span class="floating-letter" style="animation-delay: 0.9s;">D</span>
            <span class="floating-letter" style="animation-delay: 1.0s;">a</span>
            <span class="floating-letter" style="animation-delay: 1.1s;">t</span>
            <span class="floating-letter" style="animation-delay: 1.2s;">a</span>
            <span class="floating-letter" style="animation-delay: 1.3s;">n</span>
            <span class="floating-letter" style="animation-delay: 1.4s;">g</span>
        </h1>
        
        <!-- Teks ketik (kembali putih dan terlihat) -->
        <p id="typing-effect" class="font-inter text-xl md:text-2xl text-white/90 mb-8 max-w-2xl">
            <!-- Dikosongkan, diisi oleh JS -->
        </p>

        <!-- Tombol (kembali ke style awal) -->
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-6">
            <a href="register.php" class="btn-glow bg-rose-accent text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                Mulai Konseling
            </a>
            <!-- Tombol "Lihat Konselor" kembali ke style transparan awal -->
            <a href="counselors.php" class="bg-white/30 backdrop-blur-sm text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg transition-transform duration-300 hover:scale-105">
                Lihat Konselor
            </a>
        </div>
    </div>
</section>

<!-- 3. About Love Section (Konten Baru) --><section id="about" class="py-24 bg-pastel-cream">
    <div class="container mx-auto max-w-6xl px-6 grid md:grid-cols-2 gap-12 items-center">
        <div class="scroll-reveal">
            <img src="/assets/images/Ruang konseling nyaman.jpeg" 
                 alt="Ilustrasi ruang konseling yang nyaman" 
                 class="rounded-2xl shadow-lg shadow-lavender/40 w-full h-auto object-cover">
        </div>
        <div class="scroll-reveal" style="transition-delay: 0.2s;">
            <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-6">Cerita Dibalik Setiap Luka Hati</h2>
            <p class="text-lg text-gray-600 leading-relaxed mb-4">
                Patah hati adalah bagian dari perjalanan, bukan akhir dari cerita. Kami di sini untuk mendampingi Anda melalui setiap fase pemulihan emosional.
            </p>
            <p class="text-lg text-gray-600 leading-relaxed">
                Platform kami adalah ruang aman Anda untuk menyembuhkan luka, menemukan kembali kekuatan diri, dan membangun kembali fondasi kebahagiaan Anda.
            </p>
        </div>
    </div>
</section>

<!-- 4. Services Section (Konten Baru) --><section id="services" class="py-24 bg-blush-pink/30">
    <div class="container mx-auto max-w-6xl px-6">
        <div class="text-center mb-16 scroll-reveal">
            <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-4">Layanan Pemulihan Kami</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Dirancang untuk membantu Anda terhubung, menyembuhkan, dan merayakan diri Anda kembali.</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Service Card 1 --><div class="service-card bg-white p-8 rounded-2xl shadow-lg shadow-blush-pink/50 text-center scroll-reveal" style="transition-delay: 0.1s;">
                <div class="text-5xl text-rose-accent mb-4">💬</div>
                <h3 class="font-playfair text-2xl font-semibold mb-3">Sesi Konseling 1-on-1</h3>
                <p class="text-gray-600">Bicara langsung dengan konselor profesional kami yang berspesialisasi dalam pemulihan emosional.</p>
            </div>
            <!-- Service Card 2 --><div class="service-card bg-white p-8 rounded-2xl shadow-lg shadow-blush-pink/50 text-center scroll-reveal" style="transition-delay: 0.2s;">
                <div class="text-5xl text-rose-accent mb-4">📚</div>
                <h3 class="font-playfair text-2xl font-semibold mb-3">Materi Pemulihan Mandiri</h3>
                <p class="text-gray-600">Akses artikel, video, dan latihan yang dirancang untuk membantu Anda memahami dan memproses emosi.</p>
            </div>
            <!-- Service Card 3 --><div class="service-card bg-white p-8 rounded-2xl shadow-lg shadow-blush-pink/50 text-center scroll-reveal" style="transition-delay: 0.3s;">
                <div class="text-5xl text-rose-accent mb-4">🌙</div>
                <h3 class="font-playfair text-2xl font-semibold mb-3">Meditasi Terpandu</h3>
                <p class="text-gray-600">Panduan audio meditasi untuk menenangkan hati dan pikiran di saat-saat sulit.</p>
            </div>
        </div>
    </div>
</section>

<!-- 5. Gallery Section (Konten Baru) --><section id="gallery" class="py-24 bg-pastel-cream">
    <div class="container mx-auto max-w-6xl px-6">
        <div class="text-center mb-16 scroll-reveal">
            <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-4">Galeri Inspirasi</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Visual yang menenangkan dan memberi kekuatan.</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Gallery Item 1 --><div class="gallery-item relative rounded-xl overflow-hidden shadow-lg shadow-blush-pink/40 cursor-pointer scroll-reveal" style="transition-delay: 0.1s;">
                <img src="/assets/images/ade.jpeg" alt="Menulis jurnal" class="w-full h-full object-cover">
                <div class="gallery-hover-overlay absolute inset-0 bg-blush-pink/70 flex items-center justify-center p-4 text-center">
                    <p class="font-playfair text-lg font-semibold text-gray-800">"Menulis adalah menyembuhkan."</p>
                </div>
            </div>
            <!-- Gallery Item 2 --><div class="gallery-item relative rounded-xl overflow-hidden shadow-lg shadow-blush-pink/40 cursor-pointer scroll-reveal" style="transition-delay: 0.2s;">
                <img src="/assets/images/teh.jpeg" alt="Teh hangat" class="w-full h-full object-cover">
                <div class="gallery-hover-overlay absolute inset-0 bg-lavender/70 flex items-center justify-center p-4 text-center">
                    <p class="font-playfair text-lg font-semibold text-gray-800">"Ketenangan dalam cangkir."</p>
                </div>
            </div>
            <!-- Gallery Item 3 --><div class="gallery-item relative rounded-xl overflow-hidden shadow-lg shadow-blush-pink/40 cursor-pointer scroll-reveal" style="transition-delay: 0.3s;">
                <img src="/assets/images/lilin.jpeg" alt="Lilin" class="w-full h-full object-cover">
                <div class="gallery-hover-overlay absolute inset-0 bg-pastel-cream/70 flex items-center justify-center p-4 text-center">
                    <p class="font-playfair text-lg font-semibold text-gray-800">"Nafas yang melegakan."</p>
                </div>
            </div>
            <!-- Gallery Item 4 --><div class="gallery-item relative rounded-xl overflow-hidden shadow-lg shadow-blush-pink/40 cursor-pointer scroll-reveal" style="transition-delay: 0.4s;">
                <img src="/assets/images/bunga.jpeg" alt="Bunga" class="w-full h-full object-cover">
                <div class="gallery-hover-overlay absolute inset-0 bg-rose-accent/70 flex items-center justify-center p-4 text-center">
                    <p class="font-playfair text-lg font-semibold text-white">"Tumbuh kembali."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox/Modal (Sederhana) --><div id="lightbox" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99] hidden items-center justify-center p-4">
    <div class="relative max-w-3xl max-h-[80vh]">
        <img id="lightbox-img" src="" alt="Tampilan diperbesar" class="w-full h-auto object-contain rounded-lg">
        <button id="lightbox-close" class="absolute -top-4 -right-4 text-white bg-rose-accent rounded-full w-10 h-10 text-2xl font-bold">&times;</button>
    </div>
</div>

<!-- 6. Testimonials Section (Carousel Sederhana) (Konten Baru) --><section id="testimonials" class="py-24 bg-lavender/20 relative overflow-hidden">
    <!-- Latar belakang cat air (disimulasikan dengan gradien) dan hati mengambang --><div class="absolute inset-0 bg-gradient-to-br from-blush-pink/10 via-lavender/30 to-pastel-cream/10 z-0"></div>
    <div id="testimonial-hearts" class="absolute inset-0 z-0 opacity-30"></div>
    
    <div class="container mx-auto max-w-3xl px-6 relative z-10">
        <div class="text-center mb-12 scroll-reveal">
            <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-4">Apa Kata Mereka?</h2>
        </div>
        
        <!-- Ini adalah carousel yang sangat disederhanakan. Implementasi nyata akan menggunakan JS --><div id="testimonial-carousel" class="scroll-reveal">
            <div class="text-center p-8 bg-white/70 backdrop-blur-md rounded-2xl shadow-lg shadow-lavender/40">
                <p class="font-inter text-xl italic text-gray-700 leading-relaxed mb-6">"Saya menemukan kembali diri saya di sini. Sesi konselingnya benar-benar membuka mata dan hati saya. Terima kasih telah memberikan ruang aman ini."</p>
                <div class="font-playfair text-lg font-semibold text-rose-accent">- Klien S.</div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Love Letter Form (Diganti jadi Kontak Sederhana) --><section id="contact" class="py-24 bg-pastel-cream relative">
    <!-- Container untuk Confetti (jika formnya diaktifkan) --><div id="confetti-container" class="absolute inset-0 w-full h-full pointer-events-none z-20"></div>

    <div class="container mx-auto max-w-3xl px-6 text-center relative z-10">
        <div class="scroll-reveal mb-12">
            <h2 class="font-playfair text-4xl font-bold text-gray-800 mb-4">Tinggalkan Pesan</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Atau bisikkan ceritamu. Kami di sini untuk mendengarkan.</p>
        </div>
        
        <form id="love-letter-form" class="scroll-reveal" style="transition-delay: 0.2s;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <input type="text" placeholder="Nama Anda" class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
                <input type="email" placeholder="Email Anda" class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm">
            </div>
            <textarea placeholder="Tuliskan pesanmu di sini..." rows="8" class="w-full p-4 rounded-lg border border-blush-pink bg-white/80 focus:ring-2 focus:ring-rose-accent focus:border-rose-accent outline-none transition-all duration-300 shadow-sm mb-6"></textarea>
            
            <button id="submit-letter" type="submit" class="btn-glow bg-rose-accent text-white font-semibold py-4 px-12 rounded-full text-lg shadow-lg shadow-rose-accent/40 transition-all duration-300 hover:scale-105 hover:bg-pink-600">
                Kirim Pesan 💌
            </button>
        </form>
    </div>
</section>

<!-- 8. CTA Band (Konten Baru) --><section id="cta" class="py-20 relative text-white text-center overflow-hidden">
    <!-- Latar belakang bintang (simulasi) --><div class="absolute inset-0 z-0 bg-navy-dark">
        <!-- Bintang-bintang kecil --><div style="position: absolute; top: 20%; left: 25%; width: 1px; height: 1px; background: white; box-shadow: 0 0 5px white; border-radius: 50%;"></div>
        <div style="position: absolute; top: 50%; left: 75%; width: 2px; height: 2px; background: white; box-shadow: 0 0 7px white; border-radius: 50%;"></div>
        <div style="position: absolute; top: 80%; left: 15%; width: 1px; height: 1px; background: white; box-shadow: 0 0 5px white; border-radius: 50%;"></div>
        <div style="position: absolute; top: 40%; left: 50%; width: 1px; height: 1px; background: white; box-shadow: 0 0 5px white; border-radius: 50%;"></div>
        
        <!-- Glowing Hearts --><div class="absolute top-1/3 left-1/3 text-4xl opacity-20 text-neon-pink shadow-[0_0_20px_#FF4DA6] animate-pulse">♡</div>
        <div class="absolute top-2/3 left-3/4 text-5xl opacity-20 text-neon-pink shadow-[0_0_20px_#FF4DA6] animate-pulse">♡</div>
    </div>
    
    <div class="relative z-10 scroll-reveal">
        <h2 class="font-playfair text-4xl font-bold mb-6">Langkah Pertama Menuju Pemulihan</h2>
        <p class="font-inter text-xl text-white/90 mb-8 max-w-2xl mx-auto">Anda tidak sendirian. Temukan konselor yang tepat untuk Anda hari ini.</p>
        <a href="counselors.php" class="btn-glow bg-neon-pink text-white font-semibold py-3 px-8 rounded-full text-lg shadow-lg shadow-neon-pink/40 transition-transform duration-300 hover:scale-105">
            Cari Konselor Sekarang
        </a>
    </div>
</section>


<?php include '../templates/footer.php'; ?>

