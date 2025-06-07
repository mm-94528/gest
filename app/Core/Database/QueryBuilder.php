<?php

// app/Core/Database/QueryBuilder.php
class QueryBuilder {
    private $database;
    private $table;
    private $select = ['*'];
    private $joins = [];
    private $wheres = [];
    private $orderBy = [];
    private $groupBy = [];
    private $having = [];
    private $limit;
    private $offset;
    private $params = [];

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function select($columns = ['*']) {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = null, $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = 'param_' . count($this->params);
        $this->wheres[] = "{$column} {$operator} :{$placeholder}";
        $this->params[$placeholder] = $value;

        return $this;
    }

    public function whereIn($column, $values) {
        $placeholders = [];
        foreach ($values as $i => $value) {
            $placeholder = 'param_' . count($this->params);
            $placeholders[] = ":{$placeholder}";
            $this->params[$placeholder] = $value;
        }
        
        $this->wheres[] = "{$column} IN (" . implode(',', $placeholders) . ")";
        return $this;
    }

    public function join($table, $first, $operator, $second) {
        $this->joins[] = "INNER JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function leftJoin($table, $first, $operator, $second) {
        $this->joins[] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    public function groupBy($columns) {
        $this->groupBy = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function limit($limit, $offset = null) {
        $this->limit = $limit;
        if ($offset !== null) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function get() {
        $sql = $this->buildSelectQuery();
        return $this->database->fetchAll($sql, $this->params);
    }

    public function first() {
        $this->limit(1);
        $sql = $this->buildSelectQuery();
        return $this->database->fetchOne($sql, $this->params);
    }

    public function count() {
        $originalSelect = $this->select;
        $this->select = ['COUNT(*) as count'];
        
        $sql = $this->buildSelectQuery();
        $result = $this->database->fetchOne($sql, $this->params);
        
        $this->select = $originalSelect;
        return $result['count'] ?? 0;
    }

    private function buildSelectQuery() {
        $sql = "SELECT " . implode(', ', $this->select);
        $sql .= " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $sql .= " HAVING " . implode(' AND ', $this->having);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    public function insert($data) {
        return $this->database->insert($this->table, $data);
    }

    public function update($data) {
        if (empty($this->wheres)) {
            throw new Exception("Update queries must have a WHERE clause");
        }

        $setClause = [];
        $updateParams = [];
        
        foreach ($data as $column => $value) {
            $placeholder = 'update_' . $column;
            $setClause[] = "{$column} = :{$placeholder}";
            $updateParams[$placeholder] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause);
        $sql .= " WHERE " . implode(' AND ', $this->wheres);

        return $this->database->query($sql, array_merge($updateParams, $this->params));
    }

    public function delete() {
        if (empty($this->wheres)) {
            throw new Exception("Delete queries must have a WHERE clause");
        }

        $sql = "DELETE FROM {$this->table}";
        $sql .= " WHERE " . implode(' AND ', $this->wheres);

        return $this->database->query($sql, $this->params);
    }
}