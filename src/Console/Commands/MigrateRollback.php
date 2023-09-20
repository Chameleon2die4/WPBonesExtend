<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Console\Commands;

use Chameleon2die4\WPBonesExtend\Console\Command;

class MigrateRollback extends Command
{

  protected $signature = 'migrate:rollback';

  protected $description = 'Migration rollback';

    public function handle()
    {
        $basePath = $this->getPluginPath();

        $this->emptyLine();
        $this->info('Running migrations rollback.');
        $this->emptyLine();

        foreach (glob("{$basePath}/database/migrations/*.php") as $filename) {
            $instance = include $filename;

            $instance->down();

            $basename = explode('.', basename($filename))[0];
            $this->line($basename . '  ..........  <fg=green;options=bold>DONE</>');
        }

        $this->emptyLine();
        $this->info('All migrate rollback!');
    }

}
