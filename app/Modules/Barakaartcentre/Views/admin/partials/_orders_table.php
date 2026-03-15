<table class="admin-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Customer</th>
            <th>Phone Number</th>
            <th>Item</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Delivery / Notes</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($orders)): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-muted);">No records found in this category.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td style="font-size: 0.85rem;"><?= $order->created_at->format('M d, H:i') ?></td>
                    <td style="font-family: monospace; font-size: 0.8rem;"><?= esc($order->order_reference) ?></td>
                    <td>
                        <div style="font-weight: 500;"><?= esc($order->name) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?= esc($order->email) ?></div>
                    </td>
                    <td>
                        <a href="tel:<?= esc($order->phone_number) ?>" style="color: var(--accent-gold); text-decoration: none; font-weight: 600;">
                            <?= esc($order->phone_number) ?>
                        </a>
                    </td>
                    <td>
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="font-size: 0.65rem; text-transform: uppercase; color: var(--text-muted); background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; flex-shrink: 0; margin-top: 2px; letter-spacing: 0.05em;">
                                <?= esc($order->item_type) ?>
                            </span>
                            <span style="font-weight: 500; line-height: 1.4;"><?= esc($order->item_title) ?></span>
                        </div>
                    </td>
                    <td style="font-weight: 500;">KES <?= number_format($order->amount, 2) ?></td>
                    <td>
                        <?php
                        $status_class = 'badge-pending';
                        if ($order->status === 'success') $status_class = 'badge-success';
                        if ($order->status === 'failed') $status_class = 'badge-error';
                        ?>
                        <span class="badge <?= $status_class ?>"><?= ucfirst(esc($order->status)) ?></span>
                        <?php if ($order->is_resolved): ?>
                            <div style="font-size: 0.7rem; color: #52B44B; margin-top: 4px; font-weight: bold;">(RESOLVED)</div>
                        <?php endif; ?>
                    </td>
                    <td style="max-width: 200px;">
                        <?php if ($order->item_type === 'artwork'): ?>
                            <div style="font-size: 0.8rem; white-space: normal; line-height: 1.3;">
                                <?= esc($order->delivery_address) ?>
                            </div>
                        <?php else: ?>
                            <span style="color: #555;">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($order->status === 'success' && !$order->is_resolved): ?>
                            <form action="<?= route_to('baraka.admin.payments.resolve', $order->id) ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <button type="submit" style="background: var(--accent-gold); color: #000; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; cursor: pointer;">
                                    Mark Resolved
                                </button>
                            </form>
                        <?php elseif ($order->is_resolved): ?>
                            <span style="color: #52B44B; font-size: 0.8rem;"><i class="bi bi-check-circle-fill"></i> Done</span>
                        <?php else: ?>
                            <span style="color: #555;">&mdash;</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
