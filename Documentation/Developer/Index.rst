..  include:: /Includes.rst.txt

..  _developer:

===================
Developer reference
===================

..  _developer-architecture:

Architecture
============

Five small pieces, each with one job:

..  list-table::
    :header-rows: 1

    *   -   Class
        -   Responsibility

    *   -   :php:`Middleware\LlmsTxtMiddleware`
        -   Matches the two virtual paths against the site base, honours
            the per-site setting and returns the response. Registered in
            :file:`Configuration/RequestMiddlewares.php` after
            ``typo3/cms-frontend/site`` and before
            ``typo3/cms-frontend/base-redirect-resolver``.

    *   -   :php:`Site\SiteProfileFactory`
        -   Turns a :php:`Site` plus its root page row into a
            :php:`Domain\SiteProfile` — title, description, base URL and
            installation origin — so the builders never touch TYPO3's site
            object.

    *   -   :php:`Site\PageTreeReader`
        -   Reads the visible two levels below the root page and returns
            :php:`Domain\Section` objects holding :php:`Domain\PageLink`
            lists.

    *   -   :php:`Site\AgentSurfacesFactory`
        -   Detects which machine interfaces exist and returns a
            :php:`Domain\AgentSurfaces` value object.

    *   -   :php:`Content\LlmsTxtBuilder` and
            :php:`Content\AgentsMdBuilder`
        -   Render Markdown from those value objects. Pure functions of
            their input, which is what makes them unit-testable.

The same collaborators back the :php:`Command\DumpCommand`, so the CLI
output and the HTTP response can never drift apart.

..  _developer-surfaces:

Extending the surface detection
===============================

:php:`AgentSurfacesFactory` is a plain service, not final by accident: it
is overridable through :file:`Services.yaml` when an installation exposes
a machine interface this extension does not know about. Detection follows
one rule — never advertise what is not installed:

..  code-block:: php

    // An extension key check for anything that ships as an extension …
    ExtensionManagementUtility::isLoaded('mcp_server');

    // … and a class_exists() guard for anything read from another package,
    // because that package is only a suggestion, never a requirement.
    class_exists(\Webconsulting\Abilities\Http\RestConfiguration::class);

The abilities REST base is read from the abilities extension
configuration rather than hard-coded, so a site that moved the projection
away from :file:`/abilities/v1` still advertises the correct URL.

..  _developer-tests:

Tests
=====

..  code-block:: bash

    composer ci                   # cgl, phpstan, unit, functional
    composer ci:tests:unit
    composer ci:tests:functional  # SQLite by default, no database server
    composer ci:phpstan           # level 8, no baseline
    composer ci:cgl -- --dry-run

The unit suite covers the two builders and the path matching. The
functional suite requests both files against a fixture site and page tree
and asserts the content type, the published page list — including the
pages that must stay out of it — the abilities advertisement built from
the real registry, and the per-site opt-out.

The functional suite defaults to :bash:`pdo_sqlite` through
:file:`Build/phpunit/FunctionalTests.xml`. CI overrides the
``typo3Database*`` environment variables to run the same suite against
MariaDB; because the ``<env>`` entries are not forced, an existing
environment variable always wins.
