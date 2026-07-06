<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Site;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\LlmsTxt\Domain\PageLink;
use Webconsulting\LlmsTxt\Domain\Section;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Builds llms.txt sections from the page tree: each visible first-level
 * page becomes a section (and its own first link), its visible children
 * become the section's links. Pages that are hidden, timed out, excluded
 * from indexing (no_index) or hidden in navigation are skipped — llms.txt
 * publishes what the site deliberately offers, nothing else.
 */
class PageTreeReader
{
    private const DEFAULT_DOKTYPES = [1];
    private const MAX_LINKS_PER_SECTION = 25;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * @return list<Section>
     */
    public function readSections(Site $site, SiteProfile $profile): array
    {
        $sections = [];

        foreach ($this->readChildren($site->getRootPageId()) as $level1) {
            $links = [
                new PageLink(
                    $this->titleOf($level1),
                    $profile->urlFor($this->stringOf($level1, 'slug')),
                    trim($this->stringOf($level1, 'description')),
                ),
            ];

            foreach ($this->readChildren($this->intOf($level1, 'uid')) as $level2) {
                if (count($links) >= self::MAX_LINKS_PER_SECTION) {
                    break;
                }
                $links[] = new PageLink(
                    $this->titleOf($level2),
                    $profile->urlFor($this->stringOf($level2, 'slug')),
                    trim($this->stringOf($level2, 'description')),
                );
            }

            $sections[] = new Section($this->titleOf($level1), $links);
        }

        return $sections;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readChildren(int $parentId): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(new DeletedRestriction())
            ->add(new HiddenRestriction())
            ->add(new StartTimeRestriction())
            ->add(new EndTimeRestriction());

        $rows = $queryBuilder
            ->select('uid', 'title', 'seo_title', 'description', 'slug', 'doktype', 'no_index', 'nav_hide')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentId, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)),
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_values(array_filter(
            $rows,
            fn(array $row): bool => in_array($this->intOf($row, 'doktype'), self::DEFAULT_DOKTYPES, true)
                && $this->intOf($row, 'no_index') === 0
                && $this->intOf($row, 'nav_hide') === 0,
        ));
    }

    /**
     * @param array<string, mixed> $row
     */
    private function stringOf(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * @param array<string, mixed> $row
     */
    private function intOf(array $row, string $key): int
    {
        $value = $row[$key] ?? 0;

        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function titleOf(array $row): string
    {
        $seoTitle = trim($this->stringOf($row, 'seo_title'));

        return $seoTitle !== '' ? $seoTitle : trim($this->stringOf($row, 'title'));
    }
}
