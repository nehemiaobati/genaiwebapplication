<?php declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SitemapController extends BaseController
{
    /**
     * Generates the sitemap.xml file by rendering a view.
     */
    public function index(): ResponseInterface
    {
        // Array of public-facing, indexable static pages with their priority
        $pagePriorities = [
            'landing'            => '1.0',
            'portfolio.index'    => '0.8',
            'blog.index'         => '0.8',
            'documentation'      => '0.8',
            'documentation.agi'  => '0.7',
            'documentation.web'  => '0.7',
            'contact.form'       => '0.7',
            'register'           => '0.6',
            'login'              => '0.5',
            'terms'              => '0.4',
            'privacy'            => '0.4',
        ];

        $urls = [];
        $today = date('Y-m-d');

        foreach ($pagePriorities as $page => $priority) {
            $generatedUrl = url_to($page);

            if ($generatedUrl) {
                $urls[] = [
                    'loc'        => $generatedUrl,
                    'lastmod'    => $today,
                    'changefreq' => ($priority >= '0.8') ? 'weekly' : 'monthly',
                    'priority'   => $priority,
                ];
            } else {
                log_message('error', 'Sitemap: Failed to generate URL for route - ' . $page);
            }
        }

        $response = $this->response ?? service('response');

        // If no URLs were generated, return an error
        if (empty($urls)) {
            $response->setStatusCode(500);
            $response->setBody('Error: No valid URLs could be generated for the sitemap.');
            return $response;
        }

        // Set the correct Content-Type header for an XML sitemap
        $response->setHeader('Content-Type', 'application/xml');

        // Pass the array of URLs to the sitemap view
        $viewContent = view('sitemap/index', ['urls' => $urls]);

        // Prepend the XML declaration and set the final body
        $sitemapContent = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" . $viewContent;
        $response->setBody($sitemapContent);

        return $response;
    }
}
