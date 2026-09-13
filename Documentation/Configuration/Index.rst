..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

The extension has no extension configuration and no TypoScript. What it
publishes is decided by the page tree; what it publishes *for* is decided
per site.

..  _configuration-opt-out:

Switching a site off
====================

Set one site setting:

..  code-block:: yaml
    :caption: config/sites/<identifier>/settings.yaml

    llmsTxt:
      enabled: false

Both paths then fall through to normal page resolution, which usually
means a 404. The default is ``true``, so a site that says nothing serves
both files.

..  _configuration-sources:

What ends up in the output
==========================

..  list-table::
    :header-rows: 1

    *   -   Output
        -   Source

    *   -   Site title
        -   ``websiteTitle`` from the site configuration, falling back to
            the root page title, falling back to the site identifier.

    *   -   Summary blockquote
        -   The ``description`` of the root page.

    *   -   Sections
        -   The visible first-level standard pages below the root page,
            in backend sorting order.

    *   -   Links
        -   The section page itself plus its visible children:
            ``seo_title`` when set, otherwise ``title``, with the page
            ``description`` and a slug-based URL. Capped at 25 links per
            section.

..  _configuration-visibility:

Which pages count as visible
============================

A page is published in llms.txt only when all of these hold:

*   ``doktype`` is 1 (standard page)
*   it is not deleted and not hidden
*   ``starttime`` and ``endtime`` do not exclude it right now
*   ``no_index`` is 0
*   ``nav_hide`` is 0

Only the default language is read. Editors therefore steer the file with
the tools they already use: hide a page, mark it *no index*, or hide it in
navigation, and it disappears from the published list.
