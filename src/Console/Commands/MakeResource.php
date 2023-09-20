<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Console\Commands;


use Chameleon2die4\WPBonesExtend\Console\Command;

class MakeResource extends Command
{

    protected $signature = 'make:resource {--class= : Display class name}';

    protected $description = 'Create a new Resource class';

    public function handle()
    {
        $className = $this->getArgv();
//        $className = $this->option('class');

        // ask className if empty
        $className = $this->askClassNameIfEmpty($className);

        // current plugin name and namespace
        $namespace = $this->getNamespace();

        $vars = $this->getAdditionalPath($className);
        $className = $vars['className'];

        // stubbing
        $content = $this->prepareStub('resource', [
          '{Namespace}' => $namespace,
          '{ClassName}' => $className,
        ]);

        $this->createFile('plugin/Http/Resources', $content, $vars);
    }

}
