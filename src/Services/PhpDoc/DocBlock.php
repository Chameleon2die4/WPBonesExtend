<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Services\PhpDoc;

class DocBlock
{

    private string $typeReg = '[a-zA-Z\\\_]*';
    private string $comment = '';
    private array $params = [];
    private array $properties = [];
    private string $return = '';
    private array $comments = [];
    private array $strings = [];
    private array $methods = [];

    public function __construct(string $comment = '')
    {
        if (!empty($comment)) {
            $this->setComment($comment);
            $this->parse();
        }
    }

    public function parse(string $comment = '')
    {
        if (!empty($comment)) {
            $this->setComment($comment);
        }

        $this->parseComments();
        $this->parseParams();
        $this->parseProps();
        $this->parseMethods();
        $this->parseReturn();
    }

    protected function parseComments()
    {
        $comment = str_replace('/**', '', $this->comment);
        $comment = str_replace('*/', '', $comment);
        $comments = explode("\n", $comment);
        $comments = array_map('trim', $comments);
        $comments = array_filter($comments);
        $comments = array_values($comments);

        $this->comments = array_map(function ($item) {
            return preg_replace('/^\*\s?/', '', $item);
        }, $comments);
    }

    public function parseParams()
    {
        if (!empty($this->comment)) {
            $pattern = '/@param (' . $this->typeReg . ')?\s?\$([a-zA-Z_]*)/';
            preg_match_all($pattern, $this->comment, $matches);

            if (count($matches) === 3) {
                foreach ($matches[2] as $key => $name) {
                    $this->params[] = [
                      'name' => $name,
                      'type' => $matches[1][$key],
                    ];
                }
            }
        }
    }

    public function parseProps()
    {
        if (!empty($this->comment)) {
            $pattern = '/@property (' . $this->typeReg . ')?\s?\$([a-zA-Z_]*)/';
            preg_match_all($pattern, $this->comment, $matches);

            if (count($matches) === 3) {
                foreach ($matches[2] as $key => $name) {
                    $this->properties[] = [
                      'name' => $name,
                      'type' => $matches[1][$key],
                    ];
                }
            }
        }
    }

    public function parseMethods()
    {
        if (!empty($this->comment)) {
            $pattern = '/@method (static )?(' . $this->typeReg . ' )?([a-zA-Z_]+\()(.*)?\)/';
            preg_match_all($pattern, $this->comment, $matches);

            if (count($matches)) {
                foreach ($matches[2] as $key => $type) {
                    $name = trim($matches[3][$key], '(');

                    $this->methods[] = [
                      'name'   => $name,
                      'type'   => trim($type),
                      'params' => $this->parseParamsString($matches[4][$key]),
                      'static' => true,
                    ];
                }
            }
        }
    }

    public function parseParamsString(string $str)
    {
        $params = [];

        if (!empty($str)) {
            $strings = preg_split('/,\s?/', $str);
            $pattern = '/^(' . $this->typeReg . ') \$([a-zA-Z_]*) = (.*)/';
            $with_type = '/^(' . $this->typeReg . ') \$([a-zA-Z_]*)$/';

            foreach ($strings as $line) {
                if (preg_match($pattern, $line, $matches)) {
                    $params[] = [
                      'name'    => $matches[2],
                      'type'    => $matches[1],
                      'default' => $matches[3],
                    ];
                } elseif (preg_match($with_type, $line, $matches)) {
                    $params[] = [
                      'name'    => $matches[2],
                      'type'    => $matches[1],
                      'default' => null,
                    ];
                } elseif (preg_match('/^\$([a-zA-Z_]*)$/', $line, $matches)) {
                    $params[] = [
                      'name'    => $matches[1],
                      'type'    => '',
                      'default' => null,
                    ];
                }
            }
        }

        return $params;
    }

    public function parseReturn()
    {
        if (!empty($this->comment)) {
            $pattern = '/@return (' . $this->typeReg . ')/';
            preg_match($pattern, $this->comment, $matches);

            if (count($matches) === 2) {
                $this->return = $matches[1];
            }
        }
    }

    public function generate()
    {
        $data = $this->getData();
        foreach ($data as $type => $values) {
            switch ($type) {
                case 'strings':
                    foreach ($values as $value) {
                        $this->comments[] = $value;
                    }
                    break;
                case 'props':
                    $format = "@property %s$%s";
                    foreach ($values as $prop) {
                        $type = !empty($prop['type']) ? $prop['type'] . ' ' : '';
                        $this->comments[] = sprintf($format, $type, $prop['name']);
                    }
                    break;
                case 'params':
                    $format = "@param %s$%s";
                    foreach ($values as $prop) {
                        $type = !empty($prop['type']) ? $prop['type'] . ' ' : '';
                        $this->comments[] = sprintf($format, $type, $prop['name']);
                    }
                    break;
                case 'methods':
                    $format = "@method %s%s%s(%s)";
                    foreach ($values as $method) {
                        $static = $method['static'] ? 'static ' : '';
                        $params = $this->getParamsString($method['params']);
                        $type = !empty($method['type']) ? $method['type'] . ' ' : $method['type'];

                        $this->comments[] = sprintf($format, $static, $type, $method['name'], $params);
                    }

                    break;
            }
        }

        if (!empty($this->return)) {
            $this->comments[] = "@return {$this->return}";
        }

        $this->comment = "/** \n";
        foreach ($this->comments as $line) {
            $this->comment .= sprintf(" * %s\n", $line);
        }
        $this->comment .= " */\n";

        return $this->comment;
    }

    public function getParamsString(array $params)
    {
        $strings = [];
        foreach ($params as $param) {
            $str = '';
            if (!empty($param['type'])) {
                $str = $param['type'];
            }

            $str .= ' $' . $param['name'];

            if (!empty($param['default'])) {
                $str .= ' = ' . $param['default'];
            }

            $strings[] = $str;
        }

        return implode(', ', $strings);
    }

    public function addLine(string $str)
    {
        $this->strings[] = $str;
    }


    public function getData(): array
    {
        return [
          'strings' => $this->getStrings(),
          'props'   => $this->getProperties(),
          'params'  => $this->getParams(),
          'methods' => $this->getMethods(),
          'return'  => $this->getReturn(),
        ];
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getReturn(): string
    {
        return $this->return;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @param array $property
     */
    public function addProperty(array $property): void
    {
        $this->properties[] = $property;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return array
     */
    public function getStrings(): array
    {
        return $this->strings;
    }

    /**
     * @param string $return
     */
    public function setReturn(string $return): void
    {
        $this->return = $return;
    }

    /**
     * @param array $method
     */
    public function addMethod(array $method): void
    {
        $this->methods[] = $method;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

}
