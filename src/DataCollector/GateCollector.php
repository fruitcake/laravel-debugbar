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

        // Stringify every Model in the arguments, not just $arguments[0]. When a
        // policy method authorizes creation of a not-yet-existing resource
        // (Gate::allows('create', [Post::class, $category])), $arguments[0] is the
        // class name and the real model sits at a later index. Left as-is it is
        // stored as a live reference in the message array, which - with
        // Model::automaticallyEagerLoadRelationships() on - drags the whole
        // hydrating collection along with it, once per check.
        $target = null;
        foreach ($arguments as $i => $argument) {
            if ($argument instanceof Model) {
                if ($argument->getKeyName() && isset($argument[$argument->getKeyName()])) {
                    $stringified = get_class($argument) . '(' . $argument->getKeyName() . '=' . $argument->getKey() . ')';
                } else {
                    $stringified = get_class($argument);
                }
                $arguments[$i] = $stringified;

                if ($i === 0) {
                    $target = $stringified;
                }
            } elseif ($i === 0 && is_string($argument)) {
                $target = $argument;
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
