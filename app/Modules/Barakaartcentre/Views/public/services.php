<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<h1 style="margin-top: 2rem; text-align: center;">Our Services</h1>
<p style="text-align: center; max-width: 600px; margin: 0 auto 2rem auto;">
    Discover our range of professional creative services and community impact projects.
</p>

<section class="bento-container" style="margin-top: 1.5rem; margin-bottom: 4rem;">
    <!-- Revenue Services Container -->
    <div class="bento-card">
        <h2 style="color: var(--steam-yellow);">Revenue-Generating Services</h2>
        <p style="font-size: 0.95rem;">Professional creative solutions funding our community mission.</p>
        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($services as $service): ?>
                <?php if ($service->type === 'Revenue'): ?>
                    <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 12px; border-left: 3px solid var(--steam-yellow);">
                        <h4 style="color: #fff; margin-bottom: 0.5rem;"><?= esc($service->title) ?></h4>
                        <p style="font-size: 0.9rem; margin: 0;"><?= esc($service->short_description) ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Community Services Container -->
    <div class="bento-card">
        <h2 style="color: var(--steam-cyan);">Community & Impact</h2>
        <p style="font-size: 0.95rem;">Free or subsidized programs for youth and local organizations.</p>
        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($services as $service): ?>
                <?php if ($service->type === 'Community'): ?>
                    <div style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 12px; border-left: 3px solid var(--steam-cyan);">
                        <h4 style="color: #fff; margin-bottom: 0.5rem;"><?= esc($service->title) ?></h4>
                        <p style="font-size: 0.9rem; margin: 0;"><?= esc($service->short_description) ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div style="text-align: center; margin-bottom: 4rem;">
    <a href="<?= route_to('baraka.contact') ?>" style="padding: 1rem 3rem; background: var(--accent-gold); color: #000; text-decoration: none; border-radius: 30px; font-weight: 600; font-size: 1.1rem;">Commission a Project</a>
</div>
<?= $this->endSection() ?>
