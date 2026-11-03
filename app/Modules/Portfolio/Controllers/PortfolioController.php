<?php

declare(strict_types=1);

namespace App\Modules\Portfolio\Controllers;

use App\Controllers\BaseController;
use App\Modules\Portfolio\Libraries\PortfolioService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class PortfolioController
 *
 * Handles the display of the developer portfolio and processes contact inquiries.
 *
 * @package App\Modules\Portfolio\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class PortfolioController extends BaseController
{
    /**
     * Portfolio Service instance.
     *
     * @var PortfolioService
     */
    protected PortfolioService $portfolioService;

    /**
     * Loaded helpers.
     *
     * @var array<int, string>
     */
    protected $helpers = ['form'];

    /**
     * PortfolioController constructor.
     */
    public function __construct()
    {
        /** @var PortfolioService $service */
        $service = service('portfolioService');
        $this->portfolioService = $service;
    }

    /**
     * Display the portfolio homepage.
     *
     * @return string
     */
    public function index(): string
    {
        $schemaJson = [
            '@context'   => 'https://schema.org',
            '@type'      => 'Person',
            '@id'        => url_to('portfolio.index') . '#person',
            'name'       => 'Nehemia Obati',
            'jobTitle'   => 'Software Engineer | Software Development and Technical Support',
            'worksFor'   => ['@id' => base_url('#organization')],
            'url'        => url_to('portfolio.index'),
            'image'      => base_url('assets/images/potraitwebp.webp'),
            'sameAs'     => [
                'https://www.linkedin.com/in/nehemia-obati-b74886344',
            ],
            'knowsAbout' => [
                'Generative AI',
                'Large Language Models',
                'Full-Stack Web Development',
                'PHP CodeIgniter',
                'Python',
                'Cloud Infrastructure & Distributed Systems',
            ],
        ];

        $data = [
            'pageTitle'       => 'Nehemia Obati — Software Engineer | Software Development and Technical Support',
            'metaDescription' => 'Portfolio of Nehemia Obati: Software Engineer | Software Development and Technical Support | PHP, Python and HTML | GCP/AWS.',
            'metaKeywords'    => 'Software Engineer, Full Stack Developer, AI Solutions, Cloud',
            'metaImage'       => base_url('assets/images/potraitwebp.webp'),
            'canonicalUrl'    => url_to('portfolio.index'),
            'robotsTag'       => 'index, follow',
            'schemaJson'      => $schemaJson,
        ];

        return view('App\Modules\Portfolio\Views\portfolio_view', $data);
    }

    /**
     * Handle the contact form submission.
     *
     * @return RedirectResponse
     */
    public function sendEmail(): RedirectResponse
    {
        $rules = [
            'name'    => 'required|min_length[3]',
            'email'   => 'required|valid_email',
            'subject' => 'required|min_length[5]',
            'message' => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $recaptchaResponse = (string) ($this->request->getPost('g-recaptcha-response') ?? '');
        $recaptchaService  = service('recaptchaService');

        if (! $recaptchaService->verify($recaptchaResponse)) {
            return redirect()->back()->withInput()->with('error', 'Please complete the reCAPTCHA.');
        }

        $emailData = [
            'name'    => (string) ($this->request->getPost('name', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''),
            'email'   => (string) ($this->request->getPost('email', FILTER_SANITIZE_EMAIL) ?? ''),
            'subject' => (string) ($this->request->getPost('subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''),
            'message' => (string) ($this->request->getPost('message', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''),
        ];

        if ($this->portfolioService->sendPortfolioInquiry($emailData)) {
            return redirect()->back()->with('success', 'Your message has been sent successfully!');
        }

        return redirect()->back()->with('error', 'Failed to send your message. Please try again later.');
    }
}
