<?= $this->extend('App\Modules\Barakaartcentre\Views\layouts\admin') ?>

<?= $this->section('content') ?>
<div class="header-action">
    <h1>Payments & Order History</h1>
    <div style="display: flex; gap: 10px;">
        <span class="pillar-badge" style="background: rgba(82, 180, 75, 0.1); color: #52B44B; border-color: #52B44B;">Success</span>
        <span class="pillar-badge" style="background: rgba(244, 162, 97, 0.1); color: #f4a261; border-color: #f4a261;">Pending</span>
        <span class="pillar-badge" style="background: rgba(230, 57, 70, 0.1); color: #e63946; border-color: #e63946;">Failed</span>
    </div>
</div>

<p style="color: var(--text-muted); margin-bottom: 2rem;">Helpdesk: Use the phone numbers below to contact users with 'Pending' or 'Failed' attempts.</p>

<div class="tabs" style="margin-bottom: 2rem; display: flex; gap: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
    <button class="tab-btn active" onclick="switchTab(event, 'artworks-tab')" style="background: none; border: none; color: var(--accent-gold); font-weight: 600; cursor: pointer; padding-bottom: 5px; border-bottom: 2px solid var(--accent-gold);">Artwork Orders</button>
    <button class="tab-btn" onclick="switchTab(event, 'workshops-tab')" style="background: none; border: none; color: #aaa; font-weight: 600; cursor: pointer; padding-bottom: 5px;">Workshop Orders</button>
</div>

<div id="artworks-tab" class="tab-content active-content">
    <div class="bento-card" style="padding: 0; overflow-x: auto;">
        <?= $this->setData(['orders' => $artwork_orders])->include('App\Modules\Barakaartcentre\Views\admin\partials\_orders_table') ?>
    </div>
</div>

<div id="workshops-tab" class="tab-content" style="display: none;">
    <div class="bento-card" style="padding: 0; overflow-x: auto;">
        <?= $this->setData(['orders' => $workshop_orders])->include('App\Modules\Barakaartcentre\Views\admin\partials\_orders_table') ?>
    </div>
</div>

<script>
    function switchTab(evt, tabName) {
        var i, tabContent, tabBtns;
        tabContent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabContent.length; i++) {
            tabContent[i].style.display = "none";
        }
        tabBtns = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tabBtns.length; i++) {
            tabBtns[i].style.color = "#aaa";
            tabBtns[i].style.borderBottom = "none";
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.style.color = "var(--accent-gold)";
        evt.currentTarget.style.borderBottom = "2px solid var(--accent-gold)";
    }
</script>

<style>
    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-success { background: rgba(82, 180, 75, 0.2); color: #52B44B; }
    .badge-pending { background: rgba(244, 162, 97, 0.2); color: #f4a261; }
    .badge-error { background: rgba(230, 57, 70, 0.2); color: #e63946; }

    .admin-table th { border-bottom: 2px solid var(--glass-border); color: #aaa; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .admin-table td { padding: 1.25rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.03); }
</style>
<?= $this->endSection() ?>
