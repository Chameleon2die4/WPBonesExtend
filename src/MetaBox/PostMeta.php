<?php

namespace Chameleon2die4\WPBonesExtend\MetaBox;

abstract class PostMeta extends MetaBox
{

    public function __construct()
    {
        parent::__construct();

        add_action( 'add_meta_boxes', [$this, 'add'] );
        add_action( 'save_post', [$this, 'save'] );
    }

    public function add() {
        $screens = $this->getScreens();

        foreach ( $screens as $screen ) {
            add_meta_box(
              $this->getBaseId(),     // Unique ID
              $this->getTitle(),      // Box title
              [ $this, 'html' ],      // Content callback, must be of type callable
              $screen                 // Post type
            );
        }
    }

    public function generateScreens(): array
    {
        $exclude = ['attachment'];
        $types = get_post_types(['public' => true, '_builtin' => true]);
        return array_diff(array_keys($types), $exclude);
    }

}
