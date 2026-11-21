<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

/**
 * Previously provided by nunomaduro/essentials, which requires PHP 8.3.
 */
final class MakeActionCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:action';

    /**
     * @var string
     */
    protected $description = 'Create a new action class';

    /**
     * @var string
     */
    protected $type = 'Action';

    /**
     * @return bool|int|null
     */
    public function handle()
    {
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return 1;
        }

        return parent::handle();
    }

    protected function getNameInput(): string
    {
        /** @var string $name */
        $name = $this->argument('name');

        return Str::of(mb_trim($name))
            ->replaceEnd('.php', '')
            ->replaceEnd('Action', '')
            ->append('Action')
            ->toString();
    }

    protected function getStub(): string
    {
        return base_path('stubs/action.stub');
    }

    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Actions';
    }

    /**
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return app_path(str_replace('\\', '/', $name).'.php');
    }
}
