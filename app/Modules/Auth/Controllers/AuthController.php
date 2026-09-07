<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Libraries\AuthService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AuthController
 *
 * Handles user authentication, registration, password resets, and session management.
 *
 * @package App\Modules\Auth\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class AuthController extends BaseController
{
    /**
     * Auth Service instance.
     *
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * Helpers required by the controller views.
     *
     * @var array<int, string>
     */
    protected $helpers = ['form'];

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        /** @var AuthService $service */
        $service = service('authService');
        $this->authService = $service;
    }

    /**
     * Renders registration form.
     *
     * @return string|ResponseInterface
     */
    public function register(): string|ResponseInterface
    {
        if ($this->session->has('isLoggedIn')) {
            return redirect()->to(url_to('home'));
        }

        $data = [
            'pageTitle'       => 'Create Your Account | Afrikenkid',
            'metaDescription' => 'Sign up for a free account. Make your first deposit to get KSH 30 in starter credits.',
            'canonicalUrl'    => url_to('register'),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('App\Modules\Auth\Views\register', $data);
    }

    /**
     * Processes registration form submission.
     *
     * @return ResponseInterface
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'username'        => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'           => 'required|valid_email|is_unique[users.email]',
            'password'        => 'required|min_length[8]|max_length[255]',
            'confirmpassword' => 'matches[password]',
            'terms'           => 'required',
            'marketing'       => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $recaptchaResponse = (string) ($this->request->getPost('g-recaptcha-response') ?? '');
        $recaptchaService  = service('recaptchaService');

        if (! $recaptchaService->verify($recaptchaResponse)) {
            log_message('warning', '[AuthController] reCAPTCHA verification failed for registration.');

            return redirect()->back()->withInput()->with('error', 'reCAPTCHA verification failed. Please try again.');
        }

        $inputData = [
            'username'  => (string) $this->request->getPost('username'),
            'email'     => (string) $this->request->getPost('email'),
            'password'  => (string) $this->request->getPost('password'),
            'marketing' => $this->request->getPost('marketing'),
        ];

        $result = $this->authService->register($inputData);

        if ($result['success']) {
            return redirect()->to(url_to('login'))->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Renders login form.
     *
     * @return string|ResponseInterface
     */
    public function login(): string|ResponseInterface
    {
        if ($this->session->has('isLoggedIn')) {
            return redirect()->to(url_to('home'));
        }

        $data = [
            'pageTitle'       => 'Login | Afrikenkid',
            'metaDescription' => 'Access your dashboard.',
            'canonicalUrl'    => url_to('login'),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('App\Modules\Auth\Views\login', $data);
    }

    /**
     * Processes login authentication.
     *
     * @return ResponseInterface
     */
    public function authenticate(): ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $recaptchaResponse = (string) ($this->request->getPost('g-recaptcha-response') ?? '');
        $recaptchaService  = service('recaptchaService');

        if (! $recaptchaService->verify($recaptchaResponse)) {
            return redirect()->back()->withInput()->with('error', 'reCAPTCHA verification failed.');
        }

        $email    = (string) $this->request->getVar('email');
        $password = (string) $this->request->getVar('password');

        $result = $this->authService->authenticate($email, $password);

        if (! $result['success']) {
            log_message('warning', '[AuthController] Failed login: ' . $result['message']);

            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        $user = $result['user'];
        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'User details could not be retrieved.');
        }

        $this->session->set([
            'isLoggedIn'   => true,
            'userId'       => $user->id,
            'userEmail'    => $user->email,
            'username'     => $user->username,
            'is_admin'     => $user->is_admin,
            'member_since' => $user->created_at,
        ]);
        $this->session->regenerate();

        if ($this->session->has('redirect_url')) {
            $redirectUrl = (string) $this->session->get('redirect_url');
            $this->session->remove('redirect_url');

            return redirect()->to($redirectUrl)->with('success', 'Welcome back!');
        }

        return redirect()->to(url_to('home'))->with('success', 'Login Successful');
    }

    /**
     * Logs out the user and clears session data.
     *
     * @return ResponseInterface
     */
    public function logout(): ResponseInterface
    {
        $this->session->destroy();

        return redirect()->to(url_to('login'))->with('success', 'Logged out successfully.');
    }

    /**
     * Verifies user email via token.
     *
     * @param string $token Verification token.
     *
     * @return ResponseInterface
     */
    public function verifyEmail(string $token): ResponseInterface
    {
        if ($this->authService->verifyEmail($token)) {
            return redirect()->to(url_to('login'))->with('success', 'Email verified successfully. You can now log in.');
        }

        return redirect()->to(url_to('register'))->with('error', 'Invalid verification token.');
    }

    /**
     * Renders forgot password form.
     *
     * @return string
     */
    public function forgotPasswordForm(): string
    {
        $data = [
            'pageTitle'       => 'Forgot Password | Afrikenkid',
            'metaDescription' => 'Reset your account password.',
            'canonicalUrl'    => url_to('auth.forgot_password'),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('App\Modules\Auth\Views\forgot_password', $data);
    }

    /**
     * Sends password reset email link.
     *
     * @return ResponseInterface
     */
    public function sendResetLink(): ResponseInterface
    {
        $rules = ['email' => 'required|valid_email'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email  = (string) $this->request->getVar('email');
        $result = $this->authService->sendResetLink($email);

        if ($result['success']) {
            return redirect()->to(url_to('auth.forgot_password'))->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Renders reset password form.
     *
     * @param string $token Reset token.
     *
     * @return string|ResponseInterface
     */
    public function resetPasswordForm(string $token): string|ResponseInterface
    {
        if (! $this->authService->validateResetToken($token)) {
            return redirect()->to(url_to('auth.forgot_password'))->with('error', 'Invalid or expired password reset token.');
        }

        $data = [
            'pageTitle'       => 'Reset Your Password | Afrikenkid',
            'metaDescription' => 'Choose a new, secure password.',
            'canonicalUrl'    => current_url(),
            'robotsTag'       => 'noindex, follow',
            'token'           => $token,
        ];

        return view('App\Modules\Auth\Views\reset_password', $data);
    }

    /**
     * Updates user password using reset token.
     *
     * @return ResponseInterface
     */
    public function updatePassword(): ResponseInterface
    {
        $rules = [
            'token'           => 'required',
            'password'        => 'required|min_length[8]',
            'confirmpassword' => 'matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token    = (string) $this->request->getVar('token');
        $password = (string) $this->request->getVar('password');

        if ($this->authService->resetPassword($token, $password)) {
            return redirect()->to(url_to('login'))->with('success', 'Your password has been successfully updated. You can now log in.');
        }

        return redirect()->to(url_to('auth.forgot_password'))->with('error', 'Invalid or expired password reset token.');
    }
}
