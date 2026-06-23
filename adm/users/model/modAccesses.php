<?php

class Accesses {
    protected $objDbConn;
    protected $languageId = 'en';

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectAll() {
        $result = false;
        $error = "";
        $data = [];

        $sql = "SELECT
                    a.id,
                    COALESCE(
                        lan_name.description,
                        def_name.description,
                        CONCAT_WS(' ', a.name, 'N/A')
                    ) AS name,

                    COALESCE(
                        lan_desc.description,
                        def_desc.description,
                        CONCAT_WS(' ', a.description, 'N/A')                        
                    ) AS description
                FROM accesses a
                LEFT JOIN (
                    SELECT
                        l.name,
                        ld.description
                    FROM labels l
                    INNER JOIN labeldetails ld
                        ON ld.label_id = l.id
                    WHERE ld.language_id = '{$this->languageId}'
                ) lan_name
                    ON lan_name.name = a.name
                LEFT JOIN (
                    SELECT
                        l.name,
                        ld.description
                    FROM labels l
                    INNER JOIN labeldetails ld
                        ON ld.label_id = l.id
                    WHERE ld.language_id = 'en'
                ) def_name
                    ON def_name.name = a.name
                LEFT JOIN (
                    SELECT
                        l.name,
                        ld.description
                    FROM labels l
                    INNER JOIN labeldetails ld
                        ON ld.label_id = l.id
                    WHERE ld.language_id = '{$this->languageId}'
                ) lan_desc
                    ON lan_desc.name = a.description

                LEFT JOIN (
                    SELECT
                        l.name,
                        ld.description
                    FROM labels l
                    INNER JOIN labeldetails ld
                        ON ld.label_id = l.id
                    WHERE ld.language_id = 'en'
                ) def_desc
                    ON def_desc.name = a.description

                ORDER BY name;\n";

        //file_put_contents(__ROOT__ . '/debugaccesses.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }
}
