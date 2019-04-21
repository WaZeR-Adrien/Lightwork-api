<?php
namespace Kernel\Orm;
use Controllers\Controller;
use Controllers\Docs;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\DescriptionTag;
use Kernel\Tools\Collection\Collection;
use Kernel\Tools\Utils;
use Models\Entity;
use Models\User;

Trait Database
{
    /**
     * Get table which called.
     * @return string
     */
    public static function getTable()
    {
        $class = get_called_class();

        $doc = (new \ReflectionClass($class))->getDocComment();

        if (strpos($doc, "@table")) {
            $table = substr($doc, strpos($doc, "@table") + 6);
            $table = explode(" ", $table)[1];

            return trim($table);
        } else {
            return strtolower(explode('\\', $class)[1]);
        }
    }

    /**
     * Get fields by table name
     * @param null $table
     * @return array
     */
    public static function getColumns($table = null)
    {
        if (null == $table) {
            $table = self::getTable();
        }

        $stmt = 'SHOW COLUMNS FROM `' . $table . '`';

        try {
            $q = Connection::getPdo()->prepare($stmt);
            $q->execute();
            return $q->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get table
     * @return mixed
     */
    public static function getTables()
    {
        try {
            $q = Connection::getPdo()->prepare("SHOW TABLES");
            $q->execute();
            return $q->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get value of another table with the name of the 'class_id'
     * @param $name : class
     * @return object
     */
    public function __get($name)
    {
        if ($name == 'id') { return null; }

        $fullName = explode('\\', $name);
        $field = end($fullName) . '_id';
        $fullName = array_map(function ($v){ return ucfirst($v); }, $fullName);
        $class = ucfirst(implode('\\', $fullName));
        $class = 'Models\\' .$class;

        return new $class($this->$field);
    }

    /**
     * Get values with SELECT query and clause WHERE
     * @param $where
     * @param array $params
     * @param null $order
     * @param null $limit
     * @return Collection
     */
    public static function where($where, $params = [], $order = null, $limit = null)
    {
        $query = 'SELECT * FROM `' . self::getTable() . '` WHERE ' . $where . self::order($order) . self::limit($limit);
        return self::query($query, $params);
    }

    /**
     * Get first row with SELECT query and clause WHERE
     * @param $where
     * @param $params
     * @return self
     */
    public static function whereFirst($where, $params)
    {
        return self::where($where, $params)->getFirst();
    }

    /**
     * Get last row with SELECT query and clause WHERE
     * @param $where
     * @param $params
     * @return self
     */
    public static function whereLast($where, $params)
    {
        return self::where($where, $params)->getLast();
    }

    /**
     * Get values by params
     * @param $params
     * @param null $order
     * @param null $limit
     * @return Collection
     */
    public static function find($params, $order = null, $limit = null)
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
     * @param $params
     * @return self
     */
    public static function findFirst($params)
    {
        return self::find($params)->getFirst();
    }

    /**
     * Get last row by params
     * @param $params
     * @return self
     */
    public static function findLast($params)
    {
        return self::find($params)->getLast();
    }

    /**
     * Get first row by id
     * @param $id
     * @return self
     */
    public static function getById($id)
    {
        return self::findFirst(['id' => $id]);
    }

    /**
     * Get all data from table
     * @param null $order
     * @param null $limit
     * @return Collection
     */
    public static function getAll($order = null, $limit = null)
    {
        return self::query('SELECT * FROM '. self::getTable() . self::order($order) . self::limit($limit));
    }

    /**
     * Get the first row
     * @return self
     */
    public static function getFirst()
    {
        return self::getAll()->getFirst();
    }

    /**
     * Get the last row
     * @return self
     */
    public static function getLast()
    {
        return self::getAll()->getLast();
    }

    /**
     * Set string ORDER BY...
     * @param $order
     * @return string
     */
    private static function order($order)
    {
        return (null !== $order) ? (' ORDER BY ' . $order) : '';
    }

    /**
     * Set string LIMIT...
     * @param $limit
     * @return string
     */
    private static function limit($limit)
    {
        return (null !== $limit) ? (' LIMIT ' . $limit) : '';
    }

    /**
     * @param string $where
     * @param array $params
     * @return int
     */
    public static function count($where = null, $params = [])
    {
        if (null !== $where) { $where = ' WHERE '. $where; }
        else { $where = ''; }

        /*$query = new Query();
        $query->select("count(*)")
            ->from(self::getTable())
            ->where($where);
        return (int) self::query($query->getStatement(), $params)[0]->nb;*/

        return (int) self::query(
            'SELECT count(*) as nb FROM '. self::getTable() . $where,
            $params
        )->getFirst()->nb;
    }

    /**
     * Store the data in database
     * If the data has an ID, the function update the data
     * Else the function insert the data
     * @return int
     */
    public function store()
    {
        return (null != $this->getId()) ? $this->update() : $this->insert();
    }

    /**
     * Get the key and the value with the getter
     * @param $key
     * @return array|bool
     */
    private function setKeysAndValues(&$keys = [], &$values = [])
    {
        $reflect = new \ReflectionObject($this);

        foreach ($reflect->getProperties() as $property) {
            if (!$property->isStatic()) {

                $propertyName = $property->getName();
                $getter = "get" . Utils::toPascalCase($propertyName);

                if (is_object($this->$getter())) {
                    if (method_exists($this->$getter(), "getId")) {
                        $keys[] = '`' . $propertyName . '_id`';
                        $values[] = $this->$getter()->getId();
                    }
                } else {
                    $keys[] = '`' . $propertyName . '`';
                    $values[] = $this->$getter();
                }
            }
        }
    }

    /**
     * Insert new values
     * @return self
     */
    private function insert()
    {
        $this->setKeysAndValues($keys, $values);

        $keys = implode(',', $keys);

        $q = self::exec(
            'INSERT INTO '. self::getTable() .'(' . $keys . ') VALUES (?'. str_repeat(', ?', count($values) - 1) .')',
            $values
        );

        if ($q) {
            $this->setId(self::getLastId());

            return $this;
        }

        return false;
    }

    /**
     * Update row
     * Values and key with $this
     * @return int
     */
    private function update()
    {
        $this->setKeysAndValues($keys, $values);

        $values[] = $this->getId();

        $keys = implode(' = ?, ', $keys);

        return self::exec(
            'UPDATE '. self::getTable() .' SET '. $keys . ' = ?' . ' WHERE id = ?',
            $values
        );
    }

    /**
     * Delete row from database
     * @return mixed
     */
    public function delete() {
        $params = [];
        $values = [];
        if (empty($this->getId())) {
            foreach ($this as $k => $v) {

                $getter = "get" . Utils::toPascalCase($k);

                if (null != $this->$getter()) {
                    $params[] = $k;
                    $values[] = $this->$getter();
                }
            }
        } else {
            $params = ['id'];
            $values[] = $this->getId();
        }

        return $this->exec('DELETE FROM ' . self::getTable() . ' WHERE ' . implode(' = ? AND ', $params) . ' = ?', $values);
    }

    /**
     * @return int
     */
    public static function getLastId()
    {
        return Connection::getPdo()->lastInsertId();
    }

    /**
     * Convert all foreign key to object
     */
    private function foreignKeyToObject()
    {
        foreach ($this as $k => $v) {
            if (strpos($k, '_id')) {
                $attr = substr($k, 0, strlen($k) - 3);
                $attr = Utils::toPascalCase($attr);

                $annotations = Docs::getPhpDoc($this, "get$attr");

                if (!empty($annotations["return"])) {
                    $class = '\Models\\' . $annotations["return"]["type"];
                } else {
                    $class = '\Models\\' . $attr;
                }

                $class = new $class($v);

                $setter = "set$attr";
                $this->$setter($class);

                unset($this->$k);
            }
        }
    }

    /**
     * @param $statement string
     * @param $params array
     * @return Collection
     */
    public static function query($statement, $params = null)
    {
        $q = Connection::getPdo()->prepare($statement);
        $q->execute($params);
        $res = $q->fetchAll(\PDO::FETCH_CLASS, get_called_class());

        foreach ($res as $item) {
            $item->foreignKeyToObject();
        }

        return Collection::from($res);
    }

    /**
     * @param $statement string
     * @param $params array
     * @return int
     */
    public static function exec($statement, $params)
    {
        $q = Connection::getPdo()->prepare($statement);
        return $q->execute($params);
    }
}
