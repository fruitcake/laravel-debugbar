<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use DebugBar\AssetHandler;
use DebugBar\Bridge\Symfony\SymfonyHttpDriver;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\LaravelHttpDriver;
use Fruitcake\LaravelDebugbar\Requests\AssetRequest;
use Illuminate\Http\Response;

class AssetController
{
    public function getAssets(AssetRequest $request, AssetHandler $assetHandler, LaravelDebugbar $debugbar): Response
    {
        $type = $request->validated('type');

        $response = new Response();
        $driver = $debugbar->getHttpDriver();
        if ($driver instanceof LaravelHttpDriver || $driver instanceof SymfonyHttpDriver) {
            $driver->setResponse($response);
        }

        $assetHandler->handle([
            'type' => $type,
        ]);

        return $response;
    }
}
