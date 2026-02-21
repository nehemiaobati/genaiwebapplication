<?php

declare(strict_types=1);

namespace App\Modules\Documentation\Controllers;

use App\Controllers\BaseController;

/**
 * DocumentationController
 * 
 * Handles the display of platform documentation, user guides, and architecture overviews.
 */
class DocumentationController extends BaseController
{
    /**
     * Renders the main documentation landing page.
     *
     * @return string The rendered HTML view.
     */
    public function index(): string
    {
        $data = [
            'pageTitle'       => 'Platform Documentation | User Guides & Resources',
            'metaDescription' => 'Learn how to use the GenAI Web Platform to generate content, analyze documents, and access crypto market insights without writing code.',
            'canonicalUrl'    => url_to('documentation'), // Ensure route name exists
            'robotsTag'       => 'index, follow', // CHANGED: Allow indexing
        ];
        return view('App\Modules\Documentation\Views\index', $data);
    }

    /**
     * Renders the web platform architecture and guide.
     *
     * @return string The rendered HTML view.
     */
    public function web(): string
    {
        $data = [
            'pageTitle'       => 'Web Platform Guide |  Architecture',
            'metaDescription' => 'Detailed overview of the platform architecture and features for users and administrators.',
            'canonicalUrl'    => url_to('documentation.web'),
            'robotsTag'       => 'index, follow', // CHANGED
        ];
        return view('App\Modules\Documentation\Views\web_documentation', $data);
    }

    /**
     * Renders the AI tools and Gemini architecture guide.
     *
     * @return string The rendered HTML view.
     */
    public function agi(): string
    {
        $data = [
            'pageTitle'       => 'AI Tools Guide | Architecture',
            'metaDescription' => 'How to leverage our Gemini-powered AI tools for text generation, document analysis, and TTS solutions.',
            'canonicalUrl'    => url_to('documentation.agi'),
            'robotsTag'       => 'index, follow', // CHANGED
        ];
        return view('App\Modules\Documentation\Views\agi_documentation', $data);
    }
}
