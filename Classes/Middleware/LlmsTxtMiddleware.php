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
use Webconsulting\LlmsTxt\Content\AgentsMdBuilder;
use Webconsulting\LlmsTxt\Content\LlmsTxtBuilder;
use Webconsulting\LlmsTxt\Site\AgentSurfacesFactory;
use Webconsulting\LlmsTxt\Site\PageTreeReader;
use Webconsulting\LlmsTxt\Site\SiteProfileFactory;

/**
 * Serves <site base>/llms.txt and <site base>/agents.md, generated from
 * the page tree and site configuration. Runs after site resolution and
 * before page resolution — the two paths are virtual, no page records
 * exist for them. Disable per site with the setting llmsTxt.enabled: false.
 */
final class LlmsTxtMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SiteProfileFactory $profileFactory,
        private readonly PageTreeReader $pageTreeReader,
        private readonly AgentSurfacesFactory $surfacesFactory,
        private readonly LlmsTxtBuilder $llmsTxtBuilder,
        private readonly AgentsMdBuilder $agentsMdBuilder,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return $handler->handle($request);
        }

        $file = self::matchedFile($site->getBase()->getPath(), $request->getUri()->getPath());
        if ($file === null) {
            return $handler->handle($request);
        }

        if ($site->getSettings()->get('llmsTxt.enabled', true) === false) {
            return $handler->handle($request);
        }

        $profile = $this->profileFactory->fromSite($site);

        $content = $file === 'llms.txt'
            ? $this->llmsTxtBuilder->build($profile, $this->pageTreeReader->readSections($site, $profile))
            : $this->agentsMdBuilder->build($profile, $this->surfacesFactory->forSite($profile));

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withHeader('Cache-Control', 'public, max-age=3600')
            ->withHeader('X-Robots-Tag', 'noindex')
            ->withBody($this->streamFactory->createStream($content));
    }

    /**
     * @return 'llms.txt'|'agents.md'|null
     */
    public static function matchedFile(string $siteBasePath, string $requestPath): ?string
    {
        $prefix = rtrim($siteBasePath, '/');

        foreach (['llms.txt', 'agents.md'] as $file) {
            if ($requestPath === $prefix . '/' . $file) {
                return $file;
            }
        }

        return null;
    }
}
