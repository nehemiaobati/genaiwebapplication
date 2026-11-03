<?php

declare(strict_types=1);

namespace App\Modules\Account\Controllers;

use App\Controllers\BaseController;
use App\Modules\Account\Libraries\AccountService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AccountController
 *
 * Handles user account-related HTTP requests, profile viewing, and transaction history orchestration.
 *
 * @package App\Modules\Account\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class AccountController extends BaseController
{
    /**
     * Account Service instance.
     *
     * @var AccountService
     */
    protected AccountService $accountService;

    /**
     * Helper dependencies.
     *
     * @var array<int, string>
     */
    protected $helpers = ['form'];

    /**
     * AccountController constructor.
     *
     * Solely resolves dependencies via service container per Part 3.1.
     */
    public function __construct()
    {
        /** @var AccountService $service */
        $service = service('accountService');
        $this->accountService = $service;
    }

    /**
     * Displays the user's account information and transaction history.
     *
     * @return string|ResponseInterface
     */
    public function index(): string|ResponseInterface
    {
        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return redirect()->to(url_to('login'))->with('error', 'Please log in to access your account.');
        }

        $user = $this->accountService->getUserProfile($userId);
        if ($user === null) {
            return redirect()->to(url_to('home'))->with('error', 'User not found.');
        }

        $transactionData = $this->accountService->getUserTransactions($userId, 5);

        $viewData = [
            'pageTitle'          => 'My Account | Afrikenkid',
            'metaDescription'    => 'Manage your profile, view your account balance, and see your full transaction history.',
            'canonicalUrl'       => url_to('account.index'),
            'robotsTag'          => 'noindex, follow',
            'user'               => $user,
            'transactions'       => $transactionData['transactions'],
            'pager'              => $transactionData['pager'],
            'display_references' => $transactionData['display_references'],
        ];

        return view('App\Modules\Account\Views\index', $viewData);
    }
}
