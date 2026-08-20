<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallSkillCommand extends Command
{
    protected $signature = 'debugbar:install-skill
    {--agent=* : Where to install: claude, agents or all}
    {--symlink : Link to the package instead of copying, so the skill updates with the package}
    {--force : Overwrite an existing installation}
    ';
    protected $description = 'Install the Debugbar debugging skill for AI coding agents';

    /** Name of the skill, and of the directory it is installed in. */
    private const SKILL = 'debug-using-debugbar';

    /**
     * Skill directories per agent, relative to the project root. The `.agents`
     * convention is read by Codex, Cursor, Copilot and Gemini CLI.
     *
     * @var array<string, string>
     */
    private const TARGETS = [
        'claude' => '.claude/skills',
        'agents' => '.agents/skills',
    ];

    public function handle(Filesystem $files): int
    {
        $source = realpath(__DIR__ . '/../../resources/boost/skills/' . self::SKILL);
        if ($source === false || !$files->isDirectory($source)) {
            $this->error('Could not locate the skill inside the package.');
            return self::FAILURE;
        }

        $agents = $this->resolveAgents();
        if ($agents === null) {
            return self::FAILURE;
        }

        if ($agents === [] || !$this->confirmWhenBoostIsInstalled()) {
            $this->comment('Nothing installed.');
            return self::SUCCESS;
        }

        foreach ($agents as $agent) {
            $this->install($files, $source, $agent);
        }

        return self::SUCCESS;
    }

    /**
     * Resolve which agents to install for. Returns null when the input was invalid.
     *
     * @return list<string>|null
     */
    private function resolveAgents(): ?array
    {
        /** @var list<string> $agents */
        $agents = $this->option('agent');

        if (in_array('all', $agents, true)) {
            return array_keys(self::TARGETS);
        }

        if ($agents !== []) {
            $unknown = array_diff($agents, array_keys(self::TARGETS));
            if ($unknown !== []) {
                $this->error('Unknown agent: ' . implode(', ', $unknown) . '. Available: ' . implode(', ', array_keys(self::TARGETS)) . ', all');
                return null;
            }

            return array_values(array_unique($agents));
        }

        // Without --agent, install for every agent already set up in this project.
        $detected = array_keys(array_filter(
            self::TARGETS,
            fn(string $path): bool => is_dir(base_path(dirname($path)))
        ));

        if ($detected !== []) {
            $this->line('Detected: ' . implode(', ', $detected));
            return $detected;
        }

        /** @var list<string> $choice */
        $choice = $this->choice(
            'No agent directories found, which agents should the skill be installed for?',
            array_keys(self::TARGETS),
            'claude',
            null,
            true
        );

        return $choice;
    }

    private function install(Filesystem $files, string $source, string $agent): void
    {
        $target = base_path(self::TARGETS[$agent] . '/' . self::SKILL);
        $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $target);

        if ($files->exists($target) || is_link($target)) {
            if (!$this->option('force')) {
                $this->warn(sprintf('[%s] %s already exists, use --force to overwrite.', $agent, $relative));
                return;
            }

            is_link($target) ? $files->delete($target) : $files->deleteDirectory($target);
        }

        $files->ensureDirectoryExists(dirname($target));

        if ($this->option('symlink')) {
            $files->link($source, $target);
            $this->info(sprintf('[%s] Linked %s (do not commit this symlink)', $agent, $relative));
        } else {
            $files->copyDirectory($source, $target);
            $this->info(sprintf('[%s] Installed %s', $agent, $relative));
        }
    }

    /**
     * Boost already exposes this skill to the agents it configures, so a project
     * copy would be a duplicate. Ask before adding one.
     */
    private function confirmWhenBoostIsInstalled(): bool
    {
        if (!class_exists(InstalledVersions::class) || !InstalledVersions::isInstalled('laravel/boost')) {
            return true;
        }

        $this->warn('Laravel Boost is installed and already provides this skill to your agents.');

        return $this->option('force') || $this->confirm('Install a project copy anyway?', false);
    }
}
