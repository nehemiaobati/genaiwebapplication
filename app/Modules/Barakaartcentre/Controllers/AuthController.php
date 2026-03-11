<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Controllers;

use App\Controllers\BaseController;
use App\Modules\Barakaartcentre\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AuthController
 * Handles simple login and session management for Baraka Arts Centre Admin.
 */
class AuthController extends BaseController
{
    public function login(): string|ResponseInterface
    {
        if (session()->get('baraka_admin_logged_in')) {
            return redirect()->route('baraka.admin.dashboard');
        }

        $data = [
            'pageTitle'       => 'Baraka Admin Login',
            'metaDescription' => 'Admin portal access for Baraka Art Centre',
            'canonicalUrl'    => current_url(),
            'robotsTag'       => 'noindex, nofollow',
            'metaImage'       => '',
        ];

        return view('App\Modules\Barakaartcentre\Views\auth\login', $data);
    }

    public function processLogin(): ResponseInterface
    {
        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->where('role', 'admin')->first();

        if ($user && password_verify($password, $user->password_hash)) {
            session()->set([
                'baraka_admin_logged_in' => true,
                'baraka_admin_id'        => $user->id,
                'baraka_admin_name'      => $user->name,
            ]);
            return redirect()->route('baraka.admin.dashboard')->with('status', 'Welcome back, ' . $user->name);
        }

        return redirect()->route('baraka.login')->with('error', 'Invalid email or password.');
    }

    public function logout(): ResponseInterface
    {
        session()->remove(['baraka_admin_logged_in', 'baraka_admin_id', 'baraka_admin_name']);
        return redirect()->route('baraka.home')->with('status', 'Logged out successfully.');
    }
}
