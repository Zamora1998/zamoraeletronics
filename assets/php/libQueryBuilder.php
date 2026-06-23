<?php

/**
 * Query builder
 * @method self reset()
 * @method self table(string $table)
 * @method self select(string|array|IfStatement|CaseStatement $columns)
 * @method self distinct()
 * @method IfStatement if(string $condition, mixed $trueValue, mixed $falseValue)
 * @method CaseStatement case(?string $column = null)
 * @method self join(string $table, string $first, string $operator, string $second, string $type = 'INNER')
 * @method self leftJoin(string $table, string $first, string $operator, string $second)
 * @method self crossJoin(string $table)
 * @method self where(string $column, string $operator, mixed $value, string $boolean = 'AND')
 * @method self whereExists(callable $callback, string $boolean = 'AND')
 * @method self whereNotExists(callable $callback, string $boolean = 'AND')
 * @method self whereColumn(string $first, string $operator, string $second, string $boolean = 'AND')
 * @method self whereExpression(string $column, string $operator, string $rawExpression, string $boolean = 'AND')
 * @method self whereRaw(string $sql, string $boolean = 'AND')
 * @method self orderBy(string $column, string $direction = 'ASC')
 * @method self groupBy(string|array $columns)
 * @method self having(string $condition)
 * @method self limit(int $limit)
 * @method self offset(int $offset)
 * @method self with(string $name, callable|QueryBuilder $query)
 * @method self withRecursive(string $name, callable|QueryBuilder $query)
 * @method QueryBuilder subQuery(callable $callback)
 * @method self whereInSubQuery(string $column, callable|QueryBuilder $query)
 * @method self whereNotInSubQuery(string $column, callable|QueryBuilder $query)
 * @method self joinSubQuery(callable|QueryBuilder $query, string $alias, string $first, string $operator, string $second, string $type = 'INNER')
 * @method self leftJoinSubQuery(callable|QueryBuilder $query, string $alias, string $first, string $operator, string $second)
 * @method self union(callable|QueryBuilder $query, bool $all = false)
 * @method self unionAll(callable|QueryBuilder $query)
 * @method string toSql()
 * @method array build()
 * @method array insert(array $data)
 * @method array update(array $data)
 * @method array delete()
 * 
 * IfStatement
 * @method __construct(string $condition, mixed $trueValue, mixed $falseValue)
 * @method string toSql()
 * @method array getParams()
 * @method string getTypes()
 * 
 * CaseStatement
 * @method __construct(?string $column = null)
 * @method self when(string $condition, mixed $thenValue)
 * @method self else(mixed $value)
 * @method string toSql()
 * @method array getParams()
 * @method string getTypes()
 */
class QueryBuilder {
    #region global
    private string $table = '';
    private array $select = ['*'];
    private array $joins = [];
    private array $where = [];
    private array $orderBy = [];
    private array $groupBy = [];
    private ?string $having = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $selectParams = [];
    private string $selectTypes = '';
    private array $joinParams = [];
    private string $joinTypes = '';
    private array $whereParams = [];
    private string $whereTypes = '';
    private array $cteParams = [];
    private string $cteTypes = '';
    private array $unionParams = [];
    private string $unionTypes = '';
    private bool $distinct = false;
    private array $ctes = [];
    private array $unions = [];

    public function __construct() {
    }
    #endregion
    #region reset    
    /**
     * reset
     *
     * @return self
     */
    public function reset(): self {
        $this->table = '';
        $this->select = ['*'];
        $this->joins = [];
        $this->where = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = null;
        $this->limit = null;
        $this->offset = null;
        $this->selectParams = [];
        $this->selectTypes = '';
        $this->joinParams = [];
        $this->joinTypes = '';
        $this->whereParams = [];
        $this->whereTypes = '';
        $this->cteParams = [];
        $this->cteTypes = '';
        $this->unionParams = [];
        $this->unionTypes = '';
        $this->distinct = false;
        $this->ctes = [];
        $this->unions = [];
        return $this;
    }
    #endregion
    #region select    
    /**
     * table
     *
     * @param  mixed $table
     * @return self
     */
    public function table(string $table): self {
        $this->table = $table;
        return $this;
    }

    /**
     * select
     *
     * @param  mixed $columns
     * @return self
     */
    public function select(string|array|IfStatement|CaseStatement $columns): self {
        $this->selectParams = [];
        $this->selectTypes = '';
        if ($columns instanceof IfStatement || $columns instanceof CaseStatement) {
            $this->select = [$columns->toSql()];
            $this->selectParams = array_merge($this->selectParams, $columns->getParams());
            $this->selectTypes .= $columns->getTypes();
        } elseif (is_array($columns)) {
            $this->select = [];
            foreach ($columns as $key => $column) {
                if ($column instanceof IfStatement || $column instanceof CaseStatement) {
                    $alias = is_string($key) ? " AS {$key}" : '';
                    $this->select[] = $column->toSql() . $alias;
                    $this->selectParams = array_merge($this->selectParams, $column->getParams());
                    $this->selectTypes .= $column->getTypes();
                } else {
                    // Process string expressions for parameterization
                    $processed = $this->processExpression($column, $this->selectParams, $this->selectTypes);
                    $alias = is_string($key) ? " AS {$key}" : '';
                    $this->select[] = $processed . $alias;
                }
            }
        } else {
            // Process single string expression
            $this->select = [$this->processExpression($columns, $this->selectParams, $this->selectTypes)];
        }
        return $this;
    }

    /**
     * processExpression - shared helper that parameterizes embedded string/number
     * literals in a SQL expression, appending to the provided params/types refs.
     *
     * @param  string $expression
     * @param  array  &$params
     * @param  string &$types
     * @return string
     */
    private function processExpression(string $expression, array &$params, string &$types): string {
        // Fast path: simple column or table.column
        if (preg_match('/^[a-zA-Z0-9_`.]+$/', $expression)) {
            return $expression;
        }

        // Match:
        // - quoted strings (single or double, not backticks)
        // - numeric literals (int or float)
        $pattern = '/
        (["\'])                               # 1: quote
        (?:(?=(\\\\?))\2.)*?                   # quoted content
        \1
        |
        (?<![a-zA-Z0-9_.])                     # left boundary
        (\d+\.?\d*)                            # 3: number
        (?![a-zA-Z0-9_.])                      # right boundary
    /x';

        preg_match_all($pattern, $expression, $matches, PREG_OFFSET_CAPTURE);

        $finds = [];

        foreach ($matches[0] as $index => $match) {
            $text = $match[0];
            $position = $match[1];

            // String literal
            if ($matches[1][$index][0] !== '') {
                $params[] = trim($text, '\'"');
                $types .= 's';

                $finds[] = [
                    'position' => $position,
                    'length'   => strlen($text),
                ];
            }
            // Numeric literal
            elseif ($matches[3][$index][0] !== '') {
                $isFloat = strpos($text, '.') !== false;

                $params[] = $isFloat ? (float)$text : (int)$text;
                $types    .= $isFloat ? 'd' : 'i';

                $finds[] = [
                    'position' => $position,
                    'length'   => strlen($text),
                ];
            }
        }

        // Replace from right to left to avoid offset shifts
        usort($finds, fn($a, $b) => $b['position'] <=> $a['position']);

        foreach ($finds as $find) {
            $expression = substr_replace(
                $expression,
                '?',
                $find['position'],
                $find['length']
            );
        }

        return $expression;


        // Replace quoted strings with placeholders
        $processed = preg_replace_callback(
            '/(["\'`])(?:(?=(\\\\?))\2.)*?\1/',
            function ($matches) {
                // Check if it's a backtick (identifier, not a string literal)
                if ($matches[1] === '`') {
                    return $matches[0]; // Keep backticks as-is
                }
                $value = trim($matches[0], $matches[1]);
                $this->selectParams[] = $value;
                $this->selectTypes .= 's';
                return '?';
            },
            $expression
        );

        // Replace standalone numbers with placeholders
        // Match numbers not preceded/followed by alphanumeric or dot (to avoid matching column names)
        $processed = preg_replace_callback(
            '/(?<![a-zA-Z0-9_.])\b(\d+\.?\d*)\b(?![a-zA-Z0-9_.])/',
            function ($matches) {
                $value = $matches[1];
                // Check if it's a float or int
                if (strpos($value, '.') !== false) {
                    $this->selectParams[] = (float)$value;
                    $this->selectTypes .= 'd';
                } else {
                    $this->selectParams[] = (int)$value;
                    $this->selectTypes .= 'i';
                }
                return '?';
            },
            $processed
        );

        return $processed;
    }

    /**
     * distinct
     *
     * @return self
     */
    public function distinct(): self {
        $this->distinct = true;
        return $this;
    }

    /**
     * Create an IF statement
     *
     * @param string $condition The condition to evaluate
     * @param mixed $trueValue Value if condition is true
     * @param mixed $falseValue Value if condition is false
     * @return IfStatement
     */
    public function if(string $condition, mixed $trueValue, mixed $falseValue): IfStatement {
        return new IfStatement($condition, $trueValue, $falseValue);
    }

    /**
     * Create a CASE statement
     *
     * @param string|null $column Optional column for simple CASE (CASE column WHEN...)
     * @return CaseStatement
     */
    public function case(?string $column = null): CaseStatement {
        return new CaseStatement($column);
    }

    #endregion
    #region join

    /**
     * join
     *
     * @param  string $table
     * @param  string $first
     * @param  string $operator
     * @param  string $second
     * @param  string $type
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'conditions' => [
                ['first' => $first, 'operator' => $operator, 'second' => $second, 'boolean' => 'AND']
            ]
        ];
        return $this;
    }

    /**
     * joinOn
     *
     * @param  string $first
     * @param  string $operator
     * @param  string $second
     * @param  string $boolean
     * @return self
     */
    public function joinOn(string $first, string $operator, string $second, string $boolean = 'AND'): self {
        $lastJoin = array_key_last($this->joins);
        if ($lastJoin !== null) {
            $this->joins[$lastJoin]['conditions'][] = [
                'first' => $first,
                'operator' => $operator,
                'second' => $second,
                'boolean' => $boolean
            ];
        }
        return $this;
    }

    /**
     * leftJoin
     *
     * @param  mixed $table
     * @param  mixed $first
     * @param  mixed $operator
     * @param  mixed $second
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * rightJoin
     *
     * @param  mixed $table
     * @param  mixed $first
     * @param  mixed $operator
     * @param  mixed $second
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * crossJoin
     *
     * @param  string $table
     * @return self
     */
    public function crossJoin(string $table): self {
        $this->joins[] = [
            'type'  => 'CROSS',
            'table' => $table,
        ];
        return $this;
    }

    /**
     * buildJoinConditions
     *
     * @param  mixed $conditions
     * @return string
     */
    private function buildJoinConditions(array $conditions): string {
        $sql = '';
        foreach ($conditions as $i => $condition) {
            if ($i > 0) {
                $sql .= " {$condition['boolean']} ";
            }
            $sql .= "{$condition['first']} {$condition['operator']} {$condition['second']}";
        }
        return $sql;
    }

    #endregion
    #region where

    /**
     * where
     *
     * @param  mixed $column
     * @param  mixed $operator
     * @param  mixed $value
     * @param  mixed $boolean
     * @return self
     */
    public function where(string|callable $column, ?string $operator = null, mixed $value = null, string $boolean = 'AND'): self {
        // Handle nested WHERE groups — only treat as callable if it's a Closure/object,
        // never a plain string (which could match a PHP built-in like 'file', 'date', etc.)
        if ($column instanceof \Closure || (is_object($column) && is_callable($column))) {
            $subQuery = new static();
            $column($subQuery);

            $this->where[] = [
                'type' => 'NESTED',
                'query' => $subQuery,
                'boolean' => $operator ?? 'AND'
            ];

            $this->whereParams = array_merge($this->whereParams, $subQuery->whereParams);
            $this->whereTypes .= $subQuery->whereTypes;

            return $this;
        }

        // If value is an IfStatement or CaseStatement
        if ($value instanceof IfStatement || $value instanceof CaseStatement) {
            $this->where[] = [
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
                'boolean' => $boolean,
                'type' => 'CONDITIONAL_STATEMENT'
            ];
            $this->whereParams = array_merge($this->whereParams, $value->getParams());
            $this->whereTypes .= $value->getTypes();
        } else {
            $this->where[] = [
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
                'boolean' => $boolean
            ];

            // Don't add params for IS NULL or IS NOT NULL
            if (!in_array($operator, ['IS NULL', 'IS NOT NULL'])) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $this->addParam($val);
                    }
                } else {
                    $this->addParam($value);
                }
            }
        }
        return $this;
    }

    /**
     * whereExists
     *
     * @param  mixed $callback
     * @param  mixed $boolean
     * @return self
     */
    public function whereExists(callable $callback, string $boolean = 'AND'): self {
        $subQuery = new static();
        $callback($subQuery);

        $this->where[] = [
            'type' => 'EXISTS',
            'subquery' => $subQuery,
            'boolean' => $boolean
        ];

        $this->whereParams = array_merge($this->whereParams, $subQuery->getWhereParams());
        $this->whereTypes .= $subQuery->getWhereTypes();

        return $this;
    }

    /**
     * whereNotExists
     *
     * @param  mixed $callback
     * @param  mixed $boolean
     * @return self
     */
    public function whereNotExists(callable $callback, string $boolean = 'AND'): self {
        $subQuery = new static();
        $callback($subQuery);

        $this->where[] = [
            'type' => 'NOT EXISTS',
            'subquery' => $subQuery,
            'boolean' => $boolean
        ];

        $this->whereParams = array_merge($this->whereParams, $subQuery->getWhereParams());
        $this->whereTypes .= $subQuery->getWhereTypes();

        return $this;
    }

    /**
     * whereColumn
     *
     * @param  mixed $first
     * @param  mixed $operator
     * @param  mixed $second
     * @param  mixed $boolean
     * @return self
     */
    public function whereColumn(string $first, string $operator, string $second, string $boolean = 'AND'): self {
        $this->where[] = [
            'type' => 'COLUMN',
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'boolean' => $boolean
        ];
        return $this;
    }

    /**
     * whereExpression - WHERE with a raw SQL expression (not parameterized)
     * e.g. ->whereExpression('created_at', '>', 'NOW() - INTERVAL 7 DAY')
     *
     * @param  string $column
     * @param  string $operator
     * @param  string $rawExpression
     * @param  string $boolean
     * @return self
     */
    public function whereExpression(string $column, string $operator, string $rawExpression, string $boolean = 'AND'): self {
        $this->where[] = [
            'type'       => 'RAW',
            'column'     => $column,
            'operator'   => $operator,
            'expression' => $rawExpression,
            'boolean'    => $boolean
        ];
        return $this;
    }

    /**
     * whereRaw - WHERE with a pure SQL fragment; embedded string/number literals
     * are auto-parameterized (same logic as SELECT expressions).
     * e.g. ->whereRaw("FIND_IN_SET('foo', col) > 0 OR col IS NULL")
     *
     * @param  string $sql
     * @param  string $boolean
     * @return self
     */
    public function whereRaw(string $sql, string $boolean = 'AND'): self {
        $params = [];
        $types  = '';
        $processed = $this->processExpression($sql, $params, $types);

        $this->where[] = [
            'type'    => 'RAW_EXPRESSION',
            'sql'     => $processed,
            'boolean' => $boolean
        ];

        $this->whereParams = array_merge($this->whereParams, $params);
        $this->whereTypes .= $types;

        return $this;
    }

    private function buildWhereClause(): string {
        $conditions = [];
        foreach ($this->where as $i => $condition) {
            $clause = '';
            if ($i > 0) {
                $clause .= " {$condition['boolean']} ";
            }

            if (isset($condition['type'])) {
                if ($condition['type'] === 'NESTED') {
                    $nestedSql = $condition['query']->buildWhereClause();
                    $clause .= "($nestedSql)";
                } elseif ($condition['type'] === 'RAW') {
                    $clause .= "{$condition['column']} {$condition['operator']} {$condition['expression']}";
                } elseif ($condition['type'] === 'RAW_EXPRESSION') {
                    $clause .= "{$condition['sql']}";
                } elseif (in_array($condition['type'], ['EXISTS', 'NOT EXISTS'])) {
                    $subSql = $condition['subquery']->toSql();
                    $clause .= "{$condition['type']} ($subSql)";
                } elseif ($condition['type'] === 'COLUMN') {
                    $clause .= "{$condition['first']} {$condition['operator']} {$condition['second']}";
                } elseif ($condition['type'] === 'IN_SUBQUERY') {
                    $subSql = $condition['subquery']->toSql();
                    $clause .= "{$condition['column']} IN ($subSql)";
                } elseif ($condition['type'] === 'NOT_IN_SUBQUERY') {
                    $subSql = $condition['subquery']->toSql();
                    $clause .= "{$condition['column']} NOT IN ($subSql)";
                } elseif ($condition['type'] === 'CONDITIONAL_STATEMENT') {
                    $clause .= "{$condition['column']} {$condition['operator']} {$condition['value']->toSql()}";
                }
            } elseif ($condition['operator'] === 'IN') {
                $placeholders = implode(', ', array_fill(0, count($condition['value']), '?'));
                $clause .= "{$condition['column']} IN ($placeholders)";
            } elseif (in_array($condition['operator'], ['BETWEEN', 'NOT BETWEEN'])) {
                $clause .= "{$condition['column']} {$condition['operator']} ? AND ?";
            } elseif (in_array($condition['operator'], ['IS NULL', 'IS NOT NULL'])) {
                $clause .= "{$condition['column']} {$condition['operator']}";
            } elseif (in_array($condition['operator'], ['LIKE', 'NOT LIKE'])) {
                $clause .= "{$condition['column']} {$condition['operator']} ?";
            } else {
                $clause .= "{$condition['column']} {$condition['operator']} ?";
            }

            $conditions[] = $clause;
        }
        return implode('', $conditions);
    }
    #endregion
    #region order    
    /**
     * orderBy
     *
     * @param  mixed $column
     * @param  mixed $direction
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBy[] = "$column $direction";
        return $this;
    }
    #endregion
    #region group    
    /**
     * groupBy
     *
     * @param  mixed $columns
     * @return self
     */
    public function groupBy(string|array $columns): self {
        $this->groupBy = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    /**
     * having
     *
     * @param  mixed $condition
     * @return self
     */
    public function having(string $condition): self {
        $this->having = $condition;
        return $this;
    }
    #endregion
    #region pagination    
    /**
     * limit
     *
     * @param  mixed $limit
     * @return self
     */
    public function limit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    /**
     * offset
     *
     * @param  mixed $offset
     * @return self
     */
    public function offset(int $offset): self {
        $this->offset = $offset;
        return $this;
    }

    /**
     * addParam
     *
     * @param  mixed $value
     * @return void
     */
    private function addParam($value): void {
        $this->whereParams[] = $value;
        $this->whereTypes .= $this->getParamType($value);
    }

    /**
     * getParamType
     *
     * @param  mixed $value
     * @return string
     */
    private function getParamType($value): string {
        if (is_int($value)) return 'i';
        if (is_float($value)) return 'd';
        return 's';
    }
    #endregion
    #region cte    
    /**
     * with
     *
     * @param  mixed $name
     * @param  mixed $query
     * @return self
     */
    public function with(string $name, callable|QueryBuilder $query): self {
        if (is_callable($query)) {
            $subQuery = new static();
            $query($subQuery);
            $this->ctes[$name] = $subQuery;
        } else {
            $this->ctes[$name] = $query;
        }

        $cteQuery = $this->ctes[$name];
        $this->cteParams = array_merge($this->cteParams, $cteQuery->getAllParams());
        $this->cteTypes .= $cteQuery->getAllTypes();

        return $this;
    }

    /**
     * withRecursive
     *
     * @param  mixed $name
     * @param  mixed $query
     * @return self
     */
    public function withRecursive(string $name, callable|QueryBuilder $query): self {
        $this->with($name, $query);
        if (count($this->ctes) === 1) {
            $this->ctes = ['__recursive__' => true] + $this->ctes;
        }
        return $this;
    }

    /**
     * buildCteClause
     *
     * @return string
     */
    private function buildCteClause(): string {
        if (empty($this->ctes)) {
            return '';
        }

        $isRecursive = isset($this->ctes['__recursive__']);
        $cteStrings = [];

        foreach ($this->ctes as $name => $query) {
            if ($name === '__recursive__') continue;
            $cteStrings[] = "$name AS (" . $query->toSql() . ")";
        }

        $prefix = $isRecursive ? 'WITH RECURSIVE ' : 'WITH ';
        return $prefix . implode(', ', $cteStrings) . ' ';
    }
    #endregion
    #region subquery    
    /**
     * subQuery
     *
     * @param  mixed $callback
     * @return QueryBuilder
     */
    public function subQuery(callable $callback): QueryBuilder {
        $subQuery = new static();
        $callback($subQuery);
        return $subQuery;
    }

    /**
     * whereInSubQuery
     *
     * @param  mixed $column
     * @param  mixed $query
     * @return self
     */
    public function whereInSubQuery(string $column, callable|QueryBuilder $query): self {
        if (is_callable($query)) {
            $subQuery = new static();
            $query($subQuery);
        } else {
            $subQuery = $query;
        }

        $this->where[] = [
            'type' => 'IN_SUBQUERY',
            'column' => $column,
            'subquery' => $subQuery,
            'boolean' => 'AND'
        ];

        $this->whereParams = array_merge($this->whereParams, $subQuery->getAllParams());
        $this->whereTypes .= $subQuery->getAllTypes();

        return $this;
    }

    /**
     * whereNotInSubQuery
     *
     * @param  mixed $column
     * @param  mixed $query
     * @return self
     */
    public function whereNotInSubQuery(string $column, callable|QueryBuilder $query): self {
        if (is_callable($query)) {
            $subQuery = new static();
            $query($subQuery);
        } else {
            $subQuery = $query;
        }

        $this->where[] = [
            'type' => 'NOT_IN_SUBQUERY',
            'column' => $column,
            'subquery' => $subQuery,
            'boolean' => 'AND'
        ];

        $this->whereParams = array_merge($this->whereParams, $subQuery->getAllParams());
        $this->whereTypes .= $subQuery->getAllTypes();

        return $this;
    }

    /**
     * Set a subquery as the FROM clause
     *
     * @param callable|QueryBuilder $query The subquery
     * @param string $alias Alias for the subquery
     * @return self
     */
    public function fromSubQuery(callable|QueryBuilder $query, string $alias): self {
        if (is_callable($query)) {
            $subQuery = new static();
            $query($subQuery);
        } else {
            $subQuery = $query;
        }

        $this->table = "({$subQuery->toSql()}) AS {$alias}";

        // FROM subquery params go into joinParams so they appear after SELECT params
        // but before WHERE params (they render in the FROM/JOIN section of SQL)
        $this->joinParams = array_merge($subQuery->getAllParams(), $this->joinParams);
        $this->joinTypes = $subQuery->getAllTypes() . $this->joinTypes;

        return $this;
    }

    /**
     * joinSubQuery
     *
     * @param  mixed $query
     * @param  mixed $alias
     * @param  mixed $first
     * @param  mixed $operator
     * @param  mixed $second
     * @param  mixed $type
     * @return self
     */
    public function joinSubQuery(callable|QueryBuilder $query, string $alias, string $first, string $operator, string $second, string $type = 'INNER'): self {
        if (is_callable($query)) {
            $subQuery = new static();
            $query($subQuery);
        } else {
            $subQuery = $query;
        }

        $this->joins[] = [
            'type' => $type,
            'subquery' => $subQuery,
            'alias' => $alias,
            'conditions' => [
                ['first' => $first, 'operator' => $operator, 'second' => $second, 'boolean' => 'AND']
            ]
        ];

        $this->joinParams = array_merge($this->joinParams, $subQuery->getAllParams());
        $this->joinTypes .= $subQuery->getAllTypes();

        return $this;
    }

    /**
     * leftJoinSubQuery
     *
     * @param  mixed $query
     * @param  mixed $alias
     * @param  mixed $first
     * @param  mixed $operator
     * @param  mixed $second
     * @return self
     */
    public function leftJoinSubQuery(callable|QueryBuilder $query, string $alias, string $first, string $operator, string $second): self {
        return $this->joinSubQuery($query, $alias, $first, $operator, $second, 'LEFT');
    }

    #endregion
    #region union    
    /**
     * union
     *
     * @param  mixed $query
     * @param  mixed $all
     * @return self
     */
    public function union(callable|QueryBuilder $query, bool $all = false): self {
        if (is_callable($query)) {
            $subQuery = new static();
            $query($subQuery);
        } else {
            $subQuery = $query;
        }

        $this->unions[] = [
            'query' => $subQuery,
            'all' => $all
        ];

        // Merge union params into union bucket
        $this->unionParams = array_merge($this->unionParams, $subQuery->getAllParams());
        $this->unionTypes .= $subQuery->getAllTypes();

        return $this;
    }

    /**
     * Adds a UNION ALL clause
     */
    /**
     * unionAll
     *
     * @param  mixed $query
     * @return self
     */
    public function unionAll(callable|QueryBuilder $query): self {
        return $this->union($query, true);
    }

    /**
     * Build UNION clauses
     */
    /**
     * buildUnionClause
     *
     * @return string
     */
    private function buildUnionClause(): string {
        if (empty($this->unions)) {
            return '';
        }

        $sql = '';
        foreach ($this->unions as $union) {
            $unionType = $union['all'] ? 'UNION ALL' : 'UNION';
            $sql .= " $unionType " . $union['query']->toSql();
        }

        return $sql;
    }
    #endregion
    #region build    
    /**
     * toSql
     *
     * @return string
     */
    public function toSql(): string {
        $sql = $this->buildCteClause();

        $sql .= "SELECT ";

        if ($this->distinct) {
            $sql .= "DISTINCT ";
        }

        $sql .= implode(', ', $this->select);

        if ($this->table !== '') {
            $sql .= " FROM {$this->table}";
        }

        foreach ($this->joins as $join) {
            if ($join['type'] === 'CROSS') {
                $sql .= " CROSS JOIN {$join['table']}";
            } elseif (isset($join['subquery'])) {
                $subSql = $join['subquery']->toSql();
                $sql .= " {$join['type']} JOIN ($subSql) AS {$join['alias']} ON " . $this->buildJoinConditions($join['conditions']);
            } else {
                $sql .= " {$join['type']} JOIN {$join['table']} ON " . $this->buildJoinConditions($join['conditions']);
            }
        }

        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if ($this->having) {
            $sql .= " HAVING {$this->having}";
        }

        // Add UNIONs before ORDER BY, LIMIT, OFFSET
        $sql .= $this->buildUnionClause();

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * build
     *
     * @return array
     */
    public function build(): array {
        return [
            'sql'    => $this->toSql(),
            'types'  => $this->getAllTypes(),
            'params' => $this->getAllParams(),
        ];
    }

    /**
     * getAllParams - returns all params in the same order toSql() emits placeholders:
     * CTE → SELECT → JOIN (including subquery FROMs) → WHERE → UNION
     *
     * @return array
     */
    public function getAllParams(): array {
        return array_merge(
            $this->cteParams,
            $this->selectParams,
            $this->joinParams,
            $this->whereParams,
            $this->unionParams
        );
    }

    /**
     * getAllTypes - returns the concatenated type string matching getAllParams()
     *
     * @return string
     */
    public function getAllTypes(): string {
        return $this->cteTypes . $this->selectTypes . $this->joinTypes . $this->whereTypes . $this->unionTypes;
    }

    /**
     * getWhereParams - used by parent queries merging this as a subquery inside WHERE
     *
     * @return array
     */
    public function getWhereParams(): array {
        return $this->getAllParams();
    }

    /**
     * getWhereTypes - used by parent queries merging this as a subquery inside WHERE
     *
     * @return string
     */
    public function getWhereTypes(): string {
        return $this->getAllTypes();
    }

    #endregion
    #region insert    
    /**
     * insert
     *
     * @param  mixed $data
     * @return array
     */
    public function insert(array $data): array {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        $params = array_values($data);
        $types = '';
        foreach ($params as $param) {
            $types .= $this->getParamType($param);
        }

        return [
            'sql'    => $sql,
            'types'  => $types,
            'params' => $params,
        ];
    }
    #endregion
    #region update    
    /**
     * update
     *
     * @param  mixed $data
     * @return array
     */
    public function update(array $data): array {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "$column = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);

        $params = array_values($data);
        $types = '';
        foreach ($params as $param) {
            $types .= $this->getParamType($param);
        }

        // SET params come first, then WHERE params
        $params = array_merge($params, $this->whereParams);
        $types .= $this->whereTypes;

        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        return [
            'sql'    => $sql,
            'types'  => $types,
            'params' => $params,
        ];
    }
    #endregion
    #region delete    
    /**
     * delete
     *
     * @return array
     */
    public function delete(): array {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        return [
            'sql'    => $sql,
            'types'  => $this->whereTypes,
            'params' => $this->whereParams,
        ];
    }
}

#endregion
#region if class
/**
 * IfStatement - Represents a MySQL IF statement
 */
class IfStatement {
    private string $condition;
    private string $processedCondition;
    private mixed $trueValue;
    private mixed $falseValue;
    private string $processedTrueValue;
    private string $processedFalseValue;
    private array $params = [];
    private string $types = '';

    public function __construct(string $condition, mixed $trueValue, mixed $falseValue) {
        $this->condition = $condition;

        // Process condition and extract parameters
        $this->processedCondition = $this->processCondition($condition);

        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue;

        // Process true value
        if ($trueValue instanceof IfStatement) {
            $this->processedTrueValue = $trueValue->toSql();
            $this->params = array_merge($this->params, $trueValue->getParams());
            $this->types .= $trueValue->getTypes();
        } elseif ($this->isColumnOrExpression($trueValue)) {
            // It's an expression - process it for embedded strings/numbers
            $this->processedTrueValue = $this->processExpression($trueValue);
        } else {
            // It's a simple value
            $this->processedTrueValue = '?';
            $this->params[] = $this->processValue($trueValue);
            $this->types .= $this->getParamType($this->processValue($trueValue));
        }

        // Process false value
        if ($falseValue instanceof IfStatement) {
            $this->processedFalseValue = $falseValue->toSql();
            $this->params = array_merge($this->params, $falseValue->getParams());
            $this->types .= $falseValue->getTypes();
        } elseif ($this->isColumnOrExpression($falseValue)) {
            // It's an expression - process it for embedded strings/numbers
            $this->processedFalseValue = $this->processExpression($falseValue);
        } else {
            // It's a simple value
            $this->processedFalseValue = '?';
            $this->params[] = $this->processValue($falseValue);
            $this->types .= $this->getParamType($this->processValue($falseValue));
        }
    }

    /**
     * Process an expression and parameterize embedded strings and numbers
     *
     * @param  mixed $expression
     * @return string
     */
    private function processExpression(string $expression): string {
        // Replace quoted strings with placeholders
        $processed = preg_replace_callback(
            '/(["\'`])(?:(?=(\\\\?))\2.)*?\1/',
            function ($matches) {
                $value = trim($matches[0], $matches[1]);
                $this->params[] = $value;
                $this->types .= 's';
                return '?';
            },
            $expression
        );

        // Replace standalone numbers with placeholders
        // Match numbers not preceded/followed by alphanumeric or dot (to avoid matching column names)
        $processed = preg_replace_callback(
            '/(?<![a-zA-Z0-9_.])\b(\d+\.?\d*)\b(?![a-zA-Z0-9_.])/',
            function ($matches) {
                $value = $matches[1];
                // Check if it's a float or int
                if (strpos($value, '.') !== false) {
                    $this->params[] = (float)$value;
                    $this->types .= 'd';
                } else {
                    $this->params[] = (int)$value;
                    $this->types .= 'i';
                }
                return '?';
            },
            $processed
        );

        return $processed;
    }

    /**
     * Process condition and replace string/number literals with placeholders
     *
     * @param  mixed $condition
     * @return string
     */
    private function processCondition(string $condition): string {
        return $this->processExpression($condition);
    }

    /**
     * Check if value is a column reference or SQL expression
     *
     * @param  mixed $value
     * @return bool
     */
    private function isColumnOrExpression($value): bool {
        if (!is_string($value)) {
            return false;
        }

        // If it's a quoted string literal, it's NOT a column/expression
        if (preg_match('/^(["\'`]).*\1$/', trim($value))) {
            return false;
        }

        // Check if it contains SQL-like patterns (column names, arithmetic, functions, etc.)
        // Column names: table.column, `column`, column
        // Expressions: column * 30, CONCAT(a, b), etc.
        return preg_match('/[a-zA-Z_`.]/', $value) ||
            preg_match('/[+\-*\/]/', $value) ||
            preg_match('/\(.*\)/', $value);
    }

    /**
     * Process true/false value - strip quotes and return the raw value if it's a quoted string
     *
     * @param  mixed $value
     * @return void
     */
    private function processValue($value) {
        if (!is_string($value)) {
            return $value;
        }

        // If it's a quoted string, strip the quotes and return the value
        if (preg_match('/^(["\'])(.*)\\1$/', trim($value), $matches)) {
            return $matches[2];
        }

        return $value;
    }

    /**
     * getParamType
     *
     * @param  mixed $value
     * @return string
     */
    private function getParamType($value): string {
        if (is_int($value)) return 'i';
        if (is_float($value)) return 'd';
        return 's';
    }

    /**
     * toSql
     *
     * @return string
     */
    public function toSql(): string {
        return "IF({$this->processedCondition}, {$this->processedTrueValue}, {$this->processedFalseValue})";
    }

    /**
     * getParams
     *
     * @return array
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * getTypes
     *
     * @return string
     */
    public function getTypes(): string {
        return $this->types;
    }
}

#endregion
#region case class
/**
 * CaseStatement - Represents a MySQL CASE statement
 */
class CaseStatement {
    private ?string $caseColumn = null;
    private string $processedCaseColumn = '';
    private array $whenThens = [];
    private mixed $elseValue = null;
    private string $processedElseValue = '';
    private array $params = [];
    private string $types = '';

    /**
     * Start a simple CASE statement (no expression after CASE)
     *
     * @param  mixed $column
     * @return void
     */
    public function __construct(?string $column = null) {
        $this->caseColumn = $column;
        if ($column !== null) {
            $this->processedCaseColumn = $this->processExpression($column);
        }
    }

    /**
     * Add a WHEN ... THEN clause
     *
     * @param  mixed $condition
     * @param  mixed $thenValue
     * @return self
     */
    public function when(string $condition, mixed $thenValue): self {
        $processedCondition = $this->processExpression($condition);

        if ($thenValue instanceof IfStatement || $thenValue instanceof CaseStatement) {
            $processedThen = $thenValue->toSql();
            $this->params = array_merge($this->params, $thenValue->getParams());
            $this->types .= $thenValue->getTypes();
        } elseif ($this->isColumnOrExpression($thenValue)) {
            $processedThen = $this->processExpression($thenValue);
        } else {
            $processedThen = '?';
            $this->params[] = $this->processValue($thenValue);
            $this->types .= $this->getParamType($this->processValue($thenValue));
        }

        $this->whenThens[] = [
            'condition' => $processedCondition,
            'then' => $processedThen
        ];

        return $this;
    }

    /**
     * Set the ELSE clause
     *
     * @param  mixed $value
     * @return self
     */
    public function else(mixed $value): self {
        $this->elseValue = $value;

        if ($value instanceof IfStatement || $value instanceof CaseStatement) {
            $this->processedElseValue = $value->toSql();
            $this->params = array_merge($this->params, $value->getParams());
            $this->types .= $value->getTypes();
        } elseif ($this->isColumnOrExpression($value)) {
            $this->processedElseValue = $this->processExpression($value);
        } else {
            $this->processedElseValue = '?';
            $this->params[] = $this->processValue($value);
            $this->types .= $this->getParamType($this->processValue($value));
        }

        return $this;
    }

    /**
     * Process an expression and parameterize embedded strings and numbers
     *
     * @param  mixed $expression
     * @return string
     */
    private function processExpression(string $expression): string {
        // Replace quoted strings with placeholders
        $processed = preg_replace_callback(
            '/(["\'`])(?:(?=(\\\\?))\2.)*?\1/',
            function ($matches) {
                $value = trim($matches[0], $matches[1]);
                $this->params[] = $value;
                $this->types .= 's';
                return '?';
            },
            $expression
        );

        // Replace standalone numbers with placeholders
        $processed = preg_replace_callback(
            '/(?<![a-zA-Z0-9_.])\b(\d+\.?\d*)\b(?![a-zA-Z0-9_.])/',
            function ($matches) {
                $value = $matches[1];
                if (strpos($value, '.') !== false) {
                    $this->params[] = (float)$value;
                    $this->types .= 'd';
                } else {
                    $this->params[] = (int)$value;
                    $this->types .= 'i';
                }
                return '?';
            },
            $processed
        );

        return $processed;
    }

    /**
     * isColumnOrExpression
     *
     * @param  mixed $value
     * @return bool
     */
    private function isColumnOrExpression($value): bool {
        if (!is_string($value)) {
            return false;
        }

        if (preg_match('/^(["\'`]).*\1$/', trim($value))) {
            return false;
        }

        return preg_match('/[a-zA-Z_`.]/', $value) ||
            preg_match('/[+\-*\/]/', $value) ||
            preg_match('/\(.*\)/', $value);
    }

    /**
     * processValue
     *
     * @param  mixed $value
     * @return void
     */
    private function processValue($value) {
        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('/^(["\'])(.*)\\1$/', trim($value), $matches)) {
            return $matches[2];
        }

        return $value;
    }

    /**
     * getParamType
     *
     * @param  mixed $value
     * @return string
     */
    private function getParamType($value): string {
        if (is_int($value)) return 'i';
        if (is_float($value)) return 'd';
        return 's';
    }

    /**
     * toSql
     *
     * @return string
     */
    public function toSql(): string {
        $sql = 'CASE';

        if ($this->caseColumn !== null) {
            $sql .= ' ' . $this->processedCaseColumn;
        }

        foreach ($this->whenThens as $whenThen) {
            $sql .= " WHEN {$whenThen['condition']} THEN {$whenThen['then']}";
        }

        if ($this->elseValue !== null) {
            $sql .= " ELSE {$this->processedElseValue}";
        }

        $sql .= ' END';

        return $sql;
    }

    /**
     * getParams
     *
     * @return array
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * getTypes
     *
     * @return string
     */
    public function getTypes(): string {
        return $this->types;
    }
}
