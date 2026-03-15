<?= $this->extend('layouts/default') ?>

<?= $this->section('styles') ?>
<style>
    section[id] {
        scroll-margin-top: 5rem;
    }

    .hover-effect {
        transition: transform 0.2s ease-in-out;
    }

    .hover-effect:hover {
        transform: translateY(-5px);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="py-5 bg-body-tertiary">
    <div class="container py-5 text-center">
        <h1 class="display-4 display-md-3 fw-bold mb-4">The Ultimate <br><span class="text-primary">Generative AI Studio</span></h1>
        <p class="lead mb-4 mx-auto" style="max-width: 800px;">
            Generate enterprise-grade videos, images, text, and reports in seconds.
            <span class="text-muted d-block mt-2 fs-5">Access industry-leading models via Gemini, OpenRouter, and Local LLMs.</span>
        </p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="<?= url_to('register') ?>" class="btn btn-primary btn-lg px-5 fw-bold rounded-pill shadow-sm" aria-label="Create your free account">Start Creating Free</a>
            <a href="<?= url_to('openrouter.public') ?>" class="btn btn-outline-secondary btn-lg px-5 rounded-pill" aria-label="Explore Free Models">Explore Free Models</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary mb-2 rounded-pill px-3 py-2">Core Platform</span>
            <h2 class="display-5 fw-bold">Built for Creators & Enterprises</h2>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Gemini (Enterprise) -->
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 shadow-sm bg-primary-subtle hover-effect">
                    <div class="card-body p-4 p-sm-5 text-center d-flex flex-column justify-content-center">
                        <div class="fs-1 text-primary mb-3"><i class="bi bi-stars"></i></div>
                        <h3 class="fs-4 fw-bold mb-3">Enterprise AI</h3>
                        <p class="text-body-secondary mb-0">Powered by Gemini. Generate high-fidelity videos, images, and strategic reports.</p>
                        <a href="<?= url_to('gemini.public') ?>" class="btn btn-link mt-3 fw-bold text-decoration-none">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- OpenRouter (Aggregator) -->
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 shadow-sm bg-body-tertiary hover-effect">
                    <div class="card-body p-4 p-sm-5 text-center d-flex flex-column justify-content-center">
                        <div class="fs-1 text-info mb-3"><i class="bi bi-diagram-3-fill"></i></div>
                        <h3 class="fs-4 fw-bold mb-3">Multi-Model Hub</h3>
                        <p class="text-body-secondary mb-0">Access 100+ top-tier models through OpenRouter. Includes high-speed free models.</p>
                        <a href="<?= url_to('openrouter.public') ?>" class="btn btn-link text-info mt-3 fw-bold text-decoration-none">Explore Models <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Ollama (Private) -->
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 shadow-sm bg-body-tertiary hover-effect">
                    <div class="card-body p-4 p-sm-5 text-center d-flex flex-column justify-content-center">
                        <div class="fs-1 text-success mb-3"><i class="bi bi-shield-lock-fill"></i></div>
                        <h3 class="fs-4 fw-bold mb-3">Private Workspace</h3>
                        <p class="text-body-secondary mb-0">Local inference via Ollama. Absolute data privacy, hybrid memory, and zero latency.</p>
                        <a href="<?= url_to('ollama.public') ?>" class="btn btn-link text-success mt-3 fw-bold text-decoration-none">Go Private <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Supporting Features Row -->
            <div class="col-12 mt-5">
                <div class="row g-4 justify-content-center">
                    <!-- Blockchain Insights -->
                    <div class="col-12 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-effect">
                            <div class="card-body p-4 d-flex align-items-center">
                                <div class="fs-2 text-primary me-4"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <h3 class="fs-5 fw-bold mb-1">Data Trust Layer</h3>
                                    <p class="text-body-secondary mb-0">Verify data integrity with our immutable Blockchain Analytics tools. <a href="<?= url_to('crypto.public') ?>" class="text-decoration-none fw-bold">View Crypto Query</a>.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pay-As-You-Go -->
                    <div class="col-12 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-effect">
                            <div class="card-body p-4 d-flex align-items-center">
                                <div class="fs-2 text-primary me-4"><i class="bi bi-wallet2"></i></div>
                                <div>
                                    <h3 class="fs-5 fw-bold mb-1">Pay-As-You-Go</h3>
                                    <p class="text-body-secondary mb-0">Top up via <span class="text-success fw-bold">M-Pesa</span> or <span class="text-danger fw-bold">Airtel</span>. No subscriptions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bespoke Development Section -->
<section id="enterprise" class="py-5 bg-body-tertiary">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <!-- Left: Value Prop -->
            <div class="col-lg-5">
                <span class="badge bg-info-subtle text-info-emphasis mb-2 rounded-pill px-3">Enterprise</span>
                <h2 class="display-6 fw-bold mb-4">Need a Tailored Solution?</h2>
                <p class="lead text-body-secondary mb-4">We build bespoke web applications to solve your unique business challenges using PHP framework and modern web standards.</p>

                <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-center"><i class="bi bi-check-circle-fill text-primary me-2"></i> Custom Web Apps</li>
                    <li class="mb-2 d-flex align-items-center"><i class="bi bi-check-circle-fill text-primary me-2"></i> API Integrations</li>
                    <li class="mb-2 d-flex align-items-center"><i class="bi bi-check-circle-fill text-primary me-2"></i> Performance Tuning</li>
                </ul>
                <a href="<?= url_to('contact.form') ?>" class="btn btn-primary rounded-pill fw-bold px-4">Book Consultation</a>
            </div>

            <!-- Right: Process Steps -->
            <div class="col-12 col-lg-7">
                <div class="row g-4">
                    <!-- Step 1 -->
                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm hover-effect">
                            <div class="card-body p-4">
                                <div class="mb-3 text-primary fw-bold fs-4" aria-hidden="true">01</div>
                                <h3 class="h5 fw-bold">Consultation</h3>
                                <p class="text-body-secondary mb-0">We listen to your vision and requirements in a free discovery meeting.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm hover-effect">
                            <div class="card-body p-4">
                                <div class="mb-3 text-primary fw-bold fs-4" aria-hidden="true">02</div>
                                <h3 class="h5 fw-bold">Planning</h3>
                                <p class="text-body-secondary mb-0">You get a detailed roadmap, timeline, and transparent pricing.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm hover-effect">
                            <div class="card-body p-4">
                                <div class="mb-3 text-primary fw-bold fs-4" aria-hidden="true">03</div>
                                <h3 class="h5 fw-bold">Build & Launch</h3>
                                <p class="text-body-secondary mb-0">We develop with clean code and deploy to your secure server.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section id="cta" class="py-5 mb-5 text-center">
    <div class="container">
        <div class="card bg-primary text-white rounded-4 overflow-hidden shadow-lg border-0 hover-effect" style="background: var(--hero-gradient) !important;">
            <div class="card-body p-4 p-md-5 position-relative z-1">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="display-5 fw-bold mb-3">Ready to Start?</h2>
                        <p class="lead mb-4 text-white-50">Join thousands of users building efficiently with AFRIKENKID.</p>
                        <a href="<?= url_to('register') ?>" class="btn btn-light btn-lg px-5 fw-bold rounded-pill" aria-label="Create your free Afrikenkid account">Create Free Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>