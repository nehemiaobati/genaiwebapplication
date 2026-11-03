<?php

declare(strict_types=1);

namespace App\Modules\Account\Libraries;

use App\Models\UserModel;
use App\Modules\Payments\Models\PaymentModel;
use App\Entities\User;
use CodeIgniter\Pager\Pager;

/**
 * Class AccountService
 *
 * Handles account-related business logic, profile loading, and transaction history retrieval.
 *
 * @package App\Modules\Account\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class AccountService
{
    /**
     * User Model instance.
     *
     * @var UserModel
     */
    protected UserModel $userModel;

    /**
     * Payment Model instance.
     *
     * @var PaymentModel
     */
    protected PaymentModel $paymentModel;

    /**
     * AccountService constructor.
     *
     * @param UserModel|null    $userModel    User Model dependency.
     * @param PaymentModel|null $paymentModel Payment Model dependency.
     */
    public function __construct(?UserModel $userModel = null, ?PaymentModel $paymentModel = null)
    {
        $this->userModel    = $userModel ?? new UserModel();
        $this->paymentModel = $paymentModel ?? new PaymentModel();
    }

    /**
     * Retrieves user entity by ID.
     *
     * @param int $userId The user ID.
     *
     * @return User|null
     */
    public function getUserProfile(int $userId): ?User
    {
        /** @var User|null $user */
        $user = $this->userModel->find($userId);

        return $user;
    }

    /**
     * Retrieves paginated transaction records and display references for a given user.
     *
     * @param int $userId  The user ID.
     * @param int $perPage Number of records per page.
     *
     * @return array{transactions: array<int, mixed>, pager: Pager, display_references: array<int, string>}
     */
    public function getUserTransactions(int $userId, int $perPage = 5): array
    {
        $transactions = $this->paymentModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);

        $displayReferences = [];
        if (! empty($transactions)) {
            foreach ($transactions as $transaction) {
                $displayReferences[$transaction->id] = method_exists($transaction, 'getDisplayReference')
                    ? $transaction->getDisplayReference()
                    : ($transaction->reference ?? (string) $transaction->id);
            }
        }

        return [
            'transactions'       => $transactions,
            'pager'              => $this->paymentModel->pager,
            'display_references' => $displayReferences,
        ];
    }
}
