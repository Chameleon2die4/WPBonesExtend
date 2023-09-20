<?php

namespace Chameleon2die4\WPBonesExtend\Database;

use Closure;
use Chameleon2die4\WPBonesExtend\Database\Schema\Builder;
use WPKirk\WPBones\Support\Str;

abstract class Migration extends Builder
{

    /**
     * @var \wpdb
     */
    private $db;
    /**
     * @var string
     */
    private $charsetCollate;
    /**
     * @var string
     */
    protected $tableName;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->charsetCollate = $wpdb->get_charset_collate();
        $this->tableName      = $wpdb->prefix . Str::snake(Str::studly(get_called_class()));

        parent::__construct();
    }

    abstract public function up();
    abstract public function down();

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $this->db->prefix . $tableName;
    }

}
