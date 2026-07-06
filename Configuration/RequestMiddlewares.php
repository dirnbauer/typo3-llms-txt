<?php

declare(strict_types=1);

use Webconsulting\LlmsTxt\Middleware\LlmsTxtMiddleware;

return [
    'frontend' => [
        'webconsulting/llms-txt' => [
            'target' => LlmsTxtMiddleware::class,
            'description' => 'Serves /llms.txt and /agents.md generated from the page tree',
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
