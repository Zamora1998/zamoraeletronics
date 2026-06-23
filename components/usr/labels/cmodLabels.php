<?php
class componentLabels {
    #region globals
    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = '';
    protected $labelId = '';
    protected $labelName = '';
    protected $labelNames = [];
    protected $labelDescriptions = [];
    protected $term = "";
    protected $state = 0;
    protected $allmonths = 0;
    protected $params = [];
    protected $salt = '';

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
    public function selectLabels() {

        $sql = "SELECT
                    id,
                    CONCAT_WS(' ', name, description) as text
                FROM labels
                    LEFT JOIN (
                        SELECT *
                        FROM labeldetails
                        WHERE language_id = '{$this->languageId}'
                    ) det
                        ON id = label_id
                WHERE (
                        name LIKE '%{$this->term}%'
                        OR description LIKE '%{$this->term}%'
                    );";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectDescriptions() {
        $sSql = [];
        $fSql = [];

        $sql = "SELECT * from languages WHERE enabled = 1;";
        $return = $this->objDbConn->processQuery($sql);

        if ($return['result']) {
            $languages = $return['data'];
            $sql1 = "SELECT 
                        id,
                        name,\n";
            $sql2 = "                FROM labels\n";
            $sql3 = '';
            if ($this->id) {
                $sql3 = "                WHERE id='{$this->id}'\n";
            }
            $sql4 = "                ORDER BY id;";
            foreach ($languages as $language) {
                $fSql[] = "                    lang_{$language['id']}.description AS description_{$language['id']}";
                $sSql[] = "                    LEFT JOIN (
                        SELECT
                            label_id,
                            description
                        FROM labeldetails
                        WHERE language_id = '{$language['id']}'
                    ) lang_{$language['id']}
                        ON lang_{$language['id']}.label_id = id\n";
            }
            $sql = $sql1 . implode(",\n", $fSql) . $sql2 . implode('', $sSql) . $sql3 . $sql4;

            $return = $this->objDbConn->processQuery($sql);
            $return['languages'] = $languages;
        }

        return $return;
    }
    public function insertLabel() {
        $this->objDbConn->resetAI('labels');

        $sql = "INSERT INTO labels (id) VALUES ('{$this->labelName}');";
        $insertResult = $this->objDbConn->applyQuery($sql);

        if (!$insertResult) {
            return ['result' => false, 'error' => $this->objDbConn->getError()];
        }

        $this->labelId = $this->labelName; // sincronizar id

        return $this->saveLabelDetails();
    }

    public function updateLabel() {
        $this->objDbConn->resetAI('labels');

        $sql = "UPDATE labels SET id = '{$this->labelName}' WHERE id = '{$this->labelId}';";
        $updateResult = $this->objDbConn->applyQuery($sql);

        if (!$updateResult) {
            return ['result' => false, 'error' => $this->objDbConn->getError()];
        }

        return $this->saveLabelDetails();
    }

    private function saveLabelDetails() {
        $results = [];
        $error = '';
        $labelId = $this->labelName; // usar el nombre como ID
        $descriptions = $this->labelDescriptions;

        // INSERT/UPDATE
        if (!empty($descriptions)) {
            $values = array_map(function ($langId) use ($labelId, $descriptions) {
                $desc = addslashes($descriptions[$langId]); // escape básico si no se usa prepared statements
                return "('{$labelId}', '{$langId}', '{$desc}')";
            }, array_keys($descriptions));

            $sqlInsert = "
                INSERT INTO labeldetails (label_id, language_id, description)
                VALUES " . implode(",\n", $values) . "
                ON DUPLICATE KEY UPDATE description = VALUES(description);";

            $results[] = $this->objDbConn->applyQuery($sqlInsert);
            if (!end($results)) {
                $error = $this->objDbConn->getError();
            }
        }

        // DELETE languages not present
        $langIds = array_keys($descriptions);
        $sqlDelete = "DELETE FROM labeldetails WHERE label_id = '{$labelId}'";

        if (!empty($langIds)) {
            $sqlDelete .= " AND language_id NOT IN ('" . implode("','", $langIds) . "')";
        }

        $sqlDelete .= ";";

        $results[] = $this->objDbConn->applyQuery($sqlDelete);
        if (!end($results)) {
            $error = $this->objDbConn->getError();
        }

        return ['result' => !in_array(false, $results, true), 'error' => $error];
    }

    public function setLabelName(string $str) {
        $this->labelName = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setLabelDescriptions(array $arr) {
        foreach ($arr as $key => $description) {
            $this->labelDescriptions[$key] = $this->objDbConn->mysqlRealEscape(trim($description));
        }
    }

    public function setId(string $str) {
        $this->id = $str;
    }

    public function setTerm(string $str) {
        $this->term = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setLanguageId(string $str) {
        $this->languageId = $str;
    }

}
