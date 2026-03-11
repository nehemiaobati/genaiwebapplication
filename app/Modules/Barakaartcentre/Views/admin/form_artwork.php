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
            <div id="category_container">
                <select name="category_select" id="category_select" style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px; appearance: auto;">
                    <option value="">-- Select Category --</option>
                    <?php 
                    $currentCat = old('category', $artwork->category ?? '');
                    $presets = ['Original', 'Print', 'Mural', 'Student Project'];
                    $isCustom = !empty($currentCat) && !in_array($currentCat, $presets);
                    ?>
                    <?php foreach($presets as $p): ?>
                        <option value="<?= $p ?>" <?= $currentCat === $p ? 'selected' : '' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                    <option value="CUSTOM" <?= $isCustom ? 'selected' : '' ?>>+ Add Custom Category...</option>
                </select>
                
                <input type="text" name="category_custom" id="category_custom" value="<?= $isCustom ? esc($currentCat) : '' ?>" 
                    style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px; margin-top: 0.5rem; <?= $isCustom ? '' : 'display: none;' ?>" 
                    placeholder="Enter custom category name...">
                
                <input type="hidden" name="category" id="category_final" value="<?= esc($currentCat) ?>">
            </div>
        </div>

        <div style="margin-bottom: 1.5rem; border: 1px dashed var(--border); padding: 1rem; border-radius: 6px; background: rgba(255,255,255,0.02);">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-weight: bold;">Artwork Image</label>
            <p style="font-size: 0.85rem; color: #888; margin-bottom: 1rem;">Upload a file OR provide a URL below.</p>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Upload File</label>
                <input type="file" name="image_upload" id="img_upload" accept="image/*" style="width: 100%; color: #fff;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Or Image URL</label>
                <input type="url" name="image_path" value="<?= old('image_path', $artwork->image_path ?? '') ?>" <?= $isEdit ? '' : 'required' ?> style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;" id="url_input">
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

<script>
    // Hybrid Select Logic
    const catSelect = document.getElementById('category_select');
    const catCustom = document.getElementById('category_custom');
    const catFinal = document.getElementById('category_final');

    if (catSelect) {
        catSelect.addEventListener('change', function() {
            catCustom.style.display = (this.value === 'CUSTOM') ? 'block' : 'none';
            if (this.value === 'CUSTOM') catCustom.focus();
            updateFinal();
        });
        catCustom.addEventListener('input', updateFinal);
    }

    function updateFinal() {
        catFinal.value = (catSelect.value === 'CUSTOM') ? catCustom.value : catSelect.value;
    }

    // Image Requirement Logic
    const imgUpload = document.getElementById('img_upload');
    const urlInput = document.getElementById('url_input');
    if (imgUpload && urlInput && <?= $isEdit ? 'false' : 'true' ?>) {
        imgUpload.addEventListener('change', function() {
            if (this.files.length > 0) urlInput.removeAttribute('required');
            else urlInput.setAttribute('required', 'required');
        });
    }
</script>

<?= $this->endSection() ?>
