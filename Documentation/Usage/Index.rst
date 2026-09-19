..  include:: /Includes.rst.txt

..  _usage:

=====
Usage
=====

..  _usage-dump:

Inspecting the generated files
==============================

The :bash:`llmstxt:dump` command prints exactly what the middleware would
serve, without an HTTP request — useful in a review, or as a CI snapshot
that fails when the published surface changes unintentionally:

..  code-block:: bash

    vendor/bin/typo3 llmstxt:dump <site-identifier>
    vendor/bin/typo3 llmstxt:dump <site-identifier> llms.txt
    vendor/bin/typo3 llmstxt:dump <site-identifier> agents.md

The second argument defaults to ``llms.txt``. An unknown site identifier
lists the known ones and exits with a failure; an unknown file name exits
as invalid input.

..  _usage-example-llms:

An example llms.txt
===================

..  code-block:: markdown

    # Vienna Camp

    > A camp about deliberate publishing.

    ## Program

    - [Program](https://example.org/program): Talks and workshops.
    - [Keynotes](https://example.org/program/keynotes): The opening talks.

    ## Buy tickets

    - [Buy tickets](https://example.org/tickets)

Page titles and descriptions come from editors, so they are collapsed to
one line and the brackets that carry Markdown link syntax are escaped.

..  _usage-example-agents:

An example agents.md
====================

..  code-block:: markdown

    # Vienna Camp — agent guide

    This file tells AI agents how to work with this site. Content
    discovery: [llms.txt](https://example.org/llms.txt).

    ## Machine interfaces

    - **MCP server**: `https://example.org/mcp` … Registered abilities
      appear here as `ability_<namespace>_<name>` tools.
    - **Abilities registry**: …
      - REST: `GET https://example.org/abilities/v1/abilities` lists them …
      - Ability catalogue: … `GET https://example.org/abilities/v1/catalog` over REST …
      - CLI: `abilities:list`, `abilities:describe <ability>`, `abilities:run <ability>`.
      - Registered abilities (MCP tool name — title, risk tier, description):
        - `ability_system_site-info` — Site info (low risk): Lists the configured sites.
    - **Sitemap**: https://example.org/sitemap.xml
    - **Structured data**: pages embed schema.org JSON-LD …

    ## Ground rules

    …

Every bullet is conditional. An installation without the MCP server,
without the abilities registry and without EXT:seo still gets a valid
agents.md — it simply advertises less.

..  _usage-caching:

Caching
=======

The response carries ``Cache-Control: public, max-age=3600``. Nothing is
stored server-side, so no cache needs to be flushed after an editorial
change: the next request regenerates both files from the current page
tree.
