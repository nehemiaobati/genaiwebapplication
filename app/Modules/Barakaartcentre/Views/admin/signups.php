<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>View Email Signups</h2>
</div>

<div class="card">
    <?php if(empty($signups)): ?>
        <p style="color: #888;">No signups found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email Address</th>
                    <th>Source</th>
                    <th>Signed Up At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($signups as $s): ?>
                <tr>
                    <td><?= $s->id ?></td>
                    <td><strong style="color: #fff;"><?= esc($s->email) ?></strong></td>
                    <td>
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; color: <?= $s->source === 'newsletter' ? 'var(--steam-cyan)' : 'var(--steam-yellow)' ?>;">
                            <?= esc($s->source) ?>
                        </span>
                    </td>
                    <td style="color: #888; font-size: 0.9rem;"><?= date('F j, Y - g:i A', strtotime($s->created_at)) ?></td>
                    <td>
                        <form action="<?= route_to('baraka.admin.signups.delete', $s->id) ?>" method="POST" onsubmit="return confirm('Remove this signup?');" style="margin: 0;">
                            <?= csrf_field() ?>
                            <button type="submit" style="background: transparent; border: none; color: #e63946; text-decoration: underline; cursor: pointer; padding: 0; font-size: 1rem;">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
