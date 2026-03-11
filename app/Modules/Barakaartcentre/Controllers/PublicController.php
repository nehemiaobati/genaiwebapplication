<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Controllers;

use App\Controllers\BaseController;
use App\Modules\Barakaartcentre\Libraries\BarakaPublicService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class PublicController
 * Handles all public-facing routes for the Baraka Art Centre portal.
 */
class PublicController extends BaseController
{
    protected BarakaPublicService $publicService;

    public function __construct()
    {
        $this->publicService = new BarakaPublicService();
    }

    /**
     * Helper method to generate default SEO data for public views.
     */
    private function getBaseSeoData(string $title, string $description): array
    {
        return [
            'pageTitle'       => "Baraka Art Centre | $title",
            'metaDescription' => $description,
            'canonicalUrl'    => current_url(),
            'robotsTag'       => 'noindex, follow', // Standard public pages
            'metaImage'       => base_url('assets/images/baraka_logo.png'),
        ];
    }

    public function index(): string
    {
        $data = $this->getBaseSeoData(
            'Where Art Meets Science',
            'Empower communities through creativity, fine art, and design. Combining artistic expression with scientifically-backed benefits for the mind and nervous system.'
        );
        $data['featured_artworks'] = $this->publicService->getFeaturedArtworks(6);
        $data['services'] = $this->publicService->getAllServices();

        return view('App\Modules\Barakaartcentre\Views\public\home', $data);
    }

    public function about(): string
    {
        $data = $this->getBaseSeoData(
            'About the Centre & Our Founder',
            'Learn about Robai Nacheri and the vision behind the Baraka Art Centre in Mombasa, Kenya.'
        );

        return view('App\Modules\Barakaartcentre\Views\public\about', $data);
    }

    public function services(): string
    {
        $data = $this->getBaseSeoData(
            'Our Services',
            'Explore our revenue-generating fine art commissions and our community impact services including art workshops and mentorship.'
        );
        $data['services'] = $this->publicService->getAllServices();

        return view('App\Modules\Barakaartcentre\Views\public\services', $data);
    }

    public function science(): string
    {
        $data = $this->getBaseSeoData(
            'The Science of Art',
            'Discover the nervous system benefits of art, from stress relief and emotional regulation to mindfulness and motor skills.'
        );

        return view('App\Modules\Barakaartcentre\Views\public\science', $data);
    }

    public function gallery(): string
    {
        $data = $this->getBaseSeoData(
            'Portfolio & Gallery',
            'View original art commissions, student projects, and collaborative community murals from the Baraka Art Centre.'
        );
        $data['artworks'] = $this->publicService->getAllArtworks();

        return view('App\Modules\Barakaartcentre\Views\public\gallery', $data);
    }

    public function workshops(): string
    {
        $data = $this->getBaseSeoData(
            'Workshops & Events',
            'Join our upcoming draw your mood and mindful observation workshops designed for dopamine release and emotional expression.'
        );
        $data['workshops'] = $this->publicService->getUpcomingWorkshops();

        return view('App\Modules\Barakaartcentre\Views\public\workshops', $data);
    }

    public function contact(): string
    {
        $data = $this->getBaseSeoData(
            'Contact Us',
            'Get in touch to book a workshop, commission artwork, or support the Baraka Innovation Lab.'
        );
        $data['payment_status'] = session()->getFlashdata('payment_status');
        $data['payment_class'] = session()->getFlashdata('payment_class');

        return view('App\Modules\Barakaartcentre\Views\public\contact', $data);
    }

    public function processPayment(): ResponseInterface
    {
        $phone = (string) esc($this->request->getPost('mpesa_phone'));
        $amount = (float) $this->request->getPost('amount');

        $result = $this->publicService->simulateMpesaPayment($phone, $amount);

        $class = $result['status'] ? 'success' : 'error';

        return redirect()->to(base_url('baraka-art-centre/contact#support'))
            ->with('payment_status', $result['message'])
            ->with('payment_class', $class);
    }

    public function signupNewsletter(): ResponseInterface
    {
        $email = (string) esc($this->request->getPost('email'));

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->publicService->processSignup($email, 'newsletter');
            return redirect()->back()->with('status', 'Thank you for signing up!');
        }

        return redirect()->back()->with('error', 'Please provide a valid email.');
    }
}
