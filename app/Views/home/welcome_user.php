<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<style>
    .hover-effect {
        transition: transform 0.2s ease-in-out;
    }

    .hover-effect:hover {
        transform: translateY(-5px);
    }
</style>

<div class="container my-5">

    <?php if (isset($balance) && (float)$balance < 50): ?>
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <h5 class="alert-heading fw-bold">Your Balance is Low!</h5>
                <p class="mb-0">Don't let your creative flow be interrupted. Top up your account to continue using our services.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4 mb-5">
        <h1 class="fw-bold">Welcome back, <span class="text-primary"><?= esc($username ?? 'User') ?>!</span></h1>
        <p class="lead text-muted">Your digital toolkit is ready. What will you create today?</p>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">

        <!-- AI Studio (Gemini) -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 text-primary mb-3"><i class="bi bi-stars"></i></div>
                    <h4 class="fw-bold text-body-emphasis">Gemini AI Studio <span class="badge bg-success-subtle text-success-emphasis fs-6 ms-2">Enterprise</span></h4>
                    <p class="text-body-secondary">Your creative co-pilot for writing, <strong>image generation</strong>, <strong>video generation</strong>, and multimodal document analysis.</p>

                    <!-- Simplified Prompt Suggestion -->
                    <div class="p-3 bg-body-tertiary rounded mb-3 border border-dashed position-relative">
                        <small class="d-block text-body-secondary fw-medium mb-1">Try this prompt:</small>
                        <p class="mb-0 fst-italic me-4" id="promptText">"Write a short marketing email for a new coffee shop in Nairobi."</p>
                        <button class="btn btn-sm btn-link position-absolute top-50 end-0 translate-middle-y text-decoration-none"
                            onclick="copyPrompt()" title="Copy prompt">
                            <i class="bi bi-clipboard" id="copyIcon"></i>
                        </button>
                    </div>

                    <a href="<?= url_to('gemini.index') ?>" class="btn btn-primary mt-auto">Launch Studio <i class="bi bi-arrow-right-short"></i></a>
                </div>
            </div>
        </div>

        <!-- Multi-Model Hub (OpenRouter) -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 text-info mb-3"><i class="bi bi-diagram-3-fill"></i></div>
                    <h4 class="fw-bold text-body-emphasis">OpenRouter Hub <span class="badge bg-info-subtle text-info-emphasis fs-6 ms-2">100+ Models</span></h4>
                    <p class="text-body-secondary">Access over 100 frontier and open-source models through OpenRouter. Includes free models.</p>
                    <a href="<?= url_to('openrouter.index') ?>" class="btn btn-info text-white mt-auto">Explore Models <i class="bi bi-arrow-right-short"></i></a>
                </div>
            </div>
        </div>

        <!-- Private Workspace (Ollama) -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 text-success mb-3"><i class="bi bi-shield-lock-fill"></i></div>
                    <h4 class="fw-bold text-body-emphasis">Ollama Private LLM <span class="badge bg-success-subtle text-success-emphasis fs-6 ms-2">Private</span></h4>
                    <p class="text-body-secondary">Local inference with absolute data privacy and low latency.</p>
                    <a href="<?= url_to('ollama.index') ?>" class="btn btn-success mt-auto">Launch Ollama <i class="bi bi-arrow-right-short"></i></a>
                </div>
            </div>
        </div>

        <!-- Blockchain Explorer -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 text-primary mb-3"><i class="bi bi-shield-check"></i></div>
                    <h4 class="fw-bold text-body-emphasis">Blockchain Explorer</h4>
                    <p class="text-body-secondary">Get instant, real-time balance and transaction verification for public Bitcoin and Litecoin addresses.</p>
                    <a href="<?= url_to('crypto.index') ?>" class="btn btn-outline-primary mt-auto">Run Query <i class="bi bi-arrow-right-short"></i></a>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Balance -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-effect">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="fs-1 text-primary mb-3"><i class="bi bi-wallet2"></i></div>
                    <h4 class="fw-bold text-body-emphasis">Account & Balance</h4>
                    <div class="text-center my-3">
                        <p class="text-body-secondary text-uppercase small mb-1">Available Credits</p>
                        <div class="fs-1 fw-bold text-success lh-1">Ksh. <?= esc(number_format((float)($balance ?? 0), 2)) ?></div>
                    </div>
                    <div class="d-grid gap-2 mt-auto">
                        <a href="<?= url_to('payment.index') ?>" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Top Up Credits</a>
                        <a href="<?= url_to('account.index') ?>" class="btn btn-outline-secondary"><i class="bi bi-receipt me-1"></i> View Account History</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->section('scripts') ?>
<script>
    function copyPrompt() {
        const text = document.getElementById('promptText').innerText.replace(/^"|"$/g, '');
        const icon = document.getElementById('copyIcon');

        navigator.clipboard.writeText(text).then(() => {
            icon.classList.replace('bi-clipboard', 'bi-check-lg');
            icon.classList.add('text-success');
            setTimeout(() => {
                icon.classList.replace('bi-check-lg', 'bi-clipboard');
                icon.classList.remove('text-success');
            }, 2000);
        });
    }
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>