<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use Webconsulting\LlmsTxt\Content\AgentsMdBuilder;
use Webconsulting\LlmsTxt\Content\LlmsTxtBuilder;
use Webconsulting\LlmsTxt\Site\AgentSurfacesFactory;
use Webconsulting\LlmsTxt\Site\PageTreeReader;
use Webconsulting\LlmsTxt\Site\SiteProfileFactory;

/**
 * Prints the generated llms.txt or agents.md for a site — the same
 * content the middleware serves, for inspection and CI snapshots.
 */
#[AsCommand(
    name: 'llmstxt:dump',
    description: 'Print the generated llms.txt or agents.md for a site',
)]
final class DumpCommand extends Command
{
    public function __construct(
        private readonly SiteFinder $siteFinder,
        private readonly SiteProfileFactory $profileFactory,
        private readonly PageTreeReader $pageTreeReader,
        private readonly AgentSurfacesFactory $surfacesFactory,
        private readonly LlmsTxtBuilder $llmsTxtBuilder,
        private readonly AgentsMdBuilder $agentsMdBuilder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('site', InputArgument::REQUIRED, 'Site identifier')
            ->addArgument('file', InputArgument::OPTIONAL, 'Which file: "llms.txt" or "agents.md"', 'llms.txt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteIdentifier = $input->getArgument('site');
        $siteIdentifier = is_string($siteIdentifier) ? $siteIdentifier : '';
        $file = $input->getArgument('file');
        $file = is_string($file) ? $file : 'llms.txt';

        if (!in_array($file, ['llms.txt', 'agents.md'], true)) {
            $output->writeln('<error>file must be "llms.txt" or "agents.md".</error>');

            return Command::INVALID;
        }

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

        $profile = $this->profileFactory->fromSite($site);

        $output->write(
            $file === 'llms.txt'
                ? $this->llmsTxtBuilder->build($profile, $this->pageTreeReader->readSections($site, $profile))
                : $this->agentsMdBuilder->build($profile, $this->surfacesFactory->forSite($profile)),
        );

        return Command::SUCCESS;
    }
}
