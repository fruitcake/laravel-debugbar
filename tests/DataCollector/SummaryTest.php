<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use DebugBar\DataCollector\Renderable;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Every collector that declares a `<control>:summary` widget must actually produce
 * the key it maps to, otherwise the summary silently omits that section.
 */
class SummaryTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('debugbar.enabled', true);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('debugbar.collectors.cache', true);
        $app['config']->set('debugbar.collectors.events', true);
        $app['config']->set('debugbar.collectors.auth', true);
    }

    private function debugbar(): LaravelDebugbar
    {
        $debugbar = app(LaravelDebugbar::class);
        $debugbar->enable();
        $debugbar->boot();

        return $debugbar;
    }

    public function testEveryDeclaredSummaryWidgetResolvesToData(): void
    {
        $debugbar = $this->debugbar();

        DB::select('select 1');
        Cache::get('missing-key');
        Event::dispatch('test.event');

        $data = $debugbar->getData();

        $checked = 0;
        foreach ($debugbar->getCollectors() as $collector) {
            if (!$collector instanceof Renderable) {
                continue;
            }

            foreach ($collector->getWidgets() as $widget => $options) {
                if (!str_ends_with((string) $widget, ':summary')) {
                    continue;
                }

                $checked++;
                $value = $data;
                foreach (explode('.', (string) $options['map']) as $part) {
                    static::assertIsArray(
                        $value,
                        sprintf('%s maps to %s, which is not reachable', $widget, $options['map'])
                    );
                    static::assertArrayHasKey(
                        $part,
                        $value,
                        sprintf('%s maps to %s, but "%s" is missing', $widget, $options['map'], $part)
                    );
                    $value = $value[$part];
                }
            }
        }

        static::assertGreaterThan(5, $checked, 'expected several collectors to declare a summary');
    }

    /**
     * A `<control>:summary` widget whose control is not declared crashes the whole bar:
     * dataChangeHandler() calls .set() on the missing control. Guards data being off
     * makes this conditional, so both settings are checked.
     */
    #[\PHPUnit\Framework\Attributes\TestWith([true])]
    #[\PHPUnit\Framework\Attributes\TestWith([false])]
    public function testEverySummaryWidgetHasAMatchingControl(bool $showGuards): void
    {
        config(['debugbar.options.auth.show_guards' => $showGuards]);

        $debugbar = $this->debugbar();

        $controls = [];
        $summaries = [];
        foreach ($debugbar->getCollectors() as $collector) {
            if (!$collector instanceof Renderable) {
                continue;
            }

            foreach (array_keys($collector->getWidgets()) as $widget) {
                $widget = (string) $widget;
                if (str_ends_with($widget, ':summary')) {
                    $summaries[substr($widget, 0, -strlen(':summary'))] = get_class($collector);
                } elseif (!str_contains($widget, ':')) {
                    $controls[$widget] = true;
                }
            }
        }

        static::assertNotEmpty($summaries);

        foreach ($summaries as $control => $class) {
            static::assertArrayHasKey(
                $control,
                $controls,
                sprintf('%s declares "%s:summary" but no "%s" control exists', $class, $control, $control)
            );
        }
    }

    public function testQuerySummarySeparatesDuplicatesFromNPlusOne(): void
    {
        $debugbar = $this->debugbar();

        DB::statement('create table t (id integer primary key, k integer)');
        // Same query, same value: a duplicate.
        DB::select('select * from t where k = ?', [1]);
        DB::select('select * from t where k = ?', [1]);
        // Same query, different values: an N+1.
        DB::select('select * from t where id = ?', [1]);
        DB::select('select * from t where id = ?', [2]);
        DB::select('select * from t where id = ?', [3]);

        $queries = $debugbar->getCollector('queries')->collect();

        static::assertSame(2, $queries['nb_duplicate_statements']);
        static::assertSame(3, $queries['nb_n_plus_one_statements']);

        $summary = $queries['summary'];
        static::assertSame(2, $summary['duplicates']);
        static::assertSame(['3x select * from t where id = ?'], $summary['n_plus_one']);
    }

    public function testCacheSummaryCountsByActionAndHitRatio(): void
    {
        $debugbar = $this->debugbar();

        Cache::put('hit-me', 'value', 60);
        Cache::get('hit-me');
        Cache::get('nope');

        $summary = $debugbar->getCollector('cache')->collect()['summary'];

        static::assertSame(1, $summary['by_action']['hit'] ?? null);
        static::assertSame(1, $summary['by_action']['missed'] ?? null);
        static::assertSame('50%', $summary['hit_ratio']);
        static::assertContains('nope', $summary['missed_keys']);
    }

    public function testEventSummaryRanksByFrequency(): void
    {
        $debugbar = $this->debugbar();

        Event::dispatch('noisy.event');
        Event::dispatch('noisy.event');
        Event::dispatch('quiet.event');

        $summary = $debugbar->getCollector('event')->collect()['summary'];

        static::assertSame('2x noisy.event', $summary['top'][0]);
    }

    public function testAuthSummaryNamesTheGuardAndUser(): void
    {
        $debugbar = $this->debugbar();

        $summary = $debugbar->getCollector('auth')->collect()['summary'];

        static::assertIsArray($summary);
        static::assertContains('guest', $summary, 'an unauthenticated guard reads as guest');
        static::assertFalse(Auth::check());
    }

    /**
     * `runningInConsole()` is true under PHPUnit, so only the CLI branch is reachable
     * here. The HTTP branch, which folds in route and session key names, is covered
     * by the browser tests.
     */
    public function testRequestSummaryDescribesTheConsoleCommand(): void
    {
        $this->debugbar();

        $summary = app(LaravelDebugbar::class)->getCollector('request')->collect()['summary'];

        // `command` depends on argv, which differs between a filtered and a full run.
        static::assertSame('CLI', $summary['method']);
        static::assertSame(['method', 'command', 'command_class'], array_keys($summary + ['command' => null, 'command_class' => null]));
    }
}
