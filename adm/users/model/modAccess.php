<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';
class Access {
    use globalCte;
    #region globals
    protected $objDbConnCU = null;
    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = 0;
    protected $accessName = '';
    protected $params = [];
    protected $salt = '';
    protected $description = '';

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    #endregion

    public function selectAll() {

        $sql = "WITH
            {$this->cteLabels()}
        SELECT
            a.id,
            COALESCE(lbl_name.name, a.name) AS name,
            a.name AS text,
            COALESCE(lbl_desc.name, a.description) AS description
        FROM accesses a
        LEFT JOIN LABELS lbl_name
            ON lbl_name.id = a.name
        LEFT JOIN LABELS lbl_desc
            ON lbl_desc.id = a.description;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectAccess() {
        // Preparamos la consulta SQL
        $sql = "WITH
                    {$this->cteLabels()}
                SELECT
                    a.name AS id,
                    COALESCE(lbl_name.name, a.name) AS name,
                    a.description AS description
                FROM accesses a
                LEFT JOIN LABELS lbl_name
                    ON lbl_name.id = a.name
                LEFT JOIN LABELS lbl_desc
                    ON lbl_desc.id = a.description
                WHERE a.id = '{$this->id}'";

        return $this->objDbConn->processQuery($sql);
    }

    public function consultAccess() {
        $sql = "SELECT 1 
            FROM users 
            WHERE (access & (POW(2, {$this->id} - 1)) != 0)
            OR EXISTS (
                SELECT 1
                FROM route_accesses 
                WHERE access_id = {$this->id}
                LIMIT 1
            )
            LIMIT 1;";

        $result = $this->objDbConn->processQuery($sql);

        if (!$result['result']) {
            return ['result' => false, 'error' => $result['error'], 'data' => []];
        }

        $isConfigured = !empty($result['data']);

        return ['result' => true, 'error' => '', 'data' => ['is_configured' => $isConfigured]];
    }


    public function deleteAccess() {
        $errors = [];
        $results = [];

        // Actualizar usuarios
        $sql = "UPDATE users SET access = access & ~(POW(2, {$this->id} - 1));";
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $sql;
        }

        // Eliminar el access; los route_accesses relacionados se borrarán automáticamente por ON DELETE CASCADE
        $sql = "DELETE FROM accesses WHERE id = {$this->id};";
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $sql;
        }

        return ['result' => !in_array(false, $results, true), 'errors' => $errors];
    }

    public function insertAccess() {
        $this->objDbConn->resetAI('accesses');
        $sql = "INSERT INTO 
                accesses (name, description) VALUES ('{$this->accessName}', '{$this->description}');";
        return $this->objDbConn->processQuery($sql);
    }

    public function updateAccess() {
        $sql = "UPDATE accesses SET name = '{$this->accessName}', description = '{$this->description}' WHERE id = {$this->id};";
        return $this->objDbConn->processQuery($sql);
    }

    public function setId(int $int) {
        $this->id = $int;
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setAccessName(string $str) {
        $this->accessName = $str;
    }
        public function setDescription(string $str) {
        $this->description = $str;
    }
}
