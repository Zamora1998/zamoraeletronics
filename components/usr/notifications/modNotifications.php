s<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class notifications {
    use globalCte;
    #region globals
    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = '';
    protected $userId = 0;
    protected $userIds = [];
    protected $title = "''";
    protected $message = "''";
    protected $link = 'null';
    protected $url = 'null';
    protected $icon = 'null';
    protected $type = 'null';

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    #end region
    #region selects
    public function select(): array {
        $sql = "WITH {$this->cteLabels()}
                SELECT
                    nt.id,
                    COALESCE(l1.name, CONCAT_WS(' ', title_label, ' N/A')) AS title,
                    COALESCE(l2.name, CONCAT_WS(' ', message_label, ' N/A')) AS message,
                    COALESCE(l3.name, CONCAT_WS(' ', link_label, ' N/A')) AS link,
                    linkurl,
                    icon,
                    `type`,
                    created,
                    DATE_FORMAT(created, '%Y-%m-%dT%TZ') AS created_iso,
                    delivered,
                    viewed,
                    deleted,
                    user_id
                FROM not__notifications nt
                    LEFT JOIN LABELS l1
                        ON nt.title_label = l1.id
                    LEFT JOIN LABELS l2
                        ON nt.message_label  = l2.id
                    LEFT JOIN LABELS l3
                        ON nt.link_label  = l3.id
                WHERE user_id = {$this->userId}
                    AND deleted IS NULL
                ORDER BY created;";

        $return = $this->objDbConn->processQuery($sql);
        $return['new'] = count(array_filter(array_column($return['data'], 'viewed'), 'is_null'));
        if ($return['new'] < 1) {
            $return['new'] = '';
        }

        return $return;
    }
    #end region
    #region inserts
    private function insert(): array {
        $values = [];
        $return = [];
        foreach ($this->userIds as $userId) {
            $values[] = "(
                    {$this->title},
                    {$this->message},
                    {$this->link},
                    {$this->url},
                    {$this->icon},
                    {$this->type},
                    {$userId}
                )";
        }

        if (empty($values)) {
            $return = ['result' => false, 'error' => 'No data.'];
        } else {
            $sql = "INSERT INTO not__notifications (
                    title_label,
                    message_label,
                    link_label,
                    linkurl,
                    icon,
                    `type`,
                    user_id
                ) " . implode(",\n", $values) . ";";

            $return = $this->objDbConn->processQuery($sql);
            if ($return['result']) {
                $return['id'] = $this->objDbConn->getLastId();
            }
        }
        return $return;
    }
    #end region
    #region updates
    private function updateDelivered(): array {
        $sql = "UPDATE not__notifications
                SET delivered = current_timestamp()
                WHERE id = {$this->id}
                    AND user_id = {$this->userId};";

        return $this->objDbConn->processQuery($sql);
    }

    public function updateRead(): array {
        $sql = "UPDATE not__notifications
                SET viewed = current_timestamp()
                WHERE id = {$this->id}
                    AND user_id = {$this->userId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            return $this->select();
        } else {
            return $result;
        }
    }

    public function updateUnread(): array {
        $sql = "UPDATE not__notifications
                SET viewed = NULL
                WHERE id = {$this->id}
                    AND user_id = {$this->userId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            return $this->select();
        } else {
            return $result;
        }
    }

    public function updateDeleted(): array {
        $sql = "UPDATE not__notifications
                SET deleted = current_timestamp()
                WHERE id = {$this->id}
                    AND user_id = {$this->userId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            return $this->select();
        } else {
            return $result;
        }
    }

    public function updateUndeleted(): array {
        $sql = "UPDATE not__notifications
                SET deleted = NULL
                WHERE id = {$this->id}
                    AND user_id = {$this->userId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            return $this->select();
        } else {
            return $result;
        }
    }
    //region end
    //region sets
    public function setId(int $int) {
        $this->id = $int;
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));;
    }

    public function setUserId(int $int) {
        $this->userId = $int;
    }

    public function setUserIds(array $arr) {
        $this->userIds = $arr;
    }

    public function setTitleLabel(string $str) {
        $this->title = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
    }

    public function setMessageLabel(string $str) {
        $this->message = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
    }

    public function setLinkLabel(string $str) {
        if ($str) {
            $this->link = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
        } else {
            $this->link = "null";
        }
    }

    public function setUrl(string $str) {
        if ($str) {
            $this->url = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
        } else {
            $this->url = "null";
        }
    }

    public function setIcon(string $str) {
        if ($str) {
            $this->icon = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
        } else {
            $this->icon = "null";
        }
    }

    public function setType(string $str) {
        if ($str) {
            $this->type = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
        } else {
            $this->type = "null";
        }
    }
}
