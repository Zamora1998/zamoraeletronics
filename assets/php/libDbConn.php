<?php

/**
 * DB connector for MySQL
 * 
 * @method mysqli|false         getConn()
 * @method true                 closeConn()
 * @method mysqli_result|bool   applyQuery(string)
 * @method bool                 setAutoCommit(bool)
 * @method bool                 commit()
 * @method array                getDataQuery()
 * @method string               getError()
 * @method int|string           getAffectedRows()
 * @method int|string           getLastId()
 * @method array                getInfo()
 * @method void                 freeResultQuery()
 * @method string               mysqlRealEscape()
 * @method void                 resetAIstring(string)
 * @method array                processQuery()
 * @method mixed                get_MySQL_Variable(string)
 */
class dbConn
{
    protected $mysqli_host = '';
    protected $mysqli_user = '';
    protected $mysqli_pass = '';
    protected $mysqli_dbas = '';
    private $mysqli;
    private $_auditLock = false;
    private $_lastInsertId = 0;
    private $_auditExcludeTables = ['sys__changelogs'];

    function __construct($conn = '')
    {
        $this->loadIni($conn);
        $this->mysqli = new mysqli($this->mysqli_host, $this->mysqli_user, $this->mysqli_pass, $this->mysqli_dbas);
        if ($this->mysqli->connect_error) {
            die('Connect Error: ' . $this->mysqli->connect_error);
        } else {
            $this->mysqli->set_charset('utf8mb4');
        }
    }

    function __destruct()
    {
        /*$this->mysqli->close();Breaks Zebra_Session*/
    }

    /**
     * get mysqli connection
     *
     * @return mysqli|false Returns an object which represents the connection to a MySQL Server, or false on failure. 
     */
    public function getConn(): mysqli|false
    {
        return $this->mysqli;
    }

    /**
     * close mysqli connection
     *
     * @return bool
     */
    public function closeConn(): bool
    {
        return $this->mysqli->close();
    }

    //execute queries    
    /**
     * applyQuery
     *
     * @param  mixed $sql
     * @return mysqli_result|bool Returns false on failure. For successful queries which produce a result set, such as SELECT, SHOW, DESCRIBE or EXPLAIN, mysqli_query() will return a mysqli_result object. For other successful queries, mysqli_query() will return true. 
     */
    public function applyQuery(string $sql): mysqli_result|bool
    {
        $this->_lastInsertId = 0;
        // Auditoría automática para INSERT, UPDATE, DELETE
        if (!$this->_auditLock && isset($_SESSION)) {
            $this->_auditLock = true;
            try {
                $auditResult = $this->_auditAndExecute($sql);
            } catch (Exception $ex) {
                return false;
            } finally {
                $this->_auditLock = false;
            }
            if ($auditResult !== null) {
                return $auditResult;
            }
        }

        try {
            $result = mysqli_query($this->mysqli, $sql);
            if (preg_match('/^\s*INSERT\s+/i', $sql)) {
                $this->_lastInsertId = mysqli_insert_id($this->mysqli);
            }
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Ejecuta la consulta con captura de auditoría (OLD/NEW).
     * Retorna el resultado de la query, o null si no es una operación auditable.
     */
    private function _auditAndExecute(string $sql): mixed
    {
        $auditOldData = [];
        $newData = [];
        $auditTable = null;

        if (preg_match('/^\s*UPDATE\s+(.+?)\s+SET\s+(.+)\s+WHERE\s+(.+)$/is', $sql, $matches)) {
            // UPDATE
            $auditTable = trim($matches[1]);

            // Excluir tablas de la auditoría
            if (in_array(str_replace(['`', '[', ']', 'dbo.'], '', $auditTable), $this->_auditExcludeTables)) {
                return null;
            }

            $auditWhere = trim($matches[3]);

            $resOld = mysqli_query($this->mysqli, "SELECT * FROM $auditTable WHERE $auditWhere");
            if ($resOld && !is_bool($resOld)) {
                $auditOldData = mysqli_fetch_all($resOld, MYSQLI_ASSOC)[0] ?? [];
            }

            $result = mysqli_query($this->mysqli, $sql);

            if ($result && !empty($auditOldData)) {
                $resNew = mysqli_query($this->mysqli, "SELECT * FROM $auditTable WHERE $auditWhere");
                if ($resNew && !is_bool($resNew)) {
                    $newData = mysqli_fetch_all($resNew, MYSQLI_ASSOC)[0] ?? [];
                    $diff = ['old' => [], 'new' => []];
                    foreach ($newData as $col => $val) {
                        if (array_key_exists($col, $auditOldData) && $auditOldData[$col] != $val) {
                            $diff['old'][$col] = $this->_maskSensitiveData($col, $auditOldData[$col]);
                            $diff['new'][$col] = $this->_maskSensitiveData($col, $val);
                        }
                    }
                    if (!empty($diff['old'])) {
                        $_SESSION['last_audit_log'] = [
                            'table' => $auditTable,
                            'diff'  => $diff
                        ];
                    }
                }
            }
            return $result;
        } elseif (preg_match('/^\s*DELETE\s+FROM\s+(.+?)\s+WHERE\s+(.+)$/is', $sql, $matches)) {
            // DELETE
            $auditTable = trim($matches[1]);

            // Excluir tablas de la auditoría
            if (in_array(str_replace(['`', '[', ']', 'dbo.'], '', $auditTable), $this->_auditExcludeTables)) {
                return null;
            }

            $auditWhere = trim($matches[2]);

            $resOld = mysqli_query($this->mysqli, "SELECT * FROM $auditTable WHERE $auditWhere");
            if ($resOld && !is_bool($resOld)) {
                $auditOldData = mysqli_fetch_all($resOld, MYSQLI_ASSOC)[0] ?? [];
            }

            $result = mysqli_query($this->mysqli, $sql);

            if (!empty($auditOldData)) {
                $maskedOld = [];
                foreach ($auditOldData as $col => $val) {
                    $maskedOld[$col] = $this->_maskSensitiveData($col, $val);
                }
                $_SESSION['last_audit_log'] = [
                    'table' => $auditTable,
                    'diff'  => ['old' => $maskedOld, 'new' => []]
                ];
            }
            return $result;
        } elseif (preg_match('/^\s*INSERT\s+INTO\s+([^\s\(]+)/is', $sql, $matches)) {
            // INSERT
            $auditTable = trim($matches[1]);

            // Excluir tablas de la auditoría
            if (in_array(str_replace(['`', '[', ']', 'dbo.'], '', $auditTable), $this->_auditExcludeTables)) {
                return null;
            }

            $result = mysqli_query($this->mysqli, $sql);

            if ($result) {
                $lastId = mysqli_insert_id($this->mysqli);
                $this->_lastInsertId = $lastId;
                if ($lastId) {
                    $resNew = mysqli_query($this->mysqli, "SELECT * FROM $auditTable WHERE id = $lastId");
                    if ($resNew && !is_bool($resNew)) {
                        $newData = mysqli_fetch_all($resNew, MYSQLI_ASSOC)[0] ?? [];
                    }
                }

                // Fallback: parsear columnas/valores del SQL
                if (empty($newData) && preg_match('/\((.+)\)\s*VALUES\s*\((.+)\)/is', $sql, $valMatches)) {
                    $cols = array_map('trim', explode(',', $valMatches[1]));
                    $vals = array_map(function ($v) {
                        return trim(trim($v), "'\"");
                    }, explode(',', $valMatches[2]));
                    if (count($cols) === count($vals)) {
                        $newData = array_combine($cols, $vals);
                    }
                }

                if (!empty($newData)) {
                    $maskedNew = [];
                    foreach ($newData as $col => $val) {
                        $maskedNew[$col] = $this->_maskSensitiveData($col, $val);
                    }
                    $_SESSION['last_audit_log'] = [
                        'table' => $auditTable,
                        'diff'  => ['old' => [], 'new' => $maskedNew]
                    ];
                }
            }
            return $result;
        }

        return null; // No es operación auditable
    }

    private function _maskSensitiveData(string $column, mixed $value): mixed
    {
        if (!$value) return $value;
        $sensitiveFields = ['password', 'pass', 'clave', 'token', 'secret', 'key'];
        foreach ($sensitiveFields as $field) {
            if (stripos($column, $field) !== false) {
                return '***';
            }
        }
        return $value;
    }

    /**
     * set Auto-Commit
     *
     * @param  mixed $ac
     * @return bool Returns true on success or false on failure. 
     */
    public function setAutoCommit(bool $ac = false): bool
    {
        return mysqli_autocommit($this->mysqli, $ac);
    }

    /**
     * commit
     *
     * @return bool Returns true on success or false on failure. 
     */
    public function commit(): bool
    {
        return mysqli_commit($this->mysqli);
    }

    /**
     * get data from query result
     *
     * @param  mysqli_result $result A mysqli_result object
     * @param  mixed $mode The possible values for this parameter are the constants MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH.
     * @return array Returns an array of associative or numeric arrays holding result rows.
     */
    public function getDataQuery(mysqli_result $result, $mode = MYSQLI_ASSOC): array
    {
        return mysqli_fetch_all($result, $mode);
    }

    /**
     * get error from last executed query 
     *
     * @return string Returns the last error message for the most recent MySQLi function call that can succeed or fail. 
     */
    public function getError(): string
    {
        return mysqli_error($this->mysqli);
    }

    /**
     * getAffectedRows
     *
     * @return int|string  Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query. Works like mysqli_num_rows() for SELECT statements.
     */
    public function getAffectedRows(): int|string
    {
        return mysqli_affected_rows($this->mysqli);
    }

    //get last generated id    
    /**
     * get the ID generated by an INSERT or UPDATE query.
     *
     * @return int|string The value of the AUTO_INCREMENT field that was updated by the previous query. Returns zero if there was no previous query on the connection or if the query did not update an AUTO_INCREMENT value. 
     */
    public function getLastId(): int|string
    {
        if ($this->_lastInsertId) {
            return $this->_lastInsertId;
        }
        return mysqli_insert_id($this->mysqli);
    }

    /**
     * get Info about the most recently executed query.
     *
     * @return array representing additional information about the most recently executed query. 
     */
    public function getInfo(): array
    {
        $informations = [];
        $data = mysqli_info($this->mysqli);

        if (!empty($data)) {
            $infos = explode('  ', $data);
            foreach ($infos as $info) {
                $parts = explode(': ', $info);
                if (isset($parts[0], $parts[1])) {
                    $informations[trim($parts[0])] = trim($parts[1]);
                }
            }
        } else {
            // Si mysqli_info está vacío, usar affected_rows como fallback
            $informations['Rows matched'] = '0';
            $informations['Changed'] = (string)$this->mysqli->affected_rows;
            $informations['Warnings'] = '0';
        }

        return $informations;
    }

    /**
     * Frees the memory associated with the result. 
     *
     * @param  mysqli_result $result
     * @return void
     */
    public function freeResultQuery(mysqli_result $result)
    {
        mysqli_free_result($result);
    }

    /**
     * Create a legal SQL string that you can use in an SQL statement
     *
     * @param  string $string
     * @return string Returns an escaped string. 
     */
    public function mysqlRealEscape(string $string): string
    {
        return mysqli_real_escape_string($this->mysqli, $string);
    }

    /**
     * Create a legal SQL string-array that you can use in an SQL statement
     *
     * @param  array $arr
     * @return array Returns an escaped string-array. 
     */
    public function mysqlRealEscapeArray(array $arr): array
    {
        return array_map(function ($value) {
            return $this->mysqli->real_escape_string($value);
        }, $arr);
    }

    /**
     * reset Auto Increment of a table
     *
     * @param  string $table table name.
     * @return void
     */
    public function resetAI(string $table)
    {
        $this->applyQuery("CALL reset_ai('{$table}');");
    }

    /**
     * get MySQL system variable value.
     * Eg 'max_allowed_packet'
     *
     * @param  string $name
     * @return mixed Returns system variable value.
     */
    public function get_MySQL_Variable(string $name): mixed
    {
        $SQL = "SHOW VARIABLES LIKE '{$name}'";
        $result = $this->applyQuery($SQL);
        $value = $this->getDataQuery($result);
        $this->freeResultQuery($result);
        return $value;
    }

    /**
     * combines applyQuery and getDataQuery
     * @param mixed $sql
     * @param mixed $printSql (optional) enables writing sql to text file (default=false).
     * @param bool  $printSqlName (optional) sets text file name.
     * @return array Returns result error and data if available
     */
    public function processQuery($sql, $printSql = false, $printSqlName = 'debugSql')
    {
        $return = [];

        $result = $this->applyQuery($sql);

        //Adds error only if query application is false
        $return["result"] = !empty($result);
        if (!$result) {
            $return["error"] = $this->getError();
        }

        //Adds data only if result is object.
        if (!is_bool($result)) {
            $return["data"] = $this->getDataQuery($result);
        }

        if ($printSql) {
            file_put_contents(__ROOT__ . "/{$printSqlName}.txt", var_export($sql, true));
        }

        return $return;
    }

    public function executePrepQuery(QueryBuilder|array $qb, bool $debug = false, $printSqlName = 'debugSql'): array
    {
        if (is_array($qb)) {
            $query = $qb;
        } else {
            $query = $qb->build();
        }

        return $this->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params'],
            $debug,
            $printSqlName
        );
    }

    public function prepProcessQuery(string $sql, string $types = '', array $params = [], $printSql = false, $printSqlName = 'debugSql'): array
    {
        $response = [
            'result' => false,
            'data'   => [],
            'error'  => null,
        ];

        if ($printSql) {
            file_put_contents(__ROOT__ . "/{$printSqlName}.txt", 'SQL: ' . $sql . "\nParams: " . json_encode($params) . "\nTypes: " . $types);
        }

        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            $response['error'] = $this->mysqli->error;
            return $response;
        }

        if ($params && !$stmt->bind_param($types, ...$params)) {
            $response['error'] = $stmt->error;
            return $response;
        }

        if (!$stmt->execute()) {
            $response['error'] = $stmt->error;
            return $response;
        }

        // Check if this is a SELECT query (has a result set)
        $result = $stmt->get_result();
        if ($result) {
            // SELECT query - fetch data
            $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            // INSERT, UPDATE, DELETE - return affected rows and insert ID
            $response['data'] = [
                'affected_rows' => $stmt->affected_rows,
                'insert_id' => $stmt->insert_id
            ];
        }

        $response['result'] = true;

        return $response;
    }

    private function loadIni($conn = '')
    {
        $ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
        $key = $ini_array['general']['key'];
        if (!$conn) {
            $this->mysqli_host = $ini_array['mysql']['mysqli_host'];
            $this->mysqli_user = $ini_array['mysql']['mysqli_user'];
            $this->mysqli_pass = $ini_array['mysql']['mysqli_pass'];
            $this->mysqli_dbas = $ini_array['mysql']['mysqli_dbas'];
        } else {
            $this->mysqli_host = $ini_array['mysql_' . $conn]['mysqli_host'];
            $this->mysqli_user = $ini_array['mysql_' . $conn]['mysqli_user'];
            $this->mysqli_pass = $ini_array['mysql_' . $conn]['mysqli_pass'];
            $this->mysqli_dbas = $ini_array['mysql_' . $conn]['mysqli_dbas'];
        }

        require_once __ROOT__ . '/assets/php/libCrypt.php';
        $objCrypt = new Crypt();
        $this->mysqli_pass = trim($objCrypt->decrypt($key, $this->mysqli_pass));
    }
}
