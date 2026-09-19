<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Content;

use Webconsulting\LlmsTxt\Domain\Section;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Renders llms.txt following the convention at llmstxt.org: an H1 with the
 * site title, a blockquote summary and H2 sections holding
 * "- [title](url): description" link lists.
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
