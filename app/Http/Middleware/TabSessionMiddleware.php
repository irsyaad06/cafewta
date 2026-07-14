<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TabSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tabId = $request->header('X-Tab-Id') ?? $request->query('tab_id');

        if ($tabId) {
            // Override the session cookie name so that this tab gets its own isolated session
            config(['session.cookie' => 'cafewta_session_' . $tabId]);
        }

        $response = $next($request);

        // If the response is a redirect and we have a tabId, append it to the target URL
        if ($tabId && $response instanceof \Illuminate\Http\RedirectResponse) {
            $url = $response->getTargetUrl();
            $parsedUrl = parse_url($url);
            $query = [];
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $query);
            }
            $query['tab_id'] = $tabId;
            $newQuery = http_build_query($query);
            
            $newUrl = '';
            if (isset($parsedUrl['scheme'])) {
                $newUrl .= $parsedUrl['scheme'] . '://';
            }
            if (isset($parsedUrl['host'])) {
                $newUrl .= $parsedUrl['host'];
            }
            if (isset($parsedUrl['port'])) {
                $newUrl .= ':' . $parsedUrl['port'];
            }
            if (isset($parsedUrl['path'])) {
                $newUrl .= $parsedUrl['path'];
            }
            $newUrl .= '?' . $newQuery;
            if (isset($parsedUrl['fragment'])) {
                $newUrl .= '#' . $parsedUrl['fragment'];
            }
            $response->setTargetUrl($newUrl);
        }

        return $response;
    }
}
