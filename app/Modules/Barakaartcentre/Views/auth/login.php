<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Baraka Art Centre</title>
    <style>
        body { background: #121212; color: #fff; font-family: system-ui, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #1e1e1e; padding: 3rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { text-align: center; color: #c6a87c; margin-bottom: 2rem; }
        input { width: 100%; padding: 1rem; margin-bottom: 1rem; background: #2a2a2a; border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 1rem; background: #c6a87c; color: #000; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #b09267; }
        .error { background: rgba(230,57,70,0.1); color: #e63946; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #e63946; font-size: 0.9rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Admin Portal</h1>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <form action="<?= route_to('baraka.login') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="<?= route_to('baraka.home') ?>" style="color: #666; font-size: 0.9rem; text-decoration: none;">&larr; Back to Main Site</a>
        </div>
    </div>
</body>
</html>
