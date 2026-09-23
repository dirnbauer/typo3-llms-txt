<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * llms.txt proposal v2: every page points to the llms.txt that covers it
 * with a `Link: <…/llms.txt>; rel="describedby"` header.
 */
final class DescribedByLinkTest extends AbstractAgentFileTestCase
{
    private const string LINK = '<http://localhost/llms.txt>; rel="describedby"';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFrontendRootPage(1, ['EXT:llms_txt/Tests/Functional/Fixtures/Frontend/page.typoscript']);
    }

    #[Test]
    public function aPagePointsToTheLlmsTxtOfItsLanguage(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/program'));

        self::assertSame(200, $response->getStatusCode(), substr((string)$response->getBody(), 0, 3000));
        self::assertContains(self::LINK, $response->getHeader('Link'));
    }

    #[Test]
    public function aMissingPageDoesNot(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/does-not-exist'));

        self::assertSame(404, $response->getStatusCode());
        self::assertNotContains(self::LINK, $response->getHeader('Link'));
    }

    #[Test]
    public function aSwitchedOffSiteDoesNot(): void
    {
        $this->writeCampSite(['llmsTxt' => ['enabled' => false]]);

        $response = $this->executeFrontendSubRequest(new InternalRequest('http://localhost/program'));

        self::assertSame(200, $response->getStatusCode(), substr((string)$response->getBody(), 0, 3000));
        self::assertNotContains(self::LINK, $response->getHeader('Link'));
    }
}
