<?php


namespace wpQueryBuilder\base;


interface BuilderBase
{
    /**
     * @param $name
     * @return mixed
     */
    static public function table($name);


    /**
     * @param $closer
     * @return mixed
     */
    static public function transaction($closer);

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return mixed
     */
    public function where($column, $operator = null, $value = null);

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return mixed
     */
    public function join($table, $first, $operator = null, $second = null);

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return mixed
     */
    public function leftJoin($table, $first, $operator = null, $second = null);

    /**
     * @param $first
     * @param $operator
     * @param $second
     * @return mixed
     */
    public function on($first, $operator, $second);

    /**
     * @param $first
     * @param $operator
     * @param $second
     * @return mixed
     */
    public function orOn($first, $operator, $second);

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return mixed
     */
    public function onWhere($column, $operator = null, $value = null);

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return mixed
     */
    public function orWhere($column, $operator = null, $value = null);

    /**
     * @param $columns
     * @param $direction
     * @return mixed
     */
    public function orderBy($columns, $direction);


    /**
     * @param $number
     * @return mixed
     */
    public function limit($number);


    /**
     * @param $number
     * @return mixed
     */
    public function offset($number);


    /**
     * @param $number
     * @return mixed
     */
    public function groupBy($number);

    /**
     * @param $fields
     * @return mixed
     */
    public function select($fields);

    /**
     * @param $data
     * @return mixed
     */
    public function insert($data);

    /**
     * @param $data
     * @return mixed
     */
    public function update($data);


    /**
     * @return mixed
     */
    public function delete();

    /**
     * @return mixed
     */
    public function first();

    /**
     * @return mixed
     */
    public function get();

    /**
     * @return mixed
     */
    public function toSql();


}
