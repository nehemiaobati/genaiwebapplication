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
        $schemaJson = [
            '@context'   => 'https://schema.org',
            '@type'      => 'CollectionPage',
            '@id'        => url_to('documentation') . '#webpage',
            'url'        => url_to('documentation'),
            'name'       => 'AFRIKENKID Platform Documentation Hub',
            'breadcrumb' => [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type'    => 'ListItem',
                        'position' => 1,
                        'name'     => 'Home',
                        'item'     => url_to('landing'),
                    ],
                    [
                        '@type'    => 'ListItem',
                        'position' => 2,
                        'name'     => 'Documentation',
                        'item'     => url_to('documentation'),
                    ],
                ],
            ],
        ];

        $data = [
            'pageTitle'       => 'Documentation Hub — AI Models & Architecture | AFRIKENKID',
            'metaDescription' => 'Developer documentation for AFRIKENKID Studio: Integration guides for multimodal LLMs, Ollama local inference, blockchain verification, and custom APIs.',
            'metaKeywords'    => 'Generative AI Documentation, Models Guide, Blockchain Docs, Custom Software Architecture',
            'canonicalUrl'    => url_to('documentation'),
            'robotsTag'       => 'index, follow',
            'schemaJson'      => $schemaJson,
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
        $schemaJson = [
            '@context'  => 'https://schema.org',
            '@type'     => 'TechArticle',
            'headline'  => 'Platform Architecture and Custom Software Solutions Guide',
            'url'       => url_to('documentation.web'),
            'author'    => ['@id' => base_url('#organization')],
            'publisher' => ['@id' => base_url('#organization')],
            'inLanguage' => 'en',
        ];

        $data = [
            'pageTitle'       => 'Platform Architecture & Systems Guide | AFRIKENKID Docs',
            'metaDescription' => 'Deep dive into the AFRIKENKID web application architecture, CodeIgniter 4 framework patterns, database schemas, and microservice integration workflows.',
            'metaKeywords'    => 'Software Solutions Guide, Architecture, CodeIgniter 4 Development',
            'canonicalUrl'    => url_to('documentation.web'),
            'robotsTag'       => 'index, follow',
            'schemaJson'      => $schemaJson,
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
        $schemaJson = [
            '@context'   => 'https://schema.org',
            '@type'      => 'TechArticle',
            'headline'   => 'Generative Models and AI Studio Implementation Guide',
            'url'        => url_to('documentation.agi'),
            'author'     => ['@id' => base_url('#organization')],
            'publisher'  => ['@id' => base_url('#organization')],
            'inLanguage' => 'en',
        ];

        $data = [
            'pageTitle'       => 'Generative Models & LLM Studio Guide | AFRIKENKID Docs',
            'metaDescription' => 'Learn how to configure Google Gemini, route via OpenRouter, and deploy private Ollama LLM inference with vector memory inside AFRIKENKID Generative AI Studio.',
            'metaKeywords'    => 'Generative Models Guide, Gemini AI Studio, OpenRouter Hub, Ollama Private LLM',
            'canonicalUrl'    => url_to('documentation.agi'),
            'robotsTag'       => 'index, follow',
            'schemaJson'      => $schemaJson,
        ];
        return view('App\Modules\Documentation\Views\agi_documentation', $data);
    }
}
