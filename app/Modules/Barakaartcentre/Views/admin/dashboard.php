<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <h3 style="color: #888; font-size: 1rem; margin-bottom: 0.5rem;">Total Artworks</h3>
        <p style="font-size: 2.5rem; font-weight: bold; color: #fff;"><?= esc($stats['total_artworks']) ?></p>
    </div>
    <div class="card" style="text-align: center;">
        <h3 style="color: #888; font-size: 1rem; margin-bottom: 0.5rem;">Total Services</h3>
        <p style="font-size: 2.5rem; font-weight: bold; color: #fff;"><?= esc($stats['total_services']) ?></p>
    </div>
    <div class="card" style="text-align: center;">
        <h3 style="color: #888; font-size: 1rem; margin-bottom: 0.5rem;">Workshops</h3>
        <p style="font-size: 2.5rem; font-weight: bold; color: #fff;"><?= esc($stats['total_workshops']) ?></p>
    </div>
    <div class="card" style="text-align: center;">
        <h3 style="color: #888; font-size: 1rem; margin-bottom: 0.5rem;">Signups</h3>
        <p style="font-size: 2.5rem; font-weight: bold; color: #fff;"><?= esc($stats['total_signups']) ?></p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Recent Signups -->
    <div class="card">
        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Recent Email Signups</h2>
        <?php if(empty($stats['recent_signups'])): ?>
            <p style="color: #888;">No signups yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Source</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stats['recent_signups'] as $s): ?>
                    <tr>
                        <td><?= esc($s->email) ?></td>
                        <td><span style="background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;"><?= esc($s->source) ?></span></td>
                        <td style="color: #888; font-size: 0.9rem;"><?= date('M j, Y', strtotime($s->created_at)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Upcoming Workshops -->
    <div class="card">
        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Upcoming Workshops</h2>
        <?php if(empty($stats['recent_workshops'])): ?>
            <p style="color: #888;">No workshops scheduled.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stats['recent_workshops'] as $w): ?>
                    <tr>
                        <td><?= esc($w->title) ?></td>
                        <td>
                            <span style="color: var(--accent-gold); font-weight: bold;">
                                <?= date('M j, Y', strtotime($w->event_date)) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div style="text-align: right; margin-top: 1rem;">
            <a href="<?= base_url('baraka-art-centre/admin/workshops') ?>" style="color: var(--accent-gold); text-decoration: none; font-size: 0.9rem;">Manage Workshops &rarr;</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
