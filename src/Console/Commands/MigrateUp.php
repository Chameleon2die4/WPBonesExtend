<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Console\Commands;

use Chameleon2die4\WPBonesExtend\Console\Command;

class MigrateUp extends Command
{
    protected $signature = 'migrate:up';

    protected $description = 'Create migration';

    public function handle()
    {
        $basePath = $this->getPluginPath();

        $this->emptyLine();
        $this->info('Running migrations.');
        $this->emptyLine();

        foreach (glob("{$basePath}/database/migrations/*.php") as $filename) {
            $instance = include $filename;

            $instance->up();

            $basename = explode('.', basename($filename))[0];
            $this->line($basename . ' .......... <fg=green;options=bold>DONE</>');
        }

        $this->emptyLine();
        $this->info('All migrate done!');
    }

}
