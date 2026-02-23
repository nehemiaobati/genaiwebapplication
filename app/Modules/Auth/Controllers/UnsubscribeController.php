<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Handles marketing unsubscriptions.
 */
class UnsubscribeController extends BaseController
{
    /**
     * Processes an unsubscribe request via a unique token.
     *
     * @param string $token
     * @return string|ResponseInterface
     */
    public function index(string $token): string|ResponseInterface
    {
        if (empty($token)) {
            return redirect()->to(url_to('landing'))->with('error', 'Invalid unsubscribe token.');
        }

        $userModel = new UserModel();

        /** @var \App\Entities\User|null $user */
        $user = $userModel->where('unsubscribe_token', $token)->first();

        if (!$user) {
            // Act like it succeeded or expired to prevent enumeration, but show a safe message
            return view('App\Modules\Auth\Views\unsubscribed', [
                'pageTitle'       => 'Unsubscribed | Afrikenkid',
                'metaDescription' => 'You have been unsubscribed from our marketing campaigns.',
            ]);
        }

        // Opt the user out
        $user->marketing_opt_in = 0;
        // Optionally, we could rotate the token, but leaving it is fine so they don't get 
        // a "not found" error if they click the link again in an old email.
        $userModel->save($user);

        return view('App\Modules\Auth\Views\unsubscribed', [
            'pageTitle'       => 'Unsubscribed | Afrikenkid',
            'metaDescription' => 'You have been successfully unsubscribed from our marketing campaigns.',
        ]);
    }
}
