<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('styles') ?>
<style>
    @media (max-width: 768px) {
        .cta-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(31, 31, 35, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-top: 1px solid var(--glass-border);
            z-index: 100;
        }
        form { padding-bottom: 80px; } /* Prevent overlap */
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 600px; margin: 0 auto;">
    <div class="bento-card">
        <h2 style="text-align: center; margin-bottom: 0.5rem;">Secure Artwork</h2>
        <p style="text-align: center; margin-bottom: 2rem;">Please provide your details to proceed to secure payment via Paystack.</p>

        <div style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 1rem; margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center; border: 1px solid var(--glass-border);">
            <img src="<?= esc($item->image_path) ?>" alt="<?= esc($item->title) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
            <div>
                <h4 style="margin: 0; color: #fff;"><?= esc($item->title) ?></h4>
                <p style="margin: 0; font-size: 0.9rem; color: var(--accent-gold);">KES <?= number_format($item->price, 2) ?></p>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="msg error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form action="<?= route_to('baraka.process.order') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= esc($item->id) ?>">
            <input type="hidden" name="item_type" value="artwork">
            <input type="hidden" name="amount" value="<?= esc($item->price) ?>">

            <div style="margin-bottom: 1.5rem;">
                <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Full Name</label>
                <input type="text" id="full_name" name="name" placeholder="E.g. Jane Doe" required value="<?= old('name') ?>">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Email Address</label>
                <input type="email" id="email" name="email" placeholder="jane@example.com" required value="<?= old('email') ?>">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="phone_number" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Phone Number (M-Pesa Preferred)</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="07xxxxxxxx" required value="<?= old('phone_number') ?>">
                <small style="color: var(--text-muted); font-size: 0.8rem;">Used for order updates and helpdesk support.</small>
            </div>

            <div style="margin-bottom: 2rem;">
                <label for="delivery_address" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Delivery Address</label>
                <textarea id="delivery_address" name="delivery_address" placeholder="Please provide your physical address for delivery." required><?= old('delivery_address') ?></textarea>
            </div>

            <div class="cta-wrapper">
                <button type="submit" class="btn-mpesa" style="background: var(--accent-gold); color: #000;">
                    Proceed to Payment
                </button>
            </div>
            <p style="text-align: center; margin-top: 1rem; font-size: 0.85rem;">
                <a href="<?= route_to('baraka.gallery') ?>" style="color: var(--text-muted); text-decoration: none;">&larr; Back to Gallery</a>
            </p>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
