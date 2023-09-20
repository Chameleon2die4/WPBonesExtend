<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend;

use WPKirk\WPBones\Foundation\Plugin;

class Storage
{
    /**
     * @var Plugin
     */
    static Plugin $plugin;
    /**
     * @var string
     */
    private static string $pluginPath;

    /**
     * @var string
     */
    protected string $path;

    public function __construct(Plugin $plugin)
    {
        self::$plugin = $plugin;
        self::$pluginPath = self::getPluginPath();

        $this->disk();
    }

    /**
     * @param string $name
     * @return Storage
     */
    public function disk(string $name = '')
    {
        $disks = self::$plugin->config('filesystem');
        $plugin_path = self::$pluginPath;
        $plugin_path = preg_replace('/\/$/', '', $plugin_path);

        if (!empty($name) && isset($disks[$name])) {
            $this->path = $plugin_path . $disks[$name];
        } else {
            $default = $disks['default'];
            $this->path = $plugin_path . $disks[$default];
        }

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function path(string $name)
    {
        return $this->path . $name;
    }

    /**
     * @param string $name
     * @param string $content
     * @return false|bool
     */
    public function put(string $name, string $content = '')
    {
        $full = $this->path($name);
        $dir = dirname($full);
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return file_put_contents($full, $content);
    }

    /**
     * @param string $name
     * @param bool $decode
     * @return false|string
     */
    public function get(string $name, bool $decode = false)
    {
        $full = $this->path($name);

        $content = file_get_contents($full);

        if ($decode) {
            return json_decode($content);
        } else {
            return $content;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function delete(string $name)
    {
        $full = $this->path($name);

        return unlink($full);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name)
    {
        $full = $this->path($name);

        return file_exists($full);
    }

    /**
     * @param string $directory
     * @return array
     */
    public function files(string $directory = '')
    {
        $full = $this->path($directory);

        return scandir($full) ? array_diff(scandir($full), array('.', '..')) : [];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getDisk(string $name = '')
    {
        $this->disk($name);

        if (!file_exists($this->path)) {
            mkdir($this->path);
        }

        return $this->path;
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function resolve(string $path) {
        $full = $this->path($path);

        foreach (glob($full) as $file) {
            return $file;
        }

        return null;
    }

    public static function getPluginPath() {
        /** @noinspection PhpUndefinedFunctionInspection */
        $path = plugin_dir_path( __DIR__ );
        $exp = explode('/vendor/', $path);
        return $exp[0];
    }

    public static function getPluginUrl() {
        /** @noinspection PhpUndefinedFunctionInspection */
        $path = plugin_dir_url( __DIR__ );
        $exp = explode('/vendor/', $path);
        return $exp[0];
    }

}
