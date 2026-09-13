<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Content;

use Webconsulting\LlmsTxt\Domain\AgentSurfaces;
use Webconsulting\LlmsTxt\Domain\SiteProfile;

/**
 * Renders agents.md: how agents should operate this site — the
 * machine-operable surfaces (MCP endpoint, abilities registry, sitemap,
 * paid-content lane) plus ground rules. Content discovery lives in
 * llms.txt; this file is about operation.
 */
final class AgentsMdBuilder
{
    public function build(SiteProfile $profile, AgentSurfaces $surfaces): string
    {
        $lines = [
            '# ' . $profile->title . ' — agent guide',
            '',
            'This file tells AI agents how to work with this site. Content discovery: [llms.txt](' . $profile->urlFor('llms.txt') . ').',
        ];

        if ($profile->description !== '') {
            $lines[] = '';
            $lines[] = $profile->description;
        }

        $lines[] = '';
        $lines[] = '## Machine interfaces';
        $lines[] = '';

        if ($surfaces->mcpEndpoint !== null) {
            $lines[] = sprintf(
                '- **MCP server**: `%s` (Model Context Protocol, OAuth-protected). Tools are permissioned per capability manifest; write operations are staged in workspaces and need review before publication. Registered abilities appear here as `ability_<namespace>_<name>` tools.',
                $surfaces->mcpEndpoint,
            );
        }

        if ($surfaces->abilities !== [] || $surfaces->abilitiesRestBase !== null) {
            $lines[] = '- **Abilities registry**: typed, permissioned capabilities in one registry, projected onto every surface. Each call runs through the same pipeline — policy gate, input schema validation, scope check, permission check, execution, output schema validation — and is recorded as an execution trace.';

            if ($surfaces->abilitiesRestBase !== null) {
                $lines[] = sprintf(
                    '  - REST: `GET %1$s/abilities` lists them, `GET %1$s/abilities/{namespace}/{name}` returns the JSON Schemas, `POST %1$s/abilities/{namespace}/{name}/run` executes one. Send a bearer token; `GET %1$s/categories` groups them.',
                    $surfaces->abilitiesRestBase,
                );
            }

            $lines[] = '  - CLI: `abilities:list`, `abilities:describe <ability>`, `abilities:run <ability>`.';

            if ($surfaces->abilities !== []) {
                $lines[] = '  - Registered abilities (MCP tool name — title, risk tier, description):';
                foreach ($surfaces->abilities as $ability) {
                    $lines[] = sprintf(
                        '    - `%s` — %s (%s risk): %s',
                        $ability['name'],
                        $ability['title'],
                        $ability['risk'],
                        $ability['description'],
                    );
                }
            }
        }

        if ($surfaces->sitemapUrl !== null) {
            $lines[] = sprintf('- **Sitemap**: %s', $surfaces->sitemapUrl);
        }

        $lines[] = '- **Structured data**: pages embed schema.org JSON-LD; prefer it over scraping rendered markup.';

        if ($surfaces->paidContent) {
            $lines[] = '- **Paid content**: some content is gated with the x402 payment protocol. A `402 Payment Required` response carries machine-readable payment requirements — pay per request instead of scraping around the gate.';
        }

        $lines[] = '';
        $lines[] = '## Ground rules';
        $lines[] = '';
        $lines[] = '- Respect `robots.txt`, `noindex` markers and rate limits; identify yourself with a descriptive User-Agent.';
        $lines[] = '- Do not submit forms or create accounts unless a human instructed you to.';
        $lines[] = '- Write operations go through the authenticated interfaces above — never through the public frontend.';

        return implode("\n", $lines) . "\n";
    }
}
