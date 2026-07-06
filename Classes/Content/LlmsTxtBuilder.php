<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Content;

use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Renders the llms.txt markdown for one site following the llms.txt
 * convention: H1 title, blockquote summary, H2 sections with link lists
 * ("[title](url): description").
 */
final class LlmsTxtBuilder
{
    /**
     * @param list<\Webconsulting\LlmsTxt\Domain\Section> $sections
     */
    public function build(SiteProfile $profile, array $sections): string
    {
        $lines = ['# ' . $this->inline($profile->title)];

        if ($profile->description !== '') {
            $lines[] = '';
            $lines[] = '> ' . $this->inline($profile->description);
        }

        foreach ($sections as $section) {
            if ($section->links === []) {
                continue;
            }
            $lines[] = '';
            $lines[] = '## ' . $this->inline($section->title);
            $lines[] = '';
            foreach ($section->links as $link) {
                $entry = sprintf('- [%s](%s)', $this->inline($link->title), $link->url);
                if ($link->description !== '') {
                    $entry .= ': ' . $this->inline($link->description);
                }
                $lines[] = $entry;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Page titles and descriptions come from editors; keep them on one
     * line and escape the brackets that carry markdown link syntax.
     */
    private function inline(string $text): string
    {
        $text = (string)preg_replace('/\s+/u', ' ', trim($text));

        return str_replace(['[', ']'], ['\\[', '\\]'], $text);
    }
}
