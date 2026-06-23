<?
class main {
    protected $objDbConn;
    protected $eventId = 0;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function select() {
        $result = false;
        $error = '';
        $data = array();
        $sql = "SELECT 
                    e.id,
                    e.name AS event_name,
                    e.description,
                    e.start_datetime,
                    e.end_datetime,
                    e.location,
                    e.distance_km,
                    e.max_participants,
                    e.registration_open,
                    e.latitude,
                    e.longitude,
                    e.registration_close,
                    e.status AS status_description,
                    e.created_at,
                    e.updated_at
                FROM events AS e
                WHERE e.id = {$this->eventId}
                ORDER BY e.start_datetime DESC";

        return $this->objDbConn->processQuery($sql);
    }

    public function setEventId(int $id) {
        $this->eventId = $id;
    }
}
