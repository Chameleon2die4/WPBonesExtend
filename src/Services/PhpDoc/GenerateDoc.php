<?php

namespace Chameleon2die4\WPBonesExtend\Services\PhpDoc;

trait GenerateDoc
{

    /**
     * @param string $path
     * @return array
     */
    public function getFiles(string $path)
    {
        $dirs = array_filter(glob($path . '*'), 'is_dir');

        $files = glob($path . '*.php');
        foreach ($dirs as $dir) {
            $files = array_merge($files, glob($dir . '/*.php'));
        }

        return is_array($files) ? $files : [];
    }

    public function getClassInfo(string $filename)
    {
        $class = '';
        $namespace = '';

        $code = file_get_contents($filename);
        $tokens = token_get_all($code);

        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
              && $tokens[$i - 1][0] == T_WHITESPACE
              && $tokens[$i][0] == T_STRING
            ) {
                $class = $tokens[$i][1];
            }

            if ($tokens[$i - 2][0] == T_NAMESPACE
              && $tokens[$i - 1][0] == T_WHITESPACE
//            && $tokens[$i][0] == T_NAME_QUALIFIED
            ) {
                $namespace = $tokens[$i][1];
            }
        }

        return [
          'class'     => '\\' . $namespace . '\\' . $class,
          'namespace' => $namespace,
          'short'     => $class,
        ];
    }

    public function getTableColumns(string $table)
    {
        global $wpdb;

        $cols_sql = "DESCRIBE $table";

        return $wpdb->get_results($cols_sql);
    }

    public function getColumnVarType(string $type)
    {
        preg_match('/^([a-z]*)/', $type, $match);
        $type = $match[0];

        switch ($type) {
            case 'string':
            case 'text':
            case 'date':
            case 'time':
            case 'guid':
            case 'datetimetz':
            case 'datetime':
            case 'decimal':
            case 'varchar':
                $type = 'string';
                break;
            case 'integer':
            case 'bigint':
            case 'smallint':
                $type = 'integer';
                break;
            case 'boolean':
            case 'tinyint':
                $type = 'boolean';
                break;
            case 'float':
                $type = 'float';
                break;
            default:
                $type = 'mixed';
                break;
        }

        return $type;
    }

    protected function mergeParams(array $comment_params, array $ref_params)
    {
        return array_map(function ($c_param) use ($ref_params) {
            foreach ($ref_params as $ref_param) {
                if ($c_param['name'] === $ref_param['name']) {
                    if (empty($c_param['type'])) {
                        $c_param['type'] = $ref_param['type'];
                    }
                    $c_param['default'] = $ref_param['default'];
                }
            }

            return $c_param;
        }, $comment_params);
    }

    protected function snake(string $value, string $delimiter = '_')
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));

        return strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
    }

    protected function extractReflectionTypes(\ReflectionMethod $reflection)
    {
        $reflection_type = $reflection->getReturnType();
        if (!$reflection_type) {
            return null;
        }

        if ($reflection_type instanceof \ReflectionNamedType) {
            $types[] = $this->getReflectionNamedType($reflection_type);
        } else {
            $types = [];
            /** @noinspection PhpUndefinedMethodInspection */
            foreach ($reflection_type->getTypes() as $named_type) {
                if ($named_type->getName() === 'null') {
                    continue;
                }

                $types[] = $this->getReflectionNamedType($named_type);
            }
        }

        if ($reflection_type->allowsNull()) {
            $types[] = 'null';
        }

        return implode('|', $types);
    }

    protected function getReflectionNamedType(\ReflectionNamedType $paramType): string
    {
        $parameterName = $paramType->getName();
        if (!$paramType->isBuiltin()) {
            $parameterName = '\\' . $parameterName;
        }

        return $parameterName;
    }

}
