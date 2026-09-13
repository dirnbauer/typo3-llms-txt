# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-13

First stable release.

### Added

- `agents.md` now advertises all three projections of the abilities registry: the REST base (read from the abilities extension configuration, `/abilities/v1` by default), the CLI commands `abilities:list`, `abilities:describe` and `abilities:run`, and the `ability_<namespace>_<name>` naming of the MCP tools.
- Functional test suite: both files are requested against a fixture site and page tree, asserting the content type, the published page list, the abilities advertisement built from the real registry, and the per-site opt-out.
- `Documentation/` as a rendered TYPO3 manual — introduction, installation, configuration, usage and a developer reference.
- `CHANGELOG.md`, a `.php-cs-fixer.dist.php` on `typo3/coding-standards`, and the `composer ci`, `ci:cgl`, `ci:phpstan`, `ci:tests:unit` and `ci:tests:functional` scripts.

### Changed

- Requires TYPO3 14.3.7 and PHP 8.4.
- `webconsulting/typo3-abilities` is required in development as `^1.0`.
- Tests run through the TYPO3 testing framework; the functional suite defaults to SQLite so a local run needs no database server.
- PHPStan runs at level 8 with the TYPO3 and PHPUnit extensions and no baseline (previously level max without either extension).
- One CI workflow with lint, coding standards, PHPStan, unit tests on PHP 8.4 and 8.5, and functional tests against MariaDB 10.11.
- `extra.typo3/cms` carries the version and `Package.providesPackages`, so TYPO3 14.3 no longer deprecates the `ext_emconf.php` that TER uploads still need.

### Fixed

- `typo3/cms-seo` is now declared as a requirement. The page tree query reads `seo_title` and `no_index`, columns EXT:seo adds to `pages`, so the extension never worked without it.

[1.0.0]: https://github.com/dirnbauer/typo3-llms-txt/releases/tag/v1.0.0
