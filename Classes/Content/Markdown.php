<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Content;

final class Markdown
{
    private function __construct() {}

    /**
     * Titles and descriptions come from editors: keep them on one line and
     * escape the brackets that would otherwise open a Markdown link.
     */
    public static function inline(string $text): string
    {
        $text = (string)preg_replace('/\s+/u', ' ', trim($text));

        return str_replace(['[', ']'], ['\\[', '\\]'], $text);
    }
}
