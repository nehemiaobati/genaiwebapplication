<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($workshop);
$action = $isEdit ? route_to('baraka.admin.workshops.update', $workshop->id) : route_to('baraka.admin.workshops.store');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2><?= $isEdit ? 'Edit Workshop' : 'Schedule Workshop' ?></h2>
    <a href="<?= route_to('baraka.admin.workshops') ?>" style="color: #888; text-decoration: none;">&larr; Back to Workshops</a>
</div>

<div class="card" style="max-width: 600px;">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="msg error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Workshop Title</label>
            <input type="text" name="title" value="<?= old('title', $workshop->title ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Event Date</label>
                <input type="date" name="event_date" value="<?= old('event_date', $workshop->event_date ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Time (e.g. 10:00 AM - 1:00 PM)</label>
                <input type="text" name="time" value="<?= old('time', $workshop->time ?? '') ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Fee (KES) - 0 for Free</label>
            <input type="number" step="0.01" name="fee" value="<?= old('fee', $workshop->fee ?? 0) ?>" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 1.5rem; border: 1px dashed var(--border); padding: 1rem; border-radius: 6px; background: rgba(255,255,255,0.02);">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-weight: bold;">Workshop Image</label>
            <p style="font-size: 0.85rem; color: #888; margin-bottom: 1rem;">Upload a file OR provide a URL below. If both are provided, the uploaded file will be used.</p>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Upload File</label>
                <input type="file" name="image_upload" accept="image/*" style="width: 100%; color: #fff;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: #aaa; font-size: 0.9rem;">Or Image URL</label>
                <input type="url" name="image_path" value="<?= old('image_path', $workshop->image_path ?? '') ?>" <?= $isEdit ? '' : 'required' ?> style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;" id="url_input_ws">
                <script>
                    document.querySelector('input[name="image_upload"]').addEventListener('change', function() {
                        if(this.files.length > 0) {
                            document.getElementById('url_input_ws').removeAttribute('required');
                        } else {
                            <?php if(!$isEdit): ?>document.getElementById('url_input_ws').setAttribute('required', 'required');<?php endif; ?>
                        }
                    });
                </script>
            </div>
        </div>

        <div style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Detailed Description</label>
            <textarea name="description" rows="5" required style="width: 100%; padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: #fff; border-radius: 6px;"><?= old('description', $workshop->description ?? '') ?></textarea>
        </div>

        <button type="submit" style="background: var(--accent-gold); color: #000; padding: 0.8rem 2rem; border-radius: 6px; border: none; font-weight: bold; cursor: pointer; width: 100%;">
            <?= $isEdit ? 'Update Workshop' : 'Schedule Workshop' ?>
        </button>
    </form>
</div>

<?= $this->endSection() ?>
