<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Content;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Webconsulting\LlmsTxt\Domain\AgentFile;
use Webconsulting\LlmsTxt\Site\AgentSurfacesFactory;
use Webconsulting\LlmsTxt\Site\SiteReader;

/**
 * Renders one agent file for one site language. The middleware and the
 * dump command both go through here, so HTTP and CLI can never drift apart.
 */
final readonly class AgentFileRenderer
{
    public function __construct(
        private SiteReader $siteReader,
        private AgentSurfacesFactory $surfacesFactory,
        private LlmsTxtBuilder $llmsTxtBuilder,
        private AgentsMdBuilder $agentsMdBuilder,
    ) {}

    public function render(Site $site, SiteLanguage $language, AgentFile $file): string
    {
        $profile = $this->siteReader->profile($site, $language);

        return match ($file) {
            AgentFile::LlmsTxt => $this->llmsTxtBuilder->build($profile, $this->siteReader->sections($site, $language)),
            AgentFile::AgentsMd => $this->agentsMdBuilder->build($profile, $this->surfacesFactory->forSite($profile)),
        };
    }
}
