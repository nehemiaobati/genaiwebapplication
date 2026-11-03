<?php

declare(strict_types=1);

namespace App\Modules\Portfolio\Libraries;

use Config\Email;

/**
 * Class PortfolioService
 *
 * Handles portfolio inquiries, HTML formatting, and email dispatching.
 *
 * @package App\Modules\Portfolio\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class PortfolioService
{
    /**
     * Target recipient email for portfolio inquiries.
     *
     * @var string
     */
    protected string $recipientEmail = 'nehemiahobati@gmail.com';

    /**
     * Sends a contact email from the portfolio inquiry form.
     *
     * @param array{name: string, email: string, subject: string, message: string} $data Form input payload.
     *
     * @return bool
     */
    public function sendPortfolioInquiry(array $data): bool
    {
        $emailService = service('email');
        /** @var Email $config */
        $config    = config('Email');
        $fromEmail = $config->fromEmail;
        $fromName  = $config->fromName;

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($this->recipientEmail);
        $emailService->setReplyTo($data['email']);
        $emailService->setSubject($data['subject']);

        $htmlBody = '<html><body>'
            . '<p><strong>Name:</strong> ' . (string) esc($data['name'], 'html') . '</p>'
            . '<p><strong>Email:</strong> ' . (string) esc($data['email'], 'html') . '</p>'
            . '<p><strong>Subject:</strong> ' . (string) esc($data['subject'], 'html') . '</p>'
            . '<p><strong>Message:</strong></p>'
            . '<p>' . nl2br((string) esc($data['message'], 'html')) . '</p>'
            . '</body></html>';

        $emailService->setMessage($htmlBody);
        $emailService->setMailType('html');

        if ($emailService->send()) {
            return true;
        }

        $debuggerData = $emailService->printDebugger(['headers']);
        log_message('error', '[PortfolioService] Email sending failed: ' . print_r($debuggerData, true));

        return false;
    }
}
