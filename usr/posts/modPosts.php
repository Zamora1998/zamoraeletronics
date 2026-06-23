<?
class posts
{
    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = 0;
    protected $eventId = 0;
    //protected $selUser = 0;
    protected $name = '';
    protected $start = '';
    protected $end = '';
    protected $enabled = 0;

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
                posts.id,
                posts.name,
                posts.uuid,
                posts.enabled,
                posts.event_id,
                posts.Mark,
                GROUP_CONCAT(FORMAT(eve.distance_km, 0) ORDER BY eve.distance_km ASC) AS distances,
                posts.allow_multiple_scans
            FROM posts
            LEFT JOIN event_route_checkpoints erc
                ON erc.post_id = posts.id
            LEFT JOIN events_routes eve
                ON eve.id = erc.route_id
            GROUP BY 
                posts.id,
                posts.name,
                posts.uuid,
                posts.enabled,
                posts.event_id
            ORDER BY posts.id ASC;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectDistances()
    {
        $sql = "SELECT 
                r.id AS Id,
            CONCAT(r.name, ' ', CAST(r.distance_km AS INT), ' km') AS text
            FROM events_routes r
            LEFT JOIN events e ON r.event_id = e.id
            WHERE e.status = 'published';";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectDistancesByPost()
    {
        $sql = "SELECT
                p.id AS PostId,
                GROUP_CONCAT(r.id ORDER BY r.distance_km ASC SEPARATOR ' - ') AS RouteIds,
                GROUP_CONCAT(CAST(r.distance_km AS UNSIGNED) ORDER BY r.distance_km ASC SEPARATOR ' - ') AS DistancesKm
            FROM posts p
            LEFT JOIN event_route_checkpoints erc
                ON erc.post_id = p.id
            LEFT JOIN events_routes r
                ON r.id = erc.route_id
            WHERE p.id = '{$this->id}'
            GROUP BY p.id;
    ";

        return $this->objDbConn->processQuery($sql);
    }



    public function selectPost()
    {

        $sql = "SELECT id,
                    name,
                    enabled,
                    event_id,
                    allow_multiple_scans
                FROM posts
                WHERE event_id = {$this->eventId}
                    AND id = {$this->id}
                ORDER BY name;";

        return $this->objDbConn->processQuery($sql);
    }

    public function insert()
    {
        $this->objDbConn->applyQuery("call reset_ai('posts');");


        $sql = "INSERT INTO posts
                    (name, enabled, event_id)
                VALUES ('{$this->name}', {$this->enabled}, {$this->eventId});";

        return $this->objDbConn->processQuery($sql);
    }

    public function update()
    {
        $sql = "UPDATE posts
                SET
                    name = '{$this->name}',
                    enabled = {$this->enabled}
                WHERE id = {$this->id}
                    AND event_id = {$this->eventId};";

        return $this->objDbConn->processQuery($sql);
    }

    public function delete()
    {

        $sql = "DELETE FROM posts
                WHERE id = {$this->id};";

        return $this->objDbConn->processQuery($sql);
    }

    public function updateEnabled()
    {
        $result = false;
        $error = '';

        $sql = "UPDATE posts SET enabled = {$this->enabled} WHERE id = {$this->id};";
        return $this->objDbConn->processQuery($sql);
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setLanguageId(string $str)
    {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setEventId(int $id)
    {
        $this->eventId = $id;
    }

    public function setName(string $str)
    {
        $this->name = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setStart(string $str)
    {
        if ($this->isDate($str)) {
            $this->start = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->start = date('Y-m-d');
        }
    }

    public function setEnd(string $str)
    {
        if ($this->isDate($str)) {
            $this->end = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->end = date('Y-m-d');
        }
    }

    public function setEnabled(bool $bol)
    {
        if ($bol) {
            $this->enabled = 1;
        } else {
            $this->enabled = 0;
        }
    }

    private function isDate($date)
    {
        $date = date_parse($date);
        return (checkdate($date["month"], $date["day"], $date["year"]));
    }
}
