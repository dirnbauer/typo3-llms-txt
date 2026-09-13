<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Requests both agent files against a real site and page tree: the
 * middleware, the page tree reader and the surface detection together.
 */
final class AgentFileRequestTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'seo',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/typo3-abilities',
        'webconsulting/typo3-llms-txt',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->writeSiteConfiguration('camp', [
            'rootPageId' => 1,
            'base' => 'http://localhost/',
            'websiteTitle' => 'Vienna Camp',
            'languages' => [
                [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                    'flag' => 'us',
                ],
            ],
        ]);
    }

    #[Test]
    public function llmsTxtListsTheVisiblePageTree(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'));
        $body = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode(), substr($body, 0, 3000));
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('noindex', $response->getHeaderLine('X-Robots-Tag'));

        self::assertStringContainsString('# Vienna Camp', $body);
        self::assertStringContainsString('> A camp about deliberate publishing.', $body);
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

        // EXT:abilities is installed: all three projections are advertised,
        // with the REST base read from the extension configuration.
        self::assertStringContainsString('**Abilities registry**', $body);
        self::assertStringContainsString('`GET http://localhost/abilities/v1/abilities` lists them', $body);
        self::assertStringContainsString('`POST http://localhost/abilities/v1/abilities/{namespace}/{name}/run` executes one', $body);
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
        $this->writeSiteConfiguration('camp', [
            'rootPageId' => 1,
            'base' => 'http://localhost/',
            'websiteTitle' => 'Vienna Camp',
            'settings' => ['llmsTxt' => ['enabled' => false]],
            'languages' => [
                [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                    'flag' => 'us',
                ],
            ],
        ]);

        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'));

        self::assertNotSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function writeSiteConfiguration(string $identifier, array $configuration): void
    {
        $path = Environment::getConfigPath() . '/sites/' . $identifier;
        GeneralUtility::mkdir_deep($path);
        GeneralUtility::writeFile($path . '/config.yaml', Yaml::dump($configuration, 99, 2), true);

        // Drop the cached site set so the next resolution reads the file.
        $this->get(SiteConfiguration::class)->getAllExistingSites(false);
    }
}
