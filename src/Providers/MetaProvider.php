<?php

namespace Chameleon2die4\WPBonesExtend\Providers;

if (! defined('ABSPATH')) {
    exit;
}

use WPKirk\WPBones\Support\ServiceProvider;

class MetaProvider extends ServiceProvider
{

  public function register()
  {
      $classes = $this->plugin->config('plugin.meta', []);
      foreach ($classes as $className) {
          new $className();
      }
  }
}
