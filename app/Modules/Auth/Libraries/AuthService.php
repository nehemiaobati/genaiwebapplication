<?php

declare(strict_types=1);

namespace App\Modules\Auth\Libraries;

use App\Models\UserModel;
use App\Entities\User;
use CodeIgniter\I18n\Time;
use Config\Email;

/**
 * Class AuthService
 *
 * Handles business logic for User Authentication, Registration, and Password Management.
 *
 * @package App\Modules\Auth\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class AuthService
{
    /**
     * User Model instance.
     *
     * @var UserModel
     */
    protected UserModel $userModel;

    /**
     * AuthService constructor.
     *
     * @param UserModel|null $userModel Optional injected UserModel.
     */
    public function __construct(?UserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
    }

    // --- Helper Methods ---

    /**
     * Sends the verification email.
     *
     * @param User $user The user entity.
     *
     * @return bool
     */
    private function _sendVerificationEmail(User $user): bool
    {
        $emailService = service('email');
        /** @var Email $config */
        $config    = config('Email');
        $fromEmail = $config->fromEmail;
        $fromName  = $config->fromName;

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($user->email);
        $emailService->setReplyTo($fromEmail);
        $emailService->setSubject('Email Verification');

        $verificationLink = url_to('verify_email', $user->verification_token);

        $message = view('App\Modules\Auth\Views\emails\verification_email', [
            'name'             => $user->username,
            'verificationLink' => $verificationLink,
        ]);

        $emailService->setMessage($message);

        if ($emailService->send()) {
            return true;
        }

        log_message('error', "[AuthService] Registration email sending failed for {$user->email}: " . print_r($emailService->printDebugger(['headers']), true));

        return false;
    }

    /**
     * Sends the password reset email.
     *
     * @param User $user The user entity.
     *
     * @return bool
     */
    private function _sendPasswordResetEmail(User $user): bool
    {
        $emailService = service('email');
        /** @var Email $config */
        $config    = config('Email');
        $fromEmail = $config->fromEmail;
        $fromName  = $config->fromName;

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($user->email);
        $emailService->setReplyTo($fromEmail);
        $emailService->setSubject('Password Reset Request');

        $resetLink = url_to('auth.reset_password', $user->reset_token);

        $message = view('App\Modules\Auth\Views\emails\reset_password_email', [
            'name'      => $user->username,
            'resetLink' => $resetLink,
        ]);

        $emailService->setMessage($message);

        if ($emailService->send()) {
            return true;
        }

        log_message('error', "[AuthService] Password reset email sending failed for {$user->email}: " . print_r($emailService->printDebugger(['headers']), true));

        return false;
    }

    // --- Public Methods ---

    /**
     * Registers a new user and sends the verification email.
     *
     * @param array{username: string, email: string, password: string, marketing?: mixed} $data Input payload.
     *
     * @return array{success: bool, message: string}
     */
    public function register(array $data): array
    {
        $this->userModel->db->transStart();

        $user = new User();
        $user->username           = $data['username'];
        $user->email              = $data['email'];
        $user->password           = password_hash($data['password'], PASSWORD_DEFAULT);
        $user->verification_token = bin2hex(random_bytes(50));

        $optIn                   = ! empty($data['marketing']);
        $user->marketing_opt_in  = $optIn ? 1 : 0;
        $user->unsubscribe_token = $optIn ? bin2hex(random_bytes(32)) : null;

        if (! $this->_sendVerificationEmail($user)) {
            $this->userModel->db->transRollback();

            return ['success' => false, 'message' => 'Registration failed. Could not send verification email.'];
        }

        if (! $this->userModel->save($user)) {
            $this->userModel->db->transRollback();

            return ['success' => false, 'message' => 'Registration failed. Database error.'];
        }

        $this->userModel->db->transComplete();

        if ($this->userModel->db->transStatus() === false) {
            return ['success' => false, 'message' => 'Registration failed. Transaction error.'];
        }

        return ['success' => true, 'message' => 'Registration successful. Please check your email to verify your account.'];
    }

    /**
     * Verifies a user's email address.
     *
     * @param string $token Verification token.
     *
     * @return bool
     */
    public function verifyEmail(string $token): bool
    {
        $this->userModel->db->transStart();

        /** @var User|null $user */
        $user = $this->userModel->where('verification_token', $token)->first();

        if ($user !== null) {
            $user->is_verified        = true;
            $user->verification_token = null;
            $this->userModel->save($user);
        }

        $this->userModel->db->transComplete();

        return (bool) $this->userModel->db->transStatus();
    }

    /**
     * Authenticates a user.
     *
     * @param string $email    User email.
     * @param string $password Raw password.
     *
     * @return array{success: bool, user: ?User, message: string}
     */
    public function authenticate(string $email, string $password): array
    {
        /** @var User|null $user */
        $user = $this->userModel->where('email', $email)->first();

        if ($user === null || ! password_verify($password, (string) $user->password)) {
            return ['success' => false, 'user' => null, 'message' => 'Invalid login credentials.'];
        }

        if (! (bool) $user->is_verified) {
            return ['success' => false, 'user' => null, 'message' => 'Please verify your email before logging in.'];
        }

        return ['success' => true, 'user' => $user, 'message' => 'Login Successful'];
    }

    /**
     * Sends a password reset link.
     *
     * @param string $email User email.
     *
     * @return array{success: bool, message: string}
     */
    public function sendResetLink(string $email): array
    {
        $this->userModel->db->transStart();

        /** @var User|null $user */
        $user = $this->userModel->where('email', $email)->first();

        if ($user !== null) {
            $user->reset_token   = bin2hex(random_bytes(50));
            $user->reset_expires = Time::now()->addHours(1)->toDateTimeString();
            $this->userModel->save($user);

            if (! $this->_sendPasswordResetEmail($user)) {
                $this->userModel->db->transRollback();

                return ['success' => false, 'message' => 'Could not send password reset email. Please try again later.'];
            }
        }

        $this->userModel->db->transComplete();

        return ['success' => true, 'message' => 'If a matching account was found, a password reset link has been sent to your email address.'];
    }

    /**
     * Validates a reset token.
     *
     * @param string $token Reset token.
     *
     * @return bool
     */
    public function validateResetToken(string $token): bool
    {
        /** @var User|null $user */
        $user = $this->userModel->where('reset_token', $token)->first();

        return $user !== null && $user->reset_expires !== null && Time::parse((string) $user->reset_expires)->isAfter(Time::now());
    }

    /**
     * Resets the user's password.
     *
     * @param string $token       Reset token.
     * @param string $newPassword New plaintext password.
     *
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $this->userModel->db->transStart();

        /** @var User|null $user */
        $user = $this->userModel->where('reset_token', $token)->first();

        if ($user === null || $user->reset_expires === null || Time::parse((string) $user->reset_expires)->isBefore(Time::now())) {
            $this->userModel->db->transRollback();

            return false;
        }

        $user->password      = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->reset_token   = null;
        $user->reset_expires = null;

        $this->userModel->save($user);

        $this->userModel->db->transComplete();

        return (bool) $this->userModel->db->transStatus();
    }
}
