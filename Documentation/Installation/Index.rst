..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

..  _installation-requirements:

Requirements
============

*   TYPO3 14.3 LTS
*   PHP 8.4 or newer
*   EXT:seo — the page tree query reads the ``seo_title`` and ``no_index``
    columns that EXT:seo adds to :sql:`pages`, so it is a hard
    requirement, not an optional integration. Its presence additionally
    makes agents.md advertise the sitemap.

..  _installation-composer:

Install with Composer
=====================

..  code-block:: bash

    composer require webconsulting/typo3-llms-txt
    vendor/bin/typo3 extension:setup --extension=llms_txt

There is nothing else to do: the middleware is registered by the
extension, and every configured site starts serving both files
immediately.

..  _installation-verify:

Verify
======

Request the two paths, or print what would be served:

..  code-block:: bash

    curl -i https://example.org/llms.txt
    vendor/bin/typo3 llmstxt:dump <site-identifier> agents.md

A correct response carries ``Content-Type: text/plain; charset=utf-8``
and ``X-Robots-Tag: noindex``.

..  _installation-optional:

Optional integrations
=====================

None of these are required; each one only adds a line to agents.md when
it is installed:

*   :composer:`hn/typo3-mcp-server` — the MCP endpoint
*   `webconsulting/typo3-abilities <https://github.com/dirnbauer/typo3-abilities>`__ 1.0 or 1.1 — the abilities
    registry and its REST, CLI and MCP projections; 1.1 adds the
    capability catalogue
*   the x402 paywall — the paid-content lane
