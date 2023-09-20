<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Providers;

use WPKirk\WPBones\Support\ServiceProvider;

class WPBonesExtendProvider extends ServiceProvider
{
    public function register()
    {
        // Init services
        $classes = $this->plugin->config('plugin.services', []);
        foreach ($classes as $className) {
            new $className($this->plugin);
        }
    }
}
