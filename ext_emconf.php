<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'llms.txt / agents.md',
    'description' => 'Serves /llms.txt and /agents.md per site, generated from the page tree and site configuration — deliberate publishing for the agent-readable web.',
    'category' => 'fe',
    'author' => 'Kurt Dirnbauer',
    'author_email' => 'dirnbauer@webconsulting.at',
    'author_company' => 'webconsulting business services gmbh',
    'state' => 'alpha',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.99.99',
            'php' => '8.3.0-8.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'mcp_server' => '',
            'abilities' => '',
        ],
    ],
];
