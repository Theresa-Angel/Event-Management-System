<?php
require_once 'config.php';
require_once 'includes/header.php';

// Scan all images from assets/images folder
$imageDir = 'assets/images/';
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$excluded = ['atchaya.jpeg', 'theresa.jpeg', 'sivakami.jpeg', 'karthiga.jpeg', 'ganimozhi.jpeg'];
$images = [];

foreach (scandir($imageDir) as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, $allowedExt) && !in_array(strtolower($file), $excluded)) {
        $images[] = $imageDir . $file;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery - Campus Connect</title>
    <style>
        body { background: #f1f5f9; padding-top: 80px; }

        .gallery-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .gallery-hero h1 { font-size: 3rem; font-weight: 700; margin: 0 0 1.5rem; line-height: 1.2; }
        .gallery-hero p  { opacity: 0.9; margin: 0; font-size: 1.25rem; }

        /* Floating shapes - same as about.php */
        .shape {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 20%;
            animation: float 8s infinite ease-in-out;
        }
        .shape-1 { width: 100px; height: 100px; top: 10%;  left: 5%;  animation-delay: 0s; }
        .shape-2 { width: 150px; height: 150px; top: 50%; right: 5%; animation-delay: 2s; }
        .shape-3 { width: 60px;  height: 60px;  bottom: 10%; left: 40%; animation-delay: 4s; }

        @keyframes float {
            0%   { transform: translateY(0px)   rotate(0deg); }
            50%  { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px)   rotate(0deg); }
        }

        /* Hero text animations */
        .animate-hidden { opacity: 0; }
        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        /* Gallery image scroll-in */
        .gallery-wrap img {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.5s ease, transform 0.5s ease, box-shadow 0.3s, scale 0.3s;
        }
        .gallery-wrap img.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .gallery-wrap img:hover {
            transform: scale(1.02) translateY(0) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .gallery-wrap {
            max-width: 1300px;
            margin: 40px auto;
            padding: 0 20px;
            columns: 4;
            column-gap: 14px;
        }
        @media (max-width: 1100px) { .gallery-wrap { columns: 3; } }
        @media (max-width: 700px)  { .gallery-wrap { columns: 2; } }
        @media (max-width: 420px)  { .gallery-wrap { columns: 1; } }

        .gallery-wrap img {
            width: 100%;
            display: block;
            margin-bottom: 14px;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            break-inside: avoid;
        }
        .gallery-wrap img:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
            grid-column: 1/-1;
        }
        .empty-state i { font-size: 3.5rem; display: block; margin-bottom: 14px; }

        /* Lightbox */
        #lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.93);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
        }
        #lightbox.open { display: flex; }

        #lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 12px;
            object-fit: contain;
            animation: zoomIn 0.2s ease;
        }
        @keyframes zoomIn { from { opacity:0; transform:scale(0.92); } to { opacity:1; transform:scale(1); } }

        .lb-close {
            position: fixed;
            top: 20px; right: 24px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 40px; height: 40px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .lb-close:hover { background: rgba(255,255,255,0.3); }

        .lb-nav {
            position: fixed;
            top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.12);
            border: none; color: white;
            width: 46px; height: 46px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .lb-nav:hover { background: rgba(255,255,255,0.28); }
        .lb-prev { left: 20px; }
        .lb-next { right: 20px; }

        .lb-counter {
            position: fixed;
            bottom: 20px; left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.6);
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="gallery-hero">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <h1 class="animate-hidden" id="ghTitle"><i class="fas fa-images" style="margin-right:10px;"></i>Gallery</h1>
    <p class="animate-hidden" id="ghSub">Moments from our campus events</p>
</div>

<div class="gallery-wrap">
    <?php if (empty($images)): ?>
        <div class="empty-state">
            <i class="fas fa-image"></i>
            <p>No images found in the gallery yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($images as $i => $src): ?>
            <img src="<?= htmlspecialchars($src) ?>"
                 alt="Gallery image <?= $i + 1 ?>"
                 loading="lazy"
                 onclick="openLightbox(<?= $i ?>)"
                 onerror="this.style.display='none'">
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Lightbox -->
<div id="lightbox" onclick="if(event.target===this)closeLightbox()">
    <button class="lb-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <button class="lb-nav lb-prev" onclick="navigate(-1)"><i class="fas fa-chevron-left"></i></button>
    <img id="lbImg" src="" alt="">
    <button class="lb-nav lb-next" onclick="navigate(1)"><i class="fas fa-chevron-right"></i></button>
    <span class="lb-counter" id="lbCounter"></span>
</div>

<script>
    // Hero text animations (same timing as about.php)
    setTimeout(() => {
        const t = document.getElementById('ghTitle');
        t.classList.remove('animate-hidden');
        t.classList.add('animate-fade-up');
    }, 300);
    setTimeout(() => {
        const s = document.getElementById('ghSub');
        s.classList.remove('animate-hidden');
        s.classList.add('animate-fade-up');
    }, 600);

    // Scroll-triggered image reveal
    const imgs = document.querySelectorAll('.gallery-wrap img');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    imgs.forEach(img => observer.observe(img));

    // Lightbox
    const srcs = <?= json_encode(array_values($images)) ?>;
    let cur = 0;

    function openLightbox(i) {
        cur = i;
        update();
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    function navigate(dir) {
        cur = (cur + dir + srcs.length) % srcs.length;
        update();
    }
    function update() {
        document.getElementById('lbImg').src = srcs[cur];
        document.getElementById('lbCounter').textContent = (cur + 1) + ' / ' + srcs.length;
    }
    document.addEventListener('keydown', e => {
        if (!document.getElementById('lightbox').classList.contains('open')) return;
        if (e.key === 'ArrowRight') navigate(1);
        if (e.key === 'ArrowLeft')  navigate(-1);
        if (e.key === 'Escape')     closeLightbox();
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php require_once 'includes/footer.php'; ?>
</body>
</html>
