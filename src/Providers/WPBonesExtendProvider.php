<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Providers;

use WPKirk\WPBones\Support\ServiceProvider;

class WPBonesExtendProvider extends ServiceProvider
{
    private string $slug;

    public function register()
    {
        $this->slug = $this->getPluginSlug();
        
        // Init services
        $classes = $this->plugin->config('plugin.services', []);
        foreach ($classes as $className) {
            new $className($this->plugin);
        }
        
        
        // Add admin settings link to plugins page
        if (function_exists('add_filter')) {
            $plugin_file = "{$this->slug}/index.php";
            add_filter('plugin_action_links_' . $plugin_file, [$this, 'addSettingsLink']);
        }
    }

    public function addSettingsLink($links)
    {
        /** @noinspection HtmlUnknownTarget */
        $link = sprintf(
            '<a href="%s?page=%s">%s</a>',
            admin_url('admin.php'),
            $this->slug,
            __('Settings')
        );

        array_unshift($links, $link);

        return $links;
    }
    

    /**
     * Return the plugin slug.
     */
    public function getPluginSlug(): string
    {
        $namespace = $this->getNamespace();

        $rep = preg_replace('/([A-Z])/', '_$1', $namespace);
        return trim(strtolower($rep), '_');
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
}
