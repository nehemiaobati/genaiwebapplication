<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Manage Workshops</h2>
    <a href="<?= route_to('baraka.admin.workshops.create') ?>" style="background: var(--accent-gold); color: #000; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: bold;">+ Schedule Workshop</a>
</div>

<div class="card">
    <?php if(empty($workshops)): ?>
        <p style="color: #888;">No workshops found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date & Time</th>
                    <th>Fee</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($workshops as $ws): ?>
                <tr>
                    <td><strong style="color: #fff;"><?= esc($ws->title) ?></strong></td>
                    <td>
                        <span style="color: var(--accent-gold); font-weight: bold; display: block;"><?= date('M j, Y', strtotime($ws->event_date)) ?></span>
                        <span style="font-size: 0.85rem; color: #888;"><?= esc($ws->time) ?></span>
                    </td>
                    <td><?= $ws->fee > 0 ? 'KES ' . number_format($ws->fee) : '<span style="color:#52B44B;">Free</span>' ?></td>
                    <td style="font-size: 0.9rem; max-width: 300px;"><?= esc($ws->description) ?></td>
                    <td>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="<?= route_to('baraka.admin.workshops.edit', $ws->id) ?>" style="color: #fff; text-decoration: underline;">Edit</a>
                            <form action="<?= route_to('baraka.admin.workshops.delete', $ws->id) ?>" method="POST" onsubmit="return confirm('Cancel and delete this workshop?');" style="margin: 0;">
                                <?= csrf_field() ?>
                                <button type="submit" style="background: transparent; border: none; color: #e63946; text-decoration: underline; cursor: pointer; padding: 0; font-size: 1rem;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
