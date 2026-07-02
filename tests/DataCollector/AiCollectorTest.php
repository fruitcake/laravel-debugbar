<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Closure;
use Fruitcake\LaravelDebugbar\DataCollector\AiCollector;
use Fruitcake\LaravelDebugbar\Tests\Mocks\AiMockTool;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Laravel\Ai\AiManager;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Mockery;

class AiCollectorTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(AiManager::class)) {
            static::markTestSkipped('laravel/ai is not installed');
        }

        parent::setUp();
    }

    public function testProviderRegistersCollectorWhenAiIsInstalled()
    {
        debugbar()->enable();
        debugbar()->boot();

        static::assertInstanceOf(AiCollector::class, debugbar()->getCollector('ai'));
    }

    public function testItRecordsAnAgentRunWithFoldedToolCallsAndUsage()
    {
        $collector = new AiCollector();

        $collector->bufferToolInvocation(new ToolInvoked(
            invocationId: 'inv-1',
            toolInvocationId: 'tool-1',
            agent: $this->agent(),
            tool: new AiMockTool(),
            arguments: ['city' => 'Amsterdam'],
            result: 'sunny',
        ));

        $collector->recordAgentPrompted($this->agentPrompted('inv-1', 'What is the weather?', 'It is sunny.'));

        $runs = $this->runsOf($collector);

        static::assertCount(1, $runs);
        static::assertSame(1, $collector->collect()['count']);

        $run = $runs[0];
        static::assertSame(AnonymousAgent::class, $run['agent']);
        static::assertSame('gpt-fake', $run['model']);
        static::assertSame('fake-provider', $run['provider']);
        static::assertSame(30, $run['tokens']); // 10 prompt + 20 completion
        static::assertSame(0, $run['attachments']);
        static::assertSame('inv-1', $run['invocation_id']);

        static::assertSame('You are helpful.', $run['instructions']);
        static::assertSame('What is the weather?', $run['prompt']);
        static::assertSame('It is sunny.', $run['response']);

        static::assertCount(1, $run['tools']);
        static::assertSame('AiMockTool', $run['tools'][0]['tool']);
        static::assertSame(AiMockTool::class, $run['tools'][0]['tool_class']);
        static::assertSame(['city' => 'Amsterdam'], $run['tools'][0]['arguments']);
        static::assertSame('sunny', $run['tools'][0]['result']);
    }

    public function testToolBufferIsConsumedPerInvocation()
    {
        $collector = new AiCollector();

        $collector->bufferToolInvocation(new ToolInvoked(
            invocationId: 'inv-1',
            toolInvocationId: 'tool-1',
            agent: $this->agent(),
            tool: new AiMockTool(),
            arguments: [],
            result: 'ok',
        ));

        $collector->recordAgentPrompted($this->agentPrompted('inv-1', 'First', 'a'));
        $collector->recordAgentPrompted($this->agentPrompted('inv-2', 'Second', 'b'));

        $runs = $this->runsOf($collector);

        static::assertCount(1, $runs[0]['tools']); // folded into the matching invocation
        static::assertCount(0, $runs[1]['tools']); // second run has no buffered tools
    }

    public function testItOmitsBodiesWhenValuesAreDisabled()
    {
        $collector = new AiCollector(collectValues: false);

        $collector->bufferToolInvocation(new ToolInvoked(
            invocationId: 'inv-1',
            toolInvocationId: 'tool-1',
            agent: $this->agent(),
            tool: new AiMockTool(),
            arguments: ['secret' => 'value'],
            result: 'sensitive',
        ));

        $collector->recordAgentPrompted($this->agentPrompted('inv-1', 'Sensitive prompt', 'Sensitive response'));

        $run = $this->runsOf($collector)[0];

        // Metadata is still collected...
        static::assertSame(AnonymousAgent::class, $run['agent']);
        static::assertSame('gpt-fake', $run['model']);
        static::assertSame(30, $run['tokens']);

        // ...but prompt/response/tool bodies are omitted.
        static::assertArrayNotHasKey('prompt', $run);
        static::assertArrayNotHasKey('response', $run);
        static::assertArrayNotHasKey('instructions', $run);
        static::assertArrayNotHasKey('tools', $run);
    }

    public function testItTruncatesLongBodies()
    {
        $collector = new AiCollector();

        $long = str_repeat('a', 25000);
        $collector->recordAgentPrompted($this->agentPrompted('inv-1', $long, $long));

        $run = $this->runsOf($collector)[0];

        static::assertStringEndsWith('… [truncated]', $run['prompt']);
        static::assertStringEndsWith('… [truncated]', $run['response']);
        static::assertLessThan(25000, mb_strlen($run['prompt']));
    }

    private function agent(): AnonymousAgent
    {
        return new AnonymousAgent('You are helpful.', [], []);
    }

    private function agentPrompted(string $invocationId, string $prompt, string $response): AgentPrompted
    {
        $agentPrompt = new AgentPrompt(
            agent: $this->agent(),
            prompt: $prompt,
            attachments: [],
            provider: Mockery::mock(TextProvider::class),
            model: 'gpt-fake',
        );

        $agentResponse = new AgentResponse(
            invocationId: $invocationId,
            text: $response,
            usage: new Usage(promptTokens: 10, completionTokens: 20),
            meta: new Meta(provider: 'fake-provider', model: 'gpt-fake'),
        );

        return new AgentPrompted($invocationId, $agentPrompt, $agentResponse);
    }

    /**
     * Read the collector's protected list of recorded runs.
     *
     * @return list<array<string, mixed>>
     */
    private function runsOf(AiCollector $collector): array
    {
        return Closure::bind(fn() => $this->runs, $collector, AiCollector::class)();
    }
}
