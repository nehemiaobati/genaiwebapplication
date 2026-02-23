<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card blueprint-card text-center p-5 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-envelope-x text-muted mb-4" style="font-size: 4rem;"></i>
                    <h2 class="fw-bold mb-3">You've Been Unsubscribed</h2>
                    <p class="text-muted mb-4">
                        We're sorry to see you go! You have been successfully removed from our marketing and promotional email lists.
                        You will no longer receive campaigns from us.
                        <br><br>
                        <small><em>Note: You will still receive essential account-related emails, such as password resets and security notices.</em></small>
                    </p>
                    <a href="<?= url_to('home') ?>" class="btn btn-primary px-4 py-2">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>