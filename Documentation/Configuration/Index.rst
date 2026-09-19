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

..  _configuration-doktypes:

Publishing more than standard pages
===================================

Only standard pages (``doktype`` 1) are published by default. A site that
keeps its articles in a page type of its own widens the list:

..  code-block:: yaml
    :caption: config/sites/<identifier>/settings.yaml

    llmsTxt:
      doktypes: [1, 137]

A comma-separated string works as well. Values that are not positive
integers are ignored; an empty result falls back to the default.

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
        -   The visible first-level published pages below the root page,
            in backend sorting order.

    *   -   Links
        -   The section page itself plus its visible children:
            ``seo_title`` when set, otherwise ``title``, with the page
            ``description`` and the URL the site's page router generates
            for it. Capped at 25 links per section.

..  _configuration-visibility:

Which pages count as visible
============================

A page is published in llms.txt only when all of these hold:

*   ``doktype`` is one of ``llmsTxt.doktypes`` (1, standard page, by
    default)
*   it is not deleted and not hidden
*   ``starttime`` and ``endtime`` do not exclude it right now
*   ``no_index`` is 0
*   ``nav_hide`` is 0

Every site language is served at its own base. Editors steer the file with
the tools they already use: hide a page, mark it *no index*, or hide it in
navigation, and it disappears from the published list.
