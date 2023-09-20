<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Console;

use Chameleon2die4\WPBonesExtend\Console\Commands\IdeModels;
use Chameleon2die4\WPBonesExtend\Console\Commands\MakeMeta;
use Chameleon2die4\WPBonesExtend\Console\Commands\MakeResource;
use Chameleon2die4\WPBonesExtend\Console\Commands\MakeService;
use Chameleon2die4\WPBonesExtend\Console\Commands\MigrateRollback;
use Chameleon2die4\WPBonesExtend\Console\Commands\MigrateUp;
use WPKirk\WPBones\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  protected $commands = [
    MigrateUp::class,
    MigrateRollback::class,
    MakeResource::class,
    MakeService::class,
    MakeMeta::class,
    IdeModels::class,
  ];

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getRealCommands(): array
    {
        $commands = [];

        foreach ($this->commands as $commandClass) {
            $instance = new $commandClass;
            $commands[] = $instance->command;
        }

        return $commands;
    }

}
