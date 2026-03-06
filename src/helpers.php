<?php

declare(strict_types=1);

if (!function_exists('debugbar')) {
    /**
     * Get the Debugbar instance
     *
     */
    function debugbar(?string $collector = null): \Fruitcake\LaravelDebugbar\LaravelDebugbar|\DebugBar\DataCollector\DataCollectorInterface|null
    {
        $debugbar = app(\Fruitcake\LaravelDebugbar\LaravelDebugbar::class);
        if ($collector) {
            return $debugbar->hasCollector($collector) ? $debugbar->getCollector($collector) : null;
        }

        return $debugbar;
    }
}

if (!function_exists('debug')) {
    /**
     * Adds one or more messages to the MessagesCollector
     *
     */
    function debug(mixed ...$value): void
    {
        $debugbar = debugbar();
        foreach ($value as $message) {
            $debugbar->addMessage($message, 'debug');
        }
    }
}
