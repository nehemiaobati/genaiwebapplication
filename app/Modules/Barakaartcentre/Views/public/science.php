<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<section class="bento-card" style="margin-top: 2rem; margin-bottom: 4rem;">
    <h1 style="text-align: center; margin-bottom: 1rem;">Nervous System Benefits of Art</h1>
    <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem auto;">
        At Baraka Art Centre, we integrate neuroscience into our workshops to ensure deep, lasting impacts on the mind and body. Art is not just an aesthetic practice; it is a profound tool for mental health.
    </p>
    
    <div class="science-grid">
        <div class="science-item">
            <h4>Stress Relief</h4>
            <p style="font-size:0.9rem;">Activates the parasympathetic nervous system; lowers heart rate & cortisol.</p>
        </div>
        <div class="science-item" style="border-color: var(--steam-yellow);">
            <h4>Emotional Regulation</h4>
            <p style="font-size:0.9rem;">Safe expression of complex emotions via colors, shapes, and patterns.</p>
        </div>
        <div class="science-item" style="border-color: var(--steam-red);">
            <h4>Mindfulness & Focus</h4>
            <p style="font-size:0.9rem;">Engages the brain's attention networks and drastically improves concentration by enforcing the 'flow' state.</p>
        </div>
        <div class="science-item" style="border-color: #52B44B;">
            <h4>Neural Connectivity</h4>
            <p style="font-size:0.9rem;">Strengthens fine motor skills, hand-eye coordination, and overall brain pathways across both hemispheres.</p>
        </div>
        <div class="science-item" style="border-color: var(--accent-gold);">
            <h4>Mood Boost</h4>
            <p style="font-size:0.9rem;">Triggers dopamine release from the act of creating and completing artwork, fostering intrinsic motivation.</p>
        </div>
        <div class="science-item" style="border-color: var(--steam-cyan);">
            <h4>Social Empathy</h4>
            <p style="font-size:0.9rem;">Collaborative community art directly enhances social connection and shared empathy among participants.</p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
