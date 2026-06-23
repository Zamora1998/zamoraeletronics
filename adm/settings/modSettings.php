<?php

class settings {

    protected $objDbConn;
    protected $key = "";
    protected $keys = [];
    protected $salt = '';
    protected $value = "";
    protected $type = "";

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectAll(): array {
        $sql = "SELECT
                    `key`,
                    `value`,
                    `type`
                FROM settings;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectSetting($key): array {
        $sql = "SELECT
                    `key`,
                    `value`,
                    `type`
                FROM settings
                WHERE `key` IN ('{$key}')";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectAllDecrypt(): array {
        $data = [];
        $sql = "SELECT
                    `key`,
                    `value`,
                    `type`
                FROM settings;";

        $return = $this->objDbConn->processQuery($sql);
        if ($return['result']) {
            $rows = $return['data'];
            foreach ($rows as $rec) {
                if ($rec['key'] == 'BCCR_Token') {
                    $data[$rec['key']] = trim($this->decrypt($rec['value']));
                } else {
                    $data[$rec['key']] = $rec['value'];
                }
            }
            $return['data'] = $data;
        }

        return $return;
    }

    public function selectObject() {
        $result = $this->selectAll();
        $data = array_combine(array_column($result['data'], 'key'), array_column($result['data'], 'value'));

        return array('result' => $result['result'], 'error' => $result['error'], 'data' => $data);
    }

    public function selectSettings() {
        $return = ['result' => true, 'data' => []];
        if (count($this->keys)) {
            $keys = implode("', '", $this->keys);
            $sql = "SELECT
                        `key`,
                        `value`,
                        type
                    FROM settings
                    WHERE `key` IN ('{$keys}');";

            $return = $this->objDbConn->processQuery($sql);
            if ($return['result']) {
                $data = $return['data'];;
                foreach ($data as $key => $rec) {
                    if ($rec['type'] == 'P') {
                        $data[$key]['value'] = trim($this->decrypt($rec['value']));
                    }
                }
                $return['data'] = $data;
            }
        }

        return $return;
    }

    public function select() {
        $sql = "SELECT
                    `key`,
                    `value`,
                    `type` AS type
                FROM settings\n";
        if (!empty($this->key)) {
            $sql .= "WHERE `key` LIKE '{$this->key}';";
        }

        $return = $this->objDbConn->processQuery($sql);
        if ($return['result']) {
            $data = $return['data'];;
            foreach ($data as $key => $rec) {
                if ($rec['type'] == 'P') {
                    $data[$key]['value'] = trim($this->decrypt($rec['value']));
                }
            }
            $return['data'] = $data;
        }

        return $return;
    }

    public function insert() {
        $this->objDbConn->resetAI('settings');
        $sql = "INSERT INTO settings (`key`, `value`, `type`)
                VALUES ('{$this->key}','{$this->value}','{$this->type}')
                ON DUPLICATE KEY UPDATE `value` = VALUES (`value`);";

        $this->objDbConn->resetAI('settings');
        return $this->objDbConn->processQuery($sql);
    }

    public function updateValue() {
        $sql = "UPDATE settings
                SET `value` = ''
                WHERE `key` LIKE '{$this->key}';";

        return $this->objDbConn->processQuery($sql);
    }

    public function delete() {
        $sql = "DELETE FROM settings 
                WHERE `key` LIKE '{$this->key}';";

        return $this->objDbConn->processQuery($sql);
    }

    public function getSettings(array $keys) {
        $this->keys = [];
        foreach ($keys as $key) {
            $this->keys[] = $this->objDbConn->mysqlRealEscape(trim($key));
        }

        return $this->organizeSettings($this->selectSettings()['data']);
    }

    public function setKey(string $str) {
        $this->key = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setValue(string $str) {
        if ($this->type == 'P') {
            $this->value = $this->objDbConn->mysqlRealEscape($this->encrypt(trim($str)));
        } else {
            $this->value = $this->objDbConn->mysqlRealEscape(trim($str));
        }
    }

    public function setType(string $str) {
        $this->type = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    private function objectifySettings(array $data) {
        return array_combine(array_column($data, 'key'), array_column($data, 'value'));
    }

    private function organizeSettings($data) {
        $settings = [];
        $settings = $this->objectifySettings($data);

        $dataSettings = array_keys($settings);
        $missSettings = array_diff($this->keys, $dataSettings);
        foreach ($missSettings as $setting) {
            $settings[$setting] = "{$setting} N/A";
        }

        return $settings;
    }

    private function decrypt(string $str) {
        $objCrypt = new Crypt();

        return trim($objCrypt->decrypt($this->salt, $str));
    }

    private function encrypt(string $str) {
        $objCrypt = new Crypt();

        return trim($objCrypt->encrypt($this->salt, $str));
    }
}
