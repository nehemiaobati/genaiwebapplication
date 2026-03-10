<?php

declare(strict_types=1);

namespace App\Modules\Barakaartcentre\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class BarakaartcentreController extends BaseController
{
    /**
     * Renders the Baraka Art Centre portal and handles M-Pesa simulation.
     */
    public function index(): string|ResponseInterface
    {
        $status = session()->getFlashdata('status');
        $class = session()->getFlashdata('class');

        // M-Pesa STK Push Simulation Logic
        if ($this->request->is('post')) {
            $phone = (string) esc($this->request->getPost('mpesa_phone'));
            $amount = (string) esc($this->request->getPost('amount'));

            // Basic Safaricom format validation (07xx / 01xx / 254...)
            if (preg_match('/^(?:254|\+254|0)?([71](?:(?:0[0-8])|(?:[12][0-9])|(?:4[0-35-9])|(?:5[7-9])|(?:6[89])|(?:9[0-9]))[0-9]{6})$/', $phone)) {
                $status = "Kachiri! STK Push sent to $phone for KES $amount. Tafadhali weka M-Pesa PIN yako to empower a creator.";
                $class = 'success';
            } else {
                $status = "Namba sio sahihi. Please enter a valid Kenyan Safaricom number.";
                $class = 'error';
            }

            return redirect()->back()->with('status', $status)->with('class', $class);
        }

        // Standardized SEO Data
        $data = [
            'pageTitle'       => 'Baraka Art Centre | Where Art Meets Science',
            'metaDescription' => 'Empowering communities in Mombasa, Kenya through STEAM education, culture, and sustainable art. Join the Baraka Art Centre innovation lab.',
            'canonicalUrl'    => base_url('baraka-art-centre'),
            'robotsTag'       => 'noindex, follow',
            'metaImage'       => base_url('assets/images/baraka_logo.png'), 
            'payment_status'  => $status,
            'payment_class'   => $class,
        ];

        return view('App\Modules\Barakaartcentre\Views\index', $data);
    }
}
