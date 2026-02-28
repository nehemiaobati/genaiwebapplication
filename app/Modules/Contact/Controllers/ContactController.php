<?php

declare(strict_types=1);

namespace App\Modules\Contact\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

helper('form');

class ContactController extends BaseController
{
    public function form(): string
    {
        $data = [
            'pageTitle' => 'Contact Us | Afrikenkid',
            'metaDescription' => 'Get in touch with our team for support, enterprise inquiries, or custom AI development projects.',
            'canonicalUrl' => url_to('contact.form'),
            'robotsTag'    => 'index, follow',
        ];
        return view('App\Modules\Contact\Views\contact_form', $data);
    }

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

        // Get reCAPTCHA response from the form submission.
        $recaptchaResponse = $this->request->getPost('g-recaptcha-response');

        // Instantiate the RecaptchaService.
        $recaptchaService = service('recaptchaService');

        // Verify the reCAPTCHA response.
        if (! $recaptchaService->verify($recaptchaResponse)) {
            // If reCAPTCHA verification fails, add a validation error and redirect back.
            return redirect()->back()->withInput()->with('error', 'reCAPTCHA verification failed. Please try again.');
        }

        // Get raw POST data. Sanitization will happen at the point of output.
        $name    = (string) ($this->request->getPost('name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email   = (string) ($this->request->getPost('email', FILTER_SANITIZE_EMAIL) ?? '');
        $subject = (string) ($this->request->getPost('subject', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $message = (string) ($this->request->getPost('message', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

        $emailService = service('email');

        // Use config values which should fallback to .env
        $fromEmail = config('Email')->fromEmail;
        $fromName  = config('Email')->fromName;

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo('nehemiahobati@gmail.com'); // Admin email
        $emailService->setReplyTo($email); // User's email as reply-to
        $emailService->setSubject($subject);

        // --- FIX START: Construct an HTML email body ---
        // We build an HTML string for proper formatting and escape all user input
        // to prevent XSS attacks.
        $emailContent = "
        <html>
        <body>
            <p><strong>Name:</strong> " . (string) esc($name) . "</p>
            <p><strong>Email:</strong> " . (string) esc($email) . "</p>
            <p><strong>Subject:</strong> " . (string) esc($subject) . "</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br((string) esc($message)) . "</p>
        </body>
        </html>";

        $emailService->setMessage($emailContent);
        // Ensure the email client renders this as HTML
        $emailService->setMailType('html');
        // --- FIX END ---

        if ($emailService->send()) {
            session()->setFlashdata('warning', 'Your message has been sent. Please note that email delivery may experience slight delays.');
            return redirect()->back()->with('success', 'Your message has been sent successfully!');
        }

        // Detailed error logging
        $debuggerData = $emailService->printDebugger(['headers']);
        log_message('error', '[ContactController] Email sending failed: ' . print_r($debuggerData, true));
        log_message('error', '[ContactController] SMTP Host: ' . config('Email')->SMTPHost);

        return redirect()->back()->with('error', 'Failed to send your message. Please try again later.');
    }
}
