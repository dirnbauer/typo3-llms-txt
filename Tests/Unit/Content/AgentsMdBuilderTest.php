<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Unit\Content;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\LlmsTxt\Content\AgentsMdBuilder;
use Webconsulting\LlmsTxt\Domain\AgentSurfaces;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

final class AgentsMdBuilderTest extends TestCase
{
    private AgentsMdBuilder $builder;

    private SiteProfile $profile;

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
            abilities: [[
                'name' => 'ability_system_site-info',
                'title' => 'Site info',
                'description' => 'Lists the configured sites.',
                'risk' => 'low',
            ]],
            abilitiesRestBase: 'https://example.org/abilities/v1',
            sitemapUrl: 'https://example.org/camp/sitemap.xml',
            paidContent: true,
        ));

        self::assertStringContainsString('# Vienna Camp — agent guide', $output);
        self::assertStringContainsString('[llms.txt](https://example.org/camp/llms.txt)', $output);
        self::assertStringContainsString('**MCP server**: `https://example.org/mcp`', $output);
        self::assertStringContainsString('`ability_<namespace>_<name>` tools', $output);
        self::assertStringContainsString('`GET https://example.org/abilities/v1/abilities` lists them', $output);
        self::assertStringContainsString('`POST https://example.org/abilities/v1/abilities/{namespace}/{name}/run` executes one', $output);
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
        self::assertStringNotContainsString('Registered abilities', $output);
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
}
