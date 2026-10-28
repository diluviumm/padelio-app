<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloudflare Access matches the literal /admin path prefix; some servers
 * decode %2F in the URI afterwards, which could reach the admin routes
 * without the edge challenge. Reject encoded separators before routing.
 */
class BlockEncodedAdminPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $raw = (string) $request->server('REQUEST_URI', '');

        if (preg_match('#^//admin#i', $raw) || preg_match('#^/admin(?:%2f|%5c)#i', $raw)) {
            abort(404);
        }

        return $next($request);
    }
}
