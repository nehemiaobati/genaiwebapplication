<?php

declare(strict_types=1);

namespace App\Modules\Contact\Controllers;

use App\Controllers\BaseController;
use App\Modules\Contact\Libraries\ContactService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class ContactController
 *
 * Orchestrates incoming contact form requests and responses.
 *
 * @package App\Modules\Contact\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class ContactController extends BaseController
{
    /**
     * Contact Service instance.
     *
     * @var ContactService
     */
    protected ContactService $contactService;

    /**
     * Helpers to be loaded.
     *
     * @var array<int, string>
     */
    protected $helpers = ['form'];

    /**
     * ContactController constructor.
     */
    public function __construct()
    {
        /** @var ContactService $service */
        $service = service('contactService');
        $this->contactService = $service;
    }

    /**
     * Renders the contact form view.
     *
     * @return string
     */
    public function form(): string
    {
        $schemaJson = [
            '@context'   => 'https://schema.org',
            '@type'      => 'ContactPage',
            'name'       => 'Contact AFRIKENKID Studio',
            'url'        => url_to('contact.form'),
            'mainEntity' => [
                '@type'        => 'Organization',
                '@id'          => base_url('#organization'),
                'name'         => 'AFRIKENKID Studio',
                'url'          => base_url(),
                'contactPoint' => [
                    '@type'             => 'ContactPoint',
                    'contactType'       => 'Customer Support',
                    'availableLanguage' => ['English', 'Swahili'],
                    'url'               => url_to('contact.form'),
                ],
            ],
        ];

        $data = [
            'pageTitle'       => 'Contact AFRIKENKID Studio | Software & AI Solutions',
            'metaDescription' => 'Get in touch with AFRIKENKID Studio. Inquire about custom generative AI systems, full-stack web applications, technical consulting, and architecture audits.',
            'metaKeywords'    => 'Custom Software Solutions, software development, Contact Afrikenkid, AI Consulting',
            'canonicalUrl'    => url_to('contact.form'),
            'robotsTag'       => 'index, follow',
            'schemaJson'      => $schemaJson,
        ];

        return view('App\Modules\Contact\Views\contact_form', $data);
    }

    /**
     * Handles submission of the contact form.
     *
     * @return RedirectResponse
     */
    public function send(): RedirectResponse
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
            return redirect()->back()->withInput()->with('error', 'reCAPTCHA verification failed. Please try again.');
        }

        $name    = (string) ($this->request->getPost('name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email   = (string) ($this->request->getPost('email', FILTER_SANITIZE_EMAIL) ?? '');
        $subject = (string) ($this->request->getPost('subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $message = (string) ($this->request->getPost('message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

        if ($this->contactService->sendContactEmail($name, $email, $subject, $message)) {
            session()->setFlashdata('warning', 'Your message has been sent. Please note that email delivery may experience slight delays.');

            return redirect()->back()->with('success', 'Your message has been sent successfully!');
        }

        return redirect()->back()->with('error', 'Failed to send your message. Please try again later.');
    }
}
