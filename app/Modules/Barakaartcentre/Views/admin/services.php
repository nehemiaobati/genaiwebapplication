<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Manage Services</h2>
    <a href="<?= route_to('baraka.admin.services.create') ?>" style="background: var(--accent-gold); color: #000; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: bold;">+ Add New Service</a>
</div>

<div class="card">
    <?php if(empty($services)): ?>
        <p style="color: #888;">No services found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Icon</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($services as $svc): ?>
                <tr>
                    <td>
                        <?php if($svc->icon_or_image): ?>
                            <img src="<?= esc($svc->icon_or_image) ?>" alt="" style="width: 40px; height: 40px; border-radius: 8px;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: #333; border-radius: 8px;"></div>
                        <?php endif; ?>
                    </td>
                    <td><strong style="color: #fff;"><?= esc($svc->title) ?></strong></td>
                    <td>
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; color: <?= $svc->type === 'Revenue' ? 'var(--steam-yellow)' : 'var(--steam-cyan)' ?>;">
                            <?= esc($svc->type) ?>
                        </span>
                    </td>
                    <td style="font-size: 0.9rem; max-width: 300px;"><?= esc($svc->short_description) ?></td>
                    <td>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="<?= route_to('baraka.admin.services.edit', $svc->id) ?>" style="color: #fff; text-decoration: underline;">Edit</a>
                            <form action="<?= route_to('baraka.admin.services.delete', $svc->id) ?>" method="POST" onsubmit="return confirm('Delete this service?');" style="margin: 0;">
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
