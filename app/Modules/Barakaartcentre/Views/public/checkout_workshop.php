<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\public') ?>

<?= $this->section('content') ?>
<div style="max-width: 600px; margin: 0 auto;">
    <div class="bento-card">
        <h2 style="text-align: center; margin-bottom: 0.5rem;">Join Workshop</h2>
        <p style="text-align: center; margin-bottom: 2rem;">Register for our upcoming session and pay securely via Paystack.</p>

        <div style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 1rem; margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center; border: 1px solid var(--glass-border);">
            <img src="<?= esc($item->image_path) ?>" alt="<?= esc($item->title) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
            <div style="flex: 1;">
                <h4 style="margin: 0; color: #fff;"><?= esc($item->title) ?></h4>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($item->event_date)) ?> @ <?= esc($item->location) ?></p>
                <p style="margin: 0; font-size: 0.9rem; color: var(--accent-gold);">KES <?= number_format($item->fee, 2) ?></p>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="msg error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form action="<?= route_to('baraka.process.order') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= esc($item->id) ?>">
            <input type="hidden" name="item_type" value="workshop">
            <input type="hidden" name="amount" value="<?= esc($item->fee) ?>">

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Full Name</label>
                <input type="text" name="name" placeholder="E.g. John Doe" required value="<?= old('name') ?>">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required value="<?= old('email') ?>">
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #aaa;">Phone Number (M-Pesa Preferred)</label>
                <input type="text" name="phone_number" placeholder="07xxxxxxxx" required value="<?= old('phone_number') ?>">
                <small style="color: var(--text-muted); font-size: 0.8rem;">Used for registration confirmation and support.</small>
            </div>

            <button type="submit" class="btn-mpesa" style="background: var(--accent-gold); color: #000;">
                Register & Pay
            </button>
            <p style="text-align: center; margin-top: 1rem; font-size: 0.85rem;">
                <a href="<?= route_to('baraka.workshops') ?>" style="color: var(--text-muted); text-decoration: none;">&larr; Back to Workshops</a>
            </p>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
