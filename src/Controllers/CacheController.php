<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;

class CacheController extends BaseController
{
    /**
     * Forget a cache key
     *
     */
    public function delete(CacheManager $cache, Request $request, string $key): \Illuminate\Http\JsonResponse
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        if ($request->has('tags')) {
            $cache = $cache->tags($request->input('tags'));
        }

        $success = $cache->forget($key);

        return response()->json(compact('success'));
    }
}
