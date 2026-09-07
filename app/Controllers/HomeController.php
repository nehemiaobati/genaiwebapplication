<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\HomeService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class HomeController
 *
 * Handles landing, terms, privacy, cookie consent, and authenticated dashboard requests.
 *
 * @package App\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class HomeController extends BaseController
{
    /**
     * Home Service instance.
     *
     * @var HomeService
     */
    protected HomeService $homeService;

    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        /** @var HomeService $service */
        $service = service('homeService');
        $this->homeService = $service;
    }

    /**
     * Authenticated user dashboard view.
     *
     * @return string|ResponseInterface
     */
    public function index(): string|ResponseInterface
    {
        $userId = session()->get('userId');
        $user = null;
        $balance = '0.00';

        if ($userId) {
            $user = $this->homeService->getUser((int) $userId);
            if ($user && isset($user->balance)) {
                $balance = (string) $user->balance;
            }
        }

        $data = [
            'pageTitle'       => 'Dashboard | ' . session()->get('username'),
            'metaDescription' => 'Your dashboard. Check your account balance, manage your details, and access our AI and Crypto services.',
            'username'        => session()->get('username'),
            'email'           => session()->get('userEmail'),
            'member_since'    => $user->created_at ?? null,
            'balance'         => $balance,
            'canonicalUrl'    => url_to('home'),
            'robotsTag'       => 'noindex, follow',
        ];

        return view('home/welcome_user', $data);
    }

    /**
     * Public landing page view.
     *
     * @return string
     */
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

    /**
     * Terms of service view.
     *
     * @return string
     */
    public function terms(): string
    {
        $data = [
            'pageTitle'       => 'Terms of Service | Afrikenkid',
            'metaDescription' => 'Read the official Terms of Service for using the platform, its AI tools, and cryptocurrency data services.',
            'canonicalUrl'    => url_to('terms'),
            'robotsTag'       => 'index, follow',
        ];

        return view('home/terms', $data);
    }

    /**
     * Privacy policy view.
     *
     * @return string
     */
    public function privacy(): string
    {
        $data = [
            'pageTitle'       => 'Privacy Policy | Afrikenkid',
            'metaDescription' => 'Our Privacy Policy outlines how we collect, use, and protect your personal data when you use services.',
            'canonicalUrl'    => url_to('privacy'),
            'robotsTag'       => 'index, follow',
        ];

        return view('home/privacy', $data);
    }

    /**
     * Sets cookie consent acceptance.
     *
     * @return ResponseInterface
     */
    public function acceptCookie(): ResponseInterface
    {
        $expires = 365 * 24 * 60 * 60;

        $this->response->setCookie(
            'user_cookie_consent',
            'accepted',
            $expires
        );

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Cookie consent accepted',
            'csrf_token' => csrf_hash(),
        ]);
    }
}
