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
    protected string $tableName;
    /**
     * @var string
     */
    protected string $table;


    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->tableName = $wpdb->prefix . $this->table;

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

    /**
     * @param  string  $tableName
     *
     * @return bool
     */
    public function tableExist(string $tableName) {
        if (!preg_match("/^{$this->db->prefix}/", $tableName)) {
            $tableName = $this->db->prefix . $tableName;
        }

        $query = $this->db->prepare( 'SHOW TABLES LIKE %s',
            $this->db->esc_like( $tableName ) );

        return $this->db->get_var( $query ) == $tableName;
    }

}
