<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetStoreContext
{
    public function handle(Request $request, Closure $next)
    {
        // Get store_id from request header or query parameter
        $storeId = $request->header('X-Store-Id') ?? $request->query('store_id');

        if ($storeId) {
            // Set in request for use in controllers
            $request->attributes->set('store_id', $storeId);
        }

        return $next($request);
    }
}
