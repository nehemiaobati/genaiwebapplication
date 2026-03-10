<?= $this->extend('layouts/default') ?>

<?= $this->section('styles') ?>
<style>
    section[id] {
        scroll-margin-top: 5rem;
    }

    .hover-effect {
        transition: transform 0.22s ease-in-out;
    }

    .hover-effect:hover {
        transform: translateY(-5px);
    }

    .or-badge {
        background: var(--bs-primary);
        background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-border-subtle));
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<main class="flex-grow-1">

    <!-- Hero -->
    <section class="py-5 bg-body-tertiary">
        <div class="container py-5 text-center">
            <span class="badge or-badge text-white mb-3 rounded-pill px-3 py-2 fw-bold fs-6 shadow-sm"><i class="bi bi-stars"></i> Includes Free High-Speed Models. Zero Friction to Start.</span>
            <h1 class="display-3 fw-bold mb-4"><?= esc($heroTitle ?? 'Multi-Model AI, Unified.') ?></h1>
            <p class="lead mb-5 mx-auto text-muted font-monospace" style="max-width: 760px; font-size: 1.1rem;">
                <?= esc($heroSubtitle ?? 'Access the world\'s best open-source and proprietary AI models through one seamless interface. Intelligent conversations, fast responses, zero friction.') ?>
            </p>
            <div class="d-flex flex-row gap-3 justify-content-center">
                <a href="<?= url_to('register') ?>" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill shadow-sm">Start Prompting for Free</a>
                <a href="#capabilities" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">Explore</a>
            </div>
        </div>
    </section>

    <!-- Capabilities -->
    <section id="capabilities" class="py-5">
        <div class="container">
            <div class="row g-4">

                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-effect bg-body-tertiary">
                        <div class="card-body p-5">
                            <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-4" style="width:3rem;height:3rem;">
                                <i class="bi bi-chat-dots-fill fs-4"></i>
                            </span>
                            <h3 class="fw-bold mb-3">Intelligent Conversations</h3>
                            <p class="text-muted mb-0">Context-aware chat that remembers your history. The assistant learns your preferences and provides increasingly relevant answers over time.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-effect bg-body-tertiary">
                        <div class="card-body p-5">
                            <span class="d-inline-flex align-items-center justify-content-center bg-info-subtle text-info rounded-circle mb-4" style="width:3rem;height:3rem;">
                                <i class="bi bi-lightning-charge-fill fs-4"></i>
                            </span>
                            <h3 class="fw-bold mb-3">Real-Time Streaming</h3>
                            <p class="text-muted mb-0">Responses stream live, word by word. No waiting for long completions — get instant feedback as the model thinks through your query.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-effect bg-body-tertiary">
                        <div class="card-body p-5">
                            <span class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-4" style="width:3rem;height:3rem;">
                                <i class="bi bi-bookmarks-fill fs-4"></i>
                            </span>
                            <h3 class="fw-bold mb-3">Prompt Library</h3>
                            <p class="text-muted mb-0">Save your best prompts as reusable templates. Build a personal library of workflows that speed up your daily tasks.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-effect bg-body-tertiary">
                        <div class="card-body p-5">
                            <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-4" style="width:3rem;height:3rem;">
                                <i class="bi bi-shield-lock-fill fs-4"></i>
                            </span>
                            <h3 class="fw-bold mb-3">Private & Secure</h3>
                            <p class="text-muted mb-0">Your conversations are stored securely and associated only with your account. Full control to clear your memory at any time.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 mb-5">
        <div class="container">
            <div class="card rounded-4 border-0 shadow-lg overflow-hidden hover-effect bg-primary text-white">
                <div class="card-body p-5 text-center">
                    <h2 class="display-5 fw-bold mb-3">Ready to Explore?</h2>
                    <p class="lead mb-4 opacity-75">Join the platform and start a conversation with the world's best AI models today.</p>
                    <a href="<?= url_to('register') ?>" class="btn btn-light btn-lg px-5 fw-bold rounded-pill">Get Started Now</a>
                </div>
            </div>
        </div>
    </section>


</main>
<?= $this->endSection() ?>