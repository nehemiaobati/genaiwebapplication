<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Baraka Admin') ?></title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --bg-dark: #121212;
            --surface-dark: #1e1e1e;
            --accent-gold: #c6a87c;
            --text-light: #e0e0e0;
            --border: rgba(255,255,255,0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, sans-serif; }
        body { background: var(--bg-dark); color: var(--text-light); display: flex; min-height: 100vh; }
        
        /* Sidebar */
        aside { width: 250px; background: var(--surface-dark); border-right: 1px solid var(--border); padding: 2rem 1rem; }
        aside h2 { color: var(--accent-gold); margin-bottom: 2rem; padding-left: 1rem; }
        aside a { display: block; padding: 0.8rem 1rem; color: #aaa; text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: background 0.2s, color 0.2s; }
        aside a:hover, aside a.active { background: rgba(255,255,255,0.05); color: #fff; }
        
        /* Main Content */
        main { flex: 1; padding: 2rem; overflow-y: auto; overflow-x: hidden; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 1rem; }
        header h1 { font-size: clamp(1.4rem, 4vw, 1.8rem); line-height: 1.3; margin: 0; word-break: break-word; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .btn-logout { padding: 0.5rem 1rem; font-size: 0.9rem; border: 1px solid var(--border); background: transparent; color: #ff4d4d; border-radius: 6px; cursor: pointer; text-decoration: none; }
        .btn-logout:hover { background: rgba(255,77,77,0.1); }

        /* Mobile Navigation */
        .mobile-header { display: none; padding: 1rem; background: var(--surface-dark); border-bottom: 1px solid var(--border); align-items: center; justify-content: space-between; }
        .mobile-header h2 { color: var(--accent-gold); font-size: 1.2rem; }
        .menu-toggle { background: transparent; border: none; color: var(--text-light); font-size: 1.5rem; cursor: pointer; }
        
        @media (max-width: 768px) {
            body { flex-direction: column; }
            aside { display: none; width: 100%; border-right: none; padding: 1rem; border-bottom: 1px solid var(--border); }
            aside.show { display: block; }
            .mobile-header { display: flex; }
            aside .aside-title { display: none; }
            main { padding: 1rem; }
            header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }

        /* General UI */
        .card { background: var(--surface-dark); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; min-width: 750px; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--accent-gold); font-weight: 500; }
        
        .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .msg.success { background: rgba(82, 180, 75, 0.1); color: #52B44B; border: 1px solid #52B44B; }
        .msg.error { background: rgba(230, 57, 70, 0.1); color: #e63946; border: 1px solid #e63946; }
    </style>
</head>
<body>
    <?php $uri = uri_string(); ?>
    <div class="mobile-header">
        <h2>Admin Panel</h2>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">☰</button>
    </div>

    <aside id="sidebar">
        <h2 class="aside-title">Admin Panel</h2>
        <nav>
            <a href="<?= route_to('baraka.admin.dashboard') ?>" class="<?= (strpos($uri, 'admin') !== false && !strpos($uri, 'services') && !strpos($uri, 'artworks') && !strpos($uri, 'workshops') && !strpos($uri, 'signups')) ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= base_url('baraka-art-centre/admin/services') ?>" class="<?= strpos($uri, 'admin/services') !== false ? 'active' : '' ?>">Services</a>
            <a href="<?= base_url('baraka-art-centre/admin/artworks') ?>" class="<?= strpos($uri, 'admin/artworks') !== false ? 'active' : '' ?>">Artworks</a>
            <a href="<?= base_url('baraka-art-centre/admin/workshops') ?>" class="<?= strpos($uri, 'admin/workshops') !== false ? 'active' : '' ?>">Workshops</a>
            <a href="<?= base_url('baraka-art-centre/admin/signups') ?>" class="<?= strpos($uri, 'admin/signups') !== false ? 'active' : '' ?>">Signups</a>
            <a href="<?= route_to('baraka.home') ?>" target="_blank" style="margin-top: 2rem; color: var(--accent-gold);">View Site ↗</a>
        </nav>
    </aside>

    <main>
        <header>
            <h1><?= esc($pageTitle ?? 'Dashboard') ?></h1>
            <div class="user-info">
                <span>Welcome, <?= esc($admin_name ?? 'Admin') ?></span>
                <a href="<?= route_to('baraka.logout') ?>" class="btn-logout">Logout</a>
            </div>
        </header>

        <?php if (session()->getFlashdata('status')): ?>
            <div class="msg success"><?= esc(session()->getFlashdata('status')) ?></div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
</body>
</html>
