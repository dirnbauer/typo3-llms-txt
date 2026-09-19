<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

/**
 * The machine-operable surfaces of this installation, as advertised in
 * agents.md. Each field is null/empty/false when the corresponding
 * integration is not installed.
 */
final readonly class AgentSurfaces
{
    /**
     * @param list<AdvertisedAbility> $abilities
     * @param ?string $abilitiesRestBase Absolute URL of the abilities REST projection, e.g. "https://example.org/abilities/v1"
     */
    public function __construct(
        public ?string $mcpEndpoint = null,
        public array $abilities = [],
        public ?string $abilitiesRestBase = null,
        public bool $abilitiesCatalog = false,
        public ?string $sitemapUrl = null,
        public bool $paidContent = false,
    ) {}

    public function hasAbilities(): bool
    {
        return $this->abilities !== [] || $this->abilitiesRestBase !== null || $this->abilitiesCatalog;
    }
}
