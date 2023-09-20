<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\MetaBox;

use Chameleon2die4\WPBonesExtend\Storage;
use WPKirk\WPBones\Foundation\Plugin;

abstract class MetaBox
{

    protected Plugin $plugin;

    public array $screens = [];
    public string $base_id;
    public string $title;
    protected Storage $storage;

    public function __construct()
    {
        /** @noinspection PhpFullyQualifiedNameUsageInspection, PhpUndefinedClassInspection */
        $this->plugin = \WPKirk::$plugin;
        $this->storage = new Storage($this->plugin);

        $screens = $this->generateScreens();
        if (!empty($screens)) {
            $this->setScreens($screens);
        }
    }

    public function html() {
        $view = $this->getClassId();

        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        echo $this->plugin->view("meta.{$view}")->with([
          'title' => $this->getTitle(),
          'name' => $this->getBaseId(),
        ]);
    }

    abstract public function save(int $id);

    protected function getClassId() {
        $exp = explode("\\", get_called_class());
        $class = array_pop($exp);
        $template = preg_replace('/([A-Z])/', '_$1', $class);
        $template = trim(strtolower($template), '_');

        return strtolower($template);
    }

    protected function getClassTitle() {
        $exp = explode("\\", get_called_class());
        $class = array_pop($exp);
        $template = preg_replace('/([A-Z])/', ' $1', $class);

        $namespace = $this->getNamespace();

        return $namespace . ' ' . trim($template);
    }

    /**
     * @return array
     */
    public function getScreens(): array
    {
        return $this->screens;
    }

    /**
     * @return string
     */
    public function getBaseId(): string
    {
        return $this->base_id ?? $this->getClassId();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?? $this->getClassTitle();
    }

    /**
     * @param array $screens
     */
    public function setScreens(array $screens): void
    {
        $this->screens = $screens;
    }

    public function generateScreens(): array
    {
        return [];
    }

    /**
     * Return the current Plugin namespace defined in the namespace file.
     *
     * @return string
     * @noinspection PhpUnusedLocalVariableInspection
     */
    protected function getNamespace(): string
    {
        [$null, $namespace] = $this->getPluginNameAndNamespace();

        return $namespace;
    }

    /**
     * Return the current Plugin name and namespace defined in the namespace file.
     *
     * @return array
     */
    protected function getPluginNameAndNamespace(): array
    {
        $path = $this->plugin->getBasePath();

        return explode(",", file_get_contents($path . '/namespace'));
    }

    protected function viewExist(string $key) {
        $filename = str_replace('.', '/', $key) . '.php';
        $path = $this->plugin->getBasePath() . '/resources/views/' . $filename;

        return file_exists($path);
    }

}
