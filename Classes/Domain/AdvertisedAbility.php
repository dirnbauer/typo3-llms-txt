<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

/**
 * One ability of the abilities registry as agents.md lists it.
 */
final readonly class AdvertisedAbility
{
    public function __construct(
        /** The MCP tool name, "ability_<namespace>_<name>" */
        public string $toolName,
        public string $title,
        public string $description,
        /** "low", "medium", "high" or "critical" */
        public string $riskTier,
    ) {}
}
