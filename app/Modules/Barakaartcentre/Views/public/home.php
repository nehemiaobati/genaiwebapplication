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
        <div class="cta-group" style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?= route_to('baraka.gallery') ?>" class="btn-primary">View Portfolio</a>
            <a href="<?= route_to('baraka.workshops') ?>" class="btn-secondary">Explore Workshops</a>
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

<!-- Core Impact Preview (Cognitive Clarity) -->
<section class="bento-container" style="margin-top: 4rem;">
    <div class="bento-card span-3" style="text-align: center; border-bottom: 2px solid var(--accent-gold);">
        <h2 style="margin-bottom: 0.5rem;">One Mission. Multiple Paths.</h2>
        <p style="max-width: 800px; margin: 0 auto 2rem auto;">We fuse precise scientific methodologies with the 'Baraka' (blessing) of craftsmanship to heal nervous systems and build sustainable creative ecosystems.</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: left;">
            <div>
                <h4 style="color: var(--steam-yellow);">Revenue Projects</h4>
                <p style="font-size: 0.9rem;">High-value art commissions & design solutions that fund our primary community mission.</p>
            </div>
            <div>
                <h4 style="color: var(--steam-cyan);">Community Impact</h4>
                <p style="font-size: 0.9rem;">Guided therapeutic workshops and subsidized creative programs for youth and local creators.</p>
            </div>
            <div style="display: flex; align-items: center; justify-content: center;">
                <a href="<?= route_to('baraka.services') ?>" class="btn-secondary" style="width: auto;">Explore All Services &rarr;</a>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
