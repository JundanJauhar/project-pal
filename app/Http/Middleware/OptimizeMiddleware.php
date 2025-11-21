<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add cache headers for static assets
        if ($request->is('images/*') || $request->is('css/*') || $request->is('js/*')) {
            $response->header('Cache-Control', 'public, max-age=31536000');
        }

        // Compress response
        if (
            $response instanceof Response &&
            !empty($response->getContent()) &&
            $this->shouldCompress($request)
        ) {
            $response->setContent($this->compressHtml($response->getContent()));
        }

        return $response;
    }

    /**
     * Check if response should be compressed
     */
    private function shouldCompress(Request $request): bool
    {
        return $request->header('Accept-Encoding') &&
               str_contains($request->header('Accept-Encoding'), 'gzip');
    }

    /**
     * Compress HTML output
     */
    private function compressHtml(string $html): string
    {
        // Remove comments
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Remove whitespace
        $html = preg_replace('/\s+/', ' ', $html);

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }
}
