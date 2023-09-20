<?php
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection, PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnusedPrivateMethodInspection */

namespace Chameleon2die4\WPBonesExtend\Model;

use Chameleon2die4\WPBonesExtend\Contracts\Arrayable;
use Chameleon2die4\WPBonesExtend\Str;
use WPKirk\WPBones\Database\DB;

/**
 * The Database Model provides a base class for all database models.
 *
 * @package WPKirk\WPBones\Database
 *
 * @property \WPKirk\WPBones\Database\Support\Collection $collection
 * @method static \WPKirk\WPBones\Database\Support\Collection get()
 * @method static \WPKirk\WPBones\Database\Support\Collection all()
 * @method static array|int insert(array $values)
 * @method static int|string count()
 * @method static \Chameleon2die4\WPBonesExtend\Model\Model last()
 * @method static \Chameleon2die4\WPBonesExtend\Model\Model first()
 * @method static \Chameleon2die4\WPBonesExtend\Model\Model find(int $id)
 * @method static \WPKirk\WPBones\Database\QueryBuilder limit(int $value = 1)
 * @method static \WPKirk\WPBones\Database\QueryBuilder offset(int $value = 0)
 * @method static \WPKirk\WPBones\Database\QueryBuilder orderBy(string $column, string $order = 'asc')
 * @method static \WPKirk\WPBones\Database\QueryBuilder where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \WPKirk\WPBones\Database\QueryBuilder orWhere(string $column, string $operator = null, mixed $value = null, string $boolean = 'or')
 * @method static \WPKirk\WPBones\Database\QueryBuilder orWhereIn(string $column, array|string $value)
 * @method static \WPKirk\WPBones\Database\QueryBuilder orWhereNotIn(string $column, array|string $value)
 * @method static \WPKirk\WPBones\Database\QueryBuilder orWhereBetween(string $column, array|string $value)
 * @method static \WPKirk\WPBones\Database\QueryBuilder orWhereNotBetween(string $column, array|string $value)
 * @method static \WPKirk\WPBones\Database\QueryBuilder whereIn(string $column, array|string $value, string $boolean = 'and')
 * @method static \WPKirk\WPBones\Database\QueryBuilder whereNotIn(string $column, array|string $value, string $boolean = 'and')
 * @method static \WPKirk\WPBones\Database\QueryBuilder whereBetween(string $column, array|string $value, string $boolean = 'and')
 * @method static \WPKirk\WPBones\Database\QueryBuilder whereNotBetween(string $column, array|string $value, string $boolean = 'and')
 * @method static \WPKirk\WPBones\Database\QueryBuilder select(array|string $columns)
 * @method static \WPKirk\WPBones\Database\QueryBuilder delete()
 * @method static \WPKirk\WPBones\Database\QueryBuilder truncate()
 *
 */
class Model extends DB implements Arrayable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected array $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected array $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected array $appends = [];

    /**
     * DB table columns
     *
     * @var array
     */
    protected array $columns;

    public function __construct(array $data = [])
    {
        if (!isset($this->table)) {
            $this->table = self::getTableName(get_called_class());
        } else {
            $this->table = self::getTableName($this->table, true);
        }

        parent::__construct($this->table, $this->primaryKey);

        $this->columns = $this->getTableColumns();

        if (!empty($data)) {
            $this->setAttributes($data);
        }
    }

    /**
     * Return a WordPress table name with the WordPress prefix.
     *
     * @param string $class
     * @param bool $prop
     * @return string
     * @example Model::tableName('User') returns 'wp_users'
     *          Model::tableName('WPMyTable') returns 'wp_w_p_my_table'
     *          Model::tableName('WP_MyTable') returns 'wp_w_p_my_table'
     */
    public static function getTableName(string $class, bool $prop = false): string
    {
        global $wpdb;

        if ($prop) {
            $name = $class;
        } else {
            $paths = explode('\\', $class);
            $only = array_pop($paths);
            $name = Str::snake(Str::studly($only));
            $name = Str::plural($name);
        }

        return Str::startsWith($name, $wpdb->prefix) ? $name : $wpdb->prefix . $name;
    }

    /**
     * Save model data to DB
     *
     * @return \Chameleon2die4\WPBonesExtend\Model\Model|null
     */
    public function save() {
        $data = $this->toArray();
        $primary = $this->primaryKey;

        if ($primary && isset($this->{$primary})) {
            return $this->update($data);
        } else {
            return self::create($data);
        }
    }

    /**
     * Update model data in DB
     *
     * @param array $data
     * @return \Chameleon2die4\WPBonesExtend\Model\Model
     * @noinspection PhpUndefinedMethodInspection
     */
    public function update(array $data) {
        $model = $this->setAttributes($data);

        parent::update($model->toArray());

        return $this;
    }

    /**
     * Get model array representation
     *
     * @return array
     */
    public function toArray() {
        $data = [];
        $atts = $this->getSerializationAttributes();

        foreach ($atts as $name) {
            $data[$name] = $this->{$name} ?? null;
        }

        return $data;
    }

    public function __toString() {
        return json_encode($this->toArray());
    }

    public function __serialize() {
        return $this->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Magic methods
    |--------------------------------------------------------------------------
    */

    /**
     * We will this magic method to handle all static/instance methods.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return (new static)->$name(...$arguments);
    }

    /**
     * @return string
     */
    protected function getTable(): string
    {
        return $this->table;
    }

//    /**
//     * Return the values of a single column.
//     *
//     * @param string $column_name
//     * @return \Chameleon2die4\WPBonesExtend\Collection
//     */
//    public function pluck($column_name)
//    {
//        $this->select($column_name);
//        $this->all();
//
//        return collect($this->collection->getArrayCopy())->pluck($column_name);
//    }

    /**
     * Begin querying the model.
     *
     * @return \WPKirk\WPBones\Database\QueryBuilder
     */
    protected function query()
    {
        return $this->queryBuilder;
    }

    /**
     * @param string $name
     * @return \WPKirk\WPBones\Database\QueryBuilder
     */
    protected function whereNotNull(string $name)
    {
        return $this->where($name, '!=');
    }

    /**
     * @param string $name
     * @return \WPKirk\WPBones\Database\QueryBuilder
     */
    protected function whereNull(string $name)
    {
        return $this->where($name, '=');
    }

    /**
     * Create model with attributes
     *
     * @param array $data
     * @return \Chameleon2die4\WPBonesExtend\Model\Model|null
     */
    protected function create(array $data) {
        $this->setAttributes($data);
        $primary = $this->primaryKey;

        $id = self::insert($this->toArray());

        if ($id) {
            $this->{$primary} = $id;

            return $this;
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    protected function getTableColumns() {
        $columns = $this->getDBTableColumns($this->table);

        return is_array($columns) ? array_column($columns, 'Field') : [];
    }


    /*
    |--------------------------------------------------------------------------
    | Private methods
    |--------------------------------------------------------------------------
    */

    /**
     * @param array $data
     * @return \Chameleon2die4\WPBonesExtend\Model\Model
     */
    private function setAttributes(array $data) {
        foreach ($data as $key => $value) {
            if ($this->isAllowedAttribute($key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isAllowedAttribute(string $name) {
        return in_array($name, $this->columns)
          && ((empty($this->fillable) && !in_array($name, $this->guarded)) || in_array($name, $this->fillable));
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isSerializationAttribute(string $name) {
        return (in_array($name, $this->columns) || in_array($name, $this->appends))
          && !in_array($name, $this->hidden);
    }

    private function getSerializationAttributes() {
        $hidden = $this->hidden;
        $atts = array_merge($this->columns, $this->appends);

        return array_filter($atts, function ($name) use ($hidden) {
           return !in_array($name, $hidden);
        });
    }

    /**
     * @param string $table
     * @return array|null
     */
    private function getDBTableColumns(string $table)
    {
        global $wpdb;

        $cols_sql = "DESCRIBE $table";

        return $wpdb->get_results($cols_sql);
    }

}
