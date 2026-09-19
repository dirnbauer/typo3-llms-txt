<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Site;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Webconsulting\Abilities\Catalog\CapabilityCatalog;
use Webconsulting\Abilities\Http\RestConfiguration;
use Webconsulting\Abilities\Registry\AbilitiesRegistry;
use Webconsulting\LlmsTxt\Domain\AdvertisedAbility;
use Webconsulting\LlmsTxt\Domain\AgentSurfaces;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Detects which machine-operable surfaces this installation actually has —
 * agents.md only advertises what exists.
 *
 * Every integration is optional. The two abilities services below are
 * constructor arguments with a null default: when
 * webconsulting/typo3-abilities is not installed no service matches them,
 * the container passes the default, and the abilities block disappears
 * from agents.md. A registry therefore means "abilities is installed", a
 * capability catalogue means "abilities 1.1 or newer".
 */
final readonly class AgentSurfacesFactory
{
    public function __construct(
        private ExtensionConfiguration $extensionConfiguration,
        private ?AbilitiesRegistry $registry = null,
        private ?CapabilityCatalog $catalog = null,
    ) {}

    public function forSite(SiteProfile $profile): AgentSurfaces
    {
        // The MCP server and the abilities REST projection are mounted
        // before site resolution, at the installation root — not below the
        // site base.
        $origin = $profile->origin;

        return new AgentSurfaces(
            mcpEndpoint: $origin !== '' && ExtensionManagementUtility::isLoaded('mcp_server')
                ? $origin . '/mcp'
                : null,
            abilities: $this->abilities(),
            abilitiesRestBase: $this->abilitiesRestBase($origin),
            abilitiesCatalog: $this->catalog !== null,
            sitemapUrl: ExtensionManagementUtility::isLoaded('seo')
                ? $profile->urlFor('sitemap.xml')
                : null,
            paidContent: ExtensionManagementUtility::isLoaded('x402_paywall'),
        );
    }

    /**
     * Abilities 1.0 keeps RestConfiguration out of the container, so its
     * settings are read through its own factory rather than injected.
     */
    private function abilitiesRestBase(string $origin): ?string
    {
        if ($this->registry === null || $origin === '') {
            return null;
        }

        $rest = RestConfiguration::fromExtensionConfiguration($this->extensionConfiguration);

        return $rest->enabled ? $origin . $rest->basePath : null;
    }

    /**
     * @return list<AdvertisedAbility>
     */
    private function abilities(): array
    {
        $abilities = [];
        foreach ($this->registry?->getDefinitions(surface: 'mcp') ?? [] as $definition) {
            $abilities[] = new AdvertisedAbility(
                toolName: $definition->mcpToolName(),
                title: $definition->title,
                description: $definition->description,
                riskTier: $definition->riskTier->value,
            );
        }

        return $abilities;
    }
}
