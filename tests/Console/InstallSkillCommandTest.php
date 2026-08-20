<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallSkillCommandTest extends TestCase
{
    private const SKILL = 'debug-using-debugbar';

    protected function tearDown(): void
    {
        foreach (['.claude', '.agents'] as $dir) {
            File::deleteDirectory(base_path($dir));
        }

        parent::tearDown();
    }

    public function testInstallsForAnExplicitAgent(): void
    {
        Artisan::call('debugbar:install-skill', ['--agent' => ['claude']]);

        static::assertFileExists(base_path('.claude/skills/' . self::SKILL . '/SKILL.md'));
        static::assertDirectoryDoesNotExist(base_path('.agents'));
        static::assertStringContainsString('[claude] Installed', Artisan::output());
    }

    public function testInstallsForAllAgents(): void
    {
        Artisan::call('debugbar:install-skill', ['--agent' => ['all']]);

        static::assertFileExists(base_path('.claude/skills/' . self::SKILL . '/SKILL.md'));
        static::assertFileExists(base_path('.agents/skills/' . self::SKILL . '/SKILL.md'));
    }

    public function testInstalledSkillKeepsItsFrontmatter(): void
    {
        Artisan::call('debugbar:install-skill', ['--agent' => ['claude']]);

        $contents = File::get(base_path('.claude/skills/' . self::SKILL . '/SKILL.md'));
        static::assertStringContainsString('name: ' . self::SKILL, $contents);
        static::assertStringContainsString('description:', $contents);
    }

    public function testDetectsAgentsAlreadySetUpInTheProject(): void
    {
        File::ensureDirectoryExists(base_path('.agents'));

        Artisan::call('debugbar:install-skill');

        static::assertFileExists(base_path('.agents/skills/' . self::SKILL . '/SKILL.md'));
        static::assertDirectoryDoesNotExist(base_path('.claude/skills'));
    }

    public function testDoesNotOverwriteWithoutForce(): void
    {
        $file = base_path('.claude/skills/' . self::SKILL . '/SKILL.md');
        File::ensureDirectoryExists(dirname($file));
        File::put($file, 'custom');

        Artisan::call('debugbar:install-skill', ['--agent' => ['claude']]);

        static::assertSame('custom', File::get($file));
        static::assertStringContainsString('already exists, use --force', Artisan::output());
    }

    public function testForceOverwritesAnExistingInstallation(): void
    {
        $file = base_path('.claude/skills/' . self::SKILL . '/SKILL.md');
        File::ensureDirectoryExists(dirname($file));
        File::put($file, 'custom');

        Artisan::call('debugbar:install-skill', ['--agent' => ['claude'], '--force' => true]);

        static::assertStringContainsString('name: ' . self::SKILL, File::get($file));
        static::assertStringContainsString('[claude] Installed', Artisan::output());
    }

    public function testSymlinksInsteadOfCopying(): void
    {
        Artisan::call('debugbar:install-skill', ['--agent' => ['claude'], '--symlink' => true]);

        $target = base_path('.claude/skills/' . self::SKILL);
        static::assertTrue(is_link($target));
        static::assertFileExists($target . '/SKILL.md');
        static::assertStringContainsString('Linked', Artisan::output());
    }

    public function testRejectsAnUnknownAgent(): void
    {
        $status = Artisan::call('debugbar:install-skill', ['--agent' => ['emacs']]);

        static::assertSame(1, $status);
        static::assertStringContainsString('Unknown agent: emacs', Artisan::output());
    }
}
