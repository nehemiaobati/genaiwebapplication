<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;

class HomeController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index(): string
    {
        $userId = session()->get('userId');
        $user = null;
        $balance = '0.00';

        if ($userId) {
            /** @var \App\Entities\User|null $user */
            $user = $this->userModel->find($userId);
            if ($user && isset($user->balance)) {
                $balance = $user->balance;
            }
        }

        $data = [
            'pageTitle' => 'Dashboard | ' . session()->get('username'),
            'metaDescription' => 'Your dashboard. Check your account balance, manage your details, and access our AI and Crypto services.',
            'username'  => session()->get('username'),
            'email'     => session()->get('userEmail'),
            'member_since' => $user->created_at ?? null,
            'balance'   => $balance,
            'canonicalUrl' => url_to('home'), // Corrected route name
        ];
        // Add noindex directive for authenticated pages
        $data['robotsTag'] = 'noindex, follow';
        return view('home/welcome_user', $data);
    }

    public function landing(): string
    {
        $data = [
            'pageTitle'       => 'Afrikenkid | Advanced Generative AI Solutions & Crypto Analytics',
            'metaDescription' => 'Transform your workflow with industry-leading Generative AI for video, image, and text creation, plus real-time blockchain analytics. Simple and powerful.',
            'heroTitle'       => 'Industry-Leading AI & Blockchain Analytics',
            'heroSubtitle'    => 'Accelerate innovation with state-of-the-art Generative AI and real-time crypto wallet insights. Powerful, direct, and built for scale.',
            'canonicalUrl'    => url_to('landing'),
            'robotsTag'       => 'index, follow',
        ];
        return view('home/landing_page', $data);
    }

    public function terms(): string
    {
        $data = [
            'pageTitle' => 'Terms of Service | Afrikenkid',
            'metaDescription' => 'Read the official Terms of Service for using the platform, its AI tools, and cryptocurrency data services.',
            'canonicalUrl' => url_to('terms'),
            'robotsTag'    => 'index, follow',
        ];
        return view('home/terms', $data);
    }

    public function privacy(): string
    {
        $data = [
            'pageTitle' => 'Privacy Policy | Afrikenkid',
            'metaDescription' => 'Our Privacy Policy outlines how we collect, use, and protect your personal data when you use services.',
            'canonicalUrl' => url_to('privacy'),
            'robotsTag'    => 'index, follow',
        ];
        return view('home/privacy', $data);
    }

    public function acceptCookie()
    {
        // 1 year expiration
        $expires = 365 * 24 * 60 * 60;

        // Must use $this->response (the controller's own response object) so the
        // Set-Cookie header is included in the same response that returns the JSON.
        // Using service('response') returns a different instance and the cookie header is lost.
        $this->response->setCookie(
            'user_cookie_consent',
            'accepted',
            $expires
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Cookie consent accepted',
            'csrf_token' => csrf_hash()
        ]);
    }
}
