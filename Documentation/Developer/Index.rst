..  include:: /Includes.rst.txt

..  _developer:

===================
Developer reference
===================

..  _developer-architecture:

Architecture
============

Six small pieces, each with one job:

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

    *   -   :php:`Domain\AgentFile`
        -   The enum of the two files, and the path matching: only
            :file:`llms.txt` and :file:`agents.md` directly below a
            language base ever match.

    *   -   :php:`Content\AgentFileRenderer`
        -   Renders one file for one site language. The middleware and
            :php:`Command\DumpCommand` both go through here, so the HTTP
            response and the CLI output can never drift apart.

    *   -   :php:`Site\SiteReader`
        -   Reads what one site language publishes: the
            :php:`Domain\SiteProfile` (title, summary, base URL, origin)
            and the visible two levels below the root page as
            :php:`Domain\Section` objects of :php:`Domain\PageLink` lists.
            Page URLs come from the site's own :php:`PageRouter`.

    *   -   :php:`Site\AgentSurfacesFactory`
        -   Detects which machine interfaces exist and returns a
            :php:`Domain\AgentSurfaces` value object.

    *   -   :php:`Content\LlmsTxtBuilder` and
            :php:`Content\AgentsMdBuilder`
        -   Render Markdown from those value objects, escaping editorial
            text through :php:`Content\Markdown`. Pure functions of their
            input, which is what makes them unit-testable.

..  _developer-surfaces:

How the surface detection stays optional
========================================

Never advertise what is not installed. Two mechanisms carry that rule:

..  code-block:: php

    // An extension key check for anything that ships as an extension.
    ExtensionManagementUtility::isLoaded('mcp_server');

..  code-block:: php

    // Constructor arguments with a null default for anything read from
    // another package. When that package is absent no service matches
    // the type, the container passes the default, and the corresponding
    // block disappears from agents.md.
    public function __construct(
        private ExtensionConfiguration $extensionConfiguration,
        private ?AbilitiesRegistry $registry = null,
        private ?CapabilityCatalog $catalog = null,
    ) {}

A registry therefore means "abilities is installed", and a capability
catalogue means "abilities 1.1 or newer". The REST base is read from the
abilities extension configuration rather than hard-coded, so a site that
moved the projection away from :file:`/abilities/v1` still advertises the
correct URL.

:php:`AgentSurfacesFactory` is :php:`final readonly`; an installation that
exposes an interface this extension does not know about replaces the
service in its own :file:`Services.yaml`.

..  _developer-tests:

Tests
=====

..  code-block:: bash

    composer ci                   # cgl, phpstan, unit, functional
    composer ci:tests:unit
    composer ci:tests:functional  # SQLite by default, no database server
    composer ci:phpstan           # level 8, no baseline
    composer ci:cgl -- --dry-run

The unit suite covers the two builders, the Markdown escaping and the
path matching. The functional suite runs three scenarios against the same
fixture site: with the abilities registry installed, without it — which is
what proves the optional constructor arguments really are optional — and
through the :bash:`llmstxt:dump` command.

The functional suite defaults to :bash:`pdo_sqlite` through
:file:`Build/phpunit/FunctionalTests.xml`. CI overrides the
``typo3Database*`` environment variables to run the same suite against
MariaDB; because the ``<env>`` entries are not forced, an existing
environment variable always wins.
