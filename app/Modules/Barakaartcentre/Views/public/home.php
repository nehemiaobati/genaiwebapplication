<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<!-- Hero Section -->
<section class="hero-split">
    <div>
        <span class="tagline">Where Art Meets Science</span>
        <h1>Empowering Communities Through Art</h1>
        <p style="font-size: 1.1rem; max-width: 90%;">
            We empower communities through creativity, fine art, and design—combining artistic expression with scientifically-backed benefits for the mind and nervous system.
        </p>
        <div class="pillars">
            <span class="pillar-badge">Empowerment</span>
            <span class="pillar-badge">Creativity</span>
            <span class="pillar-badge">Community</span>
            <span class="pillar-badge">Sustainability</span>
        </div>
        <div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?= route_to('baraka.gallery') ?>" style="padding: 0.8rem 2rem; background: var(--accent-gold); color: #000; text-decoration: none; border-radius: 30px; font-weight: 600;">View Portfolio</a>
            <a href="<?= route_to('baraka.workshops') ?>" style="padding: 0.8rem 2rem; border: 1px solid var(--accent-gold); color: var(--accent-gold); text-decoration: none; border-radius: 30px; font-weight: 600;">Book a Workshop</a>
        </div>
    </div>
    <div class="hero-image-wrapper">
        <img src="https://picsum.photos/seed/bac-hero-art/800/1000" alt="Featured Swahili Coastal Art Piece">
    </div>
</section>

<!-- Featured Artworks -->
<?php if (!empty($featured_artworks)): ?>
<section style="margin-top: 4rem;">
    <h2>Featured Artworks</h2>
    <div class="masonry-grid" style="margin-top: 1.5rem;">
        <?php foreach ($featured_artworks as $art): ?>
        <div class="masonry-item">
            <img src="<?= esc($art->image_path) ?>" alt="<?= esc($art->title) ?>" loading="lazy">
            <div class="masonry-info">
                <h4><?= esc($art->title) ?></h4>
                <p><?= esc($art->category) ?> Project</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align: center;">
        <a href="<?= route_to('baraka.gallery') ?>" style="color: var(--accent-gold); text-decoration: underline;">View All Artworks &rarr;</a>
    </div>
</section>
<?php endif; ?>

<!-- Quick Services Preview -->
<section class="bento-container" style="margin-top: 4rem;">
    <div class="bento-card span-2" style="display: flex; flex-direction: column; justify-content: center;">
        <h2>Our Services</h2>
        <p>We fuse precise scientific methodologies with the 'Baraka' (blessing or skilled intent) of traditional craftsmanship to build sustainable ecosystems for creators.</p>
        <a href="<?= route_to('baraka.services') ?>" style="margin-top: 1rem; color: var(--accent-gold); text-decoration: none; font-weight: 600;">Explore All Services &rarr;</a>
    </div>
    <?php if (!empty($services)): ?>
        <?php foreach (array_slice($services, 0, 2) as $service): ?>
        <div class="bento-card">
            <?php if ($service->icon_or_image): ?>
                <img src="<?= esc($service->icon_or_image) ?>" alt="" style="width: 48px; height: 48px; border-radius: 8px; margin-bottom: 1rem;">
            <?php endif; ?>
            <h4 style="color: <?= $service->type === 'Revenue' ? 'var(--steam-yellow)' : 'var(--steam-cyan)' ?>;"><?= esc($service->title) ?></h4>
            <p style="font-size: 0.9rem; margin-bottom: 0;"><?= esc($service->short_description) ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
