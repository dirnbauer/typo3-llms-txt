..  include:: /Includes.rst.txt

..  _start:

==========================
TYPO3 llms.txt / agents.md
==========================

:Extension key:
    llms_txt

:Package name:
    webconsulting/typo3-llms-txt

:Version:
    |release|

:Language:
    en

:Author:
    Kurt Dirnbauer, webconsulting business services gmbh

:License:
    This document is published under the
    `Creative Commons BY 4.0 <https://creativecommons.org/licenses/by/4.0/>`__
    license.

:Rendered:
    |today|

----

Two generated files per site, served from the page tree and the site
configuration: :file:`llms.txt` for content discovery and :file:`agents.md`
for operation. Deliberate publishing for the agent-readable web — not
hiding from agents, and not letting them guess either.

----

..  card-grid::
    :columns: 1
    :columns-md: 2
    :gap: 4
    :class: pb-4
    :card-height: 100

    ..  card:: Introduction

        What the two files are, what goes into them and why a site
        publishes them.

        ..  card-footer:: :ref:`Read the introduction <introduction>`
            :button-style: btn btn-secondary stretched-link

    ..  card:: Installation

        One :bash:`composer require`, then every site serves both files.

        ..  card-footer:: :ref:`Install the extension <installation>`
            :button-style: btn btn-secondary stretched-link

    ..  card:: Configuration

        The per-site opt-out, and which page fields end up in the output.

        ..  card-footer:: :ref:`Configure a site <configuration>`
            :button-style: btn btn-secondary stretched-link

    ..  card:: Developer reference

        The middleware, the builders, the surface detection and the test
        suites.

        ..  card-footer:: :ref:`Read the reference <developer>`
            :button-style: btn btn-secondary stretched-link

..  toctree::
    :maxdepth: 2
    :titlesonly:

    Introduction/Index
    Installation/Index
    Configuration/Index
    Usage/Index
    Developer/Index

..  toctree::
    :hidden:

    Sitemap
