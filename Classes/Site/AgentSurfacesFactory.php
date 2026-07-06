<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Site;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\LlmsTxt\Domain\AgentSurfaces;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Detects which machine-operable surfaces this installation actually has.
 * Every integration is optional — agents.md only advertises what exists.
 */
class AgentSurfacesFactory
{
    public function forSite(SiteProfile $profile): AgentSurfaces
    {
        return new AgentSurfaces(
            mcpEndpoint: $this->mcpEndpoint($profile),
            abilities: $this->abilities(),
            sitemapUrl: ExtensionManagementUtility::isLoaded('seo')
                ? $profile->urlFor('sitemap.xml')
                : null,
            paidContent: ExtensionManagementUtility::isLoaded('x402_paywall'),
        );
    }

    private function mcpEndpoint(SiteProfile $profile): ?string
    {
        if (!ExtensionManagementUtility::isLoaded('mcp_server') || $profile->origin === '') {
            return null;
        }

        // The MCP server listens at the installation root, not the site base.
        return $profile->origin . '/mcp';
    }

    /**
     * @return list<array{name: string, title: string, description: string, risk: string}>
     */
    private function abilities(): array
    {
        if (!class_exists(\Webconsulting\Abilities\Registry\AbilitiesRegistry::class)) {
            return [];
        }

        $registry = GeneralUtility::makeInstance(\Webconsulting\Abilities\Registry\AbilitiesRegistry::class);

        $abilities = [];
        foreach ($registry->getDefinitions() as $definition) {
            if (!$definition->isExposedTo('mcp')) {
                continue;
            }
            $abilities[] = [
                'name' => $definition->mcpToolName(),
                'title' => $definition->title,
                'description' => $definition->description,
                'risk' => $definition->riskTier->value,
            ];
        }

        return $abilities;
    }
}
