<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

namespace Chameleon2die4\WPBonesExtend\Console;

use WPKirk\WPBones\Console\Command as BaseCommand;

abstract class Command extends BaseCommand
{

    protected static array $foreground = [
      'default' => '255',
      'black'   => '30',
      'red'     => '31',
      'green'   => '32',
      'brown'   => '33',
      'blue'    => '34',
      'magenta' => '35',
      'cyan'    => '36',
      'white'   => '37',
    ];

    protected static array $background = [
      'black'      => '40',
      'red'        => '41',
      'green'      => '42',
      'yellow'     => '43',
      'blue'       => '44',
      'magenta'    => '45',
      'cyan'       => '46',
      'light-grey' => '47',
    ];

    protected static array $styles = [
      'bold'      => '1',
      'faint'     => '2',
      'italic'    => '3',
      'underline' => '4',
      'blink'     => '5',
    ];

    /**
     * Commodity function to check if ClassName has been requested.
     *
     * @param string $className Optional. Command to check.
     *
     * @return string
     */
    protected function askClassNameIfEmpty(string $className = ""): string
    {
        if (empty($className)) {
            $className = $this->ask('ClassName:');
            if (empty($className)) {
                $this->error('ClassName is required');
                exit(0);
            }
        }

        return $className;
    }

    /** @noinspection PhpUnused */
    protected function askIfEmpty(string $value, string $name, string $default = '', bool $require = false): string
    {
        if (empty($value)) {
            $name = ucfirst($name);

            if (!empty($default)) {
                $value = $this->ask("{$name}:", $default);
            } else {
                $value = $this->ask("{$name}:");
                if (empty($value) && $require) {
                    $this->error("{$name} is required");
                    exit(0);
                }
            }
        }

        return $value;
    }

    /**
     * Return the current Plugin namespace defined in the namespace file.
     *
     * @return string
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function getNamespace(): string
    {
        [$null, $namespace] = $this->getPluginNameAndNamespace();

        return $namespace;
    }

    /**
     * Return the current Plugin name and namespace defined in the namespace file.
     *
     * @return array
     */
    public function getPluginNameAndNamespace(): array
    {
        return explode(",", file_get_contents('namespace'));
    }

    /**
     * Return option value or false
     *
     * @param string $name
     * @return bool|string
     */
    public function option(string $name)
    {
        $sanitizeOption = '--' . $name;
        $valueParam = '';

        $argv = [];
        foreach ($this->argv as $item) {
            $exp = explode('=', $item);

            if (count($exp) > 1) {
                $argv[$exp[0]] = $exp[1];
            } else {
                $argv[] = $item;
            }
        }

        if (!in_array($sanitizeOption, array_keys($argv))) {
            return false;
        }

        if (in_array($sanitizeOption, array_keys($this->options))) {
            $option = $this->options[$sanitizeOption];

            if (isset($option['param']) && $option['param']) {
                foreach ($argv as $key => $value) {
                    if ($key == $sanitizeOption) {
                        $valueParam = $value;
                    }
                }

                if (empty($valueParam)) {
                    $this->info('Missing param');

                    return false;
                }

                return $valueParam;
            }

            return true;
        }

        return false;
    }

    /**
     * Return argument
     *
     * @param int $ind
     * @return string|null
     */
    public function getArgv(int $ind = 0)
    {
        return $this->argv[$ind] ?? null;
    }

    /**
     * Return the content of a stub file with all replacements.
     *
     * @param string $filename The stub file name without extension
     * @param array $replacements
     * @return string
     */
    public function prepareStub(string $filename, array $replacements = []): string
    {
        $stub = $this->getStubContent($filename);

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Return the content of a stub file.
     *
     * @param string $filename
     * @return string
     */
    public function getStubContent(string $filename): string
    {
        return file_get_contents("plugin/Console/stubs/{$filename}.stub");
    }

    public function getAdditionalPath(string $className)
    {
        // get additional path
        $path = $namespacePath = '';
        if (false !== strpos($className, '/')) {
            $parts = explode('/', $className);
            $path = implode('/', $parts) . '/';
            $namespacePath = '\\' . implode('\\', $parts);
            $className = array_pop($parts);
        }

        return compact('className', 'path', 'namespacePath');
    }

    public function createFile(string $base, string $content, array $params)
    {
        if (!file_exists($base)) {
            mkdir($base, 0777, true);
        }

        if (!empty($params['path']) && !empty($params['namespacePath'])) {
            $content = str_replace('{Path}', $params['namespacePath'], $content);
            mkdir("{$base}/{$params['path']}", 0777, true);
        } else {
            $content = str_replace('{Path}', '', $content);
        }

        $filename = sprintf('%s.php', $params['className']);
        $filepath = "{$base}/{$params['path']}{$filename}";
        if (!file_exists($filepath)) {
            file_put_contents($filepath, $content);
            $this->line("Created {$filepath}");
        } else {
            $this->error('File already exist!');
        }
    }

    /**
     * Commodity to display an error message in the console.
     *
     * @param string $str The message to display.
     */
    protected function error(string $str)
    {
        $prefix = "<bg=red;fg=black;style=bold> ERROR </> ";

        $this->write($prefix . $str, true, true);
    }

    protected function info(string $str): void
    {
        $prefix = "<bg=blue;fg=black;style=bold> INFO </> ";

        $this->write($prefix . $str, true, true);
    }

    protected function warning(string $str): void
    {
        $prefix = "<bg=yellow;fg=black;style=bold> WARNING </> ";

        $this->write($prefix . $str, true, true);
    }

    protected function line(string $str): void
    {
        $this->write($str, true, true);
    }

    protected function emptyLine(): void
    {
        $this->write('', false, true);
    }

    protected function write(string $str, bool $tab = false, bool $newline = false)
    {
        if ($tab) {
            echo '  ';
        }

        echo $this->formatStyles($str);

        if ($newline) {
            echo "\n";
        }
    }

    function formatStyles(string $str)
    {
        preg_match_all('/<([a-z=;\-_]*)>(.*)<\/>/', $str, $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $index => $styles) {
                $exp = explode(';', $styles);
                $styles = [];
                foreach ($exp as $item) {
                    list($k, $v) = explode('=', $item);
                    $styles[$k] = $v;
                }

                $replace = "\e[";

                $params = [];
                foreach ($styles as $key => $value) {
                    switch ($key) {
                        case 'bg':
                            if (isset(self::$background[$value])) {
                                $params[] = self::$background[$value];
                            }
                            break;
                        case 'fg':
                            if (isset(self::$foreground[$value])) {
                                $params[] = self::$foreground[$value];
                            }
                            break;
                        case 'style':
                        case 'options':
                            if (isset(self::$styles[$value])) {
                                $params[] = self::$styles[$value];
                            }
                            break;
                    }
                }

                $replace .= implode(';', $params) . 'm';
                $replace .= $matches[2][$index];
                $replace .= "\033[0m";

                $str = str_replace($matches[0][$index], $replace, $str);
            }
        }

        return $str;
    }

    /** @noinspection PhpUndefinedFunctionInspection */
    public function getPluginPath() {
        $path = plugin_dir_path( __DIR__ );
        $exp = explode(DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR, $path);
        return $exp[0];
    }

}
