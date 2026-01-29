<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\Bridge\Symfony\SymfonyRequestCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Symfony\Component\Console\Input\ArgvInput;

class RequestCollector extends SymfonyRequestCollector implements DataCollectorInterface, Renderable
{
    /**
     * {@inheritdoc}
     */
    public function collect(): array
    {
        if ($job = debugbar()->getProcessingJob()) {
            return $this->collectJob($job);
        }
        if (app()->runningInConsole()) {
            return $this->collectCli();
        }

        $this->request = request();
        $result = parent::collect();
        if ($this->request->hasSession()) {
            $sessionAttributes = $this->hideMaskedValues($this->request->session()->all());
            $sessionAttributes = $this->getDataFormatter()->formatVar($sessionAttributes);
            $result['data']['session_attributes'] = $sessionAttributes;
        }
        $result['tooltip'] += [
            'full_url' => Str::limit($this->request->fullUrl(), 100),
        ];

        $htmlData = [];

        $route = $this->request->route();
        if ($route) {   // @phpstan-ignore-line despite what phpdocs say, this can return null
            $htmlData += $this->getRouteInformation($this->request->route());
            $result['tooltip'] += [
                'action_name' => $route->getName(),
                'controller_action' => $route->getActionName(),
            ];
        }

        if (class_exists(Telescope::class) && class_exists(IncomingEntry::class) && Telescope::isRecording()) {
            $entry = IncomingEntry::make([
                'requestId' => app(LaravelDebugbar::class)->getCurrentRequestId(),
            ])->type('debugbar');
            Telescope::$entriesQueue[] = $entry;
            $url = route('debugbar.telescope', [$entry->uuid]);
            $htmlData['telescope'] = '<a href="' . $url . '" target="_blank" class="phpdebugbar-widgets-external-link">View in Telescope</a>';
        }

        unset($htmlData['as'], $htmlData['uses']);

        $result['data'] = $htmlData + $result['data'];

        return $result;
    }

    protected function collectCli(): array
    {
        $argv = new ArgvInput();
        $command = $argv->getFirstArgument();
        $commands = Artisan::all();
        $commandClass = $commands[$command] ?? null;

        $data = [
            'method' => 'CLI',
            'command' => $command,
            'command_class' => $commandClass,
            'args' => (new ArgvInput())->getRawTokens(),
            'request_server' => $this->request->server->all(),
        ];

        $data = $this->hideMaskedValues($data);
        foreach ($data as $key => $var) {
            if (!is_string($var)) {
                $data[$key] = $this->getDataFormatter()->formatVar($var);
            }
        }

        if ($commandClass) {
            $reflector = new \ReflectionClass($commandClass);
            $filename = $this->normalizeFilePath($reflector->getFileName());

            if ($link = $this->getXdebugLink($reflector->getFileName(), $reflector->getStartLine())) {
                $data['command_class'] = [
                    'value' => sprintf('%s:%s-%s', $filename, $reflector->getStartLine(), $reflector->getEndLine()),
                    'xdebug_link' => $link,
                ];
            }
        }

        return ['data' => $data];
    }

    protected function collectJob(Job $job): array
    {
        $jobClass = $job->resolveQueuedJobClass();

        $data = [
            'method' => 'CLI',
            'job' => $job->resolveName(),
            'job_class' => $jobClass,
            'job_id' => $job->getJobId(),
            'connection' => $job->getConnectionName(),
            'queue' => $job->getQueue(),
            'payload' => $job->payload(),
        ];

        $data = $this->hideMaskedValues($data);
        foreach ($data as $key => $var) {
            if (!is_string($var)) {
                $data[$key] = $this->getDataFormatter()->formatVar($var);
            }
        }

        if ($jobClass) {
            $reflector = new \ReflectionClass($jobClass);
            $filename = $this->normalizeFilePath($reflector->getFileName());

            if ($link = $this->getXdebugLink($reflector->getFileName(), $reflector->getStartLine())) {
                $data['job_class'] = [
                    'value' => sprintf('%s:%s-%s', $filename, $reflector->getStartLine(), $reflector->getEndLine()),
                    'xdebug_link' => $link,
                ];
            }
        }

        return ['data' => $data];
    }

    protected function getRouteInformation(mixed $route): array
    {
        if (!is_a($route, 'Illuminate\Routing\Route')) {
            return [];
        }
        $uri = head($route->methods()) . ' ' . $route->uri();
        $action = $route->getAction();

        $result = [
            'uri' => $uri,
        ];

        $result = array_merge($result, $action);
        $uses = $action['uses'] ?? null;
        $controller = is_string($action['controller'] ?? null) ? $action['controller'] : '';

        if (request()->hasHeader('X-Livewire') && class_exists(HandleComponents::class)) {
            try {
                $componentData = $this->request->request->all()['components'][0] ?? null;
                if (isset($componentData['snapshot'], $componentData['updates'])) {
                    $snapshot = json_decode($componentData['snapshot'], true);
                    if (count($componentData['updates']) > 0) {
                        $method = $componentData['updates'][array_key_first($componentData['updates'])] ?? null;
                    } else {
                        $method = null;
                    }
                    [$component] = app(HandleComponents::class)->fromSnapshot($snapshot);
                    $result['controller'] = ltrim($component::class, '\\');
                    $reflector = new \ReflectionClass($component);
                    $controller = $component::class . '@' . $method;
                }
            } catch (\Throwable $e) {
                //
            }
        }

        if (str_contains($controller, '@')) {
            [$controller, $method] = explode('@', $controller);
            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflector = new \ReflectionMethod($controller, $method);
            }
            unset($result['uses']);
        } elseif ($uses instanceof \Closure) {
            $reflector = new \ReflectionFunction($uses);
            $result['uses'] = $this->getDataFormatter()->formatVar($uses);
        } elseif (is_string($uses) && str_contains($uses, '@__invoke')) {
            if (class_exists($controller) && method_exists($controller, 'render')) {
                $reflector = new \ReflectionMethod($controller, 'render');
                $result['controller'] = $controller . '@render';
            }
        }

        if (isset($reflector)) {
            $filename = $this->normalizeFilePath($reflector->getFileName());
            $result['file'] = sprintf('%s:%s-%s', $filename, $reflector->getStartLine(), $reflector->getEndLine());

            if ($link = $this->getXdebugLink($reflector->getFileName(), $reflector->getStartLine())) {
                $result['file'] = [
                    'value' => $result['file'],
                    'xdebug_link' => $link,
                ];

                if (isset($result['controller']) && is_string($result['controller'])) {
                    $result['controller'] = [
                        'value' => $result['controller'],
                        'xdebug_link' => $link,
                    ];
                }
            }
        }

        if (isset($result['middleware']) && is_array($result['middleware'])) {
            $middleware = implode(', ', $result['middleware']);
            unset($result['middleware']);
            $result['middleware'] = $middleware;
        }

        return array_filter($result);
    }
}
