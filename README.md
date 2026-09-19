# TYPO3 llms.txt / agents.md

[![CI](https://github.com/dirnbauer/typo3-llms-txt/actions/workflows/ci.yml/badge.svg)](https://github.com/dirnbauer/typo3-llms-txt/actions/workflows/ci.yml)
[![TYPO3 14.3](https://img.shields.io/badge/TYPO3-14.3-orange.svg)](https://get.typo3.org/version/14)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4%2B-777bb3.svg)](https://www.php.net/supported-versions.php)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg)](LICENSE)

## What it is

Two generated files per site, served from the page tree and the site configuration. AI Overviews cut outbound clicks, browser agents operate sites directly and crawlers get metered — the answer is not to hide, but to state machine-readably what this site offers and how to work with it.

**`<site base>/llms.txt`** — content discovery, following the [llms.txt convention](https://llmstxt.org): H1 site title, blockquote summary, one H2 section per visible first-level page with `[title](url): description` link lists. Pages that are hidden, timed out, `no_index` or hidden in navigation are never listed. URLs come from the site's own page router, so they match what the site serves.

**`<site base>/agents.md`** — an operation guide, built from the machine interfaces this installation actually has:

| Surface | Detected when | Advertised as |
|---|---|---|
| MCP server | `hn/typo3-mcp-server` is installed | endpoint URL, `ability_<namespace>_<name>` tool naming |
| Abilities registry | `webconsulting/typo3-abilities` 1.0/1.1 is installed | REST projection (`/abilities/v1`), capability catalogue (1.1+), CLI (`abilities:list\|describe\|run`), every MCP-exposed ability with title, risk tier and description |
| Sitemap | EXT:seo is installed | sitemap URL |
| Paid content | the x402 paywall is installed | the `402 Payment Required` lane |

Both files are served by a PSR-15 frontend middleware — after site resolution, before page resolution, because the two paths are virtual — as `text/plain`, cached for an hour, with `X-Robots-Tag: noindex`.

## Requirements

- TYPO3 14.3 LTS
- PHP 8.4+
- EXT:seo (the page query reads `seo_title` and `no_index`)

## Install

```bash
composer require webconsulting/typo3-llms-txt
vendor/bin/typo3 extension:setup --extension=llms_txt
```

Nothing else: every site starts serving both files immediately.

## Configure

Two site settings, both optional:

```yaml
# config/sites/<identifier>/settings.yaml
llmsTxt:
  enabled: false      # opt the site out entirely (default: true)
  doktypes: [1, 137]  # page types to publish (default: [1], standard pages)
```

The content sources are fixed, so editors steer the output by maintaining the page tree:

| Output | Source |
|---|---|
| Site title | `websiteTitle` from the site configuration, falling back to the root page title |
| Summary blockquote | Root page `description` |
| Sections | Visible first-level pages of a published doktype |
| Links | The section page itself plus its visible children (`seo_title` ?: `title`, `description`, router-generated URL), capped at 25 per section |

## Use

Inspect what would be served — for reviews, or as a CI snapshot:

```bash
vendor/bin/typo3 llmstxt:dump <site-identifier> llms.txt
vendor/bin/typo3 llmstxt:dump <site-identifier> agents.md
```

Nothing is written to disk and nothing is cached beyond the HTTP response, so an edited page tree shows up on the next request.

## Develop

```bash
composer install
composer ci                   # cgl, phpstan, unit, functional
composer ci:tests:unit
composer ci:tests:functional  # SQLite, no database server needed
composer ci:phpstan           # level 8, no baseline
composer ci:cgl -- --dry-run
docker run --rm -v $PWD:/project ghcr.io/typo3-documentation/render-guides:latest --config=Documentation
```

## Docs

Full manual in [`Documentation/`](Documentation/Index.rst): what the two files are for, installation, per-site configuration, the generated output in detail, and the developer reference for the builders, the surface detection and the middleware.

## License

GPL-2.0-or-later
