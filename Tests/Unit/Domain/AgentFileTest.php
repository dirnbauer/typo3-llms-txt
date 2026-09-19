<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\LlmsTxt\Domain\AgentFile;

final class AgentFileTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, ?AgentFile}>
     */
    public static function requestPaths(): iterable
    {
        yield 'root base, llms.txt' => ['/', '/llms.txt', AgentFile::LlmsTxt];
        yield 'root base, agents.md' => ['/', '/agents.md', AgentFile::AgentsMd];
        yield 'sub base' => ['/blog/', '/blog/llms.txt', AgentFile::LlmsTxt];
        yield 'sub base without trailing slash' => ['/blog', '/blog/agents.md', AgentFile::AgentsMd];
        yield 'language base' => ['/blog/de/', '/blog/de/llms.txt', AgentFile::LlmsTxt];

        yield 'a page, not a file' => ['/', '/about', null];
        yield 'the base itself' => ['/blog/', '/blog/', null];
        yield 'below the file' => ['/blog/', '/blog/sub/llms.txt', null];
        yield 'a sibling base with the same prefix' => ['/blog/', '/blog-archive/llms.txt', null];
        yield 'an unknown file next to them' => ['/', '/robots.txt', null];
        yield 'a page named like the file' => ['/', '/docs/agents.md', null];
    }

    #[Test]
    #[DataProvider('requestPaths')]
    public function matchesOnlyTheTwoFilesDirectlyBelowTheBase(string $basePath, string $requestPath, ?AgentFile $expected): void
    {
        self::assertSame($expected, AgentFile::fromRequestPath($basePath, $requestPath));
    }

    #[Test]
    public function theCaseValuesAreTheFileNames(): void
    {
        self::assertSame('llms.txt', AgentFile::LlmsTxt->value);
        self::assertSame('agents.md', AgentFile::AgentsMd->value);
    }
}
