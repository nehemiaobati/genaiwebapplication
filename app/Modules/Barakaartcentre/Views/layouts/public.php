<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($pageTitle ?? 'Baraka Art Centre') ?></title>
    <meta name="description" content="<?= esc($metaDescription ?? '') ?>">
    <link rel="canonical" href="<?= esc($canonicalUrl ?? current_url()) ?>">
    <meta name="robots" content="<?= esc($robotsTag ?? 'index, follow') ?>">

    <!-- Open Graph (Facebook, LinkedIn) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= esc($canonicalUrl ?? current_url()) ?>">
    <meta property="og:title" content="<?= esc($pageTitle ?? 'Baraka Art Centre') ?>">
    <meta property="og:description" content="<?= esc($metaDescription ?? '') ?>">
    <meta property="og:image" content="<?= esc($metaImage ?? '') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= esc($canonicalUrl ?? current_url()) ?>">
    <meta name="twitter:title" content="<?= esc($pageTitle ?? 'Baraka Art Centre') ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription ?? '') ?>">
    <meta name="twitter:image" content="<?= esc($metaImage ?? '') ?>">
    <meta name="twitter:image:alt" content="Baraka Art Centre Logo">

    <style>
        /* 2025 CSS Variables & Typography */
        :root {
            --bg-dark: #1f1f23;
            --accent-gold: #c6a87c;
            --steam-cyan: #008eb0;
            --steam-red: #e63946;
            --steam-yellow: #f4a261;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(198, 168, 124, 0.2);
            --text-muted: #a0a0a5;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-dark);
            color: #e0e0e0;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Fluid Geometry Background */
        .hero-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background: radial-gradient(circle at 50% 0%, rgba(0, 142, 176, 0.08), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(230, 57, 70, 0.04), transparent 50%);
            opacity: 0.8;
        }

        /* Header Styles */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(31, 31, 35, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .logo-img { height: 40px; width: auto; margin-right: 10px; }
        .logo-container span { color: var(--accent-gold); }

        nav { display: flex; gap: 2rem; }
        nav a {
            color: #e0e0e0;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.3s;
        }
        nav a:hover, nav a.active { color: var(--accent-gold); }

        /* Layout Container */
        .container {
            padding: 2rem;
            max-width: 1280px;
            margin: 0 auto;
            min-height: 80vh;
        }

        /* Typography */
        h1, h2, h3, h4 {
            color: var(--accent-gold);
            font-weight: 600;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        h1 { font-size: clamp(2.5rem, 6vw, 3.8rem); letter-spacing: -0.02em; }
        p { color: var(--text-muted); margin-bottom: 1rem; }

        .tagline {
            font-size: 1.2rem;
            background: linear-gradient(90deg, var(--steam-cyan), var(--steam-yellow), var(--steam-red));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        /* Pillars Badges */
        .pillars {
            margin-top: 1.5rem; display: flex; gap: 0.8rem; flex-wrap: wrap;
        }
        .pillar-badge {
            font-size: 0.8rem; padding: 6px 16px; border-radius: 20px; 
            background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--glass-border);
        }

        /* Bento Grid Layouts */
        .bento-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 4rem;
        }

        .bento-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .bento-card:hover { border-color: var(--accent-gold); }
        .span-2 { grid-column: span 2; }
        .span-3 { grid-column: span 3; }

        /* Nested Grid for Science section */
        .science-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .science-item {
            background: rgba(0,0,0,0.3); padding: 1.5rem; border-radius: 12px;
            border-left: 3px solid var(--steam-cyan);
        }
        .science-item h4 { color: #fff; margin-bottom: 0.5rem; font-size: 1.05rem; }

        /* Hero Section Split */
        .hero-split {
            display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 4rem;
            align-items: center; margin-top: 2rem; margin-bottom: 4rem;
        }
        .hero-image-wrapper {
            position: relative; border-radius: 20px; overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5); aspect-ratio: 4/5;
        }
        .hero-image-wrapper img {
            width: 100%; height: 100%; object-fit: cover; transition: transform 0.7s ease;
        }
        .hero-image-wrapper:hover img { transform: scale(1.03); }

        /* Masonry Portfolio Gallery */
        .masonry-grid {
            column-count: 1; column-gap: 1.5rem; margin-top: 2rem; margin-bottom: 4rem;
        }
        @media (min-width: 768px) { .masonry-grid { column-count: 2; } }
        @media (min-width: 1024px) { .masonry-grid { column-count: 3; } }

        .masonry-item {
            break-inside: avoid; margin-bottom: 1.5rem; background: var(--glass-bg);
            border: 1px solid var(--glass-border); border-radius: 16px; overflow: hidden;
            transition: transform 0.3s ease;
        }
        .masonry-item:hover { transform: translateY(-4px); border-color: rgba(255,255,255,0.2); }
        .masonry-item img { width: 100%; height: auto; display: block; }
        .masonry-info { padding: 1.25rem; border-top: 1px solid var(--glass-border); }
        .masonry-info h4 { font-size: 1.1rem; margin-bottom: 0.25rem; color: #fff; }
        .masonry-info p { font-size: 0.85rem; margin-bottom: 0; }

        /* Inputs & Flow */
        input[type="text"], input[type="number"], input[type="email"] {
            width: 100%; padding: 1.2rem; margin-bottom: 1rem;
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--glass-border);
            color: #fff; border-radius: 12px; font-size: 1rem; transition: border-color 0.3s;
        }
        input:focus { outline: none; border-color: #52B44B; }
        
        .btn-mpesa {
            width: 100%; padding: 1.2rem; background: #52B44B; color: white;
            border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 600;
            cursor: pointer; display: flex; justify-content: center; align-items: center;
            gap: 10px; transition: background 0.2s, transform 0.1s;
        }
        .btn-mpesa:hover { background: #42933c; }
        .btn-mpesa:active { transform: scale(0.98); }

        .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.95rem; }
        .msg.success { background: rgba(82, 180, 75, 0.1); color: #52B44B; border: 1px solid #52B44B; }
        .msg.error { background: rgba(230, 57, 70, 0.1); color: var(--steam-red); border: 1px solid var(--steam-red); }

        /* Responsive Video Container */
        .video-responsive {
            position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;
            max-width: 100%; background: #000; border-radius: 16px; margin-top: 1.5rem;
            border: 1px solid var(--glass-border);
        }
        .video-responsive iframe {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.6); border-top: 1px solid var(--glass-border);
            padding: 4rem 2rem 2rem 2rem; margin-top: 4rem;
        }
        .footer-content {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem; max-width: 1280px; margin: 0 auto;
        }
        .footer-col h3, .footer-col h4 { color: #fff; margin-bottom: 0.5rem; }
        .footer-col a { color: var(--text-muted); text-decoration: none; display: block; margin-bottom: 0.5rem; transition: color 0.2s; }
        .footer-col a:hover { color: var(--accent-gold); }

        /* Floating Nav & WhatsApp */
        .fab-wa {
            position: fixed; bottom: 24px; right: 24px; background: #25D366; color: white;
            width: 60px; height: 60px; border-radius: 50%; display: flex; justify-content: center;
            align-items: center; box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4); z-index: 100;
            transition: transform 0.2s;
        }
        .fab-wa:hover { transform: scale(1.1); }
        .fab-wa svg { width: 32px; height: 32px; fill: currentColor; }

        .fab-wa svg { width: 32px; height: 32px; fill: currentColor; }

        .menu-toggle { display: none; background: transparent; border: none; color: #e0e0e0; font-size: 1.8rem; cursor: pointer; }

        @media (max-width: 900px) {
            .hero-split { grid-template-columns: 1fr; gap: 2rem; }
            .hero-image-wrapper { order: -1; aspect-ratio: 16/9; }
            .span-2, .span-3 { grid-column: span 1; }
            
            nav { display: none; flex-direction: column; gap: 1.5rem; width: 100%; position: absolute; top: 100%; left: 0; background: rgba(31, 31, 35, 0.98); backdrop-filter: blur(16px); padding: 2rem; border-bottom: 1px solid var(--glass-border); text-align: center; }
            nav.show { display: flex; }
            header { flex-wrap: wrap; }
            .menu-toggle { display: block; }
        }

        <?= $this->renderSection('styles') ?>
    </style>
</head>

<body>
    <div class="hero-bg"></div>

    <header>
        <a href="<?= route_to('baraka.home') ?>" class="logo-container">
            <img src="<?= base_url('assets/images/baraka_logo.png') ?>" alt="Baraka Art Centre Logo" class="logo-img">
            Baraka Art Centre<span>.</span>
        </a>
        <button class="menu-toggle" id="publicMenuToggle" aria-label="Toggle Navigation">☰</button>
        <nav id="publicNav">
            <a href="<?= route_to('baraka.about') ?>">About</a>
            <a href="<?= route_to('baraka.services') ?>">Services</a>
            <a href="<?= route_to('baraka.science') ?>">Science of Art</a>
            <a href="<?= route_to('baraka.gallery') ?>">Portfolio</a>
            <a href="<?= route_to('baraka.workshops') ?>">Workshops</a>
            <a href="<?= route_to('baraka.contact') ?>">Support/Contact</a>
        </nav>
    </header>

    <main class="container">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h3>Baraka Art Centre.</h3>
                <p>Where Art Meets Science.</p>
                <p style="font-size: 0.85rem; margin-top: 1rem;">Empowering communities through creativity, fine art, and design.</p>
            </div>
            <div class="footer-col">
                <h4>Join Our Newsletter</h4>
                <form action="<?= route_to('baraka.newsletter') ?>" method="POST" style="margin-top: 1rem; display: flex; gap: 10px;">
                    <?= csrf_field() ?>
                    <input type="email" name="email" placeholder="Your Email" required style="margin-bottom: 0; padding: 0.8rem;">
                    <button type="submit" style="padding: 0 1.5rem; background: var(--accent-gold); color: #000; font-weight: 600; border-radius: 12px; border: none; cursor: pointer;">Join</button>
                </form>
                <?php if (session()->getFlashdata('status')): ?>
                    <p style="color: #52B44B; font-size: 0.85rem; margin-top: 5px;"><?= esc(session()->getFlashdata('status')) ?></p>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <p style="color: var(--steam-red); font-size: 0.85rem; margin-top: 5px;"><?= esc(session()->getFlashdata('error')) ?></p>
                <?php endif; ?>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="<?= route_to('baraka.about') ?>">About the Centre</a>
                <a href="<?= route_to('baraka.services') ?>">Our Services</a>
                <a href="<?= route_to('baraka.science') ?>">Science of Art</a>
                <a href="<?= route_to('baraka.gallery') ?>">Portfolio Gallery</a>
            </div>
        </div>
        <div style="text-align: center; border-top: 1px solid var(--glass-border); padding-top: 2rem; margin-top: 3rem; color: var(--text-muted); font-size: 0.85rem;">
            &copy; <?= date("Y") ?> Baraka Art Centre. All rights reserved.
        </div>
    </footer>

    <!-- WhatsApp FAB -->
    <a href="https://wa.me/+254795691611?text=Jambo%20Baraka%20Art%20Centre,%20I%20would%20like%20to%20know%20more%20about%20your%20artwork%20and%20workshops." class="fab-wa" target="_blank" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
    </a>

    <script>
        const toggle = document.getElementById('publicMenuToggle');
        const nav = document.getElementById('publicNav');
        if (toggle && nav) {
            toggle.addEventListener('click', () => {
                nav.classList.toggle('show');
            });
        }
    </script>
</body>
</html>
