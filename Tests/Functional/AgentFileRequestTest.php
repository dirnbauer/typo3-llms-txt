<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * Requests both agent files against a real site and page tree — the
 * middleware, the site reader and the surface detection together, with
 * EXT:abilities installed.
 */
final class AgentFileRequestTest extends AbstractAgentFileTestCase
{
    protected array $testExtensionsToLoad = [
        'webconsulting/typo3-abilities',
        'webconsulting/typo3-llms-txt',
    ];

    #[Test]
    public function llmsTxtListsTheVisiblePageTree(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode(), substr($body, 0, 3000));
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('noindex', $response->getHeaderLine('X-Robots-Tag'));

        // The structure llmstxt.org prescribes: H1, blockquote, H2 sections
        // of "- [name](url): notes" lists — and nothing else.
        self::assertStringStartsWith("# Vienna Camp\n\n> A camp about deliberate publishing.\n", $body);
        // The details paragraph comes before the first H2 and leads to agents.md.
        self::assertStringContainsString("\n\nHow agents can operate this site beyond reading it — its machine interfaces and ground rules — is described in [agents.md](http://localhost/agents.md).\n\n## ", $body);
        self::assertStringContainsString('## Program', $body);
        self::assertStringContainsString('- [Program](http://localhost/program): Talks and workshops.', $body);
        self::assertStringContainsString('- [Keynotes](http://localhost/program/keynotes): The opening talks.', $body);
        // seo_title wins over title, for the section heading as well.
        self::assertStringContainsString('## Buy tickets', $body);
        self::assertStringContainsString('- [Buy tickets](http://localhost/tickets)', $body);

        // What the site deliberately does not offer stays out.
        self::assertStringNotContainsString('Internal notes', $body);
        self::assertStringNotContainsString('Footer legal', $body);
        self::assertStringNotContainsString('Draft page', $body);
    }

    #[Test]
    public function agentsMdAdvertisesTheDetectedSurfaces(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/agents.md'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode(), substr($body, 0, 3000));
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));

        self::assertStringContainsString('# Vienna Camp — agent guide', $body);
        self::assertStringContainsString('[llms.txt](http://localhost/llms.txt)', $body);

        // EXT:abilities is installed: every projection is advertised, with
        // the REST base read from the extension configuration.
        self::assertStringContainsString('**Abilities registry**', $body);
        self::assertStringContainsString('`GET http://localhost/abilities/v1/abilities` lists them', $body);
        self::assertStringContainsString('`POST http://localhost/abilities/v1/abilities/{namespace}/{name}/run` executes one', $body);
        self::assertStringContainsString('`GET http://localhost/abilities/v1/catalog` over REST', $body);
        self::assertStringContainsString('`abilities:list`, `abilities:describe <ability>`, `abilities:run <ability>`', $body);
        self::assertStringContainsString('Registered abilities (MCP tool name', $body);
        self::assertMatchesRegularExpression('/^ {4}- `ability_[a-z0-9_-]+` — .+ \((low|medium|high|critical) risk\): /mu', $body);

        // EXT:seo is installed, the MCP server is not.
        self::assertStringContainsString('**Sitemap**: http://localhost/sitemap.xml', $body);
        self::assertStringNotContainsString('**MCP server**', $body);

        self::assertStringContainsString('## Ground rules', $body);
    }

    #[Test]
    public function bothFilesCanBeSwitchedOffPerSite(): void
    {
        $this->writeCampSite(['llmsTxt' => ['enabled' => false]]);

        foreach (['llms.txt', 'agents.md'] as $file) {
            $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/' . $file));

            self::assertNotSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'), $file);
        }
    }

    #[Test]
    public function theDoktypeSettingWidensWhatIsPublished(): void
    {
        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'))->getBody();
        self::assertStringNotContainsString('Sponsors', $body);

        $this->writeCampSite(['llmsTxt' => ['doktypes' => '1,137']]);

        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'))->getBody();
        self::assertStringContainsString('- [Sponsors](http://localhost/program/sponsors): Who pays for it.', $body);
    }
}
