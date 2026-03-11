<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Manage Artworks</h2>
    <a href="<?= route_to('baraka.admin.artworks.create') ?>" style="background: var(--accent-gold); color: #000; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: bold;">+ Add New Artwork</a>
</div>

<div class="card">
    <?php if(empty($artworks)): ?>
        <p style="color: #888;">No artworks found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($artworks as $art): ?>
                <tr>
                    <td><img src="<?= esc($art->image_path) ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"></td>
                    <td><?= esc($art->title) ?></td>
                    <td>
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 12px; font-size: 0.85rem;">
                            <?= esc($art->category) ?>
                        </span>
                    </td>
                    <td><?= $art->price ? 'KES ' . number_format($art->price) : '-' ?></td>
                    <td>
                        <?php if($art->is_sold): ?>
                            <span style="color: #e63946;">Sold Out</span>
                        <?php else: ?>
                            <span style="color: #52B44B;">Available</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="<?= route_to('baraka.admin.artworks.edit', $art->id) ?>" style="color: #fff; text-decoration: underline;">Edit</a>
                            <form action="<?= route_to('baraka.admin.artworks.delete', $art->id) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this artwork?');" style="margin: 0;">
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
