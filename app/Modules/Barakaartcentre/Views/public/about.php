<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<section class="bento-card" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 0.5rem;">Our Mission & Vision</h1>
    <p style="color: #fff; font-weight: 500; font-size: 1.1rem;">
        Vision: Inspire personal growth, community engagement, and artistic excellence.
    </p>
    <p style="max-width: 800px; margin-bottom: 2rem;">
        Our mission is to empower communities through creativity, fine art, and design. Operating out of the Mombasa Innovation Lab, we fuse precise scientific methodologies with the <em>'Baraka'</em> (blessing or skilled intent) of traditional craftsmanship to build sustainable ecosystems for creators.
    </p>
    
    <h3>Core Pillars</h3>
    <div class="pillars" style="margin-top: 1rem; margin-bottom: 3rem;">
        <span class="pillar-badge" style="border-color: var(--steam-cyan);">Empowerment</span>
        <span class="pillar-badge" style="border-color: var(--steam-yellow);">Creativity</span>
        <span class="pillar-badge" style="border-color: #52B44B;">Community</span>
        <span class="pillar-badge" style="border-color: var(--accent-gold);">Sustainability</span>
    </div>

    <div style="margin-top: 2rem; display: flex; align-items: center; gap: 1.5rem; padding-top: 2rem; border-top: 1px solid var(--glass-border);">
        <img src="https://picsum.photos/seed/founder/150/150" alt="Robai Nacheri" style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--accent-gold);">
        <div>
            <h3 style="margin: 0; color: #fff;">Robai Nacheri</h3>
            <p style="margin: 0; font-size: 1rem; color: var(--accent-gold);">Founder & Executive Director</p>
            <p style="margin-top: 0.5rem; font-size: 0.95rem; max-width: 600px;">
                Believing that art is not just aesthetic but therapeutic, Robai launched the Baraka Art Centre to create a safe space where young coastal creatives can find their voice, learn digital skills, and heal their nervous systems through guided workshops.
            </p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
