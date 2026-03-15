<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<div class="bento-card" style="margin-top: 2rem; margin-bottom: 4rem;">
    <h1 style="text-align: center; margin-bottom: 1rem;">Workshops & Community Stories</h1>
    <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem auto;">
        From "Mindful Observation" to "Creative Play" abstract painting, our sessions are designed for dopamine release and confidence building. Watch our community in action.
    </p>
    
    <div class="video-responsive">
        <iframe 
            src="https://www.youtube.com/embed/xahdGirc13k?si=ZzHWAWDlCbaILPh0" 
            title="YouTube video player" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
            referrerpolicy="strict-origin-when-cross-origin" 
            allowfullscreen>
        </iframe>
    </div>
</div>

<h2 style="margin-bottom: 2rem;">Upcoming Workshops</h2>

<?php if (empty($workshops)): ?>
    <div class="bento-card" style="text-align: center; border-color: var(--glass-border);">
        <p>No upcoming workshops at the moment. Sign up to our newsletter to be notified!</p>
    </div>
<?php else: ?>
    <div class="bento-container" style="margin-top: 0;">
        <?php foreach ($workshops as $workshop): ?>
            <div class="bento-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                <img src="<?= esc($workshop->image_path) ?>" alt="<?= esc($workshop->title) ?>" style="width: 100%; height: 200px; object-fit: cover;">
                <div style="padding: 1.5rem;">
                    <h3 style="color: #fff; margin-bottom: 0.5rem;"><?= esc($workshop->title) ?></h3>
                    <p style="color: var(--accent-gold); font-size: 0.9rem; margin-bottom: 1rem; font-weight: 500;">
                        🗓️ <?= date('F j, Y', strtotime($workshop->event_date)) ?> | ⏰ <?= esc($workshop->time) ?>
                    </p>
                    <p style="font-size: 0.9rem; flex-grow: 1;"><?= esc($workshop->description) ?></p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; border-top: 1px solid var(--glass-border); padding-top: 1rem;">
                        <span style="font-weight: 600; color: #52B44B;">
                            <?= $workshop->fee > 0 ? 'KES ' . number_format($workshop->fee, 0) : 'Free' ?>
                        </span>
                        <a href="<?= route_to('baraka.checkout.workshop', $workshop->id) ?>" style="padding: 0.5rem 1rem; background: var(--glass-bg); border: 1px solid var(--accent-gold); color: var(--accent-gold); border-radius: 8px; text-decoration: none; font-size: 0.85rem; transition: background 0.3s;">Join Workshop</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
