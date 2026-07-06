<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Site;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

class SiteProfileFactory
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function fromSite(Site $site): SiteProfile
    {
        $base = $site->getBase();
        $rootPage = $this->readRootPage($site->getRootPageId());

        $configuredTitle = trim($this->stringOf($site->getConfiguration(), 'websiteTitle'));
        $rootTitle = trim($this->stringOf($rootPage, 'title'));

        return new SiteProfile(
            title: $configuredTitle !== '' ? $configuredTitle : ($rootTitle !== '' ? $rootTitle : $site->getIdentifier()),
            description: trim($this->stringOf($rootPage, 'description')),
            baseUrl: rtrim((string)$base, '/'),
            origin: $base->getScheme() !== '' ? $base->getScheme() . '://' . $base->getAuthority() : '',
        );
    }

    /**
     * @param array<mixed> $row
     */
    private function stringOf(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function readRootPage(int $rootPageId): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $row = $queryBuilder
            ->select('title', 'description')
            ->from('pages')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($rootPageId, \TYPO3\CMS\Core\Database\Connection::PARAM_INT),
            ))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($row) ? $row : [];
    }
}
