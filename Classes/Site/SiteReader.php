<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Site;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\LlmsTxt\Domain\PageLink;
use Webconsulting\LlmsTxt\Domain\Section;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Reads what one site language publishes: its profile (title, summary,
 * base URL) and the two visible levels of its page tree. Visibility is
 * TYPO3's own — the PageRepository applies hidden/start/end, frontend
 * group access, workspace and language overlays including l18n_cfg — plus
 * the two editorial signals no_index and nav_hide.
 */
final readonly class SiteReader
{
    private const MAX_LINKS_PER_SECTION = 25;
    private const DEFAULT_DOKTYPES = [PageRepository::DOKTYPE_DEFAULT];

    public function __construct(
        private Context $context,
    ) {}

    public function profile(Site $site, SiteLanguage $language): SiteProfile
    {
        $base = $language->getBase();
        $rootPage = $this->pages($language)->getPage($site->getRootPageId());

        $title = trim($language->getWebsiteTitle());
        if ($title === '') {
            $title = trim(self::string($site->getConfiguration(), 'websiteTitle'));
        }
        if ($title === '') {
            $title = trim(self::string($rootPage, 'title'));
        }

        return new SiteProfile(
            title: $title !== '' ? $title : $site->getIdentifier(),
            description: trim(self::string($rootPage, 'description')),
            baseUrl: rtrim((string)$base, '/'),
            origin: $base->getScheme() !== '' ? $base->getScheme() . '://' . $base->getAuthority() : '',
        );
    }

    /**
     * Each visible first-level page becomes a section, linked first, followed
     * by its visible children. Two queries, whatever the size of the tree.
     *
     * @return list<Section>
     */
    public function sections(Site $site, SiteLanguage $language): array
    {
        $pages = $this->pages($language);
        $where = $this->publishedWhere($site);

        $level1 = $this->menu($pages, [$site->getRootPageId()], $where);
        if ($level1 === []) {
            return [];
        }

        /** @var array<int, list<array<mixed>>> $children */
        $children = [];
        foreach ($this->menu($pages, array_map(static fn(array $row): int => self::int($row, 'uid'), $level1), $where) as $child) {
            $children[self::int($child, 'pid')][] = $child;
        }

        $sections = [];
        foreach ($level1 as $parent) {
            $rows = [$parent, ...array_slice($children[self::int($parent, 'uid')] ?? [], 0, self::MAX_LINKS_PER_SECTION - 1)];
            $sections[] = new Section(
                self::title($parent),
                array_map(fn(array $row): PageLink => $this->link($row, $site, $language), $rows),
            );
        }

        return $sections;
    }

    private function pages(SiteLanguage $language): PageRepository
    {
        $context = clone $this->context;
        $context->setAspect('language', LanguageAspectFactory::createFromSiteLanguage($language));

        return GeneralUtility::makeInstance(PageRepository::class, $context);
    }

    /**
     * @param list<int> $parentIds
     * @return list<array<mixed>>
     */
    private function menu(PageRepository $pages, array $parentIds, string $where): array
    {
        $rows = [];
        foreach ($pages->getMenu($parentIds, '*', 'sorting', $where) as $row) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * The page types a site publishes default to standard pages; the site
     * setting llmsTxt.doktypes (a list, or a comma-separated string) widens
     * that to e.g. blog posts.
     */
    private function publishedWhere(Site $site): string
    {
        $setting = $site->getSettings()->get('llmsTxt.doktypes', self::DEFAULT_DOKTYPES);
        $values = is_array($setting) ? $setting : GeneralUtility::trimExplode(',', is_string($setting) ? $setting : '', true);

        $doktypes = [];
        foreach ($values as $value) {
            if (is_numeric($value) && (int)$value > 0) {
                $doktypes[] = (int)$value;
            }
        }

        return sprintf(
            'AND doktype IN (%s) AND nav_hide = 0 AND no_index = 0',
            implode(',', array_unique($doktypes === [] ? self::DEFAULT_DOKTYPES : $doktypes)),
        );
    }

    /**
     * The URL comes from TYPO3's own page router, so llms.txt links to
     * exactly what the site serves — language base, slug and any route
     * enhancer included.
     *
     * @param array<mixed> $row
     */
    private function link(array $row, Site $site, SiteLanguage $language): PageLink
    {
        return new PageLink(
            self::title($row),
            (string)$site->getRouter()->generateUri($row, ['_language' => $language]),
            trim(self::string($row, 'description')),
        );
    }

    /**
     * @param array<mixed> $row
     */
    private static function title(array $row): string
    {
        $seoTitle = trim(self::string($row, 'seo_title'));

        return $seoTitle !== '' ? $seoTitle : trim(self::string($row, 'title'));
    }

    /**
     * @param array<mixed> $row
     */
    private static function string(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * @param array<mixed> $row
     */
    private static function int(array $row, string $key): int
    {
        $value = $row[$key] ?? 0;

        return is_numeric($value) ? (int)$value : 0;
    }
}
