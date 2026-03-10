<?php

if (!isset($routes)) {
    $routes = \Config\Services::routes();
}

/**
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('', ['namespace' => 'App\Modules\OpenRouter\Controllers'], static function ($routes) {

    // Public Route — distinct slug avoids collision with the auth group's /openrouter/ prefix
    $routes->get('openrouter-ai', 'OpenRouterController::publicPage', ['as' => 'openrouter.public']);

    // Authenticated Routes
    $routes->group('openrouter', ['filter' => 'auth'], static function ($routes) {
        $routes->get('/', 'OpenRouterController::index', ['as' => 'openrouter.index']);

        // Core Generation
        $routes->post('generate', 'OpenRouterController::generate', ['as' => 'openrouter.generate', 'filter' => ['balance', 'throttle:10,60']]);
        $routes->post('stream', 'OpenRouterController::stream', ['as' => 'openrouter.stream', 'filter' => ['balance', 'throttle:10,60']]);

        // Prompt Management
        $routes->post('prompts/add', 'OpenRouterController::addPrompt', ['as' => 'openrouter.prompts.add']);
        $routes->post('prompts/delete/(:num)', 'OpenRouterController::deletePrompt/$1', ['as' => 'openrouter.prompts.delete']);

        // Memory & History
        $routes->post('memory/clear', 'OpenRouterController::clearMemory', ['as' => 'openrouter.memory.clear']);
        $routes->post('history', 'OpenRouterController::fetchHistory', ['as' => 'openrouter.history.fetch']);
        $routes->post('history/delete', 'OpenRouterController::deleteHistory', ['as' => 'openrouter.history.delete']);

        // File Upload (Stateless Tempfile Pattern)
        $routes->post('upload-media', 'OpenRouterController::uploadMedia', ['as' => 'openrouter.upload_media']);
        $routes->post('delete-media', 'OpenRouterController::deleteMedia', ['as' => 'openrouter.delete_media']);

        // Document Export
        $routes->post('download-document', 'OpenRouterController::downloadDocument', ['as' => 'openrouter.download_document', 'filter' => 'csrf:except']);

        // Settings
        $routes->post('settings/update', 'OpenRouterController::updateSetting', ['as' => 'openrouter.update_setting']);
    });
});
