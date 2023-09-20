<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

namespace Chameleon2die4\WPBonesExtend\Console\Commands;

use Chameleon2die4\WPBonesExtend\Console\Command;
use Chameleon2die4\WPBonesExtend\Model\Model;
use Chameleon2die4\WPBonesExtend\Services\PhpDoc\DocBlock;
use Chameleon2die4\WPBonesExtend\Services\PhpDoc\GenerateDoc;

class IdeModels extends Command
{
    use GenerateDoc;

    protected $signature = 'ide:models {--model= : Model class}';

    protected $description = 'Write PhpDocs to models class';

    private DocBlock $doc;
    private string $builder = '\Chameleon2die4\WPBonesExtend\WPBones\Database\QueryBuilder';
    private string $model = '\Chameleon2die4\WPBonesExtend\Model\Model';


    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        $basePath = $this->getPluginPath();
        $folder = "/plugin/models/";
        $files = $this->getFiles($basePath . $folder);


        $this->emptyLine();
        $this->info('Writing PhpDoc...');
        $this->emptyLine();

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $info = $this->getClassInfo($file);
            $class = $info['class'];
            $model = new $class;

            $this->doc = new DocBlock();
            $this->doc->addLine(trim($class, '\\'));
            $this->doc->addLine('');

            $this->getPropertiesFromTable($model);
            $this->getPropertiesFromMethods($model);
            $this->getParentMethods($model);

            $php_doc = $this->doc->generate();

            $reflection = new \ReflectionClass($model);
            $origin = $reflection->getDocComment();

            $lines = [];
            if ($origin) {
                $doc = new DocBlock($origin);
                $lines = $doc->getComments();
            }

            if (count($lines) && $lines[0] === trim($class, '\\')) {
                $content = str_replace($origin . "\n", $php_doc, $content);
            } else {
                $classname = $info['short'];
                $pos = strpos($content, "final class {$classname}") ?: strpos($content, "class {$classname}");
                if ($pos !== false) {
                    $content = substr_replace($content, $php_doc, $pos, 0);
                }
            }
            file_put_contents($file, $content);

            $this->line('Written phpDocBlock to ' . "<fg=blue>$class</>");
        }

        $this->emptyLine();
        $this->info('All models PhpDoc written!');
    }

    /**
     * @param Model $model
     * @return void
     */
    public function getPropertiesFromTable(Model $model)
    {
        $columns = $this->getTableColumns($model->getTable());
        foreach ($columns as $column) {
            $type = $this->getColumnVarType($column->Type);

            $this->doc->addProperty([
              'type' => $type,
              'name' => $column->Field,
            ]);
        }
    }

    /**
     * @param Model $model
     * @return void
     * @throws \ReflectionException
     */
    public function getPropertiesFromMethods(Model $model)
    {
        $pattern = '/^get([a-zA-Z]*)Attribute$/';
        $methods = get_class_methods($model);
        if ($methods) {
            sort($methods);
            foreach ($methods as $method) {
                if (preg_match($pattern, $method, $matches)) {
                    $name = $this->snake($matches[1]);

                    $reflection = new \ReflectionMethod($model, $method);

                    $type = $this->extractReflectionTypes($reflection);
                    if (!$type) {
                        $doc = new DocBlock($reflection->getDocComment());
                        $type = $doc->getReturn();
                    }

                    $this->doc->addProperty([
                      'type' => $type,
                      'name' => $name,
                    ]);
                }
            }
        }
    }

    /**
     * @param $model
     * @return void
     */
    public function getParentMethods($model)
    {
        $class = new \ReflectionClass($model);
        $reflection = $class->getParentClass();
        $methods = $reflection->getMethods();

        $methods = array_filter($methods, function ($method) use ($reflection) {
            return !preg_match('/^__/', $method->getName())
              && !preg_match('/^get/', $method->getName())
              && $method->getDeclaringClass()->getName() === $reflection->getName()
              && ($method->isProtected() || $method->isStatic());
        });

        foreach ($methods as $method) {
            $this->addMethod($method, $class);
        }

        $comment = $class->getParentClass()->getDocComment();
        $doc = new DocBlock($comment);
        $methods = $doc->getMethods();
        foreach ($methods as $method) {
//            if ($method['type'] === $this->builder || $method['type'] === $this->model) {
////                $method['type'] .= '|' . $class->getShortName();
//            }
            $method['type'] = str_replace($this->builder, $this->builder . '|' . $class->getShortName(), $method['type']);
            $method['type'] = str_replace($this->model, $class->getShortName(), $method['type']);

            $this->doc->addMethod($method);
        }
    }

    public function addMethod(\ReflectionMethod $method, \ReflectionClass $class)
    {
        $doc = new DocBlock($method->getDocComment());

        $return = $this->extractReflectionTypes($method);
        if (!$return) {
            $return = $doc->getReturn();
        }

//        if ($return === '\Chameleon2die4\WPBonesExtend\WPBones\Database\QueryBuilder') {
//            $return .= '|' . $class->getShortName();
//        }
        $return = str_replace($this->builder, $this->builder . '|' . $class->getShortName(), $return);
        $return = str_replace($this->model, $class->getShortName(), $return);

        $params = $doc->getParams();

        $ref_params = [];
        foreach ($method->getParameters() as $param) {
            $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
            $type = $param->getType();
            $ref_params[] = [
              'name'    => $param->getName(),
              'type'    => $type ? $type->getName() : '',
              'default' => $default
            ];
        }
        $params = $this->mergeParams($params, $ref_params);

        $this->doc->addMethod([
          'name'   => $method->getName(),
          'type'   => $return,
          'params' => $params,
          'static' => true,
        ]);
    }

}
