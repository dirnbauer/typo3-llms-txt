<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

final readonly class PageLink
{
    public function __construct(
        public string $title,
        public string $url,
        public string $description = '',
    ) {
    }
}
