<?php
namespace Kernel\Orm;

class Query
{
    // Statements
    const SELECT_FROM = "SELECT :fields FROM :tables";
    const INSERT_INTO = "INSERT INTO :table(:fields) VALUES (:values)";
    const UPDATE_SET = "UPDATE :table SET :fields";

    // Clauses
    const WHERE = " WHERE :conditions";
    const ORDER_BY = " ORDER BY :fields";
    const LIMIT = " LIMIT :quantity";

    // Aggregation
    const AVG = "AVG(:field)";
    const SUM = "SUM(:field)";
    const COUNT = "COUNT(:field)";
    const MAX = "MAX(:field)";
    const MIN = "MIN(:field)";

    /**
     * Final statement
     * @var string
     */
    private $statement = "";

    /**
     * @return string
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param string $statement
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;
    }

    /**
     * @param string $clause
     * @throws \Exception
     */
    public function checkIfNotEmpty()
    {
        if (!empty($this->statement)) {
            throw new \Exception("There are already an statement");
        }
    }

    /**
     * @param string $clause
     * @throws \Exception
     */
    public function checkIfNotAlreadyExist($clause)
    {
        if (strpos($this->statement, $clause)) {
            throw new \Exception("The clause $clause already exist");
        }
    }

    /**
     * @param string $clause
     * @param string $prevClause
     * @throws \Exception
     */
    public function checkIfPreviousClauseIsSet($clause, $prevClause)
    {
        if (strpos($this->statement, $prevClause)) {
            throw new \Exception("The clause $clause need the previous clause $prevClause");
        }
    }

    /**
     * @param string $clause
     * @param string $nextClause
     * @throws \Exception
     */
    public function checkIfNextClauseIsSet($clause, $nextClause)
    {
        if (strpos($this->statement, $nextClause)) {
            throw new \Exception("The clause $clause need the next clause $nextClause");
        }
    }

    /**
     * Push content in the statement
     * @param int $clause
     * @param array $needle
     */
    private function pushInStatement($key, $value, $clause = null)
    {
        if (null != $clause) {
            $statement = $clause;
        } else {
            $statement = $this->statement;
        }

        $this->statement .= preg_replace("/$key/", $value, $statement);
    }

    /**
     * @param string $select
     * @return Query
     * @throws \Exception
     */
    public function select(...$select)
    {
        $this->checkIfNotAlreadyExist("SELECT");

        $this->pushInStatement(":fields", implode(", ", $select), self::SELECT_FROM);

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
     * @param string $from
     * @return Query
     * @throws \Exception
     */
    public function from(...$from)
    {
        $this->checkIfPreviousClauseIsSet("FROM", "SELECT");

        $this->checkIfNotAlreadyExist("FROM");

        $this->pushInStatement(":tables", implode(", ", $from));

        return $this;
    }

    /**
     * @param string $where
     * @return Query
     */
    public function where(...$where)
    {
        $this->checkIfPreviousClauseIsSet("WHERE", "FROM");

        $this->checkIfNotAlreadyExist("WHERE");

        $this->pushInStatement(":conditions", implode(" AND ", $where), self::WHERE);

        return $this;
    }

    /**
     * @param string $order
     * @return Query
     */
    public function orderBy(...$order)
    {
        $this->checkIfPreviousClauseIsSet("WHERE", "FROM");

        $this->checkIfNotAlreadyExist("WHERE");

        $this->pushInStatement(":conditions", implode(", ", $order), self::ORDER_BY);

        return $this;
    }

    /**
     * @param string|int $limit
     * @return Query
     */
    public function limit($limit)
    {
        $this->statement .= "LIMIT $limit ";

        return $this;
    }

    /**
     * @param string $table
     * @param array $fields
     * @return Query
     */
    public function insert($table, ...$fields)
    {
        $this->pushInStatement("INSERT", "$table(" . implode(', ', $fields) . ") ");

        return $this;
    }

    /**
     * @param array $values
     * @return Query
     */
    public function into(...$values)
    {
        $this->pushInStatement("INTO", "(" . implode(', ', $values) . ")");

        return $this;
    }

    /**
     * @param string $table
     * @return Query
     */
    public function update($table)
    {
        $this->statement = "UPDATE $table ";

        return $this;
    }

    /**
     * @param string $set
     * @return Query
     */
    public function set(...$set)
    {
        $this->statement .= "SET (" . implode(', ', $set) . ") ";

        return $this;
    }
}


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
