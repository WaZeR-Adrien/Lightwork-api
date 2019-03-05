<?php
namespace Kernel\Orm;

class Query
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $params;

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @param array $select
     * @param string|null $count
     * @param string|null $avg
     * @param string|null $sum
     * @return Query
     */
    public function select($select = [], $count = null, $avg = null, $sum = null)
    {
        $others = ["count" => $count, "avg" => $avg, "sum" => $sum];
        foreach ($others as $key => $value) {
            if (null != $value) {$select[] = $this->$key($value);}
        }

        $this->content = "SELECT " . implode(', ', $select) . " ";

        return $this;
    }

    /**
     * @param string|null $field
     * @return string
     */
    private function count($field = null)
    {
        return (null != $field) ? "count($field)" : "";
    }

    /**
     * @param string|null $field
     * @return string
     */
    private function sum($field = null)
    {
        return (null != $field) ? "sum($field)" : "";
    }

    /**
     * @param string|null $field
     * @return string
     */
    private function avg($field = null)
    {
        return (null != $field) ? "avg($field)" : "";
    }

    /**
     * @param array $from
     * @return Query
     */
    public function from($from = [])
    {
        $this->content .= "FROM " . implode(', ', $from) . " ";

        return $this;
    }

    /**
     * @param array $where
     * @return Query
     */
    public function where($where = [])
    {
        $this->content .= "WHERE " . implode(' AND ', $where) . " ";

        return $this;
    }

    /**
     * @param string $order
     * @return Query
     */
    public function orderBy($order)
    {
        $this->content .= "ORDER BY $order ";

        return $this;
    }

    /**
     * @param string|int $limit
     * @return Query
     */
    public function limit($limit)
    {
        $this->content .= "LIMIT $limit ";

        return $this;
    }

    /**
     * @param string $table
     * @param array $fields
     * @return Query
     */
    public function insert($table, $fields = [])
    {
        $this->content = "INSERT $table(" . implode(', ', $fields) . ") ";

        return $this;
    }

    /**
     * @param array $values
     * @return Query
     */
    public function into($values = [])
    {
        $this->content .= "INTO (" . implode(', ', $values) . ")";

        return $this;
    }

    /**
     * @param string $table
     * @return Query
     */
    public function update($table)
    {
        $this->content = "UPDATE $table ";

        return $this;
    }

    /**
     * @param array $set
     * @return Query
     */
    public function set($set = [])
    {
        $this->content .= "SET (" . implode(', ', $set) . ") ";

        return $this;
    }
}

$query = new Query();

$query->select(["id"], "*")
    ->from(["user"])
    ->where(["age > 10", "age <= 18"])
    ->orderBy("id DESC")
    ->limit(10);

$query->insert("user", ["name", "age"])
    ->into(["?", "?"]);

var_dump($query->getContent());


/**
 * Dans Database.php :
 * Faire un getQuery() qui renvoie un new Query(); (pour ensuite le manipuler)
 * Pourquoi pas aussi le stocker en attribut $query et le getQuery() renvoie soit $query ou new Query()
 * en fonction de si il a déjà été instancié ? A voir [Pas sur finalement, car à chaque fois qu'on veut faire une manipe
 * sur la BDD il faudra créer une nouvelle query pour pas utiliser une précédente query déjà définie...
 *
 *
 * Autre solution à voir :
 *
 * On créer des tableaux $select / $from / $where / $insert / $into...
 * On fait des setters pour ajouter les contenus dans ces tableaux
 * Ensuite on utiliser run() qui génèrera la requête via les tableaux et les méthodes déjà faites (qui seront pour le coup,
 * en privée)
 * Le run() pourrai contenir un param $statement = null qui pourra être utilisé si on décide de créer notre propre requete
 */
