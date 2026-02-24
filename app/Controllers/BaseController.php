<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    /**
     * The request object.
     * @var IncomingRequest|CLIRequest|RequestInterface
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically.
     * @var array
     */
    protected $helpers = [];

    /**
     * The CodeIgniter session service.
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = service('session');
    }

    public function acceptCookie()
    {
        // 1 year expiration
        $expires = 365 * 24 * 60 * 60;

        // Must use $this->response (the controller's own response object) so the
        // Set-Cookie header is included in the same response that returns the JSON.
        // Using service('response') returns a different instance and the cookie header is lost.
        $this->response->setCookie(
            'user_cookie_consent',
            'accepted',
            $expires
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Cookie consent accepted',
            'csrf_token' => csrf_hash()
        ]);
    }
}
