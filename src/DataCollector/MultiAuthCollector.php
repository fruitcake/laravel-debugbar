<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Collector for Laravel's Auth provider
 */
class MultiAuthCollector extends DataCollector implements Renderable
{
    protected array $guards = [];

    /** @var bool */
    protected $showName = false;

    /** @var bool */
    protected $showGuardsData = true;

    public function __construct(array $guards = [])
    {
        $this->guards = $guards;
    }

    /**
     * Set to show the users name/email
     */
    public function setShowName(bool $showName): void
    {
        $this->showName = $showName;
    }

    /**
     * Set to hide the guards tab, and show only name
     */
    public function setShowGuardsData(bool $showGuardsData): void
    {
        $this->showGuardsData = $showGuardsData;
    }

    /**
     * @{inheritDoc}
     */
    public function collect(): array
    {
        $data = [
            'guards' => [],
        ];
        $names = '';

        foreach ($this->guards as $guardName => $config) {
            try {
                $guard = auth()->guard($guardName);
                if ($guard->hasUser()) {
                    $user = $guard->user();

                    if (!is_null($user)) {
                        $data['guards'][$guardName] = $this->getUserInformation($user);
                        $names .= $guardName . ": " . $data['guards'][$guardName]['name'] . ', ';
                    }
                } else {
                    $data['guards'][$guardName] = null;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        foreach ($data['guards'] as $key => $var) {
            $data['guards'][$key] = $this->getDataFormatter()->formatVar($var);
        }

        $data['names'] = rtrim($names, ', ');
        if (!$this->showGuardsData) {
            unset($data['guards']);
        }

        return $data;
    }

    /**
     * Get displayed user information
     */
    protected function getUserInformation(mixed $user = null): array
    {
        // Defaults
        if (is_null($user)) {
            return [
                'name' => 'Guest',
                'user' => ['guest' => true],
            ];
        }

        // The default auth identifer is the ID number, which isn't all that
        // useful. Try username, email and name.
        $identifier = $user instanceof Authenticatable ? $user->getAuthIdentifier() : $user->getKey();
        if (is_numeric($identifier) || Str::isUuid($identifier) || Str::isUlid($identifier)) {
            try {
                if (isset($user->username)) {
                    $identifier = $user->username;
                } elseif (isset($user->email)) {
                    $identifier = $user->email;
                } elseif (isset($user->name)) {
                    $identifier = Str::limit($user->name, 24);
                }
            } catch (\Throwable $e) {
            }
        }

        return [
            'name' => $identifier,
            'user' => $user instanceof Arrayable ? $user->toArray() : $user,
        ];
    }

    /**
     * @{inheritDoc}
     */
    public function getName(): string
    {
        return 'auth';
    }

    /**
     * @{inheritDoc}
     */
    public function getWidgets(): array
    {
        $widgets = [];

        if ($this->showGuardsData) {
            $widget = match (true) {
                $this->isJsonVarDumperUsed() => "PhpDebugBar.Widgets.JsonVariableListWidget",
                $this->isHtmlVarDumperUsed() => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                default => "PhpDebugBar.Widgets.VariableListWidget",
            };
            $widgets["auth"] = [
                "icon" => "lock",
                "widget" => $widget,
                "map" => "auth.guards",
                "default" => "{}",
            ];
        }

        if ($this->showName) {
            $widgets['auth.name'] = [
                'icon' => 'user',
                'tooltip' => 'Auth status',
                'map' => 'auth.names',
                'default' => '',
            ];
        }

        return $widgets;
    }
}
