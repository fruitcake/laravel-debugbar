<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Requests\QueriesExplainRequest;
use Fruitcake\LaravelDebugbar\Support\Explain;
use Exception;

class QueriesController
{
    /**
     * Generate explain data for query.
     */
    public function explain(QueriesExplainRequest $request, LaravelDebugbar $debugbar, Explain $explain): \Illuminate\Http\JsonResponse
    {
        if (!$debugbar->isStorageOpen($request)) {
            return response()->json([
                'success' => false,
                'message' => 'To enable public access to previous requests, set debugbar.storage.open to true in your config, or enable DEBUGBAR_OPEN_STORAGE if you did not publish the config.',
            ], 400);
        }

        $validated = $request->validated();
        foreach ($debugbar->getStorage()->get($validated['id'])['queries']['statements'] ?? [] as $query) {
            if (($query['explain']['hash'] ?? null) === $validated['hash']) {
                $validated += ['connection' => $query['explain']['connection'], 'query' => $query['explain']['query'], 'bindings' => $query['params']];
                break;
            }
        }

        if (($validated['mode'] ?? null) === 'result') {

            if (!config('debugbar.options.db.show_query_result', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query result is currently disabled in the Debugbar.',
                ], 400);
            }

            if (!($validated['query'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => "Statement #{$validated['hash']} not found.",
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $explain->generateSelectResult($validated['connection'], $validated['query'], $validated['bindings'] ?? [], $validated['hash'], $validated['format'] ?? null),
            ]);
        }

        if (config('debugbar.options.db.explain') !== true) {
            return response()->json([
                'success' => false,
                'message' => 'EXPLAIN is currently disabled in the Debugbar.',
            ], 400);
        }

        if (!($validated['query'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => "Statement #{$validated['hash']} not found.",
            ], 400);
        }

        try {
            if (($validated['mode'] ?? null) === 'visual') {
                return response()->json([
                    'success' => true,
                    'data' => $explain->generateVisualExplain($validated['connection'], $validated['query'], $validated['bindings'] ?? [], $validated['hash']),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $explain->generateRawExplain($validated['connection'], $validated['query'], $validated['bindings'] ?? [], $validated['hash']),
                'visual' => $explain->isVisualExplainSupported($validated['connection']) ? [
                    'confirm' => $explain->confirmVisualExplain($validated['connection']),
                ] : null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
