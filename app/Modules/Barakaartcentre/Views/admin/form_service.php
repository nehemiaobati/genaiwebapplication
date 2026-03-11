<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($service);
$action = $isEdit ? route_to('baraka.admin.services.update', $service->id) : route_to('baraka.admin.services.store');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2><?= $isEdit ? 'Edit Service' : 'Create Service' ?></h2>
    <a href="<?= route_to('baraka.admin.services') ?>" style="color: #888; text-decoration: none;">&larr; Back to Services</a>
</div>

<div class="card" style="max-width: 600px;">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="msg error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Service Title</label>
            <input type="text" name="title" value="<?= old('title', $service->title ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Type</label>
            <input type="text" name="type" list="type_list" value="<?= old('type', $service->type ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;" placeholder="Select or type a new service type...">
            <datalist id="type_list">
                <option value="Revenue">
                <option value="Community">
            </datalist>
        </div>

        <div style="margin-bottom: 1.5rem; border: 1px dashed var(--border); padding: 1rem; border-radius: 6px; background: rgba(255,255,255,0.02);">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-weight: bold;">Icon or Image</label>
            <p style="font-size: 0.85rem; color: #888; margin-bottom: 1rem;">Upload a file OR provide a URL below. If both are provided, the uploaded file will be used.</p>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Upload File</label>
                <input type="file" name="image_upload" accept="image/*" style="width: 100%; color: #fff;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Or Image URL</label>
                <input type="url" name="icon_or_image" value="<?= old('icon_or_image', $service->icon_or_image ?? '') ?>" style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
            </div>
        </div>

        <div style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Short Description</label>
            <textarea name="short_description" rows="3" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;"><?= old('short_description', $service->short_description ?? '') ?></textarea>
        </div>

        <button type="submit" style="background: var(--accent-gold); color: #000; padding: 0.8rem 2rem; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; width: 100%;">
            <?= $isEdit ? 'Update Service' : 'Save Service' ?>
        </button>
    </form>
</div>

<?= $this->endSection() ?>
