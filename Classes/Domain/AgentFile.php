<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Domain;

/**
 * The two virtual files served directly below every site language base.
 */
enum AgentFile: string
{
    case LlmsTxt = 'llms.txt';
    case AgentsMd = 'agents.md';

    /**
     * Matches a request path against the files of one site language:
     * "/blog/llms.txt" for the base path "/blog/" — and nothing next to or
     * below it, so "/blog-archive/llms.txt" and "/blog/sub/llms.txt" never
     * match.
     */
    public static function fromRequestPath(string $basePath, string $requestPath): ?self
    {
        $prefix = rtrim($basePath, '/') . '/';
        if (!str_starts_with($requestPath, $prefix)) {
            return null;
        }

        return self::tryFrom(substr($requestPath, strlen($prefix)));
    }
}
