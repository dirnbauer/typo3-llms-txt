<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use Webconsulting\LlmsTxt\Content\AgentFileRenderer;
use Webconsulting\LlmsTxt\Domain\AgentFile;

/**
 * Prints the generated llms.txt or agents.md for a site language — the
 * same content the middleware serves, for inspection and CI snapshots.
 */
#[AsCommand(
    name: 'llmstxt:dump',
    description: 'Print the generated llms.txt or agents.md for a site',
)]
final class DumpCommand extends Command
{
    public function __construct(
        private readonly SiteFinder $siteFinder,
        private readonly AgentFileRenderer $renderer,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('site', InputArgument::REQUIRED, 'Site identifier')
            ->addArgument('file', InputArgument::OPTIONAL, 'Which file: "llms.txt" or "agents.md"', AgentFile::LlmsTxt->value)
            ->addOption('language', 'l', InputOption::VALUE_REQUIRED, 'Language id of the site language to render', '0');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = AgentFile::tryFrom(self::string($input->getArgument('file')));
        if ($file === null) {
            $output->writeln('<error>file must be "llms.txt" or "agents.md".</error>');

            return Command::INVALID;
        }

        $siteIdentifier = self::string($input->getArgument('site'));
        try {
            $site = $this->siteFinder->getSiteByIdentifier($siteIdentifier);
        } catch (SiteNotFoundException) {
            $output->writeln(sprintf(
                '<error>Unknown site "%s". Known: %s</error>',
                $siteIdentifier,
                implode(', ', array_keys($this->siteFinder->getAllSites())),
            ));

            return Command::FAILURE;
        }

        $languageId = (int)self::string($input->getOption('language'));
        try {
            $language = $site->getLanguageById($languageId);
        } catch (\InvalidArgumentException) {
            $output->writeln(sprintf(
                '<error>Site "%s" has no language %d. Known: %s</error>',
                $siteIdentifier,
                $languageId,
                implode(', ', array_keys($site->getLanguages())),
            ));

            return Command::INVALID;
        }

        $output->write($this->renderer->render($site, $language, $file));

        return Command::SUCCESS;
    }

    private static function string(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }
}
