<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar;

use DebugBar\Bridge\Symfony\SymfonyHttpDriver;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\JavascriptRenderer;
use DebugBar\RequestIdGeneratorInterface;
use DebugBar\Storage\FileStorage;
use Fruitcake\LaravelDebugbar\CollectorProviders\ConfigCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\ExceptionsCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\HttpClientCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\InertiaCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\RequestCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\SessionCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\AuthCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\CacheCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\DatabaseCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\EventsCollectorCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\GateCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\JobsCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\LaravelCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\LivewireCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\LogCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\LogsCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\MailCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\MemoryCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\MessagesCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\ModelsCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\PennantCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\PhpInfoCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\RouteCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\TimeCollectorProvider;
use Fruitcake\LaravelDebugbar\CollectorProviders\ViewsCollectorProvider;
use Fruitcake\LaravelDebugbar\DataCollector\RequestCollector;
use Fruitcake\LaravelDebugbar\Support\Clockwork\ClockworkCollector;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataFormatter\HtmlDataFormatter;
use DebugBar\DebugBar;
use DebugBar\HttpDriverInterface;
use DebugBar\Storage\PdoStorage;
use DebugBar\Storage\RedisStorage;
use DebugBar\Storage\SqliteStorage;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\VarDumper\Cloner\Stub;
use Throwable;

/**
 * Debug bar subclass which adds all without Request and with LaravelCollector.
 * Rest is added in Service Provider
 *
 * @method void emergency(...$message)
 * @method void alert(...$message)
 * @method void critical(...$message)
 * @method void error(...$message)
 * @method void warning(...$message)
 * @method void notice(...$message)
 * @method void info(...$message)
 * @method void debug(...$message)
 * @method void log(...$message)
 */
class LaravelDebugbar extends DebugBar
{
    protected Application $app;
    protected Request $request;
    protected ?Job $processingJob = null;
    protected bool $booted = false;

    protected ?bool $enabled = null;

    /**
     * Laravel default error handler
     *
     * @var callable|null
     */
    protected $prevErrorHandler = null;

    protected ?string $editorTemplate = null;
    protected bool $responseIsModified = false;
    protected TimeDataCollector $timeCollector;
    protected MessagesCollector $messagesCollector;
    protected ExceptionsCollector $exceptionsCollector;

    public function __construct(Application $app, Request $request)
    {
        $startTime = defined('LARAVEL_START') ? (float) LARAVEL_START : microtime(true);

        $this->app = $app;
        $this->request = $request;
        $this->timeCollector = new TimeDataCollector($startTime);
        $this->messagesCollector = new MessagesCollector();
        $this->exceptionsCollector = new ExceptionsCollector();
    }

    public function setApplication(Application $app): void
    {
        $this->app = $app;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setProcessingJob(?Job $job): void
    {
        $this->processingJob = $job;
    }

    public function getProcessingJob(): ?Job
    {
        return $this->processingJob;
    }

    public function getHttpDriver(): HttpDriverInterface
    {
        if ($this->httpDriver === null) {
            $this->httpDriver = new LaravelHttpDriver($this->request);
        }

        return $this->httpDriver;
    }

    public function getRequestIdGenerator(): RequestIdGeneratorInterface
    {
        if ($this->requestIdGenerator === null) {
            $this->requestIdGenerator = new class implements RequestIdGeneratorInterface {
                public function generate(): string
                {
                    return (string) Str::ulid();
                }
            };
        }

        return $this->requestIdGenerator;
    }

    public function getTimeCollector(): TimeDataCollector
    {
        return $this->timeCollector;
    }

    public function getMessagesCollector(): MessagesCollector
    {
        return $this->messagesCollector;
    }

    public function getExceptionsCollector(): ExceptionsCollector
    {
        return $this->exceptionsCollector;
    }

    public function isCollecting(): bool
    {
        return $this->enabled && $this->booted;
    }

    /**
     * Enable the Debugbar and boot, if not already booted.
     */
    public function enable(): void
    {
        $this->enabled = true;

        if (!$this->booted) {
            $this->boot();
        }
    }

    /**
     * Boot the debugbar (add collectors, renderer and listener)
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $config = config();

        $this->editorTemplate = $config->get('debugbar.editor') ?: $config->get('app.editor');
        $this->remotePathReplacements = $this->getRemoteServerReplacements();

        // Set custom error handler
        if ($config->get('debugbar.error_handler', false)) {
            // Get the error_level config, default to E_ALL
            $errorLevel = $config->get('debugbar.error_level', E_ALL);

            // set error handler with configured error reporting level
            $this->prevErrorHandler = set_error_handler([$this, 'handleError'], $errorLevel);
        }

        $this->selectStorage($this);

        $this->registerDataFormatter();

        $this->registerCollectors();

        $this->booted = true;
    }

    public function booted(): void
    {
        $startTime = defined('LARAVEL_START') ? (float) LARAVEL_START : null;
        if ($startTime) {
            $this->addMeasure('Booting', $startTime, microtime(true));
        }
        $this->startMeasure('application', 'Application', 'time');
    }

    protected function registerCollectors(): void
    {
        // Register default Collector Provider
        $this->registerCollectorProviders([
            'symfony_request' => RequestCollectorProvider::class,
            'exceptions' => ExceptionsCollectorProvider::class,
            'phpinfo' => PhpInfoCollectorProvider::class,
            'messages' => MessagesCollectorProvider::class,
            'time' => TimeCollectorProvider::class,
            'memory' => MemoryCollectorProvider::class,
            'laravel' => LaravelCollectorProvider::class,
            'events' => EventsCollectorCollectorProvider::class,
            'views' => ViewsCollectorProvider::class,
            'route' => RouteCollectorProvider::class,
            'log' => LogCollectorProvider::class,
            'logs' => LogsCollectorProvider::class,
            'db' => DatabaseCollectorProvider::class,
            'models' => ModelsCollectorProvider::class,
            'livewire' => LivewireCollectorProvider::class,
            'inertia' => InertiaCollectorProvider::class,
            'mail' => MailCollectorProvider::class,
            'auth' => AuthCollectorProvider::class,
            'gate' => GateCollectorProvider::class,
            'cache' => CacheCollectorProvider::class,
            'jobs' => JobsCollectorProvider::class,
            'pennant' => PennantCollectorProvider::class,
            'config' => ConfigCollectorProvider::class,
            'session' => SessionCollectorProvider::class,
            'http_client' => HttpClientCollectorProvider::class,
        ]);

        // Register any Custom Collectors
        $this->registerCustomCollectorProviders(config('debugbar.custom_collectors', []));
    }

    /**
     * @param array<string, string> $providers
     */
    protected function registerCollectorProviders(array $providers): void
    {
        /** @var Repository $config */
        $config = $this->app->get(Repository::class);
        foreach ($providers as $name => $provider) {
            if (!$this->shouldCollect($name)) {
                continue;
            }
            try {
                $options = $config->get('debugbar.options.' . $name, []);
                $this->app->call($provider, ['options' => $options]);
            } catch (Exception $e) {
                $this->addCollectorException('Error calling ' . class_basename($provider), $e);
            }
        }
    }

    /**
     * @param array<string, bool> $providers
     */
    protected function registerCustomCollectorProviders(array $providers): void
    {
        foreach ($providers as $provider => $enabled) {
            if (!$enabled) {
                continue;
            }
            try {
                $provider = $this->app->make($provider);
                // Add collectors directly, otherwise invoke the class
                if (is_a($provider, DataCollectorInterface::class)) {
                    $this->addCollector($provider);
                } else {
                    $this->app->call($provider);
                }
            } catch (Exception $e) {
                $this->addCollectorException('Error calling ' . class_basename($provider), $e);
            }
        }
    }

    /**
     * Register some Casters to avoid large objects for events etc.
     */
    protected function registerDataFormatter(): void
    {
        $formatter = new HtmlDataFormatter();

        $formatter->mergeClonerOptions([
            'casters' => [
                \Illuminate\View\View::class => static function (\Illuminate\View\View $view, array $a, Stub $stub): array {
                    return [
                        'name' => $view->getName(),
                        'data' => $view->getData(),
                        'path' => $view->getPath(),
                        'engine' => get_class($view->getEngine()),
                        'factory' => get_class($view->getFactory()),
                    ];
                },
                \Illuminate\Database\ConnectionInterface::class => static function (\Illuminate\Database\ConnectionInterface $connection, array $a, Stub $stub): array {
                    return [
                        'database' => $connection->getDatabaseName(),
                    ];
                },
            ],
        ]);

        DataCollector::setDefaultDataFormatter($formatter);
    }

    public function getJavascriptRenderer(?string $baseUrl = null, ?string $basePath = null): JavascriptRenderer
    {
        if ($this->jsRenderer !== null) {
            return $this->jsRenderer;
        }

        $renderer = new JavascriptRenderer($this, $baseUrl, $basePath);

        $config = config();
        $renderer->setHideEmptyTabs($config->get('debugbar.hide_empty_tabs', true));
        $renderer->setIncludeVendors($config->get('debugbar.include_vendors', true));
        $renderer->setBindAjaxHandlerToFetch($config->get('debugbar.capture_ajax', true));
        $renderer->setBindAjaxHandlerToXHR($config->get('debugbar.capture_ajax', true));
        $renderer->setDeferDatasets($config->get('debugbar.defer_datasets', false));
        $renderer->setUseDistFiles($config->get('debugbar.use_dist_files', true));
        $renderer->setAjaxHandlerAutoShow($config->get('debugbar.ajax_handler_auto_show', true));
        $renderer->setAjaxHandlerEnableTab($config->get('debugbar.ajax_handler_enable_tab', true));
        $renderer->setTheme($config->get('debugbar.theme', 'auto'));

        $renderer->setAssetHandlerUrl(route('debugbar.assets'));
        $renderer->addAssets(cssFiles: ['laravel-debugbar.css', 'laravel-icons.css'], basePath: __DIR__ . '/../resources');

        if ($this->getStorage()) {
            $renderer->setOpenHandlerUrl(route('debugbar.openhandler'));
        }

        $this->jsRenderer = $renderer;

        return $this->jsRenderer;
    }

    public function shouldCollect(string $name, bool $default = true): bool
    {
        return config('debugbar.collectors.' . $name, $default);
    }

    /**
     * Handle silenced errors
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0, array $context = []): mixed
    {
        if ($this->hasCollector('exceptions')) {
            /** @var ExceptionsCollector $exceptionCollector */
            $exceptionCollector = $this['exceptions'];
            $exceptionCollector->addWarning($level, $message, $file, $line);
        }

        if ($this->hasCollector('messages')) {
            /** @var MessagesCollector $messagesCollector */
            $messagesCollector = $this['messages'];
            $file = $file ? ' on ' . $messagesCollector->normalizeFilePath($file) . ":{$line}" : '';
            $messagesCollector->addMessage($message . $file, 'deprecation');
        }

        if (! $this->prevErrorHandler) {
            return null;
        }

        return call_user_func($this->prevErrorHandler, $level, $message, $file, $line, $context);
    }

    /**
     * Starts a measure
     *
     * @param string      $name  Internal name, used to stop the measure
     * @param string|null $label Public name
     */
    public function startMeasure(string $name, ?string $label = null, ?string $collector = null, ?string $group = null): void
    {
        $this->timeCollector->startMeasure($name, $label, $collector, $group);
    }

    /**
     * Stops a measure
     */
    public function stopMeasure(string $name): void
    {
        try {
            $this->timeCollector->stopMeasure($name);
        } catch (Exception $e) {
            $this->addThrowable($e);
        }
    }

    /**
     * Alias for addThrowable
     *
     */
    public function addException(Throwable $e): void
    {
        $this->addThrowable($e);
    }

    /**
     * Adds an exception to be profiled in the debug bar
     */
    public function addThrowable(Throwable $e): void
    {
        $this->exceptionsCollector->addThrowable($e);
    }

    /**
     * Register collector exceptions
     *
     */
    protected function addCollectorException(string $message, Exception $exception): void
    {
        $this->addThrowable(
            new Exception(
                $message . ' on Laravel Debugbar: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            ),
        );
    }

    /**
     * Modify the response and inject the debugbar (or data in headers)
     */
    public function handleResponse(Request $request, SymfonyResponse $response): SymfonyResponse
    {
        $this->setRequest($request);

        if (
            $this->responseIsModified
            || !$this->booted
            || !$this->isEnabled()
            || $this->isDebugbarRequest($request)
            || $this->requestIsExcluded($request)
        ) {
            return $response;
        }

        $config = $this->app->get(Repository::class);

        // Prevent duplicate modification
        $this->responseIsModified = true;

        // These rely on the Response, so we add them directly here
        $httpDriver = $this->getHttpDriver();
        if ($httpDriver instanceof LaravelHttpDriver) {
            $httpDriver->setRequest($request);
            $httpDriver->setResponse($response);
        } elseif ($httpDriver instanceof SymfonyHttpDriver) {
            $httpDriver->setResponse($response);
        }

        // Show the Http Response Exception in the Debugbar, when available
        if ($response instanceof Response && isset($response->exception)) {
            $this->addThrowable($response->exception);
        }

        // Update collectors that use the request/response
        if ($this->hasCollector('request')) {
            $collector = $this->getCollector('request');
            if ($collector instanceof RequestCollector) {
                $collector->setResponse($response);
            }
        }

        if ($config->get('debugbar.clockwork') && ! $this->hasCollector('clockwork')) {
            try {
                $clockworkCollector = new ClockworkCollector($request, $response);
                $this->addCollector($clockworkCollector);
            } catch (Exception $e) {
                $this->addCollectorException('Cannot add ClockworkCollector', $e);
            }

            $this->addClockworkHeaders($response);
        }

        if ($config->get('debugbar.add_ajax_timing', false)) {
            $this->addServerTimingHeaders($response);
        }

        if ($response->isRedirection()) {
            try {
                $this->stackData();
            } catch (Exception $e) {
                $this->app['log']->error('Debugbar exception: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            return $response;
        }

        try {
            // Collect + store data, only inject the ID in theheaders
            $this->sendDataInHeaders(true);
        } catch (Exception $e) {
            $this->app['log']->error('Debugbar exception: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        // Check if it's safe to inject the Debugbar
        if (
            $config->get('debugbar.inject', true)
            && str_contains($response->headers->get('Content-Type', 'text/html'), 'html')
            && !$this->isJsonRequest($request)
            && !$this->isJsonResponse($response)
            && $response->getContent() !== false
            && in_array($request->getRequestFormat(), [null, 'html'], true)
        ) {
            try {
                $this->injectDebugbar($response);
            } catch (Exception $e) {
                $this->app['log']->error('Debugbar exception: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        return $response;
    }

    /**
     * Check if the Debugbar is enabled
     */
    public function isEnabled(): bool
    {
        if ($this->enabled === null) {
            $configEnabled = value(config('debugbar.enabled'));

            if ($configEnabled === null) {
                $configEnabled = config('app.debug');
            }

            $this->enabled = $configEnabled && !$this->app->runningInConsole() && !$this->app->environment('testing');
        }

        return $this->enabled;
    }

    public function requestIsExcluded(Request $request): bool
    {
        $except = config('debugbar.except') ?: [];
        if (!$except) {
            return false;
        }

        $except = array_map(function ($item): string {
            return $item !== '/' ? trim($item, '/') : $item;
        }, $except);

        return $request->is($except);
    }

    /**
     * Check if this is a request to the Debugbar OpenHandler
     */
    protected function isDebugbarRequest(Request $request): bool
    {
        return $request->is(config('debugbar.route_prefix') . '*');
    }

    protected function isJsonRequest(Request $request): bool
    {
        // If XmlHttpRequest, Live or HTMX, return true
        if (
            $request->isXmlHttpRequest()
            || $request->headers->has('X-Livewire')
            || ($request->headers->has('Hx-Request') && $request->headers->has('Hx-Target'))
        ) {
            return true;
        }

        // Check if the request wants Json
        $acceptable = $request->getAcceptableContentTypes();
        if (isset($acceptable[0]) && in_array($acceptable[0], ['application/json', 'application/javascript'], true)) {
            return true;
        }

        return false;
    }

    protected function isJsonResponse(SymfonyResponse $response): bool
    {
        if ($response instanceof JsonResponse || $response->headers->get('Content-Type') === 'application/json') {
            return true;
        }

        $content = $response->getContent();
        if (is_string($content)) {
            $content = trim($content);
            if ($content === '') {
                return false;
            }

            // Quick check to see if it looks like JSON
            $first = $content[0];
            $last  = $content[strlen($content) - 1];
            if (
                ($first === '{' && $last === '}')
                || ($first === '[' && $last === ']')
            ) {
                // Must contain a colon or comma
                return strpbrk($content, ':,') !== false;
            }
        }

        return false;
    }

    /**
     * Collects the data from the collectors
     *
     */
    public function collect(): array
    {
        $this->data = [
            '__meta' => [
                'id' => $this->getCurrentRequestId(),
                'datetime' => date('Y-m-d H:i:s'),
                'utime' => microtime(true),
                'method' => $this->request->getMethod(),
                'uri' => $this->request->getRequestUri(),
                'ip' => $this->request->getClientIp(),
            ],
        ];

        if ($this->processingJob) {
            $this->data['__meta']['method'] = 'JOB';
            $this->data['__meta']['uri'] =  $this->processingJob->resolveName() . '@' . $this->processingJob->getConnectionName();
        } elseif ($this->app->runningInConsole()) {
            $this->data['__meta']['method'] = 'CLI';
            $this->data['__meta']['uri'] = implode(' ', (new ArgvInput())->getRawTokens());
        }

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive($this->data, function (&$item): void {
            if (is_float($item) && !is_finite($item)) {
                $item = '[NON-FINITE FLOAT]';
            } elseif (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });

        if ($this->storage !== null) {
            $this->storage->save($this->getCurrentRequestId(), $this->data);
        }

        return $this->data;
    }

    public function terminate(): void
    {
        if ($this->isCollecting() && $this->data === null && !$this->isDebugbarRequest($this->request)) {
            $this->collect();
        }
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * Based on https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     */
    public function injectDebugbar(SymfonyResponse $response): void
    {
        $content = $response->getContent();

        $renderer = $this->getJavascriptRenderer();

        $widget = "<!-- Laravel Debugbar Widget -->\n" . $renderer->renderHead() . $renderer->render();

        // Try to put the widget at the end, directly before the </body>
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $widget . substr($content, $pos);
        } else {
            $content = $content . $widget;
        }

        $original = null;
        if ($response instanceof Response && $response->getOriginalContent()) {
            $original = $response->getOriginalContent();
        }

        // Update the new content and reset the content length
        $response->setContent($content);
        $response->headers->remove('Content-Length');

        // Restore original response (e.g. the View or Ajax data)
        if ($response instanceof Response && $original) {
            $response->original = $original;
        }
    }

    /**
     * Disable the Debugbar
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    public function reset(): void
    {
        parent::reset();
        $this->timeCollector->reset();
        $this->exceptionsCollector->reset();
        $this->messagesCollector->reset();
        $this->enabled = null;
        $this->responseIsModified = false;
        $this->httpDriver = null;
    }

    /**
     * Adds a measure
     */
    public function addMeasure(string $label, float $start, ?float $end = null, array $params = [], ?string $collector = null, ?string $group = null): void
    {
        $this->timeCollector->addMeasure($label, $start, $end, $params, $collector, $group);
    }

    /**
     * Utility function to measure the execution of a Closure
     */
    public function measure(string $label, \Closure $closure, ?string $collector = null, ?string $group = null): mixed
    {
        return $this->timeCollector->measure($label, $closure, $collector, $group);
    }

    /**
     * Magic calls for adding messages
     */
    public function __call(string $method, array $args): void
    {
        $messageLevels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'];
        if (in_array($method, $messageLevels, true)) {
            foreach ($args as $arg) {
                $this->addMessage($arg, $method);
            }
        }
    }

    /**
     * Adds a message to the MessagesCollector
     *
     * A message can be anything from an object to a string
     */
    public function addMessage(mixed $message, string $label = 'info', array $context = []): void
    {
        $this->messagesCollector->addMessage($message, $label, $context);
    }

    /**
     * Check the version of Laravel
     */
    public function checkVersion(string $version, string $operator = ">="): bool
    {
        return version_compare($this->app->version(), $version, $operator);
    }

    protected function selectStorage(DebugBar $debugbar): void
    {
        /** @var Repository $config */
        $config = config();
        if ($config->get('debugbar.storage.enabled')) {
            $driver = strtolower($config->get('debugbar.storage.driver', 'file'));

            switch ($driver) {
                case 'pdo':
                    $connection = $config->get('debugbar.storage.connection');
                    $table = $this->app['db']->getTablePrefix() . 'phpdebugbar';
                    $pdo = $this->app['db']->connection($connection)->getPdo();
                    $storage = new PdoStorage($pdo, $table);
                    break;
                case 'redis':
                    $connection = $config->get('debugbar.storage.connection');
                    $client = $this->app['redis']->connection($connection);
                    if (is_a($client, 'Illuminate\Redis\Connections\Connection', false)) {
                        $client = $client->client();
                    }
                    $storage = new RedisStorage($client);
                    break;
                case 'custom':
                    $class = $config->get('debugbar.storage.provider');
                    $storage = $this->app->make($class);
                    break;
                case 'socket':
                    throw new \RuntimeException('Socket storage is not supported anymore.');
                case 'file':
                    $path = $config->get('debugbar.storage.path');
                    $storage = new FileStorage($path);
                    break;
                case 'sqlite':
                    $path = $config->get('debugbar.storage.path');
                    $storage = new SqliteStorage($path . '/debugbar.sqlite');
                    break;
                default:
                    throw new \RuntimeException('Invalid storage selected: ' . $driver);

            }

            $debugbar->setStorage($storage);
        }
    }

    protected function addClockworkHeaders(SymfonyResponse $response): void
    {
        $prefix = config('debugbar.route_prefix');
        $response->headers->set('X-Clockwork-Id', $this->getCurrentRequestId(), true);
        $response->headers->set('X-Clockwork-Version', "9", true);
        $response->headers->set('X-Clockwork-Path', $prefix . '/clockwork/', true);
    }

    /**
     * Add Server-Timing headers for the TimeData collector
     *
     * @see https://www.w3.org/TR/server-timing/
     */
    protected function addServerTimingHeaders(SymfonyResponse $response): void
    {
        if ($this->hasCollector('time')) {
            $collector = $this->timeCollector;

            $headers = [];
            foreach ($collector->collect()['measures'] as $m) {
                $headers[] = sprintf('app;desc="%s";dur=%F', str_replace(["\n", "\r"], ' ', str_replace('"', "'", $m['label'])), $m['duration'] * 1000);
            }

            $response->headers->set('Server-Timing', $headers, false);
        }
    }

    private function getRemoteServerReplacements(): array
    {
        $localPath = config('debugbar.local_sites_path') ?: base_path();
        $remotePaths = array_filter(explode(',', config('debugbar.remote_sites_path') ?: '')) ?: [base_path()];

        return array_fill_keys($remotePaths, $localPath);
    }
}
