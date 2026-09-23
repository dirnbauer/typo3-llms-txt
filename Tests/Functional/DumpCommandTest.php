<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Site\SiteFinder;
use Webconsulting\LlmsTxt\Command\DumpCommand;
use Webconsulting\LlmsTxt\Content\AgentFileRenderer;

/**
 * llmstxt:dump prints exactly what the middleware serves, and says
 * something useful when the site or language does not exist.
 */
final class DumpCommandTest extends AbstractAgentFileTestCase
{
    private CommandTester $tester;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->tester = new CommandTester(new DumpCommand(
            $this->get(SiteFinder::class),
            $this->get(AgentFileRenderer::class),
        ));
    }

    #[Test]
    public function printsLlmsTxtByDefault(): void
    {
        self::assertSame(Command::SUCCESS, $this->tester->execute(['site' => 'camp']));

        $output = $this->tester->getDisplay();
        self::assertStringStartsWith('# Vienna Camp', $output);
        self::assertStringContainsString('- [Keynotes](http://localhost/program/keynotes): The opening talks.', $output);
    }

    #[Test]
    public function printsAgentsMdOnRequest(): void
    {
        self::assertSame(Command::SUCCESS, $this->tester->execute(['site' => 'camp', 'file' => 'agents.md']));

        self::assertStringContainsString('# Vienna Camp — agent guide', $this->tester->getDisplay());
    }

    #[Test]
    public function rejectsAnUnknownFile(): void
    {
        self::assertSame(Command::INVALID, $this->tester->execute(['site' => 'camp', 'file' => 'humans.txt']));

        self::assertStringContainsString('must be "llms.txt" or "agents.md"', $this->tester->getDisplay());
    }

    #[Test]
    public function namesTheKnownSitesWhenTheSiteIsUnknown(): void
    {
        self::assertSame(Command::FAILURE, $this->tester->execute(['site' => 'nope']));

        $output = $this->tester->getDisplay();
        self::assertStringContainsString('Unknown site "nope"', $output);
        self::assertStringContainsString('camp', $output);
    }

    #[Test]
    public function namesTheKnownLanguagesWhenTheLanguageIsUnknown(): void
    {
        self::assertSame(Command::INVALID, $this->tester->execute(['site' => 'camp', '--language' => '7']));

        self::assertStringContainsString('has no language 7', $this->tester->getDisplay());
    }
}
