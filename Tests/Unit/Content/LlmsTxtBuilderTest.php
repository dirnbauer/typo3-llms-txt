<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Tests\Unit\Content;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\LlmsTxt\Content\LlmsTxtBuilder;
use Webconsulting\LlmsTxt\Domain\PageLink;
use Webconsulting\LlmsTxt\Domain\Section;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

final class LlmsTxtBuilderTest extends TestCase
{
    private LlmsTxtBuilder $builder;

    private SiteProfile $profile;

    protected function setUp(): void
    {
        $this->builder = new LlmsTxtBuilder();
        $this->profile = new SiteProfile(
            title: 'Vienna Camp',
            description: 'A demo site about camping in Vienna.',
            baseUrl: 'https://example.org/camp',
            origin: 'https://example.org',
        );
    }

    #[Test]
    public function rendersTitleSummaryAndSections(): void
    {
        $output = $this->builder->build($this->profile, [
            new Section('Offers', [
                new PageLink('Tent pitches', 'https://example.org/camp/offers/tents', 'Pitches for tents.'),
                new PageLink('Cabins', 'https://example.org/camp/offers/cabins'),
            ]),
        ]);

        self::assertSame(
            <<<MD
                # Vienna Camp

                > A demo site about camping in Vienna.

                ## Offers

                - [Tent pitches](https://example.org/camp/offers/tents): Pitches for tents.
                - [Cabins](https://example.org/camp/offers/cabins)

                MD,
            $output,
        );
    }

    #[Test]
    public function omitsBlockquoteWithoutDescriptionAndSkipsEmptySections(): void
    {
        $profile = new SiteProfile('Bare', '', 'https://example.org', 'https://example.org');

        $output = $this->builder->build($profile, [new Section('Empty', [])]);

        self::assertSame("# Bare\n", $output);
    }

    #[Test]
    public function escapesMarkdownBracketsAndCollapsesWhitespace(): void
    {
        $output = $this->builder->build($this->profile, [
            new Section("News\n[2026]", [
                new PageLink("A [draft]\ttitle", 'https://example.org/a', "line\nbroken  description"),
            ]),
        ]);

        self::assertStringContainsString('## News \\[2026\\]', $output);
        self::assertStringContainsString('- [A \\[draft\\] title](https://example.org/a): line broken description', $output);
    }
}
