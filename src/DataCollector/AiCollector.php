<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\ToolInvoked;

/**
 * Collects laravel/ai agent activity. Each agent invocation is shown as a
 * single entry combining the prompt, response, token usage and any tool
 * calls made during that run.
 *
 * Tool invocations are buffered by invocation id as they fire and folded
 * into the entry recorded when the agent finishes ({@see AgentPrompted}).
 */
class AiCollector extends DataCollector implements DataCollectorInterface, Renderable
{
    /**
     * Completed agent runs, ready for display.
     *
     * @var list<array<string, mixed>>
     */
    protected array $runs = [];

    /**
     * Tool calls collected during the current request, keyed by invocation id.
     *
     * @var array<string, list<array<string, mixed>>>
     */
    protected array $toolInvocations = [];

    public function __construct(
        protected bool $collectValues = true,
    ) {}

    public function bufferToolInvocation(ToolInvoked $event): void
    {
        $this->toolInvocations[$event->invocationId][] = [
            'tool' => class_basename($event->tool),
            'tool_class' => $event->tool::class,
            'arguments' => $this->collectValues ? $event->arguments : null,
            'result' => $this->collectValues ? $this->truncate((string) $event->result) : null,
        ];
    }

    public function recordAgentPrompted(AgentPrompted|AgentStreamed $event): void
    {
        $tools = $this->toolInvocations[$event->invocationId] ?? [];
        unset($this->toolInvocations[$event->invocationId]);

        $prompt = $event->prompt;
        $usage = $event->response->usage;

        $run = [
            'agent' => $prompt->agent::class,
            'model' => $prompt->model,
            'tokens' => $usage->promptTokens + $usage->completionTokens,
            'provider' => $event->response->meta->provider,
            'attachments' => $prompt->attachments->count(),
            'usage' => $usage->toArray(),
            'invocation_id' => $event->invocationId,
        ];

        if ($this->collectValues) {
            $run['instructions'] = (string) $prompt->agent->instructions();
            $run['prompt'] = $this->truncate($prompt->prompt);
            $run['tools'] = $tools;
            $run['response'] = $this->truncate($event->response->text);
        }

        $this->runs[] = $run;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $runs = [];

        foreach ($this->runs as $index => $run) {
            $label = class_basename($run['agent']) . ' #' . ($index + 1);
            $runs[$label] = $this->getDataFormatter()->formatVar($run);
        }

        return [
            'runs' => $runs,
            'count' => count($this->runs),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'ai';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        $widget = match (true) {
            $this->isJsonVarDumperUsed() => "PhpDebugBar.Widgets.JsonVariableListWidget",
            $this->isHtmlVarDumperUsed() => "PhpDebugBar.Widgets.HtmlVariableListWidget",
            default => "PhpDebugBar.Widgets.VariableListWidget",
        };

        return [
            'ai' => [
                'icon' => 'magic',
                'widget' => $widget,
                'map' => 'ai.runs',
                'default' => '{}',
            ],
            'ai:badge' => [
                'map' => 'ai.count',
                'default' => 'null',
            ],
        ];
    }

    /**
     * Keep large prompt/response/tool bodies from bloating the debug bar.
     */
    protected function truncate(string $value, int $limit = 20000): string
    {
        return mb_strlen($value) > $limit
            ? mb_substr($value, 0, $limit) . ' … [truncated]'
            : $value;
    }
}
