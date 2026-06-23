<?php
class cronjobs {
    protected $objDbConn;
    protected $id = 0;
    protected $elScript = '';
    protected $elSchedule = '';
    protected $status = 0;
    protected $protected = 0;
    protected $hasProtected;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectAll() {
        $sql = "SELECT
                id,
                script,
                schedule,
                enabled,
                protected
            FROM cronjobs";
        return $this->objDbConn->processQuery($sql);
    }

    public function selectCronjob() {
        $sql = "SELECT
            id,
            script,
            schedule,
            enabled,
            protected
        FROM cronjobs
        WHERE id = '{$this->id}';";
        return $this->objDbConn->processQuery($sql);
    }

    public function updateCronjobStatus($status) {
        $sql = "UPDATE cronjobs
                SET enabled = '{$status}'
                WHERE id = {$this->id};";

        return $this->objDbConn->processQuery($sql);
    }

    public function deleteCronjob() {
        $sql = "DELETE FROM cronjobs
                WHERE id = {$this->id};\n";

        return $this->objDbConn->processQuery($sql);
    }

    public function insertCronjob() {
        $this->objDbConn->resetAI('cronjobs');
        $sql = "INSERT INTO cronjobs (script, schedule, enabled, protected)
                VALUES ('{$this->elScript}', '{$this->elSchedule}', '{$this->status}', '{$this->protected}');";

        return $this->objDbConn->processQuery($sql);
    }

    public function updateCronjob() {
        $sql = "UPDATE cronjobs
                SET script = '{$this->elScript}', schedule = '{$this->elSchedule}', enabled = '{$this->status}', protected = '{$this->protected}'
                WHERE id = {$this->id};";
        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        return $this->objDbConn->processQuery($sql);
    }

    public function setId(int $int) {
        $this->id = $int;
    }
    public function setScript(string $str) {
        $this->elScript = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function SetSchedule(string $str) {
        $this->elSchedule = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setStatus(int $status) {
        $this->status = $status;
    }

    public function setProtected(int $protected) {
        $this->protected = $protected;
    }
    public function setHasProtected(int $int) {
        $this->hasProtected = $int;
    }
}
