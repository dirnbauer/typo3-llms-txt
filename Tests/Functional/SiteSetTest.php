<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * A site that depends on the webconsulting/llms-txt set gets the settings
 * typed by their definitions — which is what the backend settings editor
 * reads and writes.
 */
final class SiteSetTest extends AbstractAgentFileTestCase
{
    #[Test]
    public function theSetProvidesTypedDefaults(): void
    {
        $this->writeCampSite([], ['webconsulting/llms-txt']);

        $settings = $this->get(SiteFinder::class)->getSiteByIdentifier('camp')->getSettings();

        self::assertTrue($settings->get('llmsTxt.enabled'));
        self::assertSame(['1'], $settings->get('llmsTxt.doktypes'));
    }

    #[Test]
    public function aListOfDoktypesWidensWhatIsPublished(): void
    {
        $this->writeCampSite(['llmsTxt' => ['doktypes' => ['1', '137']]], ['webconsulting/llms-txt']);

        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'))->getBody();

        self::assertStringContainsString('- [Sponsors](http://localhost/program/sponsors): Who pays for it.', $body);
    }

    #[Test]
    public function theSwitchTurnsBothFilesOff(): void
    {
        $this->writeCampSite(['llmsTxt' => ['enabled' => false]], ['webconsulting/llms-txt']);

        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/llms.txt'));

        self::assertNotSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }
}
