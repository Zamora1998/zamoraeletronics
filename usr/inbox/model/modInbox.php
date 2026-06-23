<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class InboxModel
{
    #region general
    protected $objDbConn;
    protected $id           = 0;
    protected $languageId   = 'en';
    protected $messageId    = '';
    protected $mailaccountId = 0;
    protected $fromEmail    = '';
    protected $fromName     = '';
    protected $subject      = '';
    protected $body         = '';
    protected $receivedAt   = '';
    protected $read         = 0;
    protected $clienteId    = null;

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    public function reconnect()
    {
        require_once __ROOT__ . '/assets/php/libDbConn.php';
        $this->objDbConn = new dbConn();
    }
    #endregion

    #region selects
    public function selectAll()
    {
        $sql = "SELECT
                    i.id,
                    i.message_id,
                    i.mailaccount_id,
                    i.from_email,
                    i.from_name,
                    i.subject,
                    i.body,
                    i.received_at,
                    i.read,
                    i.cliente_id,
                    c.nombre    AS cliente_nombre,
                    c.cedula    AS cliente_cedula,
                    c.empresa   AS cliente_empresa
                FROM inbox i
                LEFT JOIN clientes c ON c.id = i.cliente_id
                ORDER BY i.received_at DESC";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectOne()
    {
        $sql = "SELECT
                    i.id,
                    i.message_id,
                    i.mailaccount_id,
                    i.from_email,
                    i.from_name,
                    i.subject,
                    i.body,
                    i.received_at,
                    i.read,
                    i.cliente_id,
                    c.nombre    AS cliente_nombre,
                    c.cedula    AS cliente_cedula,
                    c.empresa   AS cliente_empresa
                FROM inbox i
                LEFT JOIN clientes c ON c.id = i.cliente_id
                WHERE i.id = {$this->id}
                LIMIT 1";

        $result = $this->objDbConn->processQuery($sql);

        // Devolver el primer registro directamente
        if ($result['result'] && !empty($result['data'])) {
            $result['data'] = $result['data'][0];
        }

        return $result;
    }

    public function existsByMessageId(string $messageId): bool
    {
        $messageId = $this->objDbConn->mysqlRealEscape($messageId);

        $sql = "SELECT id FROM inbox
                WHERE message_id = '{$messageId}'
                LIMIT 1";

        $result = $this->objDbConn->processQuery($sql);

        return $result['result'] && !empty($result['data']);
    }

    public function findClienteByEmail(string $email)
    {
        $email = $this->objDbConn->mysqlRealEscape(trim($email));

        $sql = "SELECT id, nombre, cedula, empresa
                FROM clientes
                WHERE email  = '{$email}'
                   OR email2 = '{$email}'
                LIMIT 1";

        $result = $this->objDbConn->processQuery($sql);

        return ($result['result'] && !empty($result['data']))
            ? $result['data'][0]
            : null;
    }
    public function getAllClientEmails(): array
{
    $sql = "SELECT id, nombre, cedula, empresa, email, email2
            FROM clientes
            WHERE activo = 1
            AND (email != '' OR email2 != '')";

    $result = $this->objDbConn->processQuery($sql);
    if (!$result['result'] || empty($result['data'])) return [];

    // Construir mapa email → cliente para búsqueda O(1)
    $map = [];
    foreach ($result['data'] as $cliente) {
        if (!empty($cliente['email'])) {
            $map[strtolower(trim($cliente['email']))] = $cliente;
        }
        if (!empty($cliente['email2'])) {
            $map[strtolower(trim($cliente['email2']))] = $cliente;
        }
    }

    return $map;
}
    #endregion

    #region insert
    public function insert()
    {
        $clienteId = is_null($this->clienteId) ? 'NULL' : (int)$this->clienteId;

        $sql = "INSERT INTO inbox (
                    message_id,
                    mailaccount_id,
                    from_email,
                    from_name,
                    subject,
                    body,
                    received_at,
                    `read`,
                    cliente_id
                ) VALUES (
                    '{$this->messageId}',
                    {$this->mailaccountId},
                    '{$this->fromEmail}',
                    '{$this->fromName}',
                    '{$this->subject}',
                    '{$this->body}',
                    '{$this->receivedAt}',
                    {$this->read},
                    {$clienteId}
                )";

        return $this->objDbConn->processQuery($sql);
    }
    #endregion

    #region update
    public function markRead()
    {
        $sql = "UPDATE inbox
                SET `read` = 1
                WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }

    public function update()
    {
        $clienteId = is_null($this->clienteId) ? 'NULL' : (int)$this->clienteId;

        $sql = "UPDATE inbox
                SET
                    from_email     = '{$this->fromEmail}',
                    from_name      = '{$this->fromName}',
                    subject        = '{$this->subject}',
                    body           = '{$this->body}',
                    `read`         = {$this->read},
                    cliente_id     = {$clienteId}
                WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }
    #endregion

    #region delete
    public function delete()
    {
        $sql = "DELETE FROM inbox WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }
    #endregion

    #region setters
    public function setId(int $int)
    {
        $this->id            = $int;
    }
    public function setLanguageId(string $str)
    {
        $this->languageId    = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setMessageId(string $str)
    {
        $this->messageId     = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setMailaccountId(int $int)
    {
        $this->mailaccountId = $int;
    }
    public function setFromEmail(string $str)
    {
        $this->fromEmail     = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setFromName(string $str)
    {
        $this->fromName      = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setSubject(string $str)
    {
        $this->subject       = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setBody(string $str)
    {
        $this->body          = $this->objDbConn->mysqlRealEscape($str);
    }
    public function setReceivedAt(string $str)
    {
        $this->receivedAt    = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setRead(int $int)
    {
        $this->read          = $int ? 1 : 0;
    }
    public function setClienteId($val)
    {
        $this->clienteId     = is_null($val) ? null : (int)$val;
    }
    #endregion
}
