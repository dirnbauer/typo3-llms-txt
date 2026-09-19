<?php

declare(strict_types=1);

namespace Webconsulting\LlmsTxt\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Webconsulting\LlmsTxt\Content\AgentFileRenderer;
use Webconsulting\LlmsTxt\Domain\AgentFile;

/**
 * Serves <language base>/llms.txt and <language base>/agents.md for every
 * site language. Runs after site resolution and before page resolution —
 * the two paths are virtual, no page records exist for them. Disable per
 * site with the setting llmsTxt.enabled: false.
 */
final readonly class LlmsTxtMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AgentFileRenderer $renderer,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        $language = $request->getAttribute('language');
        if (!$site instanceof Site || !$language instanceof SiteLanguage) {
            return $handler->handle($request);
        }

        $file = AgentFile::fromRequestPath($language->getBase()->getPath(), $request->getUri()->getPath());
        if ($file === null || !self::isEnabled($site)) {
            return $handler->handle($request);
        }

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withHeader('Cache-Control', 'public, max-age=3600')
            ->withHeader('X-Robots-Tag', 'noindex')
            ->withBody($this->streamFactory->createStream($this->renderer->render($site, $language, $file)));
    }

    private static function isEnabled(Site $site): bool
    {
        $setting = $site->getSettings()->get('llmsTxt.enabled', true);

        return filter_var($setting, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }
}
