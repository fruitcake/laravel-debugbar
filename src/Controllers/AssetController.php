<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use DebugBar\AssetHandler;
use DebugBar\Bridge\Symfony\SymfonyHttpDriver;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\LaravelHttpDriver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AssetController
{
    public function getAssets(Request $request, AssetHandler $assetHandler, LaravelDebugbar $debugbar): Response
    {
        $type = (string) $request->input('type');

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
