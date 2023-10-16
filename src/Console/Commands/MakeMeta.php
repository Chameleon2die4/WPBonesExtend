<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Console\Commands;

use Chameleon2die4\WPBonesExtend\Console\Command;
use Chameleon2die4\WPBonesExtend\Str;

class MakeMeta extends Command
{

  protected $signature = 'make:meta {--class= : Display your name} {--type= : Display your name}';

  protected $description = 'Create a new Metabox class';

  public function handle()
    {
        $className = $this->getArgv();
        $className = $this->askClassNameIfEmpty($className);

        $type = $this->option('type');
        $type = $this->askIfEmpty($type, 'Type', 'post');
        $useType = $this->getUseTypeName($type);

        // current plugin name and namespace
        $namespace = $this->getNamespace();

        $vars = $this->getAdditionalPath($className);
        $className = $vars['className'];

        // stubbing
        $content = $this->prepareStub('meta', [
          '{Namespace}' => $namespace,
          '{ClassName}' => $className,
          '{UseType}' => $useType,
        ]);

        $view = Str::snake($className);

        $this->createFile('plugin/MetaBox', $content, $vars);
        $this->createFile('resources/views/meta', '', [
            'className' => $view,
        ]);
    }

    public function getUseTypeName(string $type) {
        switch ($type) {
            case 'post':
            case 'Post':
            case 'PostMeta':
                return 'PostMeta';
            case 'term':
            case 'Term':
            case 'TermMeta':
                return 'TermMeta';
            default:
                $this->error("Type must be 'post' or 'term'");
                exit(0);
        }
    }

}
