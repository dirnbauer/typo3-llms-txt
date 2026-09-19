<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * The same site without EXT:abilities. The surface detection injects the
 * abilities services optionally, so this is the test that proves the
 * container still builds and agents.md simply says less.
 */
final class AgentFileWithoutAbilitiesTest extends AbstractAgentFileTestCase
{
    #[Test]
    public function agentsMdDropsTheWholeAbilitiesBlock(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/agents.md'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode(), substr($body, 0, 3000));
        self::assertStringContainsString('# Vienna Camp — agent guide', $body);

        self::assertStringNotContainsString('Abilities registry', $body);
        self::assertStringNotContainsString('abilities:list', $body);
        self::assertStringNotContainsString('Capability catalogue', $body);
        self::assertStringNotContainsString('**MCP server**', $body);

        // What is installed is still advertised.
        self::assertStringContainsString('**Sitemap**: http://localhost/sitemap.xml', $body);
        self::assertStringContainsString('**Structured data**', $body);
        self::assertStringContainsString('## Ground rules', $body);
    }

    #[Test]
    public function llmsTxtIsUnaffected(): void
    {
        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'))->getBody();

        self::assertStringContainsString('- [Keynotes](http://localhost/program/keynotes): The opening talks.', $body);
    }
}
