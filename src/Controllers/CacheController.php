<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use Fruitcake\LaravelDebugbar\Requests\CacheDeleteRequest;
use Illuminate\Cache\CacheManager;

class CacheController
{
    /**
     * Forget a cache key
     *
     */
    public function delete(CacheManager $cache, CacheDeleteRequest $request, string $key): \Illuminate\Http\JsonResponse
    {
        if ($tags = $request->validated('tags')) {
            $cache = $cache->tags($tags);
        }

        $success = $cache->forget($key);

        return response()->json(compact('success'));
    }
}
