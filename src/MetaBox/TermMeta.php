<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\MetaBox;

abstract class TermMeta extends MetaBox
{

    public function __construct()
    {
        parent::__construct();

        $screens = $this->getScreens();
        foreach ($screens as $screen) {
            add_action( "{$screen}_add_form_fields", [$this, 'addForm'] );
            add_action( "{$screen}_edit_form", [$this, 'editForm'], 90 );

            add_action( "edit_{$screen}",   [$this, 'save'] );
            add_action( "create_{$screen}", [$this, 'save'] );
        }
    }

    public function addForm() {
        $view = $this->getClassId();
        $key = "meta.add.{$view}";

        if ($this->viewExist($key)) {
            echo $this->plugin->view($key)->with([
              'title' => $this->getTitle(),
              'name'  => $this->getBaseId(),
            ]);
        }
    }

    public function editForm($term) {
        $view = $this->getClassId();
        $key = "meta.edit.{$view}";

        if ($this->viewExist($key)) {
            echo $this->plugin->view($key)->with([
              'title' => $this->getTitle(),
              'name'  => $this->getBaseId(),
            ]);
        }
    }

}
