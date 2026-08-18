<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\TemplateCollector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Collector for Models.
 */
class LivewireCollector extends TemplateCollector
{
    public function addLivewireComponent(Component $component, ?Request $request = null): void
    {
        $id = $component->getId();
        $data = $component->all();

        if ((new \ReflectionClass($component))->isAnonymous()) {
            $key = Str::ascii($component->getName()) . ' #' . $id;
        } else {
            $key = get_class($component) . ' ' . $component->getName() . ' #' . $id;
        }

        if ($request && $request->request->get('id') === $id) {
            $data['#oldData'] = $request->request->get('data');
            $data['#actionQueue'] = $request->request->get('actionQueue');
        }

        $data['#name'] = $component->getName();
        $data['#component'] = get_class($component);
        $data['#id'] = $id;

        $this->addTemplate($key, $data, 'livewire', $this->resolveSourcePath($component));
    }

    /**
     * Resolve the file the component was written in.
     *
     * Livewire 4 compiles single- and multi-file components into the cache
     * directory, so reflection reports the compiled class rather than the
     * source the user can edit.
     */
    protected function resolveSourcePath(Component $component): ?string
    {
        $path = (new \ReflectionClass($component))->getFileName() ?: null;

        if (!app()->bound('livewire.finder')) {
            return $path;
        }

        try {
            $finder = app('livewire.finder');
            $name = $component->getName();

            // A multi-file component resolves to its directory, so point at the
            // class file inside it rather than the directory itself.
            if ($directory = $finder->resolveMultiFileComponentPath($name)) {
                $basename = basename($directory);

                foreach ([$basename . '.php', $basename . '.blade.php'] as $candidate) {
                    if (is_file($file = $directory . DIRECTORY_SEPARATOR . $candidate)) {
                        return $file;
                    }
                }

                return $directory;
            }

            if ($singleFile = $finder->resolveSingleFileComponentPath($name)) {
                return $singleFile;
            }
        } catch (\Throwable $e) {
            //
        }

        return $path;
    }

    /**
     * @return array{nb_templates: int, templates: array<string, array{name: string, param_count: int, params: array<string, mixed>, type: string, xdebug_link?: string}>, sentence: string}
     */
    public function collect(): array
    {
        $data = parent::collect();

        $data['sentence'] = 'Livewire component' . ($data['nb_templates'] !== 1 ? 's' : '');

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'livewire';
    }

    /**
     * @return array<string, array{icon: string, widget: string, map: string, default: string}>
     */
    public function getWidgets(): array
    {
        $widgets = parent::getWidgets();
        $widgets[$this->getName()]['icon'] = 'brand-livewire';
        return $widgets;
    }
}
