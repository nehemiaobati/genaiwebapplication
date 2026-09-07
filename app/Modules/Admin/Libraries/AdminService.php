<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Models\UserModel;
use App\Entities\User;
use CodeIgniter\Pager\Pager;

/**
 * Class AdminService
 *
 * Handles admin user management, search, updates, and statistical aggregations.
 *
 * @package App\Modules\Admin\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class AdminService
{
    /**
     * UserModel instance.
     *
     * @var UserModel
     */
    protected UserModel $userModel;

    /**
     * AdminService constructor.
     *
     * @param UserModel|null $userModel Injected UserModel.
     */
    public function __construct(?UserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
    }

    /**
     * Gets paginated users with statistical metadata.
     *
     * @param int $perPage Items per page.
     *
     * @return array{users: array<int, User>, pager: Pager, total_users: int, total_balance: string}
     */
    public function getPaginatedUsers(int $perPage = 10): array
    {
        $users = $this->userModel->paginate($perPage);

        return [
            'users'         => $users,
            'pager'         => $this->userModel->pager,
            'total_users'   => (int) $this->userModel->pager->getTotal(),
            'total_balance' => (string) $this->userModel->getTotalBalance(),
        ];
    }

    /**
     * Searches users by query across username and email.
     *
     * @param string $query   Search term.
     * @param int    $perPage Items per page.
     *
     * @return array{users: array<int, User>, pager: Pager, search_query: string}
     */
    public function searchUsers(string $query, int $perPage = 10): array
    {
        $users = $this->userModel
            ->like('username', $query)
            ->orLike('email', $query)
            ->paginate($perPage);

        return [
            'users'        => $users,
            'pager'        => $this->userModel->pager,
            'search_query' => $query,
        ];
    }

    /**
     * Updates user balance.
     *
     * @param int   $userId     Target user ID.
     * @param float $newBalance Updated balance amount.
     *
     * @return bool
     */
    public function updateBalance(int $userId, float $newBalance): bool
    {
        /** @var User|null $user */
        $user = $this->userModel->find($userId);
        if ($user === null) {
            return false;
        }

        $user->balance = $newBalance;

        return (bool) $this->userModel->save($user);
    }

    /**
     * Deletes user account.
     *
     * @param int $userId Target user ID.
     *
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        return (bool) $this->userModel->delete($userId);
    }
}
