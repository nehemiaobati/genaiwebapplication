<?php
/**
 * Flash messages partial view.
 * Renders session flash data with native Bootstrap 5 alerts, icons, and dismiss buttons.
 */
$flash_data = [
    'success' => session()->getFlashdata('success'),
    'error'   => session()->getFlashdata('error'),
    'errors'  => session()->getFlashdata('errors'),
    'alert'   => session()->getFlashdata('alert'),
    'warning' => session()->getFlashdata('warning'),
    'info'    => session()->getFlashdata('info'),
];

$alert_types = [
    'success' => 'alert-success',
    'error'   => 'alert-danger',
    'errors'  => 'alert-danger',
    'alert'   => 'alert-warning',
    'warning' => 'alert-warning',
    'info'    => 'alert-info',
];

$alert_icons = [
    'success' => '<i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>',
    'error'   => '<i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>',
    'errors'  => '<i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>',
    'alert'   => '<i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i>',
    'warning' => '<i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i>',
    'info'    => '<i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>',
];

foreach ($flash_data as $key => $data):
    if ($data):
        $message = is_array($data) ? implode('<br>', array_map(static fn($item): string => esc((string)$item, 'html'), $data)) : esc((string)$data, 'html');
?>
        <div class="alert <?= esc($alert_types[$key], 'attr') ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <?= $alert_icons[$key] ?>
            <div class="flex-grow-1">
                <?= $message ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
    endif;
endforeach;
?>