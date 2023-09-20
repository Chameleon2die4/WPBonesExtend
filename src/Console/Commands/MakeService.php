<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Console\Commands;

use Chameleon2die4\WPBonesExtend\Console\Command;

class MakeService extends Command
{

  protected $signature = 'make:service {--class= : Display your name}';

  protected $description = 'Create a new Servicea class';

  public function handle()
    {
        $className = $this->getArgv();

        // ask className if empty
        $className = $this->askClassNameIfEmpty($className);

        // current plugin name and namespace
        $namespace = $this->getNamespace();

        $vars = $this->getAdditionalPath($className);
        $className = $vars['className'];

        // stubbing
        $content = $this->prepareStub('service', [
          '{Namespace}' => $namespace,
          '{ClassName}' => $className,
        ]);

        $this->createFile('plugin/Http/Services', $content, $vars);
    }

}
