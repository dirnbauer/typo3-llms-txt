<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

/**
 * The machine-operable surfaces of this installation, as advertised in
 * agents.md. Each field is null/empty when the corresponding integration
 * is not installed.
 */
final readonly class AgentSurfaces
{
    /**
     * @param list<array{name: string, title: string, description: string, risk: string}> $abilities
     */
    public function __construct(
        public ?string $mcpEndpoint = null,
        public array $abilities = [],
        public ?string $sitemapUrl = null,
        public bool $paidContent = false,
    ) {
    }
}
