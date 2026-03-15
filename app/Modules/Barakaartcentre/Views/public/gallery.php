<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<h1 style="margin-top: 2rem; text-align: center;">Gallery & Portfolio</h1>
<p style="text-align: center; max-width: 600px; margin: 0 auto 2rem auto;">
    A curated display of exquisite original art commissions, engaging student projects, and vibrant collaborative community murals.
</p>

<?php if (empty($artworks)): ?>
    <div class="bento-card" style="text-align: center; margin-top: 4rem;">
        <p>Gallery is currently empty. Check back soon for beautiful additions!</p>
    </div>
<?php else: ?>
    <div class="masonry-grid">
        <?php foreach ($artworks as $art): ?>
            <div class="masonry-item">
                <img src="<?= esc($art->image_path) ?>" alt="<?= esc($art->title) ?>" loading="lazy">
                <div class="masonry-info">
                    <h4><?= esc($art->title) ?></h4>
                    <p style="color: var(--accent-gold); font-weight: 500; font-size: 0.8rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;"><?= esc($art->category) ?></p>
                    <?php if ($art->description): ?>
                        <p style="margin-bottom: 0.5rem;"><?= esc($art->description) ?></p>
                    <?php endif; ?>
                    <?php if ($art->category === 'Original' && $art->price && !$art->is_sold): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <p style="margin: 0; font-weight: 600; color: #52B44B;">
                                KES <?= number_format($art->price, 2) ?>
                            </p>
                            <a href="<?= route_to('baraka.checkout.artwork', $art->id) ?>" style="padding: 0.4rem 0.8rem; background: var(--accent-gold); color: #000; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">Buy Now</a>
                        </div>
                    <?php elseif ($art->is_sold): ?>
                        <p style="margin-top: 1rem; margin-bottom: 0; font-weight: 600; color: var(--steam-red);">
                            Sold Out
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
