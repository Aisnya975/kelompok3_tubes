   <?php
if (!isset($base_path_for_links)) {
    $base_path_for_links = './'; 
}
?>
    
    </div> <!-- Penutup <div class="relative z-10"> (dari header.php) -->

    <!-- 9. Footer (BARU, menggantikan <footer> lama) -->
    <footer id="footer-section" class="py-16 bg-gradient-to-t from-blush-pink/60 to-pastel-cream relative">
        <div class="container mx-auto max-w-6xl px-6 text-center text-gray-700">
            
            <!-- Easter Egg: Whisper Quote -->
            <div id="whisper-quote" class="absolute bottom-24 left-1/2 -translate-x-1/2 opacity-0 font-playfair text-lg text-rose-accent/70 whisper-quote">
                "Cinta sejati adalah bisikan jiwa."
            </div>

            <!-- Logo Hati (untuk Easter Egg) -->
            <div id="footer-logo" class="inline-block text-4xl text-rose-accent mb-4 cursor-pointer transition-transform duration-300 hover:scale-110 animate-pulse">
                ♡
            </div>

            <div class="flex justify-center space-x-6 mb-6">
                <a href="https://curpurpro.great-site.net/?fbclid=PAb21jcANq1bNleHRuA2FlbQIxMQABp4Z5pjE4e4kt2_tCz0Lq1ui27g0s0q1w5n0BmeDNudHCsQvj5IuWMvM6T2s4_aem_qiKTNOTJ0DxcFGCbegu0BA&i=1" class="hover:text-rose-accent transition-colors" target="_blank">facebook </a>
                <a href="https://www.instagram.com/curpurpro?igsh=b2UwaGw1c295aGlx" class="hover:text-rose-accent transition-colors" target="_blank">instagram </a>
                <a href="https://curpurpro.great-site.net/?fbclid=PAb21jcANq1bNleHRuA2FlbQIxMQABp4Z5pjE4e4kt2_tCz0Lq1ui27g0s0q1w5n0BmeDNudHCsQvj5IuWMvM6T2s4_aem_qiKTNOTJ0DxcFGCbegu0BA&i=1" class="hover:text-rose-accent transition-colors" target="_blank">twiter </a>
            </div>
            <p class="font-inter text-gray-500">
                &copy; <span id="current-year">2024</span> Konseling Hati. Dibuat dengan Hati.
            </p>
        </div>
    </footer>

<!-- JavaScript untuk Semua Animasi Interaktif (BARU) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // --- 1. Animasi Partikel Hati Mengambang (Hero & Testimoni) ---
        function createFloatingHeart(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const heart = document.createElement('div');
            heart.innerHTML = '♡'; // Bisa ganti 💖, 💕, 💌
            heart.classList.add('floating-heart');
            
            heart.style.left = `${Math.random() * 100}vw`;
            heart.style.animationDuration = `${Math.random() * 5 + 8}s`; // Durasi antara 8-13 detik
            heart.style.fontSize = `${Math.random() * 10 + 10}px`; // Ukuran 10-20px
            heart.style.animationDelay = `${Math.random() * 5}s`; // Delay acak

            container.appendChild(heart);

            // Hapus hati setelah animasi selesai
            setTimeout(() => {
                heart.remove();
            }, 13000); // Sedikit lebih lama dari durasi maks
        }
        
        // Cek apakah kita di halaman yang butuh banyak hati (misal, index.php)
        if (document.getElementById('hero')) { // Asumsi #hero hanya ada di index.php
            // Buat 15 hati di Hero
            for (let i = 0; i < 15; i++) {
                createFloatingHeart('floating-particle-container');
            }
            // Buat hati baru secara berkala
            setInterval(() => createFloatingHeart('floating-particle-container'), 1000);
        } else {
            // Halaman lain dapat lebih sedikit hati
            for (let i = 0; i < 5; i++) {
                createFloatingHeart('floating-particle-container');
            }
            setInterval(() => createFloatingHeart('floating-particle-container'), 2500);
        }

        // Hati untuk testimoni (jika ada)
        if (document.getElementById('testimonial-hearts')) {
            for (let i = 0; i < 10; i++) {
                createFloatingHeart('testimonial-hearts');
            }
        }
        
        // --- 2. Efek Ketik (Typing Effect) ---
        const typingText = "Temukan kata untuk perasaanmu, sembuhkan hatimu, dan rayakan cintamu.";
        const typingElement = document.getElementById('typing-effect');
        if (typingElement) {
            typingElement.innerHTML = ''; // Kosongkan dulu
            let i = 0;
            function typeWriter() {
                if (i < typingText.length) {
                    typingElement.innerHTML += typingText.charAt(i);
                    i++;
                    setTimeout(typeWriter, 60); // Kecepatan ketik
                } else {
                    // Tambahkan kursor setelah selesai
                    typingElement.innerHTML += '<span class="typing-cursor"></span>';
                }
            }
            setTimeout(typeWriter, 1500); // Mulai setelah animasi judul
        }

        // --- 3. Scroll Reveal Animations ---
        const revealElements = document.querySelectorAll('.scroll-reveal');
        if (revealElements.length > 0) {
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        revealObserver.unobserve(entry.target); // Hanya animasi sekali
                    }
                });
            }, { threshold: 0.1 }); // Muncul saat 10% terlihat

            revealElements.forEach(el => revealObserver.observe(el));
        }

        // --- 4. Gallery Lightbox (Modal) ---
        const galleryItems = document.querySelectorAll('.gallery-item');
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxClose = document.getElementById('lightbox-close');

        if (lightbox && lightboxImg && lightboxClose && galleryItems.length > 0) {
            galleryItems.forEach(item => {
                item.addEventListener('click', () => {
                    const imgSrc = item.querySelector('img').src;
                    lightboxImg.src = imgSrc;
                    lightbox.classList.remove('hidden');
                    lightbox.classList.add('flex');
                });
            });

            lightboxClose.addEventListener('click', () => {
                lightbox.classList.add('hidden');
                lightbox.classList.remove('flex');
            });
            
            // Tutup saat klik di luar gambar
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                }
            });
        }

        // --- 5. Animasi Confetti (Form Submit) ---
        const form = document.getElementById('love-letter-form');
        const submitButton = document.getElementById('submit-letter');
        const confettiContainer = document.getElementById('confetti-container');

        if (form && submitButton && confettiContainer) {
            form.addEventListener('submit', (e) => {
                e.preventDefault(); // Hentikan pengiriman form
                
                // Ganti teks tombol
                submitButton.innerHTML = 'Terkirim! 💖';
                submitButton.disabled = true;

                // Tembakkan confetti
                for (let i = 0; i < 50; i++) {
                    createConfetti(submitButton);
                }
                
                // TODO: Kirim data form sesungguhnya (AJAX/Fetch)
                // setTimeout(() => form.submit(), 2000); // Contoh jika ingin submit biasa
            });
        }
        
        function createConfetti(button) {
            const rect = button.getBoundingClientRect();
            const x = rect.left + rect.width / 2;
            const y = rect.top + rect.height / 2;

            const particle = document.createElement('div');
            particle.classList.add('confetti-particle');
            
            // Warna acak
            const colors = ['#FF6B9A', '#FFD5E5', '#EAD9FF', '#FFC700'];
            particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            
            particle.style.left = `${x}px`;
            particle.style.top = `${y}px`;

            confettiContainer.appendChild(particle);

            // Animasikan
            const angle = Math.random() * 2 * Math.PI; // Arah acak
            const distance = Math.random() * 150 + 100; // Jarak 100-250px
            const finalX = x + Math.cos(angle) * distance;
            const finalY = y + Math.sin(angle) * distance;
            
            // Minta browser menganimasikan
            requestAnimationFrame(() => {
                particle.style.left = `${finalX}px`;
                particle.style.top = `${finalY}px`;
                particle.style.transform = `scale(0)`;
                particle.style.opacity = '0';
            });
            
            // Hapus partikel
            setTimeout(() => {
                particle.remove();
            }, 1000);
        }

        // --- 6. Hover Trail (Sparkles) ---
        const trail = document.getElementById('sparkle-trail');
        if (trail) {
            window.addEventListener('mousemove', (e) => {
                // Update posisi trail utama
                trail.style.left = `${e.clientX}px`;
                trail.style.top = `${e.clientY}px`;
                trail.style.opacity = '1';
                
                // Buat partikel "percikan"
                const sparkle = document.createElement('div');
                sparkle.classList.add('sparkle-trail');
                sparkle.style.left = `${e.clientX}px`;
                sparkle.style.top = `${e.clientY}px`;
                document.body.appendChild(sparkle);

                // Animasikan percikan
                setTimeout(() => {
                    sparkle.style.opacity = '0';
                    sparkle.style.transform = `translate(-50%, -50%) scale(2)`;
                }, 10); // Mulai memudar setelah 10ms

                // Hapus percikan
                setTimeout(() => {
                    sparkle.remove();
                }, 500); // Hapus setelah 0.5 detik
            });
        }

        // --- 7. "Whisper Mode" Easter Egg ---
        const footerLogo = document.getElementById('footer-logo');
        const whisperQuote = document.getElementById('whisper-quote');
        let clickCount = 0;
        
        if (footerLogo && whisperQuote) {
            footerLogo.addEventListener('click', () => {
                clickCount++;
                if (clickCount === 3) {
                    whisperQuote.style.opacity = '1';
                    // Reset setelah beberapa detik
                    setTimeout(() => {
                        whisperQuote.style.opacity = '0';
                        clickCount = 0;
                    }, 4000);
                }
            });
        }
        
        // --- 8. Hujan Hati (Saat Scroll ke Bawah) ---
        const footerSection = document.getElementById('footer-section');
        const heartsRainContainer = document.getElementById('hearts-rain-container');
        let rainInterval;
        
        if (footerSection && heartsRainContainer) {
            const rainObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    // Mulai hujan
                    if (!rainInterval) {
                        rainInterval = setInterval(createHeartRain, 200);
                    }
                } else {
                    // Hentikan hujan
                    clearInterval(rainInterval);
                    rainInterval = null;
                }
            }, { threshold: 0.1 });

            rainObserver.observe(footerSection);
        }

        function createHeartRain() {
            const heart = document.createElement('div');
            heart.innerHTML = '♡';
            heart.classList.add('heart-rain-particle');
            heart.style.left = `${Math.random() * 100}vw`;
            heart.style.animationDuration = `${Math.random() * 3 + 4}s`; // 4-7 detik
            heartsRainContainer.appendChild(heart);
            
            setTimeout(() => heart.remove(), 7000);
        }

        // --- 9. Set Tahun di Footer ---
        const yearEl = document.getElementById('current-year');
        if (yearEl) {
            yearEl.textContent = new Date().getFullYear();
        }
    });
</script>

</body>
</html>

