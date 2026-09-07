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
        $data = [
            'pageTitle'       => 'Nehemia Obati | Software Developer Portfolio',
            'metaDescription' => 'The professional portfolio of Nehemia Obati, a full-stack software developer specializing in scalable AI integrations, PHP, Python, and cloud solutions.',
            'metaImage'       => base_url('assets/images/potraitwebp.webp'),
            'canonicalUrl'    => url_to('portfolio.index'),
            'robotsTag'       => 'index, follow',
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
