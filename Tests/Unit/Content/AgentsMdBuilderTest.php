<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Unit\Content;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\LlmsTxt\Content\AgentsMdBuilder;
use Webconsulting\LlmsTxt\Domain\AdvertisedAbility;
use Webconsulting\LlmsTxt\Domain\AgentSurfaces;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

final class AgentsMdBuilderTest extends TestCase
{
    private AgentsMdBuilder $builder;

    private SiteProfile $profile;

    #[\Override]
    protected function setUp(): void
    {
        $this->builder = new AgentsMdBuilder();
        $this->profile = new SiteProfile(
            title: 'Vienna Camp',
            description: 'A demo site.',
            baseUrl: 'https://example.org/camp',
            origin: 'https://example.org',
        );
    }

    #[Test]
    public function advertisesAllDetectedSurfaces(): void
    {
        $output = $this->builder->build($this->profile, new AgentSurfaces(
            mcpEndpoint: 'https://example.org/mcp',
            abilities: [new AdvertisedAbility(
                toolName: 'ability_system_site-info',
                title: 'Site info',
                description: 'Lists the configured sites.',
                riskTier: 'low',
            )],
            abilitiesRestBase: 'https://example.org/abilities/v1',
            abilitiesCatalog: true,
            sitemapUrl: 'https://example.org/camp/sitemap.xml',
            paidContent: true,
        ));

        self::assertStringContainsString('# Vienna Camp — agent guide', $output);
        self::assertStringContainsString('[llms.txt](https://example.org/camp/llms.txt)', $output);
        self::assertStringContainsString('**MCP server**: `https://example.org/mcp`', $output);
        self::assertStringContainsString('`ability_<namespace>_<name>` tools', $output);
        self::assertStringContainsString('`GET https://example.org/abilities/v1/abilities` lists them', $output);
        self::assertStringContainsString('`POST https://example.org/abilities/v1/abilities/{namespace}/{name}/run` executes one', $output);
        self::assertStringContainsString('`GET https://example.org/abilities/v1/catalog` over REST, `abilities:catalog` on the CLI', $output);
        self::assertStringContainsString('`abilities:list`, `abilities:describe <ability>`, `abilities:run <ability>`', $output);
        self::assertStringContainsString('`ability_system_site-info` — Site info (low risk): Lists the configured sites.', $output);
        self::assertStringContainsString('**Sitemap**: https://example.org/camp/sitemap.xml', $output);
        self::assertStringContainsString('x402 payment protocol', $output);
        self::assertStringContainsString('## Ground rules', $output);
    }

    #[Test]
    public function advertisesTheAbilityProjectionsWithoutAnMcpServer(): void
    {
        $output = $this->builder->build($this->profile, new AgentSurfaces(
            abilitiesRestBase: 'https://example.org/abilities/v1',
        ));

        self::assertStringNotContainsString('MCP server', $output);
        self::assertStringContainsString('**Abilities registry**', $output);
        self::assertStringContainsString('https://example.org/abilities/v1/abilities', $output);
        self::assertStringContainsString('`abilities:list`', $output);
        self::assertStringNotContainsString('Ability catalogue', $output);
        self::assertStringNotContainsString('Registered abilities', $output);
    }

    #[Test]
    public function advertisesTheCatalogueWithoutTheRestProjection(): void
    {
        $output = $this->builder->build($this->profile, new AgentSurfaces(abilitiesCatalog: true));

        self::assertStringContainsString('**Abilities registry**', $output);
        self::assertStringContainsString('annotations. `abilities:catalog` on the CLI, `ability_abilities_catalog` over MCP.', $output);
        self::assertStringNotContainsString('GET ', $output);
    }

    #[Test]
    public function omitsAbsentSurfaces(): void
    {
        $output = $this->builder->build($this->profile, new AgentSurfaces());

        self::assertStringNotContainsString('MCP server', $output);
        self::assertStringNotContainsString('Abilities registry', $output);
        self::assertStringNotContainsString('Sitemap', $output);
        self::assertStringNotContainsString('x402', $output);
        self::assertStringContainsString('Structured data', $output);
        self::assertStringContainsString('## Ground rules', $output);
    }

    #[Test]
    public function keepsEditorialTextOnOneLine(): void
    {
        $profile = new SiteProfile("Camp\n[2026]", "A demo\tsite.", 'https://example.org', 'https://example.org');

        $output = $this->builder->build($profile, new AgentSurfaces());

        self::assertStringContainsString('# Camp \\[2026\\] — agent guide', $output);
        self::assertStringContainsString("\nA demo site.\n", $output);
    }
}
