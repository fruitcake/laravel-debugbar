<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use DebugBar\Bridge\Symfony\SymfonyHttpDriver;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\LaravelHttpDriver;
use Fruitcake\LaravelDebugbar\Requests\OpenHandlerRequest;
use Fruitcake\LaravelDebugbar\Support\Clockwork\Converter;
use DebugBar\OpenHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OpenHandlerController
{
    public function handle(OpenHandlerRequest $request, LaravelDebugbar $debugbar, OpenHandler $openHandler): Response|JsonResponse
    {
        if ($request->validated('op') !== 'get' && !$debugbar->isStorageOpen($request)) {
            return new JsonResponse([
                [
                    'datetime' => date("Y-m-d H:i:s"),
                    'id' => null,
                    'ip' => $request->getClientIp(),
                    'method' => 'ERROR',
                    'uri' => '!! To enable public access to previous requests, set debugbar.storage.open to true in your config, or enable DEBUGBAR_OPEN_STORAGE if you did not publish the config. !!',
                    'utime' => microtime(true),
                ],
            ]);
        }

        $response = new Response();
        $driver = $debugbar->getHttpDriver();
        if ($driver instanceof LaravelHttpDriver || $driver instanceof SymfonyHttpDriver) {
            $driver->setResponse($response);
        }

        $openHandler->handle($request->input());

        return $response;
    }

    /**
     * Return Clockwork output
     *
     * @throws \DebugBar\DebugBarException
     */
    public function clockwork(OpenHandler $openHandler, $id): \Illuminate\Http\JsonResponse
    {
        $request = [
            'op' => 'get',
            'id' => $id,
        ];

        $data = $openHandler->handle($request, false, false);

        // Convert to Clockwork
        $converter = new Converter();
        $output = $converter->convert(json_decode($data, true));

        return response()->json($output);
    }
}
