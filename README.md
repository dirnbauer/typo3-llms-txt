# TYPO3 llms.txt / agents.md

Serves **`/llms.txt`** and **`/agents.md`** per TYPO3 site, generated from the page tree and site configuration. Deliberate publishing for the agent-readable web: AI Overviews cut outbound clicks, browser agents operate sites directly, crawlers get metered — the answer is not to hide but to state, machine-readably, what this site offers and how to work with it.

## What it serves

**`<site base>/llms.txt`** — content discovery, following the [llms.txt convention](https://llmstxt.org): H1 site title, blockquote summary, one H2 section per visible first-level page with `[title](url): description` link lists. Generated from the page tree — pages that are hidden, timed out, `no_index` or hidden in navigation are never listed.

**`<site base>/agents.md`** — operation guide for agents: the machine interfaces this installation actually has, detected at runtime:

- the **MCP endpoint** (when [hn/typo3-mcp-server](https://github.com/dirnbauer/typo3-mcp-server) is installed)
- the **abilities registry** (when [webconsulting/typo3-abilities](https://github.com/dirnbauer/typo3-abilities) is installed) — every MCP-exposed ability with title, risk tier and description
- the **sitemap** (when EXT:seo is installed)
- the **x402 paid-content lane** (when the x402 paywall is installed)

plus ground rules (respect robots/noindex/rate limits, no form submissions, writes only through authenticated interfaces).

Both files are served by a PSR-15 frontend middleware (after site resolution, before page resolution — the paths are virtual), `text/plain`, one hour cache, `X-Robots-Tag: noindex`.

## Usage

Nothing to configure — install and every site serves both files. Per-site opt-out via site settings:

```yaml
# config/sites/<identifier>/settings.yaml
llmsTxt:
  enabled: false
```

Inspect what would be served (CI snapshots, reviews):

```bash
vendor/bin/typo3 llmstxt:dump <site-identifier> llms.txt
vendor/bin/typo3 llmstxt:dump <site-identifier> agents.md
```

## Content sources

| Output | Source |
|---|---|
| Site title | `websiteTitle` from site config, falling back to the root page title |
| Summary blockquote | Root page `description` |
| Sections | Visible first-level standard pages (doktype 1) |
| Links | The section page itself + its visible children (`seo_title` ?: `title`, `description`, slug-based URL), capped at 25 per section |

## Development

```bash
composer install
composer test      # PHPUnit
composer phpstan   # level max
```

## Status & roadmap

Alpha. Next: per-site doktype/depth configuration via site settings, `llms-full.txt` (inlined content) for high-value sections, and pairing with x402 gating tiers so machine readership becomes a licensed channel instead of lost clicks (strategy item 20).

Part of the TYPO3 agentic strategy — item 20: *the machine-readable, monetizable site*.
