<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * One site "camp" on the fixture page tree, for every functional test.
 * Subclasses decide which optional integrations are installed.
 */
abstract class AbstractAgentFileTestCase extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'seo',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/typo3-llms-txt',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->writeCampSite();
    }

    /**
     * @param array<string, mixed> $settings Site settings, e.g. ['llmsTxt' => ['enabled' => false]]
     */
    protected function writeCampSite(array $settings = []): void
    {
        $configuration = [
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
        ];
        if ($settings !== []) {
            $configuration['settings'] = $settings;
        }

        $path = Environment::getConfigPath() . '/sites/camp';
        GeneralUtility::mkdir_deep($path);
        GeneralUtility::writeFile($path . '/config.yaml', Yaml::dump($configuration, 99, 2), true);

        // Drop the cached site set so the next resolution reads the file.
        $this->get(SiteConfiguration::class)->getAllExistingSites(false);
    }
}
