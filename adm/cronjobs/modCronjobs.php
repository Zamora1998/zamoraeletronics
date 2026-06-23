<?php
class cronjobs {
    protected $objDbConn;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function select() {
        $sql = "SELECT
                    id,
                    script,
                    schedule,
                    enabled
                FROM cronjobs
                WHERE enabled = 1;";

        return $this->objDbConn->processQuery($sql);
    }
}
