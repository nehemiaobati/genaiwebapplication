<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\UserModel;
use App\Entities\User;

/**
 * Class HomeService
 *
 * Provides shared services for home/dashboard calculations.
 *
 * @package App\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class HomeService
{
    /**
     * User Model instance.
     *
     * @var UserModel
     */
    protected UserModel $userModel;

    /**
     * HomeService constructor.
     *
     * @param UserModel|null $userModel Optional injected UserModel.
     */
    public function __construct(?UserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
    }

    /**
     * Retrieves user entity by ID.
     *
     * @param int $userId The user ID.
     *
     * @return User|null
     */
    public function getUser(int $userId): ?User
    {
        /** @var User|null $user */
        $user = $this->userModel->find($userId);

        return $user;
    }
}
