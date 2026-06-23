<?php

/**
 * DB Connector for SQL Server
 * 
 * @method mixed        getConn()
 * @method void         closeConn()
 * @method mixed        applyQuery()
 * @method void         commit()
 * @method array        getDataQuery()
 * @method mixed        getError()
 * @method int|false    getAffectedRows()
 * @method bool         freeResultQuery()
 * @method mixed        configure()
 * @method string       sqlRealEscape()
 * @method array        processQuery()
 * @method int|string   getLastId()
 * @method bool         below2008()
 */
class dbConnSqlSrv {
    protected $sqlsrv_host = "";
    protected $sqlsrv_port = "1433";
    protected $sqlsrv_user = "";
    protected $sqlsrv_pass = "";
    protected $sqlsrv_dbas = "";
    private $sqlsrv;
    private $_auditLock = false;
    private $_lastInsertId = 0;
    private $_auditExcludeTables = ['sys__changelogs'];

    /**
     * __construct
     *
     * @param  mixed $conn
     * @return void
     */
    function __construct($conn = '') {
        $this->loadIni($conn);
        $conf = array("Database" => $this->sqlsrv_dbas, "UID" => $this->sqlsrv_user, "PWD" => $this->sqlsrv_pass, 'ReturnDatesAsStrings' => true, "CharacterSet" => "UTF-8", 'TrustServerCertificate' => 'yes');
        $this->sqlsrv = sqlsrv_connect($this->sqlsrv_host, $conf) or die(print_r(sqlsrv_errors(), true));
    }

    /**
     * get sqlsrv connection
     *
     * @return mixed Returns the connection resource. If a connection was not successfully opened, false is returned.
     */
    public function getConn(): mixed {
        return $this->sqlsrv;
    }

    /**
     * close sqlsrv connection
     *
     * @return bool Returns true on success or false on failure.
     */
    public function closeConn(): bool {
        return sqlsrv_close($this->sqlsrv);
    }

    /**
     * execute queries
     *
     * @param  string $sql
     * @param  array $params
     * @return mixed Returns a statement resource on success and false if an error occurred.
     */
    public function applyQuery($sql, $params = []): mixed
    {
        $this->_lastInsertId = 0;
        // Auditoría automática para INSERT, UPDATE, DELETE
        if (!$this->_auditLock && empty($params) && isset($_SESSION)) {
            $this->_auditLock = true;
            try {
                $auditResult = $this->_auditAndExecute($sql);
            } finally {
                $this->_auditLock = false;
            }
            if ($auditResult !== null) {
                return $auditResult;
            }
        }

        try {
            $result = sqlsrv_query($this->sqlsrv, $sql, $params);
            if (preg_match('/^\s*INSERT\s+/i', $sql)) {
                $this->_lastIdAfterInsert();
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

            $resOld = sqlsrv_query($this->sqlsrv, "SELECT * FROM $auditTable WHERE $auditWhere");
            if ($resOld && !is_bool($resOld)) {
                $rows = [];
                while ($row = sqlsrv_fetch_array($resOld, SQLSRV_FETCH_ASSOC)) {
                    $rows[] = $row;
                }
                $auditOldData = $rows[0] ?? [];
            }

            $result = sqlsrv_query($this->sqlsrv, $sql);

            if ($result && !empty($auditOldData)) {
                $resNew = sqlsrv_query($this->sqlsrv, "SELECT * FROM $auditTable WHERE $auditWhere");
                if ($resNew && !is_bool($resNew)) {
                    $rows = [];
                    while ($row = sqlsrv_fetch_array($resNew, SQLSRV_FETCH_ASSOC)) {
                        $rows[] = $row;
                    }
                    $newData = $rows[0] ?? [];
                    $diff = ['old' => [], 'new' => []];
                    foreach ($newData as $col => $val) {
                        if (array_key_exists($col, $auditOldData) && $auditOldData[$col] != $val) {
                            $diff['old'][$col] = $auditOldData[$col];
                            $diff['new'][$col] = $val;
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

            $resOld = sqlsrv_query($this->sqlsrv, "SELECT * FROM $auditTable WHERE $auditWhere");
            if ($resOld && !is_bool($resOld)) {
                $rows = [];
                while ($row = sqlsrv_fetch_array($resOld, SQLSRV_FETCH_ASSOC)) {
                    $rows[] = $row;
                }
                $auditOldData = $rows[0] ?? [];
            }

            $result = sqlsrv_query($this->sqlsrv, $sql);

            if (!empty($auditOldData)) {
                $_SESSION['last_audit_log'] = [
                    'table' => $auditTable,
                    'diff'  => ['old' => $auditOldData, 'new' => []]
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

            $result = sqlsrv_query($this->sqlsrv, $sql);

            if ($result) {
                // SCOPE_IDENTITY para obtener el ID real
                $resId = sqlsrv_query($this->sqlsrv, "SELECT SCOPE_IDENTITY() AS last_id");
                $lastId = null;
                if ($resId && !is_bool($resId)) {
                    $idRow = sqlsrv_fetch_array($resId, SQLSRV_FETCH_ASSOC);
                    $lastId = $idRow['last_id'] ?? null;
                    $this->_lastInsertId = $lastId;
                }

                if ($lastId) {
                    $resNew = sqlsrv_query($this->sqlsrv, "SELECT TOP 1 * FROM $auditTable WHERE id = $lastId");
                    if ($resNew && !is_bool($resNew)) {
                        $rows = [];
                        while ($row = sqlsrv_fetch_array($resNew, SQLSRV_FETCH_ASSOC)) {
                            $rows[] = $row;
                        }
                        $newData = $rows[0] ?? [];
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
                    $_SESSION['last_audit_log'] = [
                        'table' => $auditTable,
                        'diff'  => ['old' => [], 'new' => $newData]
                    ];
                }
            }
            return $result;
        }

        return null; // No es operación auditable
    }

    /**
     * commit
     *
     * @return bool Returns true on success or false on failure. 
     */
    public function commit(): bool {
        return sqlsrv_commit($this->sqlsrv);
    }

    /**
     * get data from last executed query
     *
     * @param  mixed $result statement resource.
     * @return array Returns an array on success, and false if an error occurs.
     */
    public function getDataQuery($result): array {
        $data = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * get error from last executed query
     *
     * @return mixed If errors and/or warnings occurred on the last sqlsrv operation, an array of arrays containing error information is returned. If no errors and/or warnings occurred on the last sqlsrv operation, null is returned.
     */
    public function getError(): mixed {
        return sqlsrv_errors();
    }

    /**
     * get affected rows
     *
     * @param  mixed $result statement resource.
     * @return int|false Returns the number of rows affected by the last INSERT, UPDATE, or DELETE query. If no rows were affected, 0 is returned. If the number of affected rows cannot be determined, -1 is returned. If an error occurred, false is returned. 
     */
    public function getAffectedRows($result): int|false {
        return sqlsrv_rows_affected($result);
    }

    /**
     * flush result data
     *
     * @param  mixed $result
     * @return bool
     */
    public function freeResultQuery($result): bool {
        return sqlsrv_free_stmt($result);
    }

    /**
     * Change the driver error handling and logging configurations.
     * Returns true on success or false on failure.
     *
     * @param string $setting  The name of the setting to set. The possible values are "WarningsReturnAsErrors", "LogSubsystems", and "LogSeverity".
     * @param mixed $value The value of the specified setting:
     *     WarningsReturnAsErrors   1 (true) or 0 (false)
     *     LogSubsystems 	        SQLSRV_LOG_SYSTEM_ALL (-1),
     *                              SQLSRV_LOG_SYSTEM_CONN (2),
     *                              SQLSRV_LOG_SYSTEM_INIT (1),
     *                              SQLSRV_LOG_SYSTEM_OFF (0),
     *                              SQLSRV_LOG_SYSTEM_STMT (4),
     *                              SQLSRV_LOG_SYSTEM_UTIL (8)
     *     LogSeverity 	            SQLSRV_LOG_SEVERITY_ALL (-1),
     *                              SQLSRV_LOG_SEVERITY_ERROR (1),
     *                              SQLSRV_LOG_SEVERITY_NOTICE (4),
     *                              SQLSRV_LOG_SEVERITY_WARNING (2)
     *
     * @return bool
     */

    public function configure(string $setting, mixed $value): bool {
        return sqlsrv_configure($setting, $value);
    }

    /**
     * clean string data to insert or update
     *
     * @param  string $string
     * @return string
     */
    public function sqlRealEscape(string $string = ''): string {
        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ($non_displayables as $regex) {
            $string = preg_replace($regex, '', $string);
        }
        $string = str_replace("'", "''", $string);

        return $string;
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

        // Manejo de resultado
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

    /**
     * prepProcessQuery
     *
     * @param  string $sql
     * @param  string $types
     * @param  array $params
     * @param  bool $printSql
     * @param  string $printSqlName
     * @return array
     */
    public function prepProcessQuery(string $sql, string $types = '', array $params = [], bool $printSql = false, string $printSqlName = 'debugSql'): array {
        $response = [
            'result' => false,
            'data'   => [],
            'error'  => null,
        ];
        if ($printSql) {
            file_put_contents(__ROOT__ . "/{$printSqlName}.txt", 'SQL: ' . $sql . "\nParams: " . json_encode($params) . "\nTypes: " . $types);
        }

        $stmt = sqlsrv_prepare($this->sqlsrv, $sql, $params);

        if (!$stmt) {
            $response['error'] = $this->getError();
            return $response;
        }

        if (!sqlsrv_execute($stmt)) {
            $response['error'] = $this->getError();
            return $response;
        }

        $response['data']   = $this->getDataQuery($stmt);
        $response['result'] = true;

        return $response;
    }

    /**
     * clean string data to insert
     *
     * @param  string $conn conneestor segment.
     * @return void
     */
    private function loadIni(string $conn) {
        $ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
        $key = $ini_array["general"]["key"];
        $this->sqlsrv_host = $ini_array['sqlsrv_' . $conn]["sqlsrv_host"];
        $this->sqlsrv_port = $ini_array['sqlsrv_' . $conn]["sqlsrv_port"];
        $this->sqlsrv_user = $ini_array['sqlsrv_' . $conn]["sqlsrv_user"];
        $this->sqlsrv_pass = $ini_array['sqlsrv_' . $conn]["sqlsrv_pass"];
        $this->sqlsrv_dbas = $ini_array['sqlsrv_' . $conn]["sqlsrv_dbas"];

        require_once __ROOT__ . '/assets/php/libCrypt.php';
        $objCrypt = new Crypt();
        $this->sqlsrv_pass = trim($objCrypt->decrypt($key, $this->sqlsrv_pass));
    }

    /**
     * Check if sqlserver version is older than 2008
     */
    public function below2008() {
        $return = 0;
        $sql = "SELECT
                CASE
                    WHEN CONVERT(DATETIME, SubString(@@Version, (PatIndex('%[0-9][0-9][0-9][0-9]%', @@Version)), 4), 120) < CONVERT(DATETIME, '2008', 120)
                    THEN 1
                    ELSE 0
                END AS below2008";

        $result = $this->applyQuery($sql);
        $return = $this->getDataQuery($result)[0]['below2008'];
        $this->freeResultQuery($result);

        return $return;
    }

    public function getLastId(): int|string
    {
        if ($this->_lastInsertId) {
            return $this->_lastInsertId;
        }
        $query = "SELECT SCOPE_IDENTITY() AS lastId";
        $stmt = sqlsrv_query($this->sqlsrv, $query);
        if ($stmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row['lastId'] ?? 0;
    }

    private function _lastIdAfterInsert()
    {
        $query = "SELECT SCOPE_IDENTITY() AS lastId";
        $stmt = sqlsrv_query($this->sqlsrv, $query);
        if ($stmt !== false) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $this->_lastInsertId = $row['lastId'] ?? 0;
        }
    }
}
