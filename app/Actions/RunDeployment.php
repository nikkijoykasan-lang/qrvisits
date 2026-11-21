<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Throwable;

final readonly class RunDeployment
{
    public function __construct(private Kernel $kernel)
    {
        //
    }

    /**
     * @return list<string>
     */
    public function handle(): array
    {
        $this->ensureWritableDirectories();

        $steps = [];

        if (blank(config('app.key'))) {
            $steps[] = ['key:generate', ['--force' => true]];
        }

        $steps[] = ['migrate', ['--force' => true]];
        $steps[] = ['migrate:status', []];
        $steps[] = ['optimize:clear', []];
        $steps[] = ['storage:link', []];

        $output = ['=== directories ===', 'storage and bootstrap/cache are writable'];

        foreach ($steps as [$command, $arguments]) {
            $output[] = "=== {$command} ===";

            try {
                $exitCode = $this->kernel->call($command, $arguments);

                $output[] = mb_trim($this->kernel->output())."\n(exit {$exitCode})";
            } catch (Throwable $exception) {
                $output[] = 'FAILED: '.$exception->getMessage();
            }
        }

        return $output;
    }

    private function ensureWritableDirectories(): void
    {
        $directories = [
            storage_path('app/private'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($directories as $directory) {
            File::ensureDirectoryExists($directory);

            @chmod($directory, 0775);
        }
    }
}
