<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Content;

use Webconsulting\LlmsTxt\Domain\AgentFile;
use Webconsulting\LlmsTxt\Domain\Section;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Renders llms.txt following the llms.txt proposal v2 (llmstxt.org), in its
 * order: an H1 with the site title, a blockquote summary, a details
 * paragraph pointing to agents.md, then H2 sections holding
 * "- [title](url): description" file lists.
 */
final class LlmsTxtBuilder
{
    /**
     * @param list<Section> $sections
     */
    public function build(SiteProfile $profile, array $sections): string
    {
        $lines = ['# ' . Markdown::inline($profile->title)];

        if ($profile->description !== '') {
            $lines[] = '';
            $lines[] = '> ' . Markdown::inline($profile->description);
        }

        $lines[] = '';
        $lines[] = sprintf(
            'How agents can operate this site beyond reading it — its machine interfaces and ground rules — is described in [%s](%s).',
            AgentFile::AgentsMd->value,
            $profile->urlFor(AgentFile::AgentsMd->value),
        );

        foreach ($sections as $section) {
            if ($section->links === []) {
                continue;
            }
            $lines[] = '';
            $lines[] = '## ' . Markdown::inline($section->title);
            $lines[] = '';
            foreach ($section->links as $link) {
                $entry = sprintf('- [%s](%s)', Markdown::inline($link->title), $link->url);
                if ($link->description !== '') {
                    $entry .= ': ' . Markdown::inline($link->description);
                }
                $lines[] = $entry;
            }
        }

        return implode("\n", $lines) . "\n";
    }
}
