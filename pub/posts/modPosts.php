<?
class posts
{
    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = 0;
    protected $cedula = '';
    protected $uuid = '';

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function select()
    {

        $sql = "SELECT 
                    posts.event_id,
                    events.name AS event_name,
                    posts.id AS post_id,
                    posts.name AS post_name
                FROM posts
                    INNER JOIN events
                        ON posts.event_id = events.id
                WHERE posts.enabled = 1
                    AND events.status = 'initiated'
                    AND posts.uuid = '{$this->uuid}';";
        return $this->objDbConn->processQuery($sql);
    }

    public function createEntry()
    {
        // Fecha actual Costa Rica
        $datetimeCR = new DateTime("now", new DateTimeZone('America/Costa_Rica'));
        $registeredAt = $datetimeCR->format('Y-m-d H:i:s');

        $sql = "SELECT allow_multiple_scans
            FROM posts
            WHERE uuid = '{$this->uuid}'
            LIMIT 1";

        $postQuery = $this->objDbConn->processQuery($sql);

        // Si el puesto no existe → no bloquea, solo no inserta
        if (!$postQuery['result'] || empty($postQuery['data'])) {
            return [
                'result' => true,
                'error'  => null
            ];
        }

        $allowMultiple = (int)$postQuery['data'][0]['allow_multiple_scans'];


        $sql = "SELECT scanned_at
            FROM participant_post_scans
            WHERE idcard = '{$this->cedula}'
            AND post_uuid = '{$this->uuid}'
            ORDER BY scanned_at DESC
            LIMIT 1";

        $lastScanQuery = $this->objDbConn->processQuery($sql);

        if ($lastScanQuery['result'] && !empty($lastScanQuery['data'])) {

            // Ya existe un escaneo previo
            if ($allowMultiple === 0) {
                // Solo uno permitido → no insertar, pero es OK
                return [
                    'result' => true,
                    'error'  => null
                ];
            }

            $lastTime = strtotime($lastScanQuery['data'][0]['scanned_at']);
            $now = strtotime($registeredAt);

            if (($now - $lastTime) < 300) {
                return [
                    'result' => true,
                    'error'  => null
                ];
            }
        }

        /* ===============================
       3️⃣ Insertar escaneo
    =============================== */
        $sql = "INSERT INTO participant_post_scans (idcard, post_uuid, scanned_at)
            VALUES ('{$this->cedula}', '{$this->uuid}', '$registeredAt')";

        $insert = $this->objDbConn->processQuery($sql);

        if ($insert['result']) {
            return [
                'result' => true,
                'error'  => null
            ];
        }

        return [
            'result' => false,
            'error'  => $insert['error'] ?? 'Error al insertar escaneo'
        ];
    }


    public function selectDeliverables()
    {
        $result = false;
        $error = '';
        $data = array();
        $sql = "SELECT 
                    deliverables.id,
                    deliverables.name
                FROM posts
                    INNER JOIN post_deliverables
                        ON posts.id = post_id
                    INNER JOIN deliverables
                        ON deliverable_id = deliverables.id
                WHERE posts.enabled = 1
                    AND deliverables.enabled = 1
                    AND posts.uuid = '{$this->uuid}';";

        //file_put_contents(__ROOT__ . '/debugOrg.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function setUUID(string $str)
    {
        $this->uuid = $this->objDbConn->mysqlRealEscape($str);
    }
    public function setCedula(int $cedula)
    {
        $this->cedula = $cedula;
    }
}
