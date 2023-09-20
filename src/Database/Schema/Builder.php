<?php /** @noinspection PhpUnused */

namespace Chameleon2die4\WPBonesExtend\Database\Schema;

use Closure;
use Chameleon2die4\WPBonesExtend\Container\Container;
use Chameleon2die4\WPBonesExtend\Database\Connection;
use Chameleon2die4\WPBonesExtend\Database\Schema\Grammars\Grammar;

//use Chameleon2die4\WPBonesExtend\Database\Schema\Blueprint;

class Builder
{
    /**
     * The database connection instance.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The schema grammar instance.
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * The Blueprint resolver callback.
     *
     * @var Closure
     */
    protected $resolver;

    /**
     * The default string length for migrations.
     *
     * @var int
     */
    public static $defaultStringLength = 255;

    /**
     * The default relationship morph key type.
     *
     * @var string
     */
    public static $defaultMorphKeyType = 'int';

    public function __construct()
    {
        $this->connection = new Connection();
        $this->grammar = $this->connection->getSchemaGrammar();
    }

    /**
     * Modify a table on the schema.
     *
     * @param string $table
     * @param Closure $callback
     * @return void
     */
    public function table(string $table, Closure $callback)
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Create a new table on the schema.
     *
     * @param string $table
     * @param Closure $callback
     * @return void
     */
    public function create(string $table, Closure $callback)
    {
        $this->build(tap($this->createBlueprint($table), function (Blueprint $blueprint) use ($callback) {
            $blueprint->create();

            $callback($blueprint);
        }));
    }

    /**
     * Drop a table from the schema.
     *
     * @param string $table
     * @return void
     */
    public function drop(string $table)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->drop();
        }));
    }

    /**
     * Drop a table from the schema if it exists.
     *
     * @param string $table
     * @return void
     */
    public function dropIfExists(string $table)
    {
        $this->build(tap($this->createBlueprint($table), function (Blueprint $blueprint) {
            $blueprint->dropIfExists();
        }));
    }

    /**
     * Drop columns from a table schema.
     *
     * @param string $table
     * @param string|array $columns
     * @return void
     */
    public function dropColumns(string $table, $columns)
    {
        $this->table($table, function (Blueprint $blueprint) use ($columns) {
            $blueprint->dropColumn($columns);
        });
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param string $table
     * @param Closure|null $callback
     * @return Blueprint
     */
    protected function createBlueprint(string $table, Closure $callback = null)
    {
        global $wpdb;

//        $prefix = $this->connection->getConfig('prefix_indexes')
//          ? $this->connection->getConfig('prefix')
//          : '';

        $prefix = $wpdb->prefix;
        $table = $prefix . $table;

        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }

        return Container::getInstance()->make(Blueprint::class, compact('table', 'callback', 'prefix'));
    }

    /**
     * Execute the blueprint to build / modify the table.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }

    /**
     * Enable foreign key constraints.
     *
     * @return bool
     */
    public function enableForeignKeyConstraints()
    {
        return $this->connection->statement(
          $this->grammar->compileEnableForeignKeyConstraints()
        );
    }

    /**
     * Disable foreign key constraints.
     *
     * @return bool
     */
    public function disableForeignKeyConstraints()
    {
        return $this->connection->statement(
          $this->grammar->compileDisableForeignKeyConstraints()
        );
    }

}
