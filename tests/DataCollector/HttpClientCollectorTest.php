<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use DebugBar\DataFormatter\HtmlDataFormatter;
use Fruitcake\LaravelDebugbar\DataCollector\HttpClientCollector;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;

class HttpClientCollectorTest extends TestCase
{
    public function testItCollectsResponseReceivedEvents()
    {
        $collector = new HttpClientCollector();

        $request = new Request(new \GuzzleHttp\Psr7\Request('GET', 'https://example.com/api/test'));
        $psrResponse = new Psr7Response(200, ['Content-Type' => 'application/json'], '{"success":true}');
        $response = new Response($psrResponse);

        $event = new ResponseReceived($request, $response);
        $collector->addEvent($event);

        $data = $collector->collect();

        static::assertEquals(1, $data['nb_requests']);
        static::assertCount(1, $data['requests']);

        $requestData = $data['requests'][0];
        static::assertEquals('GET', $requestData['method']);
        static::assertEquals('https://example.com/api/test', $requestData['url']);
        static::assertEquals(200, $requestData['status']);
        static::assertArrayHasKey('details', $requestData);
    }

    public function testItCollectsConnectionFailedEvents()
    {
        $collector = new HttpClientCollector();

        $request = new Request(new \GuzzleHttp\Psr7\Request('POST', 'https://example.com/api/fail'));
        $exception = new ConnectionException('Connection failed');

        $event = new ConnectionFailed($request, $exception);
        $collector->addEvent($event);

        $data = $collector->collect();

        static::assertEquals(1, $data['nb_requests']);
        static::assertCount(1, $data['requests']);

        $requestData = $data['requests'][0];
        static::assertEquals('POST', $requestData['method']);
        static::assertEquals('https://example.com/api/fail', $requestData['url']);
        static::assertNull($requestData['status']);
        static::assertArrayHasKey('details', $requestData);

        // Exception is not available in Laravel 10
        if (isset($event->exception)) {
            static::assertArrayHasKey('exception', $requestData['details']);
        }
    }

    public function testItMasksAuthorizationHeader()
    {
        HttpClientCollector::setDefaultDataFormatter(new HtmlDataFormatter());
        $collector = new HttpClientCollector();

        $request = new Request(
            new \GuzzleHttp\Psr7\Request(
                'GET',
                'https://example.com/api/test',
                ['Authorization' => 'Bearer secret-token']
            )
        );
        $psrResponse = new Psr7Response(200, [], '');
        $response = new Response($psrResponse);

        $event = new ResponseReceived($request, $response);
        $collector->addEvent($event);

        $data = $collector->collect();

        $requestData = $data['requests'][0];
        static::assertArrayHasKey('request_headers', $requestData['details']);
        static::assertStringNotContainsString('secret-token', $requestData['details']['request_headers']);
    }

    public function testItCollectsMultipleEvents()
    {
        $collector = new HttpClientCollector();

        $request1 = new Request(new \GuzzleHttp\Psr7\Request('GET', 'https://example.com/api/1'));
        $psrResponse1 = new Psr7Response(200, [], '{"id":1}');
        $response1 = new Response($psrResponse1);
        $event1 = new ResponseReceived($request1, $response1);

        $request2 = new Request(new \GuzzleHttp\Psr7\Request('POST', 'https://example.com/api/2'));
        $psrResponse2 = new Psr7Response(201, [], '{"id":2}');
        $response2 = new Response($psrResponse2);
        $event2 = new ResponseReceived($request2, $response2);

        $collector->addEvent($event1);
        $collector->addEvent($event2);

        $data = $collector->collect();

        static::assertEquals(2, $data['nb_requests']);
        static::assertCount(2, $data['requests']);
        static::assertEquals('GET', $data['requests'][0]['method']);
        static::assertEquals('POST', $data['requests'][1]['method']);
    }
}
