<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

final class SiteProfileTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function slugs(): iterable
    {
        yield 'root base' => ['https://example.org', 'llms.txt', 'https://example.org/llms.txt'];
        yield 'sub base' => ['https://example.org/camp', 'sitemap.xml', 'https://example.org/camp/sitemap.xml'];
        yield 'leading slash' => ['https://example.org/camp', '/agents.md', 'https://example.org/camp/agents.md'];
    }

    #[Test]
    #[DataProvider('slugs')]
    public function urlForJoinsTheBaseWithExactlyOneSlash(string $baseUrl, string $slug, string $expected): void
    {
        $profile = new SiteProfile('Camp', '', $baseUrl, 'https://example.org');

        self::assertSame($expected, $profile->urlFor($slug));
    }
}
