<?php

namespace wpQueryBuilder;

use wpQueryBuilder\base\BuilderBase;
use wpQueryBuilder\exception\ExceptionHandler;

class DB implements BuilderBase
{

    use ExceptionHandler;

    protected $table;
    protected $sql;
    protected $table_prefix;
    protected $connection;
    protected $whereValues = [];
    protected $whereClause = '';
    protected $closerCounter;
    protected $closerSession = false;
    protected $selectQuery = ' * ';
    protected $orderQuery = '';
    protected $groupQuery = '';
    protected $limitQuery = '';
    protected $offsetQuery = '';
    protected $join;
    protected $joinClosure;
    protected $joinOn = '';


    /**
     * DB constructor.
     */
    public function __construct()
    {
        global $table_prefix, $wpdb;
        $this->table_prefix = $table_prefix;
        $this->connection = $wpdb;
    }

    /**
     * @param $name
     * @return DB
     */
    public static function table($name)
    {
        $self = (new self());
        $self->table = $self->table_prefix . $name;
        $self->whereValues = [];
        return $self;
    }


    /**
     * @param $closer
     * @return DB|void
     */
    public static function transaction($closer)
    {
        $self = new self;
        try {
            $self->connection->query('START TRANSACTION');

            if ($closer instanceof \Closure) {
                call_user_func($closer, $self);
            }

            $self->connection->query('COMMIT');
            return $self;
        } catch (\Exception $e) {
            $self->connection->query('ROLLBACK');
            return $self->exceptionHandler();
        }
    }


    /**
     * @param $fields
     * @return DB
     */
    public function select($fields)
    {
        $this->selectQuery = " " . $fields . " ";
        return $this;
    }


    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return mixed|DB
     */
    public function join($table, $first, $operator = null, $second = null)
    {
        if ($first instanceof \Closure) {
            $this->joinClosure = true;
            $this->join .= ' INNER JOIN ' . $this->table_prefix . $table . ' ON ';
            call_user_func($first, $this);
            $this->join .= $this->joinOn;
            $this->joinClosure = false;
        } else {
            $this->join .= ' INNER JOIN ' . $this->table_prefix . $table . ' ON ' . $first . ' ' . $operator . ' ' . $second . ' ';
        }
        return $this;
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return mixed|DB
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        if ($first instanceof \Closure) {
            $this->joinClosure = true;
            $this->join .= ' LEFT JOIN ' . $this->table_prefix . $table . ' ON ';
            call_user_func($first, $this);
            $this->join .= $this->joinOn;
            $this->joinClosure = false;
        } else {
            $this->join .= ' LEFT JOIN ' . $this->table_prefix . $table . ' ON ' . $first . ' ' . $operator . ' ' . $second . ' ';
        }

        return $this;
    }

    /**
     * @param $first
     * @param $operator
     * @param $second
     * @return mixed
     */
    public function on($first, $operator, $second)
    {
        $this->joinOn .= ($this->joinOn ? ' AND ' : '') . $first . ' ' . $operator . ' ' . $second;
        return $this;
    }

    /**
     * @param $first
     * @param $operator
     * @param $second
     * @return mixed
     */
    public function orOn($first, $operator, $second)
    {
        $this->joinOn .= ($this->joinOn ? ' OR ' : '') . $first . ' ' . $operator . ' ' . $second;
        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return mixed
     */
    public function onWhere($column, $operator = null, $value = null)
    {
        if (!$value) {
            $value = $operator;
            $operator = ' = ';
        }

        $this->joinOn .= ($this->joinOn ? ' AND ' : '') . $column . ' ' . $operator . ' %s ';
        array_push($this->whereValues, (string)$value);
        return $this;
    }


    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return DB
     */
    public function where($column, $operator = null, $value = null)
    {

        $finalOperator = $value ? $operator : '=';
        $finalValue = $value ? $value : $operator;

        if ($column instanceof \Closure) {
            $this->closerSession = true;
            $this->closerCounter = 1;
            $this->whereClause .= ($this->whereClause ? ' AND (' : ' where (');
            call_user_func($column, $this);
            $this->whereClause .= ')';
            $this->closerSession = false;
            $this->closerCounter = 0;
        } else {
            $this->whereClause .= $this->closerSession && $this->closerCounter == 1 ? '' : ($this->whereClause ? ' AND ' : ' where ');
            $this->whereClause .= '`' . $column . '` ' . $finalOperator . ' %s';
            array_push($this->whereValues, (string)$finalValue);
            $this->closerCounter++;
        }
        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return DB
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        $finalOperator = $value ? $operator : '=';
        $finalValue = $value ? $value : $operator;

        if ($column instanceof \Closure) {
            $this->closerSession = true;
            $this->closerCounter = 1;
            $this->whereClause .= ($this->whereClause ? ' OR (' : ' where (');
            call_user_func($column, $this);
            $this->whereClause .= ')';
            $this->closerSession = false;
            $this->closerCounter = 0;
        } else {
            $this->whereClause .= $this->closerSession && $this->closerCounter == 1 ? '' : ($this->whereClause ? ' OR ' : ' where ');
            $this->whereClause .= '`' . $column . '` ' . $finalOperator . ' %s';
            array_push($this->whereValues, (string)$finalValue);
            $this->closerCounter++;
        }

        return $this;
    }


    /**
     * @param $query
     */
    public function whereRaw($query)
    {
        $this->whereClause .= $this->closerSession && $this->closerCounter == 1 ? ' ' : ($this->whereClause ? ' AND ' : ' where ');
        $this->whereClause .= $query;
		return $this;
    }

    /**
     * @param $query
     */
    public function orWhereRaw($query)
    {
        $this->whereClause .= $this->closerSession && $this->closerCounter == 1 ? ' ' : ($this->whereClause ? ' OR ' : ' where ');
        $this->whereClause .= $query;
		return $this;
    }


    /**
     * @param $number
     * @return $this
     */
    public function limit($number)
    {
        $this->limitQuery = " LIMIT $number ";
        return $this;
    }


    /**
     * @param $number
     * @return $this
     */
    public function offset($number)
    {
        $this->offsetQuery = " OFFSET $number ";
        return $this;
    }


    /**
     * @param $columns | columns with comma separator
     * @param $direction
     * @return DB
     */
    public function orderBy($columns, $direction)
    {
        $this->orderQuery = " ORDER BY " . $columns . " " . $direction;
        return $this;
    }

    /**
     * @param $columns | columns with comma separator
     * @param $direction
     * @return DB
     */
    public function groupBy($columns)
    {
        $this->orderQuery = " GROUP BY " . $columns . " ";
        return $this;
    }


    /**
     * @param $data
     * @return false|int
     */
    public function insert($data)
    {
        $this->buildInsertQuery($data);
        $sql = $this->getPreparedSql();

        try {
            $insert = $this->connection->query($sql);
            if ($this->connection->last_error) {
                throw new \Exception("Error");
            }
            return $this->connection->insert_id;
        } catch (\Exception $e) {
            $this->exceptionHandler();
        }

    }


    /**
     * @param $data
     */
    private function buildInsertQuery($data)
    {
        $fields = '';
        $values = '';
        $this->whereValues = [];
        foreach ($data as $key => $value) {
            $fields .= '`' . $key . '`,';
            $values .= '%s,';
            array_push($this->whereValues, $value);
        }

        $this->sql = "INSERT INTO `" . $this->table . "` (" . substr($fields, 0, -1) . ") VALUES(" . substr($values, 0, -1) . ")";
    }

    /**
     * @param $query
     * @param $values
     * @return string|void
     */
    protected function getPreparedSql()
    {
        return $this->connection->prepare($this->sql, $this->whereValues);
    }


    /**
     * @param $data
     * @param $where
     * @return false|int
     */
    public function update($data)
    {
        $this->buildUpdateQuery($data);

        $sql = $this->getPreparedSql();

        try {
            $update = $this->connection->query($sql);
            if ($this->connection->last_error) {
                throw new \Exception("Error");
            }
            return $update;
        } catch (\Exception $e) {
            $this->exceptionHandler();
        }
    }


    /**
     * @param $data
     */
    private function buildUpdateQuery($data)
    {
        $fields = '';
        $prevWhereValues = $this->whereValues;
        $this->whereValues = [];
        foreach ($data as $key => $value) {
            $fields .= '`' . $key . '` = %s,';
            array_push($this->whereValues, $value);
        }

        $this->whereValues = array_merge($this->whereValues, $prevWhereValues);

        $this->sql = "UPDATE `" . $this->table . "` SET " . substr($fields, 0, -1) . $this->whereClause;
    }


    /**
     * @param $where
     * @return false|int
     */
    public function delete()
    {

        $this->sql = "DELETE FROM `" . $this->table . '` ' . $this->whereClause;
        $sql = $this->getPreparedSql();
        try {
            $delete = $this->connection->query($sql);
            if ($delete === false) {
                throw new \Exception("Error");
            }
            return $delete;
        } catch (\Exception $e) {
            $this->exceptionHandler();
        }

    }

    /**
     * @return array|object|void|null
     */
    public function first()
    {
        $this->getSelectQuery();
        try {
            $first = $this->connection->get_row($this->getPreparedSql(), OBJECT);
            if ($this->connection->last_error) {
                throw new \Exception("Error");
            }
            return $first;
        } catch (\Exception $e) {
            $this->exceptionHandler();
        }
    }

    /**
     * @return string
     */
    private function getSelectQuery()
    {
        $this->sql = "SELECT " . $this->selectQuery . " FROM $this->table " . $this->join . $this->whereClause . $this->groupQuery . $this->orderQuery . $this->limitQuery . $this->offsetQuery;
        return $this->sql;
    }

    /**
     * @return array|object|null
     */
    public function get()
    {
        $this->getSelectQuery();
        return $this->connection->get_results($this->getPreparedSql());
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return $this->sql ? $this->sql : $this->getSelectQuery();
    }
}
