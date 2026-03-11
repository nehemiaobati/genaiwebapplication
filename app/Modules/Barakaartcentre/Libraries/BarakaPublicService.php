<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Libraries;

use App\Modules\Barakaartcentre\Models\ArtworkModel;
use App\Modules\Barakaartcentre\Models\ServiceModel;
use App\Modules\Barakaartcentre\Models\WorkshopModel;
use App\Modules\Barakaartcentre\Models\SignupModel;
use App\Modules\Barakaartcentre\Entities\Signup;

/**
 * Class BarakaPublicService
 * Handles all business logic and data retrieval for the public-facing Baraka Art Centre portal.
 */
class BarakaPublicService
{
    protected ArtworkModel $artworkModel;
    protected ServiceModel $serviceModel;
    protected WorkshopModel $workshopModel;
    protected SignupModel $signupModel;

    public function __construct()
    {
        $this->artworkModel = new ArtworkModel();
        $this->serviceModel = new ServiceModel();
        $this->workshopModel = new WorkshopModel();
        $this->signupModel = new SignupModel();
    }

    /**
     * Fetches all artworks (for the gallery).
     * @return array
     */
    public function getAllArtworks(): array
    {
        return $this->artworkModel->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Fetches a limited number of featured artworks for the homepage.
     * @param int $limit
     * @return array
     */
    public function getFeaturedArtworks(int $limit = 6): array
    {
        return $this->artworkModel->where('is_sold', false)->orderBy('created_at', 'DESC')->findAll($limit);
    }

    /**
     * Fetches all registered services.
     * @return array
     */
    public function getAllServices(): array
    {
        return $this->serviceModel->orderBy('type', 'ASC')->orderBy('title', 'ASC')->findAll();
    }

    /**
     * Fetches upcoming workshops.
     * @return array
     */
    public function getUpcomingWorkshops(): array
    {
        return $this->workshopModel->where('event_date >=', date('Y-m-d'))->orderBy('event_date', 'ASC')->findAll();
    }

    /**
     * Processes a newsletter signup.
     * @param string $email
     * @param string $source
     * @return bool
     */
    public function processSignup(string $email, string $source = 'newsletter'): bool
    {
        $existing = $this->signupModel->where('email', $email)->first();
        if ($existing) {
            return true; // Already signed up, act like success to avoid enumeration
        }

        $signup = new Signup([
            'email'  => $email,
            'source' => $source
        ]);

        return $this->signupModel->insert($signup) !== false;
    }

    /**
     * Simulates an M-Pesa STK Push.
     * @param string $phone
     * @param float $amount
     * @return array
     */
    public function simulateMpesaPayment(string $phone, float $amount): array
    {
        // Basic Safaricom format validation
        if (preg_match('/^(?:254|\+254|0)?([71](?:(?:0[0-8])|(?:[12][0-9])|(?:4[0-35-9])|(?:5[7-9])|(?:6[89])|(?:9[0-9]))[0-9]{6})$/', $phone)) {
            return [
                'status' => true,
                'message' => "Kachiri! STK Push sent to $phone for KES $amount. Tafadhali weka M-Pesa PIN yako to empower a creator."
            ];
        }

        return [
            'status' => false,
            'message' => "Namba sio sahihi. Please enter a valid Kenyan Safaricom number."
        ];
    }
}
