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
            'metaDescription' => 'Your dashboard. Check your account balance, manage your details, and access our AI and Blockchain services.',
            'pageIdentifier'  => 'dashboard',
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
        $schemaJson = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'  => 'Organization',
                    '@id'    => base_url('#organization'),
                    'name'   => 'AFRIKENKID Studio',
                    'url'    => base_url(),
                    'logo'   => base_url('assets/images/logo.png'),
                    'sameAs' => [
                        'https://www.linkedin.com/in/nehemia-obati-b74886344',
                    ],
                ],
                [
                    '@type'     => 'WebSite',
                    '@id'       => base_url('#website'),
                    'url'       => base_url(),
                    'name'      => 'AFRIKENKID Studio',
                    'publisher' => ['@id' => base_url('#organization')],
                ],
                [
                    '@type'               => 'SoftwareApplication',
                    '@id'                 => base_url('#software'),
                    'name'                => 'AFRIKENKID Studio Platform',
                    'applicationCategory' => 'DeveloperApplication',
                    'operatingSystem'     => 'Web Browser',
                    'url'                 => base_url(),
                    'description'         => 'Generative AI studio offering unified access to frontier models, private local LLM inference, and blockchain verification.',
                    'author'              => ['@id' => base_url('#organization')],
                    'offers'              => [
                        '@type'         => 'Offer',
                        'price'         => '0',
                        'priceCurrency' => 'KES',
                        'description'   => 'Freemium access with credit-based top-ups.',
                    ],
                ],
            ],
        ];

        $data = [
            'pageTitle'       => 'AFRIKENKID | Generative AI Studio & Custom Software Platform',
            'metaDescription' => 'Explore AFRIKENKID: Access top generative models (Gemini, OpenRouter, Ollama), private local inference, blockchain verification, and custom web engineering.',
            'metaKeywords'    => 'Generative AI Studio, Generative Models, Google Gemini, OpenRouter AI, Ollama Private LLM, Blockchain Audit, Custom Software Solutions',
            'heroTitle'       => 'Generative AI Studio Platform',
            'heroSubtitle'    => 'Access to generative models, real-time blockchain, and custom software solutions.',
            'canonicalUrl'    => url_to('landing'),
            'robotsTag'       => 'index, follow, max-image-preview:large, max-snippet:-1',
            'schemaJson'      => $schemaJson,
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
            'pageTitle'       => 'Terms of Service | AFRIKENKID Generative AI',
            'metaDescription' => 'Review the Terms of Service governing the use of AFRIKENKID Studio platform, generative AI APIs, model billing, credit top-ups, and user rights.',
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
            'pageTitle'       => 'Privacy Policy | AFRIKENKID Generative AI',
            'metaDescription' => 'Understand how AFRIKENKID Studio handles your data, ensures confidentiality during local and cloud LLM inference, and protects your account privacy.',
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
