<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

/**
 * Everything the markdown builders need to know about one site language,
 * decoupled from TYPO3's Site objects for testability.
 */
final readonly class SiteProfile
{
    public function __construct(
        public string $title,
        public string $description,
        /** Base URL of the site language without trailing slash, e.g. "https://example.org/blog/de" */
        public string $baseUrl,
        /** Scheme + host of the installation, e.g. "https://example.org" */
        public string $origin,
    ) {}

    public function urlFor(string $slug): string
    {
        return $this->baseUrl . '/' . ltrim($slug, '/');
    }
}
