<?php
class mailQueue {
    #region general
    protected $objDbConn;
    protected $mailId = 0;
    protected $mailAccount = 0;
    protected $subject = '';
    protected $body = '';
    protected $altBody = '';
    protected $fromName = '';
    protected $toEmails = [];
    protected $toNames = [];
    protected $ccEmails = [];
    protected $ccNames = [];
    protected $bccEmails = [];
    protected $bccNames = [];
    protected $replyToEmails = [];
    protected $replyToNames = [];
    protected $attNames = [];
    protected $attFiles = [];
    protected $templateId = 0;
    protected $folder = '';
    protected $attachments = [];

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    #endregion
    #region selects
    
    public function select(int $limit = 50, int $maxAttempts = 5): array
    {
        $sql = "SELECT 
                mailqueue.id,
                mailqueue.fromname,
                mailqueue.subject,
                mailqueue.body,
                mailqueue.altbody,
                mailaccounts.protocol,
                mailaccounts.smtpauth,
                mailaccounts.port,
                mailaccounts.smtpsecure,
                mailaccounts.username,
                mailaccounts.password,
                mailaccounts.host,
                mailaccounts.debug,
                mailaccounts.oauth,
                mailaccounts.oauth_type,
                mailaccounts.oauth_client_id,
                mailaccounts.oauth_client_secret,
                mailaccounts.oauth_refresh_token,
                GROUP_CONCAT(mailaddresses.mailaddresstype_id ORDER BY mailaddresses.id SEPARATOR '|') AS addresstype_ids,
                GROUP_CONCAT(mailaddresses.address ORDER BY mailaddresses.id SEPARATOR '|') AS addresses,
                GROUP_CONCAT(mailaddresses.name ORDER BY mailaddresses.id SEPARATOR '|') AS names,
                attFiles,
                attNames
            FROM mailqueue
                INNER JOIN mailaccounts
                    ON mailqueue.mailaccount_id = mailaccounts.id
                INNER JOIN mailaddresses
                    ON mailaddresses.mailqueue_id = mailqueue.id
                LEFT JOIN (
                    SELECT
                        mailqueue_id,
                        GROUP_CONCAT(filename ORDER BY id SEPARATOR '|') AS attFiles,
                        GROUP_CONCAT(name ORDER BY id SEPARATOR '|') AS attNames
                    FROM mailattachments
                    GROUP BY mailqueue_id
                ) attachments
                    ON attachments.mailqueue_id = mailqueue.id
            WHERE mailqueue.sent IS NULL
                AND mailqueue.attempts < {$maxAttempts}
            GROUP BY mailqueue.id,
                mailqueue.fromname,
                mailqueue.subject,
                mailqueue.body,
                mailqueue.altbody,
                mailaccounts.protocol,
                mailaccounts.smtpauth,
                mailaccounts.port,
                mailaccounts.smtpsecure,
                mailaccounts.username,
                mailaccounts.password,
                mailaccounts.host,
                mailaccounts.oauth,
                mailaccounts.oauth_type,
                mailaccounts.oauth_client_id,
                mailaccounts.oauth_client_secret,
                mailaccounts.oauth_refresh_token
            ORDER BY mailqueue.id
            LIMIT {$limit}";

        return $this->objDbConn->processQuery($sql);
    }
    public function selectMail(): array
    {
        $return = [];
        $sql = "WITH REPLYS AS (
                    SELECT
                        mad.mailqueue_id,
                        mad.address AS replyaddress
                    FROM mailaddresses mad
                    INNER JOIN (
                        SELECT
                            mailqueue_id,
                            MIN(id) AS min_id
                        FROM mailaddresses
                        GROUP BY mailqueue_id
                    ) ad1
                        ON mad.mailqueue_id = ad1.mailqueue_id
                            AND mad.id = ad1.min_id
                ),
                TOS AS (
                    SELECT
                        mad.mailqueue_id,
                        GROUP_CONCAT(mad.name ORDER BY mad.id SEPARATOR '|') AS tonames,
                        GROUP_CONCAT(mad.address ORDER BY mad.id SEPARATOR '|') AS toaddresses
                    FROM mailaddresses mad
                        INNER JOIN mailaddresstypes mat
                            ON mat.id = mad.mailaddresstype_id
                    WHERE mat.id = 2
                    GROUP BY mad.mailqueue_id
                ),
                CCS AS (
                    SELECT
                        mad.mailqueue_id,
                        GROUP_CONCAT(mad.name ORDER BY mad.id SEPARATOR '|') AS ccnames,
                        GROUP_CONCAT(mad.address ORDER BY mad.id SEPARATOR '|') AS ccaddresses
                    FROM mailaddresses mad
                        INNER JOIN mailaddresstypes mat
                            ON mat.id = mad.mailaddresstype_id
                    WHERE mat.id = 3
                    GROUP BY mad.mailqueue_id
                ),
                BCCS AS (
                    SELECT
                        mad.mailqueue_id,
                        GROUP_CONCAT(mad.name ORDER BY mad.id SEPARATOR '|') AS bccnames,
                        GROUP_CONCAT(mad.address ORDER BY mad.id SEPARATOR '|') AS bccaddresses
                    FROM mailaddresses mad
                        INNER JOIN mailaddresstypes mat
                            ON mat.id = mad.mailaddresstype_id
                    WHERE mat.id = 4
                    GROUP BY mad.mailqueue_id
                ),
                ATTACHMENTS AS (
                    SELECT
                        mailqueue_id,
                        GROUP_CONCAT(filename ORDER BY id SEPARATOR '|') AS attfiles,
                        GROUP_CONCAT(name ORDER BY id SEPARATOR '|') AS attnames
                    FROM mailattachments
                    GROUP BY mailqueue_id
                )

                SELECT
                    mq.id,
                    mq.fromname,
                    mq.subject,
                    mq.body,
                    mq.altbody,
                    mq.created,
                    mq.sent,
                    COALESCE(mq.sent, mq.created) AS maildate,
                    ma.username AS fromaddress,
                    replyaddress,
                    tonames,
                    toaddresses,
                    ccnames,
                    ccaddresses,
                    bccnames,
                    bccaddresses,
                    attnames,
                    attfiles,
                    CASE 
                        WHEN mq.sent IS NOT NULL THEN 'check-circle'
                        WHEN mq.attempts BETWEEN 0 AND 1 THEN 'clock'
                        WHEN mq.attempts BETWEEN 2 AND 4 THEN 'clock'
                        WHEN mq.attempts >= 5 THEN 'exclamation-circle'
                        ELSE 'default'
                    END AS icon,
                    CASE 
                        WHEN mq.sent IS NOT NULL THEN 'text-success'
                        WHEN mq.attempts BETWEEN 0 AND 1 THEN 'text-secondary'
                        WHEN mq.attempts BETWEEN 2 AND 4 THEN 'text-warning'
                        WHEN mq.attempts >= 5 THEN 'text-danger'
                        ELSE 'bg-light'
                    END AS class,
                    CASE 
                        WHEN mq.sent IS NOT NULL THEN 'lblSent'
                        WHEN mq.attempts BETWEEN 0 AND 4 THEN 'lblQueued'
                        WHEN mq.attempts >= 5 THEN 'lblFailed'
                    END AS title
                FROM mailqueue mq
                    INNER JOIN mailaccounts ma
                        ON ma.id = mq.mailaccount_id
                    LEFT JOIN REPLYS
                        ON REPLYS.mailqueue_id = mq.id
                    LEFT JOIN TOS
                        ON TOS.mailqueue_id = mq.id
                    LEFT JOIN CCS
                        ON CCS.mailqueue_id = mq.id
                    LEFT JOIN BCCS
                        ON BCCS.mailqueue_id = mq.id
                    LEFT JOIN ATTACHMENTS
                        ON ATTACHMENTS.mailqueue_id = mq.id
                WHERE mq.id = {$this->mailId};";

        $result = $this->objDbConn->processQuery($sql);
        $return['result'] = $result['result'];
        if (!$result['result']) {
            $return['error'] = $result['error'];
        } else {
            $data = $result['data'][0];
            //print_r($data);
            $return['data']['id'] = $data['id'];
            $return['data']['fromname'] = $data['fromname'];
            $return['data']['subject'] = $data['subject'];
            $return['data']['body'] = $data['body'];
            $return['data']['altbody'] = $data['altbody'];
            $return['data']['created'] = $data['created'];
            $return['data']['sent'] = $data['sent'];
            $return['data']['maildate'] = $data['maildate'];
            $return['data']['fromaddress'] = $data['fromaddress'];
            $return['data']['replyaddress'] = $data['replyaddress'];
            $return['data']['icon'] = $data['icon'];
            $return['data']['class'] = $data['class'];
            $return['data']['title'] = $data['title'];

            //Tos
            $toAddresses = explode('|', $data['toaddresses'] ?? '');
            $toNames = explode('|', $data['tonames'] ?? '');
            $return['data']['mailto'] = [];

            if (!empty($toAddresses) and $toAddresses[0]) {
                foreach ($toAddresses as $key => $toAddress) {
                    if (isset($toAddress)) {
                        $return['data']['mailto'][$key]['address'] = $toAddress;
                        $return['data']['mailto'][$key]['name'] = $toNames[$key];
                    }
                }
            }

            //CCs
            $ccAddresses = explode('|', $data['ccaddresses'] ?? '');
            $ccNames = explode('|', $data['ccnames'] ?? '');
            $return['data']['mailcc'] = [];

            if (!empty($ccAddresses) and $ccAddresses[0]) {
                foreach ($ccAddresses as $key => $ccAddress) {
                    if (isset($ccAddress)) {
                        $return['data']['mailcc'][$key]['address'] = $ccAddress;
                        $return['data']['mailcc'][$key]['name'] = $ccNames[$key];
                    }
                }
            }

            //BCCs
            $bccAddresses = explode('|', $data['bccaddresses'] ?? '');
            $bccNames = explode('|', $data['bccnames'] ?? '');
            $return['data']['mailbcc'] = [];

            if (!empty($bccAddresses) and $bccAddresses[0]) {
                foreach ($bccAddresses as $key => $bccAddress) {
                    if (isset($bccAddress)) {
                        $return['data']['mailbcc'][$key]['address'] = $bccAddress;
                        $return['data']['mailbcc'][$key]['name'] = $bccNames[$key];
                    }
                }
            }

            //Attachments
            $attFiles = explode('|', $data['attfiles'] ?? '');
            $attNames = explode('|', $data['attnames'] ?? '');
            $return['data']['attachments'] = [];

            if (!empty($attFiles) and $attFiles[0]) {
                foreach ($attFiles as $key => $attFile) {
                    if (isset($attFile)) {
                        $return['data']['attachments'][$key]['file'] = $attFile;
                        $return['data']['attachments'][$key]['name'] = $attNames[$key];
                        $fdata = '';
                        if (file_exists(__ROOT__ . $attFile)) {
                            $fileData = file_get_contents(__ROOT__ . $attFile);
                            $fdata = base64_encode($fileData);
                        }
                        $return['data']['attachments'][$key]['data'] = $fdata;
                        $return['data']['attachments'][$key]['size'] = strlen($fdata);
                        // $return['data']['attachments'][$key]['type'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    }
                }
            }
        }

        return $return;
    }
    #endregion
    #region inserts
    public function insert() {
        $result = array();
        $errors = array();

        if (!empty($this->toEmails)) {
            $this->objDbConn->resetAI('mailqueue');
            $sql = "INSERT INTO mailqueue(fromname, subject, body, altbody, mailaccount_id) 
                VALUES ('$this->fromName','$this->subject','$this->body','$this->altBody',$this->mailAccount);\n";

            //file_put_contents(__ROOT__ . '/debugMailQueue.txt', var_export($sql, true));
            $result = $this->objDbConn->processQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
            }

            $this->mailId = $this->objDbConn->getLastId();
            $values = array();
            foreach ($this->replyToEmails as $key => $email) {
                $name = '';
                if (isset($this->replyToNames[$key])) {
                    $name = $this->replyToNames[$key];
                }
                $values[] = "({$this->mailId}, 1,'{$email}', '{$name}')";
            }

            foreach ($this->toEmails as $key => $email) {
                $name = '';
                if (isset($this->toNames[$key])) {
                    $name = $this->toNames[$key];
                }
                $values[] = "({$this->mailId}, 2,'{$email}', '{$name}')";
            }

            foreach ($this->ccEmails as $key => $email) {
                $name = '';
                if (isset($this->ccNames[$key])) {
                    $name = $this->ccNames[$key];
                }
                $values[] = "({$this->mailId}, 3,'{$email}', '{$name}')";
            }

            foreach ($this->bccEmails as $key => $email) {
                $name = '';
                if (isset($this->bccNames[$key])) {
                    $name = $this->bccNames[$key];
                }
                $values[] = "({$this->mailId}, 4,'{$email}', '{$name}')";
            }

            if (!empty($values)) {
                $this->objDbConn->resetAI('mailaddresses');
                $sql = "INSERT INTO mailaddresses(mailqueue_id, mailaddresstype_id, address, name) 
                    VALUES " . implode(",\n", $values) . ";";

                //file_put_contents(__ROOT__ . '/debugMailQueue.txt', var_export($sql, true));
                $result = $this->objDbConn->processQuery($sql);
                $results[] = $result;
                if (!$result) {
                    $errors[] = $this->objDbConn->getError();
                }
            }

            //Attachments
            $values = [];
            foreach ($this->attFiles as $key => $attFile) {
                $values[] = "({$this->mailId}, '{$attFile}', '{$this->attNames[$key]}')";
            }

            if (!empty($values)) {
                $sql = "INSERT 
                    INTO mailattachments (
                        mailqueue_id,
                        filename,
                        name
                    ) VALUES\n" . implode(",\n", $values) . ";";

                $this->objDbConn->resetAI('mailattachments');
                $result = $this->objDbConn->processQuery($sql);
                $results[] = $result;
                if (!$result) {
                    $errors[] = $this->objDbConn->getError();
                }
            }

            return array('result' => !in_array(false, $results, true), 'errors' => $errors, 'mailId' => $this->mailId);
        } else {
            return array('result' => false, 'errors' => 'No recipient email provided!', 'mailId' => $this->mailId);
        }
    }
    #endregion
    #region updates
    public function updateSent($id) {
        $result = false;
        $error = '';
        $sql = "UPDATE mailqueue 
                SET sent = CURRENT_TIMESTAMP(),
                    message = null
                WHERE id = {$id};";

        return $this->objDbConn->processQuery($sql);
    }

    public function updateAttempt($id, $message) {
        if ($message) {
            $message = "'" . $this->objDbConn->mysqlRealEscape($message) . "'";
        } else {
            $message = 'null';
        }
        $sql = "UPDATE mailqueue 
                SET attempts = attempts + 1,
                    message = {$message}
                WHERE id = {$id};";

        return $this->objDbConn->processQuery($sql);
    }
    #endregion
    #region gets
    function getMailId() {
        return $this->mailId;
    }

    function getAttachments(): array {
        return $this->attachments;
    }
    #endregion
    #region sets
    function set_MailId(int $int) {
        $this->mailId = $int;
    }

    function set_Maileraccount(int $int) {
        $this->mailAccount = $int;
    }

    function set_Subject(string $str) {
        $this->subject = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    function set_Body(string $str) {
        $this->body = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    function set_Altbody(string $str)
    {
        $this->altBody = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    function set_Fromname(string $str) {
        $this->fromName = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    function setTo(string $email, string $name = '') {
        $this->toEmails[] = $email;
        $this->toNames[] = $name;
    }

    function setTos(array $arrEmails, array $arrNames = []) {
        $this->toEmails = $arrEmails;
        $this->toNames = $arrNames;
    }

    function setCc(string $email, string $name = '') {
        $this->ccEmails[] = $email;
        $this->ccNames[] = $name;
    }

    function setCcs(array $arrEmails, array $arrNames = []) {
        $this->ccEmails = $arrEmails;
        $this->ccNames = $arrNames;
    }
    function setBcc(string $email, string $name = '') {
        $this->bccEmails[] = $email;
        $this->bccNames[] = $name;
    }

    function setBccs(array $arrEmails, array $arrNames = []) {
        $this->bccEmails = $arrEmails;
        $this->bccNames = $arrNames;
    }
    function setReplyTo(string $email, string $name = '') {
        $this->replyToEmails[] = $email;
        $this->replyToNames[] = $name;
    }

    function setReplyTos(array $arrEmails, array $arrNames = [])
    {
        $this->replyToEmails = $arrEmails;
        $this->replyToNames = $arrNames;
    }

    function setType(int $mailaccount) {
        $this->mailAccount = $mailaccount;
    }

    function setAttachment(string $strFile, string $strName = '') {
        $this->attFiles[] = $strFile;
        if (!$strName) {
            $arrFile = explode('/', $strFile);
            $strName = end($arrFile);
        }
        $this->attNames[] = $strName;
    }

    function setAttachments(array $arrFiles, array $arrName = []) {
        $this->attFiles = [];
        $this->attNames = [];

        foreach ($arrFiles as $key => $file) {
            $name = $arrName[$key] ?? ''; //todo use filename
            $this->setAttachment($file, $name);
        }
    }

    function setTemplateId(int $id) {
        $this->templateId = $id;
    }
}
