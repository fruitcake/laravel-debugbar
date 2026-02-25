<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\Resettable;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

/**
 * Collector for Laravel's gate checks
 */
class GateCollector extends MessagesCollector implements Resettable
{
    protected array $reflection = [];
    protected int $backtraceLimit = 20;

    public function addCheck(mixed $user, string|int $ability, mixed $result, array $arguments = []): void
    {
        $userKey = 'user';
        $userId = null;

        if ($user) {
            $userKey = Str::snake(class_basename($user));
            $userId = $user instanceof Authenticatable ? $user->getAuthIdentifier() : $user->getKey();
        }

        $label = $result ? 'success' : 'error';

        if ($result instanceof Response) {
            $label = $result->allowed() ? 'success' : 'error';
        }

        $target = null;
        if (isset($arguments[0])) {
            if ($arguments[0] instanceof Model) {
                $model = $arguments[0];
                if ($model->getKeyName() && isset($model[$model->getKeyName()])) {
                    $target = get_class($model) . '(' . $model->getKeyName() . '=' . $model->getKey() . ')';
                } else {
                    $target = get_class($model);
                }
                $arguments[0] = $target;
            } elseif (is_string($arguments[0])) {
                $target = $arguments[0];
            }
        }

        $this->addMessage("{ability} {target}", $label, [
            'ability' => $ability,
            'target' => $target,
            'result' => $result,
            $userKey => $userId,
            'arguments' => $arguments,
        ]);
    }

    protected function getStackTraceItem(array $stacktrace): array
    {
        foreach ($stacktrace as $i => $trace) {
            if (!isset($trace['file'])) {
                continue;
            }

            if (str_ends_with($trace['file'], 'Illuminate/Routing/ControllerDispatcher.php')) {
                $trace = $this->findControllerFromDispatcher($trace);
            } elseif (str_starts_with($trace['file'], storage_path())) {
                $hash = pathinfo($trace['file'], PATHINFO_FILENAME);

                if ($file = $this->findViewFromHash($hash)) {
                    $trace['file'] = $file;
                }
            }

            if ($this->fileIsInExcludedPath($trace['file'])) {
                continue;
            }

            return $trace;
        }

        return $stacktrace[0];
    }

    /**
     * Find the route action file
     */
    protected function findControllerFromDispatcher(array $trace): array
    {
        /** @var \Closure|string|array $action */
        $action = app(Router::class)->current()->getAction('uses');

        if (is_string($action)) {
            [$controller, $method] = explode('@', $action);

            $reflection = new \ReflectionMethod($controller, $method);
            $trace['file'] = $reflection->getFileName();
            $trace['line'] = $reflection->getStartLine();
        } elseif ($action instanceof \Closure) {
            $reflection = new \ReflectionFunction($action);
            $trace['file'] = $reflection->getFileName();
            $trace['line'] = $reflection->getStartLine();
        }

        return $trace;
    }

    /**
     * Find the template name from the hash.
     */
    protected function findViewFromHash(string $hash): ?string
    {
        $finder = app('view')->getFinder();

        if (isset($this->reflection['viewfinderViews'])) {
            $property = $this->reflection['viewfinderViews'];
        } else {
            $reflection = new \ReflectionClass($finder);
            $property = $reflection->getProperty('views');
            $this->reflection['viewfinderViews'] = $property;
        }

        $xxh128Exists = in_array('xxh128', hash_algos(), true);

        foreach ($property->getValue($finder) as $name => $path) {
            if (($xxh128Exists && hash('xxh128', 'v2' . $path) === $hash) || sha1('v2' . $path) === $hash) {
                return $path;
            }
        }

        return null;
    }

    public function reset(): void
    {
        $this->reflection = [];
    }
}
