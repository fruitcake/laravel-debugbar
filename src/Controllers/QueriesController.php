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
        $validated = $request->validated();

        if (($validated['mode'] ?? null) === 'result') {

            if (!config('debugbar.options.db.show_query_result', false) || !$debugbar->isStorageOpen($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query result is currently disabled in the Debugbar.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $explain->generateSelectResult($validated['connection'], $validated['query'], $validated['bindings'] ?? null, $validated['hash'], $validated['format'] ?? null),
            ]);
        }

        if (config('debugbar.options.db.explain') !== true || !$debugbar->isStorageOpen($request)) {
            return response()->json([
                'success' => false,
                'message' => 'EXPLAIN is currently disabled in the Debugbar.',
            ], 400);
        }

        try {
            if (($validated['mode'] ?? null) === 'visual') {
                return response()->json([
                    'success' => true,
                    'data' => $explain->generateVisualExplain($validated['connection'], $validated['query'], $validated['bindings'] ?? null, $validated['hash']),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $explain->generateRawExplain($validated['connection'], $validated['query'], $validated['bindings'] ?? null, $validated['hash']),
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
