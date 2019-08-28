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
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;

trait Database
{
    /**
     * Get the model name with the namespace
     * @return string
     * @throws \ReflectionException
     */
    private function getModel()
    {
        $doc = Docs::getPhpDoc(get_called_class());
        return "Models\\" . $doc["model"];
    }

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
     * @param string|null $table
     * @return Collection
     */
    public static function getColumns(string $table = null)
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
     * Get table
     * @return Collection
     */
    public static function getTables()
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
    public static function where(string $where, array $params = [], string $order = null, string $limit = null)
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
    public static function whereFirst(string $where, array $params)
    {
        return self::where($where, $params)->getFirst();
    }

    /**
     * Get last row with SELECT query and clause WHERE
     * @param string $where
     * @param array $params
     * @return object
     */
    public static function whereLast(string $where, array $params)
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
    public static function find(array $params, string $order = null, string $limit = null)
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
    public static function findFirst(array $params)
    {
        return self::find($params)->getFirst();
    }

    /**
     * Get last row by params
     * @param array $params
     * @return object
     */
    public static function findLast(array $params)
    {
        return self::find($params)->getLast();
    }

    /**
     * Get first row by id
     * @param string|int $id
     * @return object
     */
    public static function getById($id)
    {
        return self::findFirst(['id' => $id]);
    }

    /**
     * Get all data from table
     * @param string|null $order
     * @param string|null $limit
     * @return Collection
     */
    public static function getAll(string $order = null, string $limit = null)
    {
        return self::query('SELECT * FROM '. self::getTable() . self::order($order) . self::limit($limit));
    }

    /**
     * Get the first row
     * @return object
     */
    public static function getFirst()
    {
        return self::getAll()->getFirst();
    }

    /**
     * Get the last row
     * @return object
     */
    public static function getLast()
    {
        return self::getAll()->getLast();
    }

    /**
     * Set string ORDER BY...
     * @param string $order
     * @return string
     */
    private static function order(?string $order)
    {
        return (null !== $order) ? (' ORDER BY ' . $order) : '';
    }

    /**
     * Set string LIMIT...
     * @param string $limit
     * @return string
     */
    private static function limit(?string $limit)
    {
        return (null !== $limit) ? (' LIMIT ' . $limit) : '';
    }

    /**
     * @param string|null $where
     * @param array $params
     * @return int
     */
    public static function count(string $where = null, array $params = [])
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
    public function store()
    {
        return (null != $this->getId()) ? $this->update() : $this->insert();
    }

    /**
     * Get the key and the value with the getter
     * @param array $keys
     * @param array $values
     * @return object
     */
    private function setKeysAndValues(array &$keys = [], array &$values = [])
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
     * @return object
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
     * @return int
     */
    public function delete()
    {
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
     * Get the last id inserted
     * @return int
     */
    public static function getLastId()
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
    public static function query(string $statement, array $params = null)
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
    public static function exec(string $statement, array $params)
    {
        $q = Connection::getInstance()->prepare($statement);
        return $q->execute($params);
    }
}
