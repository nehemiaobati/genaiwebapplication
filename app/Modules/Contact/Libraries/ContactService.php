<?php

declare(strict_types=1);

namespace App\Modules\Contact\Libraries;

use Config\Email;

/**
 * Class ContactService
 *
 * Handles business logic, email generation, and dispatching for contact form messages.
 *
 * @package App\Modules\Contact\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class ContactService
{
    /**
     * Admin recipient email address.
     *
     * @var string
     */
    protected string $adminEmail = 'nehemiahobati@gmail.com';

    /**
     * Sends a contact email to the platform administrator.
     *
     * @param string $name    User's name.
     * @param string $email   User's email.
     * @param string $subject Message subject.
     * @param string $message Message body content.
     *
     * @return bool True if message dispatched successfully, false otherwise.
     */
    public function sendContactEmail(string $name, string $email, string $subject, string $message): bool
    {
        $emailService = service('email');
        /** @var Email $config */
        $config    = config('Email');
        $fromEmail = $config->fromEmail;
        $fromName  = $config->fromName;

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($this->adminEmail);
        $emailService->setReplyTo($email);
        $emailService->setSubject($subject);

        $htmlBody = '<html><body>'
            . '<p><strong>Name:</strong> ' . (string) esc($name, 'html') . '</p>'
            . '<p><strong>Email:</strong> ' . (string) esc($email, 'html') . '</p>'
            . '<p><strong>Subject:</strong> ' . (string) esc($subject, 'html') . '</p>'
            . '<p><strong>Message:</strong></p>'
            . '<p>' . nl2br((string) esc($message, 'html')) . '</p>'
            . '</body></html>';

        $emailService->setMessage($htmlBody);
        $emailService->setMailType('html');

        if ($emailService->send()) {
            return true;
        }

        $debuggerData = $emailService->printDebugger(['headers']);
        log_message('error', '[ContactService] Email sending failed: ' . print_r($debuggerData, true));

        return false;
    }
}
