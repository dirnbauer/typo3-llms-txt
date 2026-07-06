<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

/**
 * Everything the markdown builders need to know about a site,
 * decoupled from TYPO3's Site object for testability.
 */
final readonly class SiteProfile
{
    public function __construct(
        public string $title,
        public string $description,
        /** Site base URL without trailing slash, e.g. "https://example.org/blog" */
        public string $baseUrl,
        /** Scheme + host of the installation, e.g. "https://example.org" */
        public string $origin,
    ) {
    }

    public function urlFor(string $slug): string
    {
        return $this->baseUrl . '/' . ltrim($slug, '/');
    }
}
