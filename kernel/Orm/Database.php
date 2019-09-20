<?php
namespace Kernel\Orm;
use AdrienM\Collection\Collection;
use Kernel\Tools\Utils;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;

trait Database
{
    /**
     * Get the model name with the namespace
     * @return string
     */
    public static function getModel(): string
    {
        if (isset(static::$model)) {
            return "Models\\" . static::$model;
        } else {
            $classExploded = explode('\\', static::class);
            return "Models\\" . substr($classExploded[count($classExploded) - 1], 0, -3);
        }
    }

    /**
     * Get table which called.
     * @return string
     */
    public static function getTable(): string
    {
        if (isset(static::$table)) {
            return static::$table;
        } else {
            $classExploded = explode('\\', static::class);
            return strtolower( substr($classExploded[count($classExploded) - 1], 0, -3) );
        }
    }

    /**
     * Get fields by table name
     * @param string|null $table
     * @return Collection
     */
    public static function getColumns(string $table = null): Collection
    {
        if (null == $table) {
            $table = self::getTable();
        }

        $stmt = 'SHOW COLUMNS FROM `' . $table . '`';

        try {
            $q = Connection::getInstance()->prepare($stmt);
            $q->execute();
            return Collection::from( $q->fetchAll(\PDO::FETCH_ASSOC) );

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }


    /**
     * Get list of foreign keys
     * @param string|null $table
     * @return Collection
     */
    public static function getForeignKeys(string $table = null): Collection
    {
        if (null == $table) {
            $table = self::getTable();
        }

        $dbname = Utils::getConfigElement("database")->dbname;

        $stmt = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME ".
            "FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ".
            "WHERE REFERENCED_TABLE_SCHEMA = ? AND TABLE_NAME = ? ".
            "AND (COLUMN_NAME != 'id' OR CONSTRAINT_NAME != 'PRIMARY')";

        try {
            $q = Connection::getInstance()->prepare($stmt);
            $q->execute([$dbname, $table]);
            return Collection::from( $q->fetchAll(\PDO::FETCH_ASSOC) );

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get table
     * @return Collection
     */
    public static function getTables(): Collection
    {
        try {
            $q = Connection::getInstance()->prepare("SHOW TABLES");
            $q->execute();
            return Collection::from( $q->fetchAll(\PDO::FETCH_ASSOC) );

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get values with SELECT query and clause WHERE
     * @param string $where
     * @param array $params
     * @param string|null $order
     * @param string|null $limit
     * @return Collection
     * @throws \ReflectionException
     */
    public static function where(string $where, array $params = [], string $order = null, string $limit = null): Collection
    {
        $query = 'SELECT * FROM `' . self::getTable() . '` WHERE ' . $where . self::order($order) . self::limit($limit);
        return self::query($query, $params);
    }

    /**
     * Get first row with SELECT query and clause WHERE
     * @param string $where
     * @param array $params
     * @return object
     */
    public static function whereFirst(string $where, array $params): object
    {
        return self::where($where, $params)->getFirst();
    }

    /**
     * Get last row with SELECT query and clause WHERE
     * @param string $where
     * @param array $params
     * @return object
     */
    public static function whereLast(string $where, array $params): object
    {
        return self::where($where, $params)->getLast();
    }

    /**
     * Get values by params
     * @param array $params
     * @param string|null $order
     * @param string|null $limit
     * @return Collection
     */
    public static function find(array $params, string $order = null, string $limit = null): Collection
    {
        $where = [];
        $p = [];
        foreach ($params as $k => $v) {
            $where[] = '`' . $k . '` = ?';
            $p[] = $v;
        }

        return self::where(implode(' and ', $where), $p, $order, $limit);
    }

    /**
     * Get first row by params
     * @param array $params
     * @return object
     */
    public static function findFirst(array $params): object
    {
        return self::find($params)->getFirst();
    }

    /**
     * Get last row by params
     * @param array $params
     * @return object
     */
    public static function findLast(array $params): object
    {
        return self::find($params)->getLast();
    }

    /**
     * Get first row by id
     * @param string|int $id
     * @return object
     */
    public static function getById($id): object
    {
        return self::findFirst(['id' => $id]);
    }

    /**
     * Get all data from table
     * @param string|null $order
     * @param string|null $limit
     * @return Collection
     */
    public static function getAll(string $order = null, string $limit = null): Collection
    {
        return self::query('SELECT * FROM '. self::getTable() . self::order($order) . self::limit($limit));
    }

    /**
     * Get the first row
     * @return object
     */
    public static function getFirst(): object
    {
        return self::getAll()->getFirst();
    }

    /**
     * Get the last row
     * @return object
     */
    public static function getLast(): object
    {
        return self::getAll()->getLast();
    }

    /**
     * Set string ORDER BY...
     * @param string $order
     * @return string
     */
    private static function order(?string $order): string
    {
        return (null !== $order) ? (' ORDER BY ' . $order) : '';
    }

    /**
     * Set string LIMIT...
     * @param string $limit
     * @return string
     */
    private static function limit(?string $limit): string
    {
        return (null !== $limit) ? (' LIMIT ' . $limit) : '';
    }

    /**
     * @param string|null $where
     * @param array $params
     * @return int
     */
    public static function count(string $where = null, array $params = []): int
    {
        if (null !== $where) { $where = ' WHERE '. $where; }
        else { $where = ''; }

        /*$query = new Query();
        $query->select("count(*)")
            ->from(self::getTable())
            ->where($where);
        return (int) self::query($query->getStatement(), $params)[0]->nb;*/

        return (int) self::query('SELECT count(*) as nb FROM '. self::getTable() . $where, $params)
            ->getFirst()->nb;
    }

    /**
     * Store the data in database
     * If the data has an ID, the function update the data
     * Else the function insert the data
     * @return int|object
     */
    public static function store($obj)
    {
        $class = self::getModel();
        if ($obj instanceof $class) {
            return (null != $obj->getId()) ? self::update($obj) : self::insert($obj);
        } else {
            throw new OrmException("Argument passed must be an instance of $class, instance of ". get_class($obj) ." given.");
        }
    }

    /**
     * Get the key and the value with the getter
     * @param array $keys
     * @param array $values
     */
    private static function setKeysAndValues(object $obj, ?array &$keys = [], ?array &$values = [])
    {
        $reflect = new \ReflectionObject($obj);

        foreach ($reflect->getProperties() as $property) {
            if (!$property->isStatic()) {

                $propertyName = $property->getName();
                $getter = "get" . Utils::toPascalCase($propertyName);

                if (is_object($obj->$getter())) {
                    if (method_exists($obj->$getter(), "getId")) {
                        $keys[] = '`' . $propertyName . '_id`';
                        $values[] = $obj->$getter()->getId();
                    }
                } else {
                    $keys[] = '`' . $propertyName . '`';
                    $values[] = $obj->$getter();
                }
            }
        }
    }

    /**
     * Insert new values
     * @return object
     */
    private static function insert($obj)
    {
        self::setKeysAndValues($obj, $keys, $values);

        $keys = implode(',', $keys);

        $q = self::exec(
            'INSERT INTO '. self::getTable() .'(' . $keys . ') VALUES (?'. str_repeat(', ?', count($values) - 1) .')',
            $values
        );

        if ($q) {
            $obj->setId(self::getLastId());

            return $obj;
        }

        return false;
    }

    /**
     * Update row
     * Values and key with $this
     * @return int
     */
    private static function update($obj): int
    {
        self::setKeysAndValues($obj, $keys, $values);

        $values[] = $obj->getId();

        $keys = implode(' = ?, ', $keys);

        return self::exec(
            'UPDATE '. self::getTable() .' SET '. $keys . ' = ?' . ' WHERE id = ?',
            $values
        );
    }

    /**
     * Delete row from database
     * @return int
     */
    public static function delete(object $obj): int
    {
        $params = [];
        $values = [];
        if (empty($obj->getId())) {
            foreach ($obj as $k => $v) {

                $getter = "get" . Utils::toPascalCase($k);

                if (null != $obj->$getter()) {
                    $params[] = $k;
                    $values[] = $obj->$getter();
                }
            }
        } else {
            $params = ['id'];
            $values[] = $obj->getId();
        }

        return self::exec('DELETE FROM ' . self::getTable() . ' WHERE ' . implode(' = ? AND ', $params) . ' = ?', $values);
    }

    /**
     * Get the last id inserted
     * @return int
     */
    public static function getLastId(): int
    {
        return Connection::getInstance()->lastInsertId();
    }

    /**
     * Execute query
     * @param string $statement
     * @param array|null $params
     * @return Collection
     * @throws \ReflectionException
     */
    public static function query(string $statement, array $params = null): Collection
    {
        $q = Connection::getInstance()->prepare($statement);
        $q->execute($params);
        $res = $q->fetchAll(\PDO::FETCH_CLASS, self::getModel());

        foreach ($res as $item) {
            $item->foreignKeyToObject();
        }

        return Collection::from($res);
    }

    /**
     * @param string $statement
     * @param array $params
     * @return int
     */
    public static function exec(string $statement, array $params): int
    {
        $q = Connection::getInstance()->prepare($statement);
        return $q->execute($params);
    }
}
