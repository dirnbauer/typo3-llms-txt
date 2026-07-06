<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Unit\Middleware;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\LlmsTxt\Middleware\LlmsTxtMiddleware;

final class PathMatchingTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string|null}>
     */
    public static function paths(): iterable
    {
        yield 'root site llms.txt' => ['/', '/llms.txt', 'llms.txt'];
        yield 'root site agents.md' => ['/', '/agents.md', 'agents.md'];
        yield 'root site other path' => ['/', '/contact', null];
        yield 'root site nested llms.txt' => ['/', '/sub/llms.txt', null];
        yield 'subdir site with trailing slash' => ['/blog/', '/blog/llms.txt', 'llms.txt'];
        yield 'subdir site without trailing slash' => ['/14', '/14/agents.md', 'agents.md'];
        yield 'subdir site root file does not leak' => ['/blog/', '/llms.txt', null];
        yield 'similar prefix does not match' => ['/blog/', '/blog-archive/llms.txt', null];
    }

    #[Test]
    #[DataProvider('paths')]
    public function matchesOnlyTheSitesOwnVirtualFiles(string $basePath, string $requestPath, ?string $expected): void
    {
        self::assertSame($expected, LlmsTxtMiddleware::matchedFile($basePath, $requestPath));
    }
}
