<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Telescope\Telescope;

readonly class StopRecordingTelescope
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     */
    public function handle($request, Closure $next): mixed
    {
        if (class_exists(Telescope::class)) {
            Telescope::stopRecording();
        }

        return $next($request);
    }
}
