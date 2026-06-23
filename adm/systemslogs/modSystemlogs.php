<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class syslogs {
    use globalCte;
    //region general
    protected $objDbConn;
    protected $id = 0;
    protected $languageId = 'en';
    protected $LabelName = '';
    protected $Icon = '';
    protected $Url = '';
    protected $File = '';
    protected $chrLocale = 'en_US';
    protected $Alluser = 0;
    protected $Public = 0;
    protected $ParentC = 0;
    protected $Status = 0;
    protected $accessIds = [];
    protected $params = [];
    protected $optionals = [];
    protected $requireds = [];

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    //region end
    //region selects
    public function selectAll() {
        $sql = "SELECT 
                    l.id,
                    l.user_id,
                    CONCAT(u.first, ' ', u.last) AS full_name,
                    u.email,
                    l.method,
                    l.tableh,
                    l.action_code,
                    l.url,
                    l.payload,
                    l.created_at
                FROM sys__changelogs l
                LEFT JOIN users u ON u.id = l.user_id
                WHERE l.action_code <> 'R'
                AND l.url NOT LIKE '%/controller/auth%'
                ORDER BY l.created_at DESC;";
        return  $this->objDbConn->processQuery($sql);
    }

    public function selectLogTypes()
    {
        $sql = "SELECT 
                    lt.id,
                    lt.name
                FROM sys__logtypes lt
                ORDER BY lt.name";
        return $this->objDbConn->processQuery($sql);
    }

    public function selectLogTypeTables()
    {
        $sql = "SELECT 
                    lt.id AS logtype_id,
                    lt.name AS logtype_name,
                    GROUP_CONCAT(ltt.`table` SEPARATOR ',') AS tables
                FROM sys__logtypes lt
                INNER JOIN sys__logtype_tables ltt ON ltt.logtype_id = lt.id
                GROUP BY lt.id, lt.name
                ORDER BY lt.name";
        return $this->objDbConn->processQuery($sql);
    }
    //endregion

    //region modifications
    protected $name = '';
    protected $tableName = '';

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setName($name)
    {
        $this->name = $this->objDbConn->mysqlRealEscape(trim($name));
    }

    public function setTableName($tableName)
    {
        $this->tableName = $this->objDbConn->mysqlRealEscape(trim($tableName));
    }

    public function insertLogType()
    {
        $sql = "INSERT INTO sys__logtypes (name) VALUES ('{$this->name}')";
        return $this->objDbConn->processQuery($sql);
    }

    public function updateLogType()
    {
        $sql = "UPDATE sys__logtypes SET name = '{$this->name}' WHERE id = {$this->id}";
        return $this->objDbConn->processQuery($sql);
    }

    public function deleteLogType()
    {
        $this->objDbConn->applyQuery("DELETE FROM sys__logtype_tables WHERE logtype_id = {$this->id}");
        $sql = "DELETE FROM sys__logtypes WHERE id = {$this->id}";
        return $this->objDbConn->processQuery($sql);
    }

    public function insertLogTypeTable()
    {
        // Ignorar si ya existe
        $sqlCheck = "SELECT 1 FROM sys__logtype_tables WHERE logtype_id = {$this->id} AND `table` = '{$this->tableName}'";
        $res = $this->objDbConn->applyQuery($sqlCheck);
        if ($res && !is_bool($res) && $res->num_rows > 0) return ['result' => true];

        $sql = "INSERT INTO sys__logtype_tables (logtype_id, `table`) VALUES ({$this->id}, '{$this->tableName}')";
        return $this->objDbConn->processQuery($sql);
    }

    public function deleteLogTypeTable()
    {
        $sql = "DELETE FROM sys__logtype_tables WHERE logtype_id = {$this->id} AND `table` = '{$this->tableName}'";
        return $this->objDbConn->processQuery($sql);
    }
    //endregion
}
