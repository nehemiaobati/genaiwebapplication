<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Libraries\AdminService;
use App\Libraries\WalletService;
use App\Libraries\LogViewer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AdminController
 *
 * Manages administrative functions related to user accounts, search, balance updates, and logs.
 *
 * @package App\Modules\Admin\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class AdminController extends BaseController
{
    /**
     * Admin Service instance.
     *
     * @var AdminService
     */
    protected AdminService $adminService;

    /**
     * Loaded helpers.
     *
     * @var array<int, string>
     */
    protected $helpers = ['form'];

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        /** @var AdminService $service */
        $service = service('adminService');
        $this->adminService = $service;
    }

    /**
     * Displays a paginated list of all users.
     *
     * @return string|ResponseInterface
     */
    public function index(): string|ResponseInterface
    {
        $userData = $this->adminService->getPaginatedUsers(10);

        $data = [
            'users'           => $userData['users'],
            'pager'           => $userData['pager'],
            'total_users'     => $userData['total_users'],
            'total_balance'   => $userData['total_balance'],
            'pageTitle'       => 'User Management | Admin',
            'metaDescription' => 'Manage registered users, view statistics, and update account balances.',
            'canonicalUrl'    => url_to('admin.index'),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('App\Modules\Admin\Views\index_view', $data);
    }

    /**
     * Handles user search functionality based on a query parameter.
     *
     * @return string|ResponseInterface
     */
    public function searchUsers(): string|ResponseInterface
    {
        $searchQuery = (string) ($this->request->getGet('q') ?? '');

        if ($searchQuery === '') {
            return redirect()->to(url_to('admin.index'));
        }

        $results = $this->adminService->searchUsers($searchQuery, 10);

        $data = [
            'users'           => $results['users'],
            'pager'           => $results['pager'],
            'search_query'    => $results['search_query'],
            'total_users'     => count($results['users']),
            'pageTitle'       => 'Search Users | Admin',
            'metaDescription' => 'Search results for users matching: ' . esc($searchQuery, 'html'),
            'canonicalUrl'    => current_url(),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('App\Modules\Admin\Views\user_search_results', $data);
    }

    /**
     * Displays the details of a specific user.
     *
     * @param int|string $id The unique identifier of the user to display.
     *
     * @return string|ResponseInterface
     */
    public function show(int|string $id): string|ResponseInterface
    {
        $userModel = model(\App\Models\UserModel::class);
        /** @var \App\Entities\User|null $user */
        $user = $userModel->find((int) $id);

        if ($user === null) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $data = [
            'user'            => $user,
            'pageTitle'       => 'User Details: ' . esc($user->username, 'html') . ' | Admin',
            'metaDescription' => 'View and manage profile details for ' . esc($user->username, 'html'),
            'canonicalUrl'    => url_to('admin.users.show', (int) $id),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('App\Modules\Admin\Views\user_details', $data);
    }

    /**
     * Updates a user's balance.
     *
     * @param int|string $id The user ID.
     *
     * @return ResponseInterface
     */
    public function updateBalance(int|string $id): ResponseInterface
    {
        $rules = [
            'amount' => 'required|numeric|greater_than[0]',
            'action' => 'required|in_list[deposit,withdraw]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $walletService = new WalletService();
        $result = $walletService->updateBalance(
            (int) $id,
            (float) $this->request->getPost('amount'),
            (string) $this->request->getPost('action')
        );

        if (! $result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        return redirect()->to(url_to('admin.users.show', (int) $id))->with('success', $result['message']);
    }

    /**
     * Deletes a specific user.
     *
     * @param int|string $id User ID.
     *
     * @return ResponseInterface
     */
    public function delete(int|string $id): ResponseInterface
    {
        $currentUserId = session()->get('userId');

        if ((int) $id === (int) $currentUserId) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->adminService->deleteUser((int) $id)) {
            return redirect()->to(url_to('admin.index'))->with('success', 'User deleted successfully.');
        }

        return redirect()->back()->with('error', 'Failed to delete user.');
    }

    /**
     * Displays the application log files for debugging.
     *
     * @return string|ResponseInterface
     */
    public function logs(): string|ResponseInterface
    {
        if (! session()->get('is_admin')) {
            return redirect()->to(url_to('home'))->with('error', 'Permission denied.');
        }

        $logViewer    = new LogViewer();
        $logFiles     = $logViewer->getLogFiles();
        $selectedFile = (string) ($this->request->getGet('file') ?? ($logFiles[0] ?? ''));
        $logContent   = ($selectedFile !== '') ? $logViewer->getLogContent($selectedFile) : '';

        $data = [
            'pageTitle'       => 'Application Logs | Admin',
            'metaDescription' => 'Review system log files for debugging and monitoring.',
            'canonicalUrl'    => url_to('admin.logs'),
            'logFiles'        => $logFiles,
            'selectedFile'    => $selectedFile,
            'logContent'      => $logContent,
            'robotsTag'       => 'noindex, nofollow',
        ];

        return view('App\Modules\Admin\Views\logs_view', $data);
    }
}
