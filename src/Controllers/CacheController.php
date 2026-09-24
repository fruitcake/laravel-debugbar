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
    public function delete(CacheManager $cache, CacheDeleteRequest $request): \Illuminate\Http\JsonResponse
    {
        $form = $request->validated();
        if ($form['tags'] ?? null) {
            $cache = $cache->tags($form['tags']);
        }

        $success = $cache->forget($form['key']);

        return response()->json(compact('success'));
    }
}
