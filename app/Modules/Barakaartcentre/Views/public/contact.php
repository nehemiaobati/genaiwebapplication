<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<div class="hero-split" style="margin-top: 2rem; align-items: flex-start;">
    
    <!-- Left side: Information -->
    <div class="bento-card" style="height: 100%;">
        <h1 style="color: #fff; margin-bottom: 0.5rem;">Support & Commission</h1>
        <p style="margin-bottom: 2rem;">
            Whether you are looking to book a workshop, commission a digital design, or financially support the Innovation Lab, we’d love to hear from you. Contributions directly fund our "Design for Good" projects across the coastal community.
        </p>
        
        <h4 style="color: var(--steam-cyan); margin-bottom: 1rem;">Contact Info</h4>
        <ul style="list-style: none; padding: 0; color: var(--text-muted); line-height: 2;">
            <li>📍 <strong>Location:</strong> Mombasa, Kenya</li>
            <li>✉️ <strong>Email:</strong> <a href="mailto:info@barakaartcentre.org" style="color: var(--accent-gold); text-decoration: none;">info@barakaartcentre.org</a></li>
            <li>📱 <strong>WhatsApp:</strong> +254 795 691 611</li>
        </ul>

        <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px; border-left: 3px solid var(--accent-gold);">
            <h4 style="color: #fff;">Looking for a specific commission?</h4>
            <p style="font-size: 0.9rem; margin-bottom: 0;">Drop us a line via Email or WhatsApp. Please include details such as subject, size, and medium if applying for a Fine Art Commission.</p>
        </div>
    </div>

    <!-- Right side: M-Pesa Checkout Flow -->
    <section class="bento-card checkout-flow" id="support">
        <h2 style="color: #52B44B; margin-bottom: 0.5rem;">Donate via M-Pesa STK</h2>
        <p style="font-size: 0.95rem;">Empower a creator instantly.</p>

        <?php if (!empty($payment_status)): ?>
            <div class="msg <?= esc($payment_class ?? 'success') ?>">
                <?= esc($payment_status) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('baraka-art-centre') ?>" method="POST" style="margin-top: 2rem;">
            <?= csrf_field() ?>
            <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; color:var(--text-muted);">Safaricom Phone Number</label>
            <input type="text" name="mpesa_phone" placeholder="e.g. 0712 345 678" required>
            
            <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; color:var(--text-muted);">Amount (KES)</label>
            <input type="number" name="amount" placeholder="Amount" required min="10">
            
            <button type="submit" class="btn-mpesa" style="margin-top: 1rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Initiate Prompt
            </button>
        </form>
    </section>

</div>
<?= $this->endSection() ?>
