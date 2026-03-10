<?= $this->extend('layouts/default') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/highlight/styles/atom-one-dark.min.css') ?>">
<style>
    /* 
    |--------------------------------------------------------------------------
    | OpenRouter AI Studio — Internal Styles
    |--------------------------------------------------------------------------
    */

    :root {
        --openrouter-header-height: 60px;
        --openrouter-sidebar-width: 350px;
        --openrouter-code-bg: #282c34;
        --openrouter-z-header: 1020;
        --openrouter-z-sidebar: 1050;
        --openrouter-z-overlay: 1040;
        --openrouter-timing: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* =========================================
       1. Global Layout Overrides
       ========================================= */
    #mainNavbar,
    .footer,
    .container.my-4 {
        display: none !important;
    }

    body {
        overflow: hidden;
        padding: 0 !important;
        background-color: var(--bs-body-bg);
    }

    @media (max-width: 991.98px) {
        body {
            overflow: auto;
            /* Allow scroll on mobile for keyboard */
        }
    }

    /* =========================================
       2. Main Layout Container
       ========================================= */
    .openrouter-view-container {
        position: fixed;
        inset: 0;
        height: 100vh;
        height: 100dvh;
        width: 100vw;
        display: flex;
        overflow: hidden;
        z-index: 1000;
        background-color: var(--bs-body-bg);
    }

    .openrouter-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        min-width: 0;
        overflow: hidden;
    }

    /* =========================================
       3. Header
       ========================================= */
    .openrouter-header {
        position: sticky;
        top: 0;
        z-index: var(--openrouter-z-header);
        background: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color);
        padding: 0.5rem 1.5rem;
        height: var(--openrouter-header-height);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* =========================================
       4. Sidebar
       ========================================= */
    .openrouter-sidebar {
        width: var(--openrouter-sidebar-width);
        border-left: 1px solid var(--bs-border-color);
        background: var(--bs-tertiary-bg);
        overflow-y: auto;
        height: 100%;
        padding: 1.5rem;
        transition: transform 0.3s ease, margin-right 0.3s ease;
    }

    .openrouter-sidebar.collapse:not(.show) {
        display: none;
    }

    @media (max-width: 991.98px) {
        .openrouter-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: var(--openrouter-z-sidebar);
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        }
    }

    /* =========================================
       5. Content Areas
       ========================================= */
    .openrouter-response-area {
        flex: 1;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding: 2rem;
        scroll-behavior: smooth;
        min-height: 0;
    }

    .openrouter-prompt-area {
        width: 100%;
        background: var(--bs-body-bg);
        border-top: 1px solid var(--bs-border-color);
        padding: 1rem 1.5rem calc(1rem + env(safe-area-inset-bottom));
        z-index: 10;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
    }

    /* =========================================
       6. Components
       ========================================= */

    /* Custom Toggles */
    .form-switch .form-check-input {
        transition: background-color var(--openrouter-timing), border-color var(--openrouter-timing), transform 0.2s ease-in-out;
    }

    .form-switch .form-check-input:active {
        transform: scale(0.9);
    }

    /* Custom Tabs */
    .nav-pills.openrouter-tabs .nav-link {
        transition: all var(--openrouter-timing);
        position: relative;
        overflow: hidden;
    }

    .nav-pills.openrouter-tabs .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background-color: var(--bs-primary);
        transition: width var(--openrouter-timing), left var(--openrouter-timing);
    }

    .nav-pills.openrouter-tabs .nav-link.active {
        background-color: transparent !important;
        color: var(--bs-primary) !important;
        font-weight: 600;
    }

    .nav-pills.openrouter-tabs .nav-link.active::after {
        width: 100%;
        left: 0;
    }

    /* Textarea */
    .prompt-textarea {
        resize: none;
        overflow-y: hidden;
        min-height: 40px;
        max-height: 120px;
        border-radius: 1.5rem;
        padding: 0.6rem 1rem;
        line-height: 1.5;
        transition: border-color 0.2s;
    }

    .prompt-textarea:focus {
        box-shadow: none;
        border-color: var(--bs-primary);
    }

    /* Model Cards */
    .model-card {
        cursor: pointer;
        transition: 0.2s;
        border: 2px solid transparent;
        background-color: var(--bs-body-bg);
    }

    .model-card:hover {
        border-color: var(--bs-primary);
        transform: translateY(-2px);
    }

    .model-card.active {
        border-color: var(--bs-primary);
        background-color: var(--bs-primary-bg-subtle);
    }

    /* File Chips */
    #upload-list-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        max-height: 100px;
        overflow-y: auto;
        margin-bottom: 0.5rem;
    }

    .file-chip {
        display: flex;
        align-items: center;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.85rem;
        max-width: 220px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .file-chip .file-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-right: 8px;
        max-width: 150px;
    }

    .file-chip .progress-ring {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        border: 2px solid var(--bs-secondary-bg);
        border-top: 2px solid var(--bs-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Code Blocks */
    pre {
        background: var(--openrouter-code-bg);
        color: #fff;
        padding: 1rem;
        border-radius: 5px;
        position: relative;
        margin-top: 1rem;
    }

    .copy-code-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        opacity: 0;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(5px);
        background: rgba(0, 0, 0, 0.2) !important;
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        z-index: 5;
    }

    pre:hover .copy-code-btn {
        opacity: 1;
    }

    .copy-code-btn:hover {
        background: rgba(0, 0, 0, 0.4) !important;
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-1px);
    }

    .copy-code-btn.copied {
        background: rgba(40, 167, 69, 0.8) !important;
        border-color: rgba(40, 167, 69, 1);
    }

    /* Thinking State Skeletons */
    .thinking-skeleton-pulse {
        animation: thinking-shimmer 2s infinite linear;
        background: linear-gradient(90deg,
                var(--bs-tertiary-bg) 0%,
                var(--bs-secondary-bg) 50%,
                var(--bs-tertiary-bg) 100%);
        background-size: 200% 100%;
        border-radius: 4px;
        height: 1.2em;
        margin-bottom: 0.5rem;
    }

    @keyframes thinking-shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    /* Memory Stream */
    .memory-item {
        font-size: 0.9rem;
        border-left: 3px solid transparent;
        transition: all 0.2s;
        cursor: default;
        background-color: var(--bs-body-bg);
    }

    .memory-item:hover {
        background-color: var(--bs-tertiary-bg);
    }

    .memory-item.active-context {
        border-left-color: var(--bs-warning);
        background-color: rgba(255, 193, 7, 0.1) !important;
        border-radius: 4px;
    }

    .memory-date-header {
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        color: var(--bs-secondary);
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        position: sticky;
        top: 0;
        background: var(--bs-body-bg);
        z-index: 5;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    .delete-memory-btn {
        opacity: 0;
        transition: opacity 0.2s;
    }

    .memory-item:hover .delete-memory-btn {
        opacity: 1;
    }

    /* Thinking Blocks */
    .thinking-block {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
        transition: all 0.2s;
        border: 1px solid var(--bs-border-color);
    }

    [data-bs-theme="light"] .thinking-block {
        background-color: var(--bs-tertiary-bg);
    }

    .thinking-block[open] {
        background-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="light"] .thinking-block[open] {
        background-color: var(--bs-secondary-bg);
    }

    /* Results Card */
    #results-card {
        overflow: visible;
        border-radius: var(--bs-border-radius);
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    #results-card .card-header {
        border-top-left-radius: calc(var(--bs-border-radius) - 1px);
        border-top-right-radius: calc(var(--bs-border-radius) - 1px);
    }

    .polling-pulse {
        animation: pulse-border 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse-border {

        0%,
        100% {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.5);
        }

        50% {
            border-color: var(--bs-info);
            box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="openrouter-view-container">

    <!-- Main Content Area -->
    <main class="openrouter-main">
        <!-- Header -->
        <header class="openrouter-header">
            <div class="d-flex align-items-center gap-3">
                <a href="<?= url_to('home') ?>" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                    <i class="bi bi-cpu text-primary fs-4"></i>
                    <span class="fw-bold fs-5">OpenRouter Studio</span>
                </a>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm theme-toggle" id="themeToggleBtn" title="Toggle Theme">
                    <i class="bi bi-circle-half"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="sidebarToggleBtn" data-bs-toggle="collapse" data-bs-target="#orSidebar">
                    <i class="bi bi-layout-sidebar-reverse"></i> Settings
                </button>
            </div>
        </header>

        <!-- Chat / Response Area -->
        <div class="openrouter-response-area" id="response-area-wrapper">
            <div id="flash-messages-container"><?= view('App\Views\partials\flash_messages') ?></div>

            <!-- Results or Empty State -->
            <?php if ($result = session()->getFlashdata('result')): ?>
                <div class="card shadow-sm border-primary" id="results-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="bi bi-stars me-2"></i>Studio Output</span>
                        <!-- Toolbar -->
                        <div class="d-flex gap-2">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-light copy-btn" id="copyFullResponseBtn" data-format="text">
                                    <i class="bi bi-clipboard me-1"></i> Copy
                                </button>
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                    <span class="visually-hidden">Toggle</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-clipboard me-1"></i> Copy As</h6>
                                    </li>
                                    <li><a class="dropdown-item copy-format-action" href="#" data-format="text">Plain Text</a></li>
                                    <li><a class="dropdown-item copy-format-action" href="#" data-format="markdown">Markdown</a></li>
                                    <li><a class="dropdown-item copy-format-action" href="#" data-format="html">HTML</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <h6 class="dropdown-header"><i class="bi bi-download me-1"></i> Export As</h6>
                                    </li>
                                    <li><a class="dropdown-item download-action" href="#" data-format="pdf">PDF Document</a></li>
                                    <li><a class="dropdown-item download-action" href="#" data-format="docx">Word Document</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Body -->
                    <div class="card-body response-content" id="ai-response-body"><?= $result ?></div>
                    <textarea id="raw-response" name="raw_response" class="d-none"><?= esc(session()->getFlashdata('raw_result')) ?></textarea>

                    <div class="card-footer bg-body border-top text-center py-2">
                        <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-1">
                            <i class="bi bi-cpu text-body-tertiary opacity-50"></i>
                            <span class="text-body-tertiary small opacity-50" style="font-size: 0.75rem;">Generated by OpenRouter · <?= esc($currentModel) ?></span>
                        </div>
                        <div class="text-center">
                            <small class="text-muted opacity-75" style="font-size: 0.65rem;">AI may make mistakes. Verify important information.</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center text-muted mt-5 pt-5" id="empty-state">
                    <div class="display-1 text-body-tertiary mb-3"><i class="bi bi-cpu"></i></div>
                    <h5>Start Creating</h5>
                    <p>Access hundreds of models via OpenRouter. Enter your prompt below.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Prompt Input Area -->
        <div class="openrouter-prompt-area">
            <form id="openrouterForm" action="<?= url_to('openrouter.generate') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Mode Tabs -->
                <ul class="nav nav-pills openrouter-tabs nav-sm mb-2" id="generationTabs" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active py-2 px-3" data-bs-toggle="tab" data-type="text">
                            <i class="bi bi-chat-text me-2"></i>Text
                        </button>
                    </li>
                </ul>

                <div id="upload-list-wrapper"></div>
                <!-- Hidden inputs for uploaded media IDs will be injected here during JS multi-upload -->
                <div id="uploaded-files-container"></div>

                <div class="d-flex align-items-end gap-2 bg-body-tertiary p-2 rounded-4 border">
                    <!-- Attachment Button -->
                    <div id="mediaUploadArea" class="d-inline-block p-0 border-0 bg-transparent mb-1">
                        <input type="file" id="media-input-trigger" multiple class="d-none">
                        <label for="media-input-trigger" class="btn btn-link text-secondary p-1" title="Attach context files">
                            <i class="bi bi-paperclip fs-4"></i>
                        </label>
                    </div>

                    <!-- Main Text Input -->
                    <div class="flex-grow-1">
                        <textarea id="prompt" name="prompt" class="form-control border-0 bg-transparent prompt-textarea shadow-none" placeholder="Message OpenRouter..." rows="1" style="height: auto;"><?= old('prompt') ?></textarea>
                    </div>

                    <!-- Submit & Save -->
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <button type="button" class="btn btn-link text-secondary p-1" data-bs-toggle="modal" data-bs-target="#savePromptModal" title="Save Prompt">
                            <i class="bi bi-bookmark-plus fs-5"></i>
                        </button>
                        <button type="submit" id="generateBtn" class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Generate">
                            <i class="bi bi-arrow-up text-white fs-5"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Right Sidebar (Settings & History) -->
    <aside class="openrouter-sidebar collapse collapse-horizontal show" id="openrouterSidebar">
        <!-- Header with Tabs -->
        <div class="d-flex align-items-center mb-3">
            <ul class="nav nav-pills nav-fill flex-grow-1 p-1 bg-body rounded or-tabs" id="sidebarTabs" role="tablist" style="font-size: 0.9rem;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-1" id="config-tab" data-bs-toggle="tab" data-bs-target="#config-pane" type="button" role="tab"><i class="bi bi-sliders me-1"></i> Config</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1" id="memory-tab" data-bs-toggle="tab" data-bs-target="#memory-pane" type="button" role="tab"><i class="bi bi-activity me-1"></i> History</button>
                </li>
            </ul>
            <button class="btn-close ms-2 d-lg-none" data-bs-toggle="collapse" data-bs-target="#orSidebar"></button>
        </div>

        <div class="tab-content h-100 overflow-hidden d-flex flex-column">
            <!-- Configuration Pane -->
            <div class="tab-pane fade show active h-100 overflow-auto custom-scrollbar" id="config-pane" role="tabpanel">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input setting-toggle" type="checkbox" id="assistantMode" data-key="assistant_mode_enabled" <?= $assistant_mode_enabled ? 'checked' : '' ?>>
                    <label class="form-check-label fw-medium" for="assistantMode">Conversational Memory</label>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input setting-toggle" type="checkbox" id="streamOutput" data-key="stream_output_enabled" <?= ($stream_output_enabled ?? false) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-medium" for="streamOutput">Stream Responses</label>
                </div>

                <hr>

                <h6 class="border-bottom pb-2 mb-3 mt-4 text-secondary"><i class="bi bi-cpu me-2"></i>Model Selection</h6>
                <div class="mb-3 form-floating">
                    <select class="form-select setting-toggle" id="openrouter_model" name="openrouter_model" data-key="openrouter_model">
                        <?php foreach ($recommendedModels as $id => $name): ?>
                            <option value="<?= esc($id) ?>" <?= $id === $currentModel ? 'selected' : '' ?>>
                                <?= esc($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="openrouter_model">Active AI Model</label>
                </div>

                <hr>

                <!-- Saved Prompts -->
                <label class="form-label small fw-bold text-uppercase text-muted">Saved Prompts</label>
                <div id="saved-prompts-wrapper">
                    <div class="input-group mb-3 <?= empty($prompts) ? 'd-none' : '' ?>" id="savedPromptsContainer">
                        <select class="form-select form-select-sm" id="savedPrompts">
                            <option value="" disabled selected>Select...</option>
                            <?php if (!empty($prompts)): ?>
                                <?php foreach ($prompts as $p): ?>
                                    <option value="<?= esc($p->prompt_text, 'attr') ?>" data-id="<?= $p->id ?>"><?= esc($p->title) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="usePromptBtn">Load</button>
                        <button class="btn btn-outline-danger btn-sm" type="button" id="deletePromptBtn" disabled><i class="bi bi-trash"></i></button>
                    </div>
                </div>

                <hr>

                <form action="<?= url_to('openrouter.memory.clear') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm"><i class="bi bi-trash me-2"></i> Clear History</button>
                </form>

                <div class="mt-4 pt-4 text-center">
                    <small class="text-muted">AFRIKENKID OpenRouter Studio</small>
                </div>
            </div>

            <!-- Memory Stream Pane -->
            <div class="tab-pane fade h-100 overflow-auto custom-scrollbar" id="memory-pane" role="tabpanel">
                <div id="memory-loading" class="text-center py-4 d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
                <div id="history-list" class="d-flex flex-column pb-5">
                    <div class="text-center text-muted small mt-5">
                        <i class="bi bi-clock-history fs-4 mb-2 d-block"></i> Select History to load.
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Hidden Support Forms -->
<form id="downloadForm" method="post" action="<?= url_to('openrouter.download_document') ?>" target="_blank" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="raw_response" id="dl_raw">
    <input type="hidden" name="format" id="dl_format">
</form>

<!-- Save Prompt Modal -->
<div class="modal fade" id="savePromptModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url_to('openrouter.prompts.add') ?>" method="post" class="modal-content" id="savePromptForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Save Prompt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" id="savePromptTitle" name="title" class="form-control" placeholder="Prompt Title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="prompt_text" id="modalPromptText" class="form-control" rows="5" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </form>
    </div>
</div>

<!-- Global Toasts -->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3 openrouter-toast-container">
    <div id="liveToast" class="toast text-bg-dark" role="alert">
        <div class="toast-body"></div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/highlight/highlight.js') ?>"></script>
<script src="<?= base_url('assets/tinymce/tinymce.min.js') ?>"></script>
<script src="<?= base_url('assets/marked/marked.min.js') ?>"></script>
<script>
    /**
     * ==========================================
     * OpenRouter AI Studio - Frontend Application
     * ==========================================
     */

    // Configuration Constants
    const APP_CONFIG = {
        csrfName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        limits: {
            maxFileSize: <?= $maxFileSize ?>,
            maxFiles: <?= $maxFiles ?>,
            supportedTypes: <?= $supportedMimeTypes ?>
        },
        endpoints: {
            upload: '<?= url_to('openrouter.upload_media') ?>',
            deleteMedia: '<?= url_to('openrouter.delete_media') ?>',
            settings: '<?= url_to('openrouter.update_setting') ?>',
            deletePromptBase: '<?= url_to('openrouter.prompts.delete', 0) ?>'.slice(0, -1),
            stream: '<?= url_to('openrouter.stream') ?>',
            generate: '<?= url_to('openrouter.generate') ?>',
            history: '<?= url_to('openrouter.history.fetch') ?>',
            deleteHistory: '<?= url_to('openrouter.history.delete') ?>',
            clearHistory: '<?= url_to('openrouter.memory.clear') ?>',
            updateSetting: '<?= url_to('openrouter.update_setting') ?>'
        },
        localization: {
            currency: '<?= $currency_symbol ?? 'KSH' ?>'
        }
    };

    /**
     * ViewTemplates
     */
    const _ViewTemplates = {
        resultCard: (title, toolbar, bodyContent, processingClass) => `
            <div class="card blueprint-card shadow-sm border-primary ${processingClass}" id="results-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-stars me-2"></i>${title}</span>
                    ${toolbar}
                </div>
                ${bodyContent}
                <div class="card-footer bg-body border-top text-center py-2">
                    <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-stars text-body-tertiary opacity-50"></i>
                        <span class="text-body-tertiary small opacity-50" style="font-size: 0.75rem;">Generated by OpenRouter AI</span>
                    </div>
                </div>
            </div>`,

        textBody: `
            <div class="card-body response-content" id="ai-response-body"></div>
            <textarea id="raw-response" class="d-none"></textarea>`,

        toolbar: `
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-light copy-btn" id="copyFullResponseBtn" data-format="text"><i class="bi bi-clipboard me-1"></i> Copy</button>
                    <button type="button" class="btn btn-sm btn-light dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"><span class="visually-hidden">Toggle</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><i class="bi bi-clipboard me-1"></i> Copy As</h6></li>
                        <li><a class="dropdown-item copy-format-action" href="#" data-format="text">Plain Text</a></li>
                        <li><a class="dropdown-item copy-format-action" href="#" data-format="markdown">Markdown</a></li>
                        <li><a class="dropdown-item copy-format-action" href="#" data-format="html">HTML</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header"><i class="bi bi-download me-1"></i> Export As</h6></li>
                        <li><a class="dropdown-item download-action" href="#" data-format="pdf">PDF Document</a></li>
                        <li><a class="dropdown-item download-action" href="#" data-format="docx">Word Document</a></li>
                    </ul>
                </div>
            </div>`,

        fileChip: (file, id) => `
            <div class="file-chip" id="chip-${id}" title="${file.name}">
                <div class="progress-ring"></div>
                <span class="file-name text-truncate">${file.name}</span>
                <button type="button" class="btn btn-link p-0 text-secondary remove-btn disabled" data-id="${id}">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        `,
        flashMessage: (msg, type) => `
            <div class="alert alert-${type} alert-dismissible fade show border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    <div>${msg}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `,

        textSkeleton: `
            <div class="p-3 animate__animated animate__fadeIn loading-skeleton">
                <div class="d-flex align-items-center text-primary mb-3">
                    <div class="spinner-grow spinner-grow-sm me-2" role="status"></div>
                    <span class="fst-italic fw-medium">Synthesizing response...</span>
                </div>
                <div class="placeholder-glow">
                    <div class="thinking-skeleton-pulse w-75"></div>
                    <div class="thinking-skeleton-pulse w-50"></div>
                    <div class="thinking-skeleton-pulse w-100"></div>
                </div>
            </div>`,

        emptyHistory: `<div class="text-center text-muted small mt-5">No interaction history yet.</div>`,
        errorHistory: `<div class="text-center text-danger mt-4"><small>Failed to load history.</small></div>`,
        thinkingBlock: (content) => `
            <details class="thinking-block mb-3 p-2 rounded" open>
                <summary class="cursor-pointer text-muted fw-bold small">
                    Thinking Process
                </summary>
                <div class="mt-2 text-secondary small fst-italic thinking-content">
                    ${content}
                </div>
            </details>`
    };

    /**
     * ViewRenderer
     */
    class ViewRenderer {
        static escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
        static renderResultCard(title = 'Output', processing = false) {
            const processingClass = processing ? 'polling-pulse' : '';
            return _ViewTemplates.resultCard(title, _ViewTemplates.toolbar, _ViewTemplates.textBody, processingClass);
        }
        static renderFileChip(file, id) {
            return _ViewTemplates.fileChip(file, id);
        }
        static renderHistoryHeader(date) {
            const div = document.createElement('div');
            div.className = 'memory-date-header mt-3 mb-2 px-2 py-1 rounded shadow-sm';
            div.textContent = date;
            return div;
        }
        static renderHistoryItem(item) {
            const el = document.createElement('div');
            el.className = 'memory-item p-3 mb-2 rounded border shadow-sm position-relative cursor-pointer';
            el.dataset.id = item.unique_id || item.id;
            let contextBadges = '';
            if (item.context_files && item.context_files.length > 0) {
                contextBadges = `<div class="mb-2 mt-1 d-flex flex-wrap gap-1">` + item.context_files.map(file =>
                    `<span class="badge bg-secondary-subtle border text-secondary text-truncate d-inline-block" style="font-size: 0.7em; max-width: 100%;">
                        <i class="bi bi-paperclip"></i> <span title="${this.escapeHtml(file)}">${this.escapeHtml(file)}</span>
                     </span>`
                ).join('') + `</div>`;
            }
            el.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate fw-medium" style="max-width: 85%; font-size: 0.85rem;" title="${this.escapeHtml(item.user_input || item.user_input_raw)}">
                        ${this.escapeHtml(item.user_input || item.user_input_raw)}
                    </div>
                    <button class="btn btn-link text-danger p-0 delete-memory-btn" style="font-size: 0.8rem;" data-id="${item.unique_id || item.id}" title="Forget">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                ${contextBadges}
                <div class="text-muted text-truncate small" style="opacity: 0.7;">
                    ${this.escapeHtml(item.ai_output || item.ai_output_raw)}
                </div>`;
            return el;
        }
        static renderLoadMoreButton() {
            const div = document.createElement('div');
            div.className = 'text-center py-3';
            div.innerHTML = `<button class="btn btn-sm btn-outline-primary load-more-btn">Load More <i class="bi bi-arrow-down-circle ms-1"></i></button>`;
            return div;
        }
        static renderFlashMessage(msg, type = 'danger') {
            return _ViewTemplates.flashMessage(msg, type);
        }
        static renderTextSkeleton() {
            return _ViewTemplates.textSkeleton;
        }
        static renderEmptyHistory() {
            return _ViewTemplates.emptyHistory;
        }
        static renderErrorHistory() {
            return _ViewTemplates.errorHistory;
        }
    }

    /**
     * RequestQueue
     */
    class RequestQueue {
        constructor() {
            this.queue = [];
            this.processing = false;
        }
        async enqueue(fn) {
            return new Promise((resolve, reject) => {
                this.queue.push({
                    fn,
                    resolve,
                    reject
                });
                if (!this.processing) this.process();
            });
        }
        async process() {
            if (this.queue.length === 0) {
                this.processing = false;
                return;
            }
            this.processing = true;
            const {
                fn,
                resolve,
                reject
            } = this.queue.shift();
            try {
                resolve(await fn());
            } catch (e) {
                reject(e);
            }
            this.process();
        }
    }

    /**
     * OpenRouterApp
     */
    class OpenRouterApp {
        constructor() {
            this.csrfHash = APP_CONFIG.csrfHash;
            this.requestQueue = new RequestQueue();
            this.ui = new UIManager(this);
            this.uploader = new MediaUploader(this);
            this.prompts = new PromptManager(this);
            this.history = new HistoryManager(this);
            this.streamer = new StreamHandler(this);
            this.interaction = new InteractionHandler(this);
        }
        init() {
            if (typeof marked !== 'undefined') marked.use({
                breaks: true,
                gfm: true
            });
            this.ui.init();
            this.uploader.init();
            this.prompts.init();
            this.history.init();
            this.interaction.init();
            window.openRouterApp = this;
        }
        refreshCsrf(hash) {
            if (!hash) return;
            this.csrfHash = hash;
            document.querySelectorAll(`input[name="${APP_CONFIG.csrfName}"]`).forEach(el => el.value = hash);
        }
        async sendAjax(url, data = null) {
            return this.requestQueue.enqueue(async () => {
                const formData = data instanceof FormData ? data : new FormData();
                if (!formData.has(APP_CONFIG.csrfName)) formData.append(APP_CONFIG.csrfName, this.csrfHash);
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    return await this._handleAjaxResponse(res);
                } catch (e) {
                    console.error("AJAX Failure", e);
                    if (e.message.indexOf('HTTP Error') === 0 || e.message === 'Failed to fetch') this.ui.showToast('Communication error.');
                    throw e;
                }
            });
        }
        async _handleAjaxResponse(res) {
            let json = null;
            try {
                json = await res.json();
            } catch (e) {}
            if (json) {
                const token = json.csrf_token || json.token || res.headers.get('X-CSRF-TOKEN');
                if (token) this.refreshCsrf(token);
            }
            if (!res.ok) {
                if (res.status === 403) {
                    if (json?.redirect) {
                        window.location.href = json.redirect;
                        throw new Error('Redirecting...');
                    }
                    this.ui.showToast('Session updated or expired. Reloading...', 'warning');
                    setTimeout(() => window.location.reload(), 2000);
                    throw new Error('Session mismatch. Reloading page...');
                }
                const errorMsg = json?.message || json?.error || `HTTP Error: ${res.status}`;
                throw new Error(errorMsg);
            }
            if (!json) throw new Error('Empty response from server');
            if (json.status === 'error') throw new Error(json.message || 'Unknown error');
            return json;
        }
    }

    /**
     * UIManager
     */
    class UIManager {
        constructor(app) {
            this.app = app;
            this.els = {
                generateBtn: document.getElementById('generateBtn'),
                sidebar: document.getElementById('openrouterSidebar'),
                responseArea: document.getElementById('response-area-wrapper'),
                toast: document.getElementById('liveToast'),
                flashContainer: document.getElementById('flash-messages-container'),
                prompt: document.getElementById('prompt'),
                form: document.getElementById('openrouterForm'),
                streamCheck: document.getElementById('streamOutput')
            };
        }
        init() {
            this.setupResponsiveSidebar();
            this.setupThemeToggle();
            this.initTinyMCE();
            this.enableCodeFeatures();
            this.setupDownloads();
            this.setupClearHistoryConfirm();
            this.setupSettingsToggles();
        }
        setupResponsiveSidebar() {
            if (window.innerWidth < 992 && this.els.sidebar?.classList.contains('show')) this.els.sidebar.classList.remove('show');
            document.getElementById('sidebarToggleBtn')?.addEventListener('click', () => {
                const offcanvas = bootstrap.Collapse.getOrCreateInstance(this.els.sidebar);
                offcanvas.toggle();
            });
        }
        setupThemeToggle() {
            document.getElementById('themeToggleBtn')?.addEventListener('click', () => {
                const html = document.documentElement;
                const current = html.getAttribute('data-bs-theme') || 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-bs-theme', next);
            });
        }
        initTinyMCE() {
            if (typeof tinymce === 'undefined') return;
            tinymce.init({
                selector: '#prompt',
                placeholder: 'Message OpenRouter...',
                menubar: false,
                statusbar: false,
                toolbar: false,
                license_key: 'gpl',
                plugins: 'autoresize',
                autoresize_bottom_margin: 0,
                min_height: 40,
                max_height: 120,
                highlight_on_focus: false,
                content_style: 'body { outline: none !important; }',
                setup: (editor) => {
                    editor.on('keydown', (e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            if (editor.getContent({
                                    format: 'text'
                                }).trim()) {
                                editor.save();
                                this.els.form.requestSubmit();
                            }
                        }
                    });
                }
            });
        }
        showToast(msg) {
            // Toasts are for system/connectivity errors only (Gemini Parity)
            if (!this.els.toast) return;
            this.els.toast.querySelector('.toast-body').textContent = msg;
            new bootstrap.Toast(this.els.toast).show();
        }
        showStatus(msg, type = 'success') {
            if (this.els.flashContainer) {
                this.els.flashContainer.innerHTML = ViewRenderer.renderFlashMessage(msg, type);
                if (type === 'success') setTimeout(() => {
                    const alert = this.els.flashContainer.querySelector('.alert');
                    if (alert) bootstrap.Alert.getOrCreateInstance(alert).close();
                }, 5000);
            }
        }
        setError(msg) {
            if (this.els.flashContainer) this.els.flashContainer.innerHTML = ViewRenderer.renderFlashMessage(msg);
            document.getElementById('ai-response-body')?.querySelector('.loading-skeleton')?.remove();
            document.querySelectorAll('.memory-item[data-id^="pending-"]').forEach(el => el.remove());
        }
        setLoading(isLoading) {
            const btn = this.els.generateBtn;
            const card = document.getElementById('results-card');
            if (!btn) return;
            if (isLoading) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm text-white"></span>';
                if (card) card.classList.add('polling-pulse');
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-up text-white fs-5"></i>';
                if (card) card.classList.remove('polling-pulse');
            }
        }
        enableCodeFeatures() {
            if (typeof hljs !== 'undefined') hljs.highlightAll();
            document.querySelectorAll('pre code').forEach((block) => {
                if (block.parentElement.querySelector('.copy-code-btn')) return;
                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-dark copy-code-btn';
                btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                btn.onclick = (e) => {
                    e.preventDefault();
                    navigator.clipboard.writeText(block.innerText).then(() => {
                        btn.classList.add('copied');
                        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                        setTimeout(() => {
                            btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                            btn.classList.remove('copied');
                        }, 2000);
                    });
                };
                block.parentElement.appendChild(btn);
            });
        }
        setupDownloads() {
            document.querySelectorAll('.download-action').forEach(btn => {
                btn.onclick = (e) => {
                    e.preventDefault();
                    document.getElementById('dl_raw').value = document.getElementById('raw-response').value;
                    document.getElementById('dl_format').value = e.target.dataset.format;
                    document.getElementById('downloadForm').submit();
                };
            });
            const mainCopyBtn = document.getElementById('copyFullResponseBtn');
            if (mainCopyBtn) {
                mainCopyBtn.onclick = () => this.copyContent('text', mainCopyBtn);
                document.querySelectorAll('.copy-format-action').forEach(btn => {
                    btn.onclick = (e) => {
                        e.preventDefault();
                        this.copyContent(e.target.dataset.format, mainCopyBtn);
                    };
                });
            }
        }
        copyContent(format, btn) {
            const raw = document.getElementById('raw-response');
            const body = document.getElementById('ai-response-body');
            if (!raw || !body) return;
            let content = format === 'markdown' ? raw.value : (format === 'html' ? body.innerHTML : body.innerText);
            if (!content.trim()) return this.showStatus('Nothing to copy.', 'warning');
            navigator.clipboard.writeText(content).then(() => {
                this.showStatus('Copied!', 'success');
                if (btn) {
                    const original = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied';
                    setTimeout(() => btn.innerHTML = original, 2000);
                }
            });
        }
        setupSettingsToggles() {
            // Consolidated handler for all settings (toggles and select)
            const updateSetting = async (key, value) => {
                const fd = new FormData();
                fd.append('setting_key', key);

                // If it's the model selector, send as model_id. Otherwise, send as 'true'/'false' string for boolean match.
                if (key === 'openrouter_model') {
                    fd.append('model_id', value);
                } else {
                    fd.append('enabled', value ? 'true' : 'false');
                }

                try {
                    const res = await this.app.sendAjax(APP_CONFIG.endpoints.updateSetting, fd);
                    // Silent success to match Gemini UI/UX
                } catch (e) {
                    console.error("Config save error:", e);
                    this.showStatus('Config error.', 'danger');
                }
            };

            // Boolean Toggles
            document.querySelectorAll('.setting-toggle[type="checkbox"]').forEach(t => {
                t.addEventListener('change', (e) => updateSetting(e.target.dataset.key, e.target.checked));
            });

            // Model Selector Dropdown
            document.getElementById('openrouter_model')?.addEventListener('change', (e) => {
                updateSetting('openrouter_model', e.target.value);
            });
        }
        ensureResultCard() {
            if (document.getElementById('results-card') && document.getElementById('ai-response-body')) return;
            document.getElementById('results-card')?.remove();
            document.getElementById('empty-state')?.remove();
            this.els.responseArea.insertAdjacentHTML('beforeend', ViewRenderer.renderResultCard('OpenRouter Output'));
            this.setupDownloads();
        }
        scrollToBottom() {
            setTimeout(() => document.getElementById('results-card')?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            }), 100);
        }
        showServerFlash(html) {
            if (this.els.flashContainer) this.els.flashContainer.innerHTML = html;
        }
        setupClearHistoryConfirm() {
            document.getElementById('clearHistoryForm')?.addEventListener('submit', (e) => {
                if (!confirm('Clear all history?')) e.preventDefault();
            });
        }
    }

    /**
     * InteractionHandler
     */
    class InteractionHandler {
        constructor(app) {
            this.app = app;
            this.isSubmitting = false;
        }
        init() {
            this.app.ui.els.form?.addEventListener('submit', e => this.handleSubmit(e));
        }
        async handleSubmit(e) {
            e.preventDefault();
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            const prompt = this.app.ui.els.prompt.value.trim();
            if (!prompt) {
                this.isSubmitting = false;
                return this.app.ui.showToast('Please enter a prompt.');
            }

            this.currentContextFiles = Array.from(document.querySelectorAll('.file-chip .file-name')).map(el => el.textContent);
            this.app.ui.setLoading(true);
            const fd = new FormData(this.app.ui.els.form);
            const modelSelector = document.getElementById('openrouter_model');
            if (modelSelector) {
                fd.append('model', modelSelector.value);
            }

            // Optimistic UI
            this.app.ui.ensureResultCard();
            const bodyEl = document.getElementById('ai-response-body');
            if (bodyEl) bodyEl.innerHTML = ViewRenderer.renderTextSkeleton();
            this.app.history.addItem({
                id: 'pending-' + Date.now(),
                timestamp: new Date().toISOString(),
                user_input: prompt,
                context_files: this.currentContextFiles
            }, '');
            this.app.ui.scrollToBottom();

            try {
                if (this.app.ui.els.streamCheck?.checked) await this.app.streamer.start(fd, this.currentContextFiles);
                else await this.generateText(fd);
            } catch (e) {} finally {
                this.app.ui.setLoading(false);
                this.isSubmitting = false;
            }
        }
        async generateText(fd) {
            try {
                const d = await this.app.sendAjax(APP_CONFIG.endpoints.generate, fd);
                if (d.status === 'success') {
                    // Result already contains formatted thinking block from the controller
                    document.getElementById('ai-response-body').innerHTML = d.result;
                    document.getElementById('raw-response').value = (d.thought ? `<thought>\n${d.thought}\n</thought>\n\n` : '') + d.raw_result;
                    this.app.ui.enableCodeFeatures();
                    this.app.ui.scrollToBottom();
                    if (d.flash_html) this.app.ui.showServerFlash(d.flash_html);
                    if (d.new_interaction_id) {
                        this.app.history.addItem({
                            id: d.new_interaction_id,
                            timestamp: d.timestamp,
                            user_input: d.user_input,
                            context_files: this.currentContextFiles
                        }, (d.thought ? `<thought>\n${d.thought}\n</thought>\n\n` : '') + d.raw_result);
                        
                        // Switch to history tab and highlight (Gemini Parity)
                        this.app.history.highlightContext([d.new_interaction_id]);
                    }
                } else this.app.ui.setError(d.message || 'Generation failed.');
            } catch (e) {
                this.app.ui.setError(e.message || 'Error occurred.');
            }
            this.app.uploader.clear();
        }
    }

    /**
     * StreamHandler
     */
    class StreamHandler {
        constructor(app) {
            this.app = app;
            this.buffer = '';
            this.firstChunk = true;
            this.thoughtAccum = '';
            this.thoughtEl = null;
        }
        async start(formData, contextFiles = []) {
            this.contextFiles = contextFiles;
            this.app.ui.ensureResultCard();
            const els = {
                body: document.getElementById('ai-response-body'),
                raw: document.getElementById('raw-response')
            };
            els.raw.value = '';
            this.buffer = '';
            this.firstChunk = true;
            this.thoughtAccum = '';
            this.thoughtEl = null;
            try {
                if (!formData.has(APP_CONFIG.csrfName)) formData.append(APP_CONFIG.csrfName, this.app.csrfHash);
                const response = await fetch(APP_CONFIG.endpoints.stream, {
                    method: 'POST',
                    body: formData
                });
                if (response.status === 403) {
                    this.app.ui.showToast('Session updated or expired. Reloading...', 'warning');
                    setTimeout(() => window.location.reload(), 2000);
                    return;
                }
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let accum = '';
                while (true) {
                    const {
                        value,
                        done
                    } = await reader.read();
                    if (done) break;
                    this.buffer += decoder.decode(value, {
                        stream: true
                    });
                    const lines = this.buffer.split('\n');
                    this.buffer = lines.pop();
                    accum = this.processLines(lines, accum, els);
                }
                if (this.buffer.trim()) accum = this.processLines([this.buffer], accum, els);
                this.app.ui.enableCodeFeatures();
            } catch (e) {
                this.app.ui.setError('Stream Connection Lost.');
            }
            this.app.uploader.clear();
        }
        processLines(lines, accum, els) {
            lines.forEach(line => {
                if (line === 'event: close') return;
                if (!line.trim() || !line.startsWith('data: ')) return;

                try {
                    const jsonStr = line.substring(6).trim();
                    if (jsonStr === '[DONE]') return;

                    const d = JSON.parse(jsonStr);

                    if (this.firstChunk && (d.text || d.thought || d.error)) {
                        els.body.querySelector('.loading-skeleton')?.remove();
                        this.firstChunk = false;
                    }

                    // 1. Thinking Blocks
                    if (d.thought) {
                        this.thoughtAccum += d.thought;
                        if (!this.thoughtEl) {
                            this.thoughtEl = document.createElement('div');
                            this.thoughtEl.innerHTML = _ViewTemplates.thinkingBlock('');
                            this.thoughtEl = this.thoughtEl.firstElementChild;
                            els.body.insertBefore(this.thoughtEl, els.body.firstChild);
                        }
                        this.thoughtEl.querySelector('.thinking-content').innerHTML = marked.parse(this.thoughtAccum);
                    }

                    // 2. Text Content
                    if (d.text) {
                        accum += d.text;
                        let resDiv = els.body.querySelector('.stream-result-content');

                        if (this.thoughtEl || resDiv) {
                            if (!resDiv) {
                                resDiv = document.createElement('div');
                                resDiv.className = 'stream-result-content';
                                // Move existing non-thought content into resDiv if any
                                Array.from(els.body.childNodes).forEach(node => {
                                    if (node !== this.thoughtEl) resDiv.appendChild(node);
                                });
                                els.body.appendChild(resDiv);
                            }
                            resDiv.innerHTML = marked.parse(accum);
                        } else {
                            els.body.innerHTML = marked.parse(accum);
                        }
                        els.raw.value = (this.thoughtAccum ? `<thought>\n${this.thoughtAccum}\n</thought>\n\n` : '') + accum;
                    }

                    if (d.error) this.app.ui.setError(d.error);
                    if (d.csrf_token) this.app.refreshCsrf(d.csrf_token);
                    if (d.flash_html) this.app.ui.showServerFlash(d.flash_html);

                    if (d.new_interaction_id) {
                        this.app.history.addItem({
                            id: d.new_interaction_id,
                            timestamp: d.timestamp,
                            user_input: d.user_input,
                            context_files: this.contextFiles
                        }, (this.thoughtAccum ? `<thought>\n${this.thoughtAccum}\n</thought>\n\n` : '') + accum);
                        this.app.history.highlightContext([d.new_interaction_id]);
                    }
                } catch (e) {
                    console.error("Stream parse error", e, line);
                }
            });
            return accum;
        }
    }

    /**
     * MediaUploader
     */
    class MediaUploader {
        constructor(app) {
            this.app = app;
            this.queue = [];
            this.isUploading = false;
        }
        init() {
            const area = document.getElementById('mediaUploadArea');
            const inp = document.getElementById('media-input-trigger');
            const trigger = document.getElementById('trigger-media-upload');
            if (trigger) trigger.onclick = () => inp.click();

            if (area) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => area.addEventListener(e, ev => {
                    ev.preventDefault();
                    ev.stopPropagation();
                }));
                ['dragenter', 'dragover'].forEach(e => area.addEventListener(e, () => area.classList.add('dragover')));
                ['dragleave', 'drop'].forEach(e => area.addEventListener(e, () => area.classList.remove('dragover')));
                area.addEventListener('drop', e => this.handleFiles(e.dataTransfer.files));
            }

            inp.addEventListener('change', e => {
                this.handleFiles(e.target.files);
                inp.value = '';
            });
            document.getElementById('upload-list-wrapper')?.addEventListener('click', e => {
                const btn = e.target.closest('.remove-btn');
                if (btn) this.removeFile(btn);
            });
        }
        handleFiles(files) {
            if ((files.length + document.querySelectorAll('.file-chip').length) > APP_CONFIG.limits.maxFiles) {
                return this.app.ui.showStatus(`Limit reached (Max: ${APP_CONFIG.limits.maxFiles})`, 'warning');
            }
            Array.from(files).forEach(f => {
                if (APP_CONFIG.limits.supportedTypes.includes(f.type) && f.size <= APP_CONFIG.limits.maxFileSize) {
                    const id = Math.random().toString(36).substr(2, 9);
                    const chipHtml = ViewRenderer.renderFileChip(f, id);
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = chipHtml;
                    const chip = wrapper.firstElementChild;
                    document.getElementById('upload-list-wrapper').appendChild(chip);
                    this.queue.push({
                        file: f,
                        id: id,
                        ui: chip
                    });
                } else {
                    if (!APP_CONFIG.limits.supportedTypes.includes(f.type)) {
                        this.app.ui.showStatus(`Unsupported file type: ${f.name}`, 'danger');
                    } else if (f.size > APP_CONFIG.limits.maxFileSize) {
                        const maxMB = (APP_CONFIG.limits.maxFileSize / (1024 * 1024)).toFixed(1);
                        this.app.ui.showStatus(`File too large: ${f.name} (Max: ${maxMB}MB)`, 'danger');
                    }
                }
            });
            if (this.queue.length) this.processQueue();
        }
        processQueue() {
            if (this.isUploading || !this.queue.length) return;
            this.isUploading = true;
            this.uploadFile(this.queue.shift());
        }
        async uploadFile(job) {
            const fd = new FormData();
            fd.append('file', job.file);
            try {
                const r = await this.app.sendAjax(APP_CONFIG.endpoints.upload, fd);
                if (job.cancelled) return;
                if (r.status === 'success') {
                    this.updateChipStatus(job.ui, 'success');
                    job.ui.querySelector('.remove-btn').dataset.serverFileId = r.file_id;
                    job.ui.querySelector('.remove-btn').classList.remove('disabled');
                    this.appendHiddenInput(r.file_id, job.id);
                } else throw new Error(r.message);
            } catch (e) {
                if (!job.cancelled) {
                    this.updateChipStatus(job.ui, 'error');
                    this.app.ui.showStatus(e.message, 'danger');
                }
            } finally {
                this.isUploading = false;
                this.processQueue();
            }
        }
        updateChipStatus(ui, status) {
            ui.querySelector('.progress-ring')?.remove();
            const i = document.createElement('i');
            if (status === 'success') {
                i.className = 'bi bi-check-circle-fill text-success me-2';
                ui.style.borderColor = 'var(--bs-success)';
            } else {
                i.className = 'bi bi-exclamation-circle-fill text-danger me-2';
                ui.style.borderColor = 'var(--bs-danger)';
            }
            ui.prepend(i);
        }
        appendHiddenInput(fileId, jobId) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'uploaded_media[]';
            hidden.value = fileId;
            hidden.id = `input-${jobId}`;
            document.getElementById('uploaded-files-container').appendChild(hidden);
        }
        async removeFile(btn) {
            const fid = btn.dataset.serverFileId;
            const jobId = btn.dataset.id;
            btn.closest('.file-chip').remove();
            document.getElementById(`input-${jobId}`)?.remove();
            const queued = this.queue.find(j => j.id === jobId);
            if (queued) queued.cancelled = true;
            if (fid) {
                const fd = new FormData();
                fd.append('file_id', fid);
                this.app.sendAjax(APP_CONFIG.endpoints.deleteMedia, fd).catch(() => {});
            }
        }
        clear() {
            document.getElementById('upload-list-wrapper').innerHTML = '';
            document.getElementById('uploaded-files-container').innerHTML = '';
            this.queue = [];
        }
    }

    /**
     * PromptManager
     */
    class PromptManager {
        constructor(app) {
            this.app = app;
        }
        init() {
            document.getElementById('usePromptBtn')?.addEventListener('click', () => {
                const sel = document.getElementById('savedPrompts');
                if (!sel?.value) return;
                if (tinymce.get('prompt')) tinymce.get('prompt').setContent(sel.value);
                else document.getElementById('prompt').value = sel.value;
            });
            const sel = document.getElementById('savedPrompts');
            const delBtn = document.getElementById('deletePromptBtn');
            if (sel && delBtn) {
                sel.onchange = () => delBtn.disabled = !sel.value;
                delBtn.onclick = () => this.deletePrompt(sel);
            }
            const form = document.querySelector('#savePromptModal form');
            if (form) {
                document.getElementById('savePromptModal').addEventListener('show.bs.modal', () => {
                    const editor = tinymce.get('prompt');
                    document.getElementById('modalPromptText').value = editor ? editor.getContent({
                        format: 'text'
                    }) : document.getElementById('prompt').value;
                });
                form.onsubmit = async (e) => {
                    e.preventDefault();
                    const m = bootstrap.Modal.getInstance(document.getElementById('savePromptModal'));
                    try {
                        const d = await this.app.sendAjax(form.action, new FormData(form));
                        if (d.status === 'success') {
                            m.hide();
                            this.app.ui.showStatus('Prompt saved!', 'success');
                            if (d.prompt) this.addPromptToUI(d.prompt);
                            e.target.reset();
                        } else this.app.ui.showStatus('Failed to save.', 'danger');
                    } catch (e) {
                        this.app.ui.showStatus('Error saving prompt', 'danger');
                    }
                };
            }
        }
        addPromptToUI(prompt) {
            const select = document.getElementById('savedPrompts');
            document.getElementById('savedPromptsContainer').classList.remove('d-none');
            const option = document.createElement('option');
            option.value = prompt.prompt_text;
            option.dataset.id = prompt.id;
            option.textContent = prompt.title;
            select.appendChild(option);
        }
        async deletePrompt(sel) {
            if (!sel.value || !confirm('Delete this prompt?')) return;
            try {
                const id = sel.options[sel.selectedIndex].dataset.id;
                const d = await this.app.sendAjax(APP_CONFIG.endpoints.deletePromptBase + id);
                if (d.status === 'success') {
                    this.app.ui.showStatus('Prompt deleted', 'success');
                    sel.querySelector(`option[data-id="${id}"]`)?.remove();
                } else this.app.ui.showStatus('Delete failed', 'danger');
            } catch (e) {
                this.app.ui.showStatus('Error deleting prompt', 'danger');
            }
        }
    }

    /**
     * HistoryManager
     */
    class HistoryManager {
        static HISTORY_PAGE_SIZE = 5;
        constructor(app) {
            this.app = app;
            this.listEl = document.getElementById('history-list');
            this.loadingEl = document.getElementById('memory-loading');
            this.isLoaded = false;
            this.isEmpty = true;
            this.offset = 0;
            this.limit = HistoryManager.HISTORY_PAGE_SIZE;
            this.hasMore = true;
            this.currentLastDate = '';
        }
        init() {
            document.getElementById('memory-tab')?.addEventListener('shown.bs.tab', () => {
                if (!this.isLoaded) this.fetchHistory();
            });
            this.listEl.addEventListener('click', (e) => {
                const delBtn = e.target.closest('.delete-memory-btn');
                if (delBtn) {
                    e.stopPropagation();
                    this.deleteItem(delBtn.dataset.id);
                    return;
                }
                const loadBtn = e.target.closest('.load-more-btn');
                if (loadBtn) {
                    e.preventDefault();
                    this.loadMore();
                    return;
                }

                // Tap to expand (Gemini Parity)
                const item = e.target.closest('.memory-item');
                if (item) {
                    const truncates = item.querySelectorAll('.text-truncate');
                    if (truncates.length > 0) {
                        truncates.forEach(el => {
                            el.classList.remove('text-truncate');
                            el.style.whiteSpace = 'normal';
                        });
                    } else {
                        const expanded = item.querySelectorAll('[style*="white-space: normal"]');
                        expanded.forEach(el => {
                            el.classList.add('text-truncate');
                            el.style.whiteSpace = '';
                        });
                    }
                }
            });
        }
        async fetchHistory(append = false) {
            if (!append) {
                this.loadingEl.classList.remove('d-none');
                this.listEl.classList.add('d-none');
            }
            const loadMoreBtn = this.listEl.querySelector('.load-more-btn');
            if (append && loadMoreBtn) {
                loadMoreBtn.disabled = true;
                loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            }
            try {
                const fd = new FormData();
                fd.append('limit', this.limit);
                fd.append('offset', this.offset);
                const d = await this.app.sendAjax(APP_CONFIG.endpoints.history, fd);
                if (d.status === 'success') {
                    this.renderList(d.history, append);
                    this.isLoaded = true;
                    if (d.history.length > 0) this.isEmpty = false;
                    this.offset += d.history.length;
                    this.hasMore = d.history.length === this.limit;
                    this.updateLoadMoreButton();
                }
            } catch (e) {
                if (!append) this.listEl.innerHTML = ViewRenderer.renderErrorHistory();
            } finally {
                if (!append) {
                    this.loadingEl.classList.add('d-none');
                    this.listEl.classList.remove('d-none');
                }
            }
        }
        renderList(items, append = false) {
            if (!items || items.length === 0) {
                if (!append) this.listEl.innerHTML = ViewRenderer.renderEmptyHistory();
                return;
            }
            if (!append) {
                this.listEl.innerHTML = '';
                this.currentLastDate = '';
            } else document.querySelector('.load-more-btn')?.closest('div')?.remove();
            items.forEach(item => {
                const date = this.formatDate(item.timestamp);
                if (date !== this.currentLastDate) {
                    this.listEl.appendChild(ViewRenderer.renderHistoryHeader(date));
                    this.currentLastDate = date;
                }
                this.listEl.appendChild(ViewRenderer.renderHistoryItem(item));
            });
        }
        updateLoadMoreButton() {
            document.querySelector('.load-more-btn')?.closest('div')?.remove();
            if (this.hasMore) this.listEl.appendChild(ViewRenderer.renderLoadMoreButton());
        }
        formatDate(ts) {
            const date = new Date(ts);
            if (isNaN(date.getTime())) return 'Today';
            return date.toLocaleDateString(undefined, {
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            });
        }
        async deleteItem(id) {
            if (!confirm('Forget this interaction?')) return;
            const el = this.listEl.querySelector(`.memory-item[data-id="${id}"]`);
            if (el) el.style.opacity = '0.5';
            const fd = new FormData();
            fd.append('unique_id', id);
            try {
                const d = await this.app.sendAjax(APP_CONFIG.endpoints.deleteHistory, fd);
                if (d.status === 'success') el?.remove();
                else {
                    if (el) el.style.opacity = '1';
                    this.app.ui.showStatus('Failed to delete.', 'danger');
                }
            } catch (e) {
                if (el) el.style.opacity = '1';
                this.app.ui.showStatus('Error deleting item.', 'danger');
            }
        }
        addItem(item, aiRaw) {
            if (item.id && !item.id.toString().startsWith('pending-')) document.querySelectorAll('.memory-item[data-id^="pending-"]').forEach(el => el.remove());
            if (this.isEmpty) this.listEl.innerHTML = '';
            const dateStr = this.formatDate(item.timestamp);
            let header = this.listEl.querySelector('.memory-date-header');
            if (!header || header.textContent !== dateStr) {
                header = ViewRenderer.renderHistoryHeader(dateStr);
                this.listEl.insertBefore(header, this.listEl.firstChild);
            }
            const el = ViewRenderer.renderHistoryItem({
                unique_id: item.id,
                user_input: item.user_input,
                ai_output: aiRaw,
                context_files: item.context_files
            });
            if (header.nextSibling) this.listEl.insertBefore(el, header.nextSibling);
            else this.listEl.appendChild(el);
            this.isEmpty = false;
        }
        highlightContext(ids) {
            if (!ids || !ids.length) return;
            const id = ids[0]; // Highlight the primary/first item
            const memTab = new bootstrap.Tab(document.getElementById('memory-tab'));
            memTab.show();
            setTimeout(() => {
                const newItem = this.listEl.querySelector(`.memory-item[data-id="${id}"]`);
                if (newItem) {
                    newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    newItem.classList.add('bg-warning', 'bg-opacity-10');
                    setTimeout(() => newItem.classList.remove('bg-warning', 'bg-opacity-10'), 2000);
                }
            }, 300);
        }
        async loadMore() {
            await this.fetchHistory(true);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => new OpenRouterApp().init());
</script>
<?= $this->endSection() ?>