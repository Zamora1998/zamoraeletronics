<?php

class mailComposer {
    #region General
    protected $objDbConn;
    protected $id;
    protected $userId = 0;
    protected $languageId = 'en';
    protected $params = [];
    protected $tableData = [];
    protected $tzOffset = 0;

    function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    #endregion
    #region Selects
    public function select(): array {
        return $this->compose();
    }

    private function selectDateCatalog(): array {
        $return = [];
        $utcDate = new DateTime("now", new DateTimeZone('UTC'));
        $modified = clone $utcDate;
        $modified->modify("-{$this->tzOffset} minutes");

        $return = [
            '{date.medium}' => $modified->format('d-M-Y'),
            '{date.mediumtime}' => $modified->format('d-M-Y H:i'),
        ];
        $this->params = array_merge($this->params, $return);

        return $return;
    }

    private function selectUserCatalog() {
        $sql = "SELECT
                    first AS `{user.firstname}`,
                    last AS `{user.lastname}`,
                    CONCAT(first, ' ', last) AS `{user.fullname}`,
                    email AS `{user.email}`
                FROM users
                WHERE id = {$this->userId}
                LIMIT 1;";

        $return = $this->objDbConn->processQuery($sql);

        if (array_key_exists('data', $return)) {
            $this->params = array_merge($this->params, $return['data'][0]);
        }

        return $return;
    }

    function selectTemplate(): array {
        $sql = "SELECT
                    mailtemplates.id,
                    mailtemplates.name,
                    COALESCE(labeldet.description, labeldeten.description, subject_label) AS subject,
                    body,
                    altbody,
                    mailaccount_id
                FROM mailtemplates
                LEFT JOIN labels
                        ON labels.name = subject_label
                    LEFT JOIN (
                        SELECT
                            labeldetails.label_id,
                            labeldetails.description
                        FROM labeldetails
                        WHERE labeldetails.language_id = '{$this->languageId}'
                    ) labeldet
                        ON labeldet.label_id = labels.id
                    LEFT JOIN (
                        SELECT
                            labeldetails.label_id,
                            labeldetails.description
                        FROM labeldetails
                        WHERE labeldetails.language_id = 1
                    ) labeldeten
                        ON labeldeten.label_id = labels.id
                WHERE mailtemplates.id = {$this->id}
                LIMIT 1;";

        return $this->objDbConn->processQuery($sql);
    }

    function selectVariables(): array {
        $sql = "SELECT
                    variable,
                    coalesce(labeldet.description, labeldeten.description) AS value
                FROM mailtemplatevariables
                    LEFT JOIN labels
                        ON labels.name = label_name
                    LEFT JOIN (
                        SELECT
                            labeldetails.label_id,
                            labeldetails.description
                        FROM labeldetails
                        WHERE labeldetails.language_id = '{$this->languageId}'
                    ) labeldet
                        ON labeldet.label_id = labels.id
                    LEFT JOIN (
                        SELECT
                            labeldetails.label_id,
                            labeldetails.description
                        FROM labeldetails
                        WHERE labeldetails.language_id = 1
                    ) labeldeten
                        ON labeldeten.label_id = labels.id
                WHERE mailtemplate_id = {$this->id}
                ORDER BY position;";

        return $this->objDbConn->processQuery($sql);
    }
    #endregion
    #region Methods
    function compose(): array {
        $results = [];
        $errors = [];

        $result = $this->selectTemplate();
        $results[] = $result['result'];
        if (!$result['result']) {
            $errors[] = $result['error'];
        }

        if ($result['result'] && !empty($result['data'])) {
            $template = $result['data'][0];

            $variables = [];
            $result = $this->selectVariables();
            $results[] = $result['result'];
            if (!$result['result']) {
                $errors[] = $result['error'];
            } elseif (!empty($result['data'])) {
                $variables = array_combine(array_column($result['data'], 'variable'), array_column($result['data'], 'value'));
            }

            //Merge variables and parameters
            $this->selectUserCatalog();
            $this->selectDateCatalog();
            $variables = array_merge($variables, $this->params);

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $template['body'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            // Process dynamic tables
            $tables = $dom->getElementsByTagName('table');
            foreach ($tables as $table) {
                if ($table->hasAttribute('data-values')) {
                    $dataKey = $table->getAttribute('data-values');
                    $tableData = $this->tableData[$dataKey] ?? [];
                    unset($this->tableData[$dataKey]);
                    $tbody = $table->getElementsByTagName('tbody')->item(0);
                    $templateRow = $tbody->getElementsByTagName('tr')->item(0);
                    if ($templateRow) {
                        while ($tbody->hasChildNodes()) {
                            $tbody->removeChild($tbody->firstChild);
                        }
                        foreach ($tableData as $rowData) {
                            $newRow = $templateRow->cloneNode(true);
                            foreach ($newRow->getElementsByTagName('td') as $cell) {
                                preg_match('/\[(.*?)\]/', $cell->textContent, $matches);
                                if (!empty($matches[1]) && isset($rowData[$matches[1]])) {
                                    $cell->textContent = $rowData[$matches[1]];
                                }
                            }
                            $tbody->appendChild($newRow);
                        }
                    }
                }
            }
            $template['body'] = str_replace(['%7B', '%7D'], ['{', '}'], $dom->saveHTML());

            //Insert variables and parameters into body and altbody
            $vars = array_keys($variables);
            $vals = array_values($variables);
            $htmlvals = preg_replace('/[\r\n]+/', '</br>', $vals);
            $template['body'] = str_replace($vars, $vals, $template['body']);
            $template['altbody'] = str_replace($vars, $vals, $template['altbody']);
            $template['subject'] = str_replace($vars, $vals, $template['subject']);
        } else {
            $results[] = false;
            $errors[] = 'no template';
            $template['subject'] = '';
            $template['body'] = '';
            $template['altbody'] = '';
            $template['mailaccount_id'] = 0;
        }

        $json = [
            'result' => !in_array(false, $results, true),
            'errors' => $errors,
            'subject' => $template['subject'],
            'body' => $template['body'],
            'altbody' => $template['altbody'],
            'mailaccount_id' => $template['mailaccount_id'] ?? 0
        ];
        $json['result'] = !in_array(false, $results, true) && empty($attachmentErrors);

        return $json;
    }
    #endregion
    #region Setters
    public function setId(int $int) {
        $this->id = $int;
    }

    public function setUserId(int $int) {
        $this->userId = $int;
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setParameters(array $arr) {
        $this->params = $arr;
    }

    public function setTableData(array $arr) {
        $this->tableData = $arr;
    }

    public function setTimezoneOffset(int $int) {
        $this->tzOffset = $int;
    }
}
