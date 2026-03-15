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

    // --- CHECKOUT FLOW -----------------------------------------------------

    public function checkoutArtwork(int $id): string|ResponseInterface
    {
        $artwork = $this->publicService->getArtworkById($id);
        if (!$artwork || $artwork->is_sold) {
            return redirect()->route('baraka.gallery')->with('error', 'Artwork not available or not found.');
        }

        $data = $this->getBaseSeoData('Checkout Artwork', 'Secure your artwork commission from Baraka Art Centre.');
        $data['item'] = $artwork;
        $data['item_type'] = 'artwork';

        return view('App\Modules\Barakaartcentre\Views\public\checkout_artwork', $data);
    }

    public function checkoutWorkshop(int $id): string|ResponseInterface
    {
        $workshop = $this->publicService->getWorkshopById($id);
        if (!$workshop) {
            return redirect()->route('baraka.workshops')->with('error', 'Workshop not found.');
        }

        $data = $this->getBaseSeoData('Join Workshop', 'Register and join an upcoming mindfulness workshop.');
        $data['item'] = $workshop;
        $data['item_type'] = 'workshop';

        return view('App\Modules\Barakaartcentre\Views\public\checkout_workshop', $data);
    }

    public function processOrder(): ResponseInterface
    {
        $postData = $this->request->getPost();
        
        // Log attempt immediately as 'pending'
        $order = $this->publicService->createOrder($postData);

        if (!$order) {
            return redirect()->back()->withInput()->with('error', 'Failed to initialize order. Please try again.');
        }

        // Initialize Paystack
        $paystack = service('paystackService');
        $callbackUrl = base_url(route_to('baraka.payment.verify')) . '?order_ref=' . $order->order_reference;

        $response = $paystack->initializeTransaction(
            $order->email,
            (int) $order->amount,
            $callbackUrl
        );

        if ($response['status'] === true && isset($response['data']['authorization_url'])) {
            return redirect()->to($response['data']['authorization_url']);
        }

        return redirect()->back()->with('error', 'Payment gateway error: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function verifyPayment(): ResponseInterface
    {
        $orderRef = (string) $this->request->getGet('order_ref');
        $paystackRef = (string) $this->request->getGet('trxref'); // Paystack standard param

        if (!$orderRef || !$paystackRef) {
            return redirect()->route('baraka.home')->with('error', 'Invalid payment verification request.');
        }

        $result = $this->publicService->verifyOrderPayment($orderRef, $paystackRef);

        if ($result['status'] === true) {
            return redirect()->route('baraka.home')->with('status', 'Payment successful! Thank you for supporting Baraka Art Centre.');
        }

        return redirect()->route('baraka.home')->with('error', 'Payment verification failed: ' . $result['message']);
    }
}
