<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\TemplateCollector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collector for Ineratia.
 */
class InertiaCollector extends TemplateCollector
{
    public function addFromView(\Illuminate\View\View $view): void
    {
        $data = $view->getData();
        if (isset($data['page']['component'])) {
            $this->addInertiaTemplate($data['page'], $view->getName(), $view->getPath());
        }
    }

    public function addFromResponse(Response $response): void
    {
        if (!$response->headers->has('X-Inertia') || $response->headers->get('Content-Type') !== 'application/json' || $this->templates) {
            return;
        }

        $content = $response->getContent();
        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        if (is_array($content)) {
            $this->addInertiaTemplate($content);
        }
    }

    private function addInertiaTemplate(array $page, ?string $name = null, ?string $path = null): void
    {
        if (!isset($page['component'])) {
            return;
        }

        $type = '';
        $component = $page['component'];
        $props = $page['props'] ?? [];

        $pagePath = config('debugbar.options.inertia.pages', 'js/Pages');
        if ($files = glob(resource_path($pagePath . '/' . $name . '.*'))) {
            $path = $files[0];
            $type = pathinfo($path, PATHINFO_EXTENSION);

            if (in_array($type, ['js', 'jsx'], true)) {
                $type = 'react';
            }
        }

        $this->addTemplate($component, $props, $type, $path);
    }

    public function collect(): array
    {
        $data = parent::collect();

        $data['sentence'] = 'Inertia page' . ($data['nb_templates'] !== 1 ? 's' : '');

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'inertia';
    }

    public function getWidgets(): array
    {
        $widgets = parent::getWidgets();
        $widgets[$this->getName()]['icon'] = 'brand-inertia';
        return $widgets;
    }
}
