<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($artwork);
$action = $isEdit ? route_to('baraka.admin.artworks.update', $artwork->id) : route_to('baraka.admin.artworks.store');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2><?= $isEdit ? 'Edit Artwork' : 'Create Artwork' ?></h2>
    <a href="<?= route_to('baraka.admin.artworks') ?>" style="color: #888; text-decoration: none;">&larr; Back to Artworks</a>
</div>

<div class="card" style="max-width: 600px;">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="msg error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Title</label>
            <input type="text" name="title" value="<?= old('title', $artwork->title ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Category</label>
            <input type="text" name="category" list="category_list" value="<?= old('category', $artwork->category ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;" placeholder="Select or type a new category...">
            <datalist id="category_list">
                <option value="Original">
                <option value="Print">
                <option value="Mural">
                <option value="Student Project">
            </datalist>
        </div>

        <div style="margin-bottom: 1.5rem; border: 1px dashed var(--border); padding: 1rem; border-radius: 6px; background: rgba(255,255,255,0.02);">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-weight: bold;">Artwork Image</label>
            <p style="font-size: 0.85rem; color: #888; margin-bottom: 1rem;">Upload a file OR provide a URL below. If both are provided, the uploaded file will be used.</p>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Upload File</label>
                <input type="file" name="image_upload" accept="image/*" style="width: 100%; color: #fff;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Or Image URL</label>
                <input type="url" name="image_path" value="<?= old('image_path', $artwork->image_path ?? '') ?>" <?= $isEdit ? '' : 'required' ?> style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;" id="url_input">
                <script>
                    // Remove required from URL if a file is selected
                    document.querySelector('input[name="image_upload"]').addEventListener('change', function() {
                        if(this.files.length > 0) {
                            document.getElementById('url_input').removeAttribute('required');
                        } else {
                            <?php if(!$isEdit): ?>document.getElementById('url_input').setAttribute('required', 'required');<?php endif; ?>
                        }
                    });
                </script>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Description</label>
            <textarea name="description" rows="4" style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;"><?= old('description', $artwork->description ?? '') ?></textarea>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Price (KES) - Optional</label>
            <input type="number" step="0.01" name="price" value="<?= old('price', $artwork->price ?? '') ?>" style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="is_sold" id="is_sold" value="1" <?= old('is_sold', $artwork->is_sold ?? '') ? 'checked' : '' ?>>
            <label for="is_sold" style="color: #aaa;">Mark as Sold Out</label>
        </div>

        <button type="submit" style="background: var(--accent-gold); color: #000; padding: 0.8rem 2rem; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; width: 100%;">
            <?= $isEdit ? 'Update Artwork' : 'Save Artwork' ?>
        </button>
    </form>
</div>

<?= $this->endSection() ?>
