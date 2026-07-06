<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

final readonly class Section
{
    /**
     * @param list<PageLink> $links
     */
    public function __construct(
        public string $title,
        public array $links,
    ) {
    }
}
