<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class labels {
    use globalCte;
    #region global
    protected $objDbConn;
    protected $data;

    protected $labelId = '';
    protected $labelName = '';
    protected $labelNames = [];
    protected $labelDescriptions = [];
    protected $languageId = 'en';

    function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    #endregion
    #region selects
    public function selectAll() {
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
            if ($this->labelId) {
                $sql3 = "                WHERE id='{$this->labelId}'\n";
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

    private function selectLabels($withLikeCond = false) {
        if (count($this->labelNames)) {
            $nameFilter = ($withLikeCond
                ? "(id LIKE '" . implode("' OR id LIKE '", $this->labelNames) . "')"
                : "id IN ('" . implode("', '", $this->labelNames) . "')"
            );

            $sql = "WITH {$this->cteLabels()}
                    SELECT
                        id,
                        name AS description
                    FROM LABELS
                    WHERE {$nameFilter}
                    ORDER BY id;";

            return $this->objDbConn->processQuery($sql);
        }
        return ['result' => true, 'data' => [], 'error' => ''];
    }

    private function selectPrefixLabels() {
        if ($this->labelName) {
            $sql = "WITH {$this->cteLabels()}
                    SELECT
                        id,
                        name AS description
                    FROM LABELS
                    WHERE id LIKE ('{$this->labelName}')
                    ORDER BY id;";

            return $this->objDbConn->processQuery($sql);
        }
        return ['result' => true, 'data' => [], 'error' => ''];
    }
    #endregion
    #region inserts
    public function insertLabel() {
        $results = [];
        $error = '';
        $sql = "INSERT INTO labels (id) VALUES ('{$this->labelName}');";

        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        $this->objDbConn->resetAI('labels');
        $results[] = $this->objDbConn->applyQuery($sql);
        if (!end($results)) {
            $error = $this->objDbConn->getError();
        } else {
            $this->labelId = $this->labelName;
            $resultDetails = $this->insertupdateLabelDetails();
            $results[] = $resultDetails['result'];
            $error = $resultDetails['error'];
        }

        return ['result' => !in_array(false, $results, true), 'error' => $error];
    }

    private function insertupdateLabelDetails() {
        $results = [];
        $error = '';
        $values = [];
        foreach ($this->labelDescriptions as $key => $description) {
            $values[] = "('{$this->labelName}', '{$key}', '{$description}')";
        }
        if (count($values)) {
            $sql = "INSERT INTO labeldetails (label_id, language_id, description)
                VALUES " . implode(",\n", $values) . "
                ON DUPLICATE KEY UPDATE description = VALUES(description);";

            //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true),FILE_APPEND);
            $results[] = $this->objDbConn->applyQuery($sql);
            if (!end($results)) {
                $error = $this->objDbConn->getError();
            }
        }

        $labelLanguages = array_keys($this->labelDescriptions);
        $sql = "DELETE FROM labeldetails
                WHERE label_id='{$this->labelName}'\n";
        if (count($labelLanguages)) {
            $sql .= "AND language_id NOT IN ('" . implode("', '", $labelLanguages) . "');";
        }

        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true), FILE_APPEND);
        $results[] = $this->objDbConn->applyQuery($sql);
        if (!end($results)) {
            $error = $this->objDbConn->getError();
        }

        return ['result' => !in_array(false, $results, true), 'error' => $error];
    }
    #endregion
    #region updates
    public function updateLabel() {
        $results = [];
        $error = '';
        $sql = "UPDATE labels
                SET id = '{$this->labelName}'
                WHERE id = '{$this->labelId}';";

        file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        $this->objDbConn->resetAI('labels');
        $results[] = $this->objDbConn->applyQuery($sql);
        if (!end($results)) {
            $error = $this->objDbConn->getError();
        } else {
            $resultDetails = $this->insertupdateLabelDetails();
            $results[] = $resultDetails['result'];
            $error = $resultDetails['error'];
        }

        return ['result' => !in_array(false, $results, true), 'error' => $error];
    }
    #endregion
    #region deletes
    public function deleteLabel() {
        $sql = "DELETE FROM labels
                WHERE id = '{$this->labelId}';\n";

        $return = $this->objDbConn->processQuery($sql);
        $return['affected'] = $this->objDbConn->getAffectedRows();

        return $return;
    }
    #endregion
    #region getters
    public function getLabel(string $labelName, string $languageId = 'en') {
        $this->setLanguageId($languageId);
        $this->labelNames = array($this->objDbConn->mysqlRealEscape(trim($labelName)));

        return $this->organizeLabels($this->selectLabels()['data']);
    }

    public function getLabels(array $labelNames, string $languageId = 'en') {
        $this->setLanguageId($languageId);
        $this->labelNames = [];
        foreach ($labelNames as $labelName) {
            $this->labelNames[] = $this->objDbConn->mysqlRealEscape(trim($labelName));
        }

        return $this->organizeLabels($this->selectLabels()['data']);
    }

    public function getPrefixedLabels(string $prefix, string $languageId = 'en') {
        $this->languageId = $languageId;
        $this->labelName = $this->objDbConn->mysqlRealEscape(trim("{$prefix}%"));

        return $this->objectifyLabels($this->selectPrefixLabels()['data']);
    }

    #endregion
    #region setters
    public function setId(string $str) {
        $this->labelId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));;
    }

    public function setLabelName(string $str) {
        $this->labelName = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setLabelDescriptions(array $arr) {
        foreach ($arr as $key => $description) {
            $this->labelDescriptions[$key] = $this->objDbConn->mysqlRealEscape(trim($description));
        }
    }
    #endregion
    #region private methods
    private function objectifyLabels(array $data) {
        return array_combine(array_column($data, 'id'), array_column($data, 'description'));
    }

    private function organizeLabels($data, $withoutMissedLabels = false) {
        $labels = [];
        $labels = $this->objectifyLabels($data);

        if ($withoutMissedLabels) {
            return $labels;
        }

        $labelKeys = array_keys($labels);
        $missLabels = array_diff($this->labelNames, $labelKeys);
        foreach ($missLabels as $label) {
            $labels[$label] = "{$label} N/A";
        }

        return $labels;
    }
}
