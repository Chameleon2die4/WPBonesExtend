<?php

namespace Chameleon2die4\WPBonesExtend\Database;

use Closure;
use Exception;
use Chameleon2die4\WPBonesExtend\Database\Schema\Grammars\MySqlGrammar;
use Chameleon2die4\WPBonesExtend\Database\Schema\Grammars\Grammar;
use WPKirk\WPBones\Foundation\Log\LogServiceProvider;
use WPKirk\WPBones\Foundation\Plugin;
use wpdb;

class Connection
{

    /**
     * @var wpdb
     */
    protected $db;

    /**
     * @var LogServiceProvider
     */
    protected $log;

    /**
     * The schema grammar implementation.
     *
     * @var Grammar
     */
    protected Grammar $schemaGrammar;
    /**
     * The schema grammar implementation.
     *
     * @var Plugin
     */
    private $plugin;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;

        /** @noinspection PhpFullyQualifiedNameUsageInspection, PhpUndefinedClassInspection */
        $this->plugin = \WPKirk::$plugin;
        $this->log = $this->plugin->log();

        $this->useDefaultSchemaGrammar();
    }

    /**
     * Set the schema grammar to the default implementation.
     *
     * @return void
     */
    public function useDefaultSchemaGrammar()
    {
        $this->schemaGrammar = $this->getDefaultSchemaGrammar();
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return new MySqlGrammar();
    }

    /**
     * Get the schema grammar used by the connection.
     *
     * @return Grammar
     */
    public function getSchemaGrammar()
    {
        return $this->schemaGrammar;
    }

    public function getConfig(string $name) {
        return $this->db->{$name} ?? null;
    }

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->db->prefix;
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        /** @noinspection PhpUndefinedConstantInspection */
        return defined('DB_NAME') ? DB_NAME : null;
    }

    public function getColumnListing(string $table) {
        return $this->db->get_col("DESC $table");
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function statement(string $query, array $bindings = []){
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->bindValues($query, $bindings);

            return $this->db->get_results($statement);
        });
    }

//    public function statement($query, $bindings = [])
//    {
//        return $this->run($query, $bindings, function ($query, $bindings) {
//            if ($this->pretending()) {
//                return true;
//            }
//
//            $statement = $this->getPdo()->prepare($query);
//
//            $this->bindValues($statement, $this->prepareBindings($bindings));
//
//            $this->recordsHaveBeenModified();
//
//            return $statement->execute();
//        });
//    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param string $statement
     * @param array $bindings
     * @return string|null
     */
    public function bindValues(string $statement, array $bindings = [])
    {
//        foreach ($bindings as $key => $value) {
//            $statement->bindValue(
//              is_string($key) ? $key : $key + 1,
//              $value,
//              is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
//            );
//        }

        if (!empty($bindings)) {
            return $this->db->prepare($statement, ...$bindings);
        } else {
            return $statement;
        }
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     *
     * @return array
     */
    public function select(string $query, array $bindings = [], bool $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
//            if ($this->pretending()) {
//                return [];
//            }

            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // row from the database table, and will either be an array or objects.
//            $statement = $this->prepared(
//              $this->getPdoForSelect($useReadPdo)->prepare($query)
//            );
//
//            $this->bindValues($statement, $this->prepareBindings($bindings));
//
//            $statement->execute();
//
//            return $statement->fetchAll();


            $statement = $this->bindValues($query, $bindings);

            return $this->db->get_results($statement);
        });
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  Closure|null  $callback
     *
     * @return mixed
     *
     */
    protected function run(string $query, array $bindings, Closure $callback = null)
    {
        $result = null;
//        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
//            $beforeExecutingCallback($query, $bindings, $this);
//        }
//
//        $this->reconnectIfMissingConnection();

//        $start = microtime(true);

        // Here we will run this query. If an exception occurs we'll determine if it was
        // caused by a connection that has been lost. If that is the cause, we'll try
        // to re-establish connection and re-run the query with a fresh connection.
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $this->log->error($e);
        }

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time, so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.
//        $this->logQuery(
//          $query, $bindings, $this->getElapsedTime($start)
//        );

        return $result;
    }

    /**
     * Run a SQL statement.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  Closure  $callback
     * @return mixed|void
     *
     * @throws QueryException
     */
    protected function runQueryCallback(string $query, array $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try {
            return $callback($query, $bindings);
        }

            // If an exception occurs when attempting to run a query, we'll format the error
            // message to include the bindings with SQL, which will make this exception a
            // lot more helpful to the developer instead of just the database's errors.
        catch (Exception $e) {
            $this->log->error($e);
        }
    }

}
