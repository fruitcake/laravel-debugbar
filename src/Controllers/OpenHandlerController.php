<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use DebugBar\Bridge\Symfony\SymfonyHttpDriver;
use Fruitcake\LaravelDebugbar\LaravelHttpDriver;
use Fruitcake\LaravelDebugbar\Support\Clockwork\Converter;
use DebugBar\OpenHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\IpUtils;

class OpenHandlerController extends BaseController
{
    /**
     * Check if the storage is open for inspecting.
     *
     */
    protected function isStorageOpen(Request $request): bool
    {
        $open = config('debugbar.storage.open');

        if (is_callable($open)) {
            return call_user_func($open, [$request]);
        }

        if (is_string($open) && class_exists($open)) {
            return method_exists($open, 'resolve') ? $open::resolve($request) : false;
        }

        if (is_bool($open)) {
            return $open;
        }

        // Allow localhost request when not explicitly allowed/disallowed
        if (IpUtils::isPrivateIp($request->getClientIp())) {
            return true;
        }

        return false;
    }

    public function handle(Request $request): Response|JsonResponse
    {
        if ($request->input('op') !== 'get' && !$this->isStorageOpen($request)) {
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

        $openHandler = new OpenHandler($this->debugbar);
        $driver = $this->debugbar->getHttpDriver();
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
    public function clockwork(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $request = [
            'op' => 'get',
            'id' => $id,
        ];

        $openHandler = new OpenHandler($this->debugbar);
        $data = $openHandler->handle($request, false, false);

        // Convert to Clockwork
        $converter = new Converter();
        $output = $converter->convert(json_decode($data, true));

        return response()->json($output);
    }
}
