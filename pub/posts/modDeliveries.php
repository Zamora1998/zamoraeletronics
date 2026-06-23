<?
class deliveries {
    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = 0;
    protected $uuid = '';
    protected $vcard = '';
    protected $eventId = 0;
    protected $postId = 0;
    protected $contactId = 0;
    protected $deliverableId = 0;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectContact() {
        $result = false;
        $error = '';
        $data = array();
        $sql = "SELECT
                    visitors.id,
                    concat_ws(' ', contactpersons.firstname, contactpersons.lastname) AS fn,
                    contactcompanies.name
                FROM visitors
                    INNER JOIN events
                        ON visitors.event_id = events.id
                    INNER JOIN posts
                        ON posts.event_id = events.id
                    INNER JOIN contacts
                        ON visitors.contact_id = contacts.id
                    LEFT JOIN contactpersons
                        ON contactpersons.contact_id = contacts.id
                    LEFT JOIN contactcompanies
                        ON contactcompanies.contact_id = contacts.id
                WHERE events.id = {$this->eventId}
                    AND posts.id = {$this->postId}
                    AND visitors.vcard = '{$this->vcard}'
                    AND posts.uuid = '{$this->uuid}';";

        //file_put_contents(__ROOT__ . '/debugDelivery.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
            if(count($data)){
                $this->contactId=$data[0]['id'];
            }
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function selectDeliverables() {
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

    public function insert() {
        $result = false;
        $error = '';
        $data = [];

        $sql = "INSERT INTO deliveries (deliverable_id, contact_id, post_id)
                VALUES ({$this->deliverableId}, {$this->contactId}, {$this->postId});";

        //file_put_contents(__ROOT__ . '/debugOrg.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getAffectedRows();
        }
        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function setEventId(int $id) {
        $this->eventId = $id;
    }

    public function setPostId(int $id) {
        $this->postId = $id;
    }

    public function setContactId(int $id) {
        $this->contactId = $id;
    }

    public function setDeliverableId(int $id) {
        $this->deliverableId = $id;
    }

    public function setUUID(string $str) {
        $this->uuid = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setvCard(string $str) {
        $this->vcard = $this->objDbConn->mysqlRealEscape($str);
    }
}
