<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class nav {
    use globalCte;
    protected $objDbConn;
    protected $private = 0;
    protected $type = 0;
    protected $languageId = 'en';
    protected $localeId = 'en-US';
    protected $userId = 0;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function select() {
        if ($this->private) {
            // only public and all user pages
            $sql = "WITH {$this->cteLabels()}
                    SELECT
                        routes.id,
                        name,
                        if (
                            SUBSTRING(url, 1, 11) = 'javascript:'
                            OR SUBSTRING(url, 1, 2) = '//',
                            url,
                            CONCAT_WS ('', '/{$this->localeId}', if (url = '/', '', url))
                        ) AS url,
                        icon,
                        position
                    FROM routes
                        LEFT JOIN LABELS 
                            ON label_name = LABELS.id
                        LEFT JOIN (
                            SELECT DISTINCT
                                parent_route_id
                            FROM route_nav
                                INNER JOIN routes
                                    ON routes.id = route_nav.child_route_id
                                INNER JOIN route_accesses
                                    ON route_accesses.route_id = routes.id,
                                users
                            WHERE route_nav.enabled = 1
                                AND users.id = 1
                                AND access & POW (2, access_id -1) > 0
                        ) subs
                            ON subs.parent_route_id = routes.id
                    WHERE type = {$this->type}
                        AND isalluser = 1 OR ({$this->type} = 1 AND subs.parent_route_id IS NOT NULL)
                    UNION
                    SELECT
                        routes.id,
                        name,
                        if (
                            SUBSTRING(url, 1, 11) = 'javascript:'
                            OR SUBSTRING(url, 1, 2) = '//',
                            url,
                            CONCAT_WS ('', '/{$this->localeId}', if (url = '/', '', url))
                        ) AS url,
                        icon,
                        position
                    FROM routes
                        LEFT JOIN LABELS 
                            ON label_name = LABELS.id
                        INNER JOIN route_accesses
                            ON route_accesses.route_id = routes.id,
                        users
                    WHERE type = {$this->type}
                        AND users.id = {$this->userId}
                        AND access & POW (2, access_id -1) > 0
                    ORDER BY position;";
        } else {
            // only public pages
            $sql = "WITH {$this->cteLabels()}
                    SELECT
                        routes.id,
                        name,
                        if (
                            SUBSTRING(url, 1, 11) = 'javascript:'
                            OR SUBSTRING(url, 1, 2) = '//',
                            url,
                            CONCAT_WS ('', '/{$this->localeId}', if (url = '/', '', url))
                        ) AS url,
                        icon
                    FROM routes
                        LEFT JOIN LABELS 
                            ON label_name = LABELS.id
                    WHERE type = {$this->type}
                        AND ispublic = 1
                    ORDER BY position;";
        }

        return $this->objDbConn->processQuery($sql);
    }

    public function selectPublicDrop(): array {
        $sql = "WITH {$this->cteLabels()}
                SELECT DISTINCT
                    parent_route_id,
                    child_route_id,
                    routes.position,
                    name,
                    IF(
                        SUBSTRING(url, 1, 11) = 'javascript:'
                        OR SUBSTRING(url, 1, 2) = '//',
                        url,
                        CONCAT_WS('', '/{$this->localeId}', IF(url = '/', '', url))
                    ) AS url,
                    icon
                FROM route_nav
                    INNER JOIN routes
                        ON routes.id = route_nav.child_route_id
                    LEFT JOIN LABELS
                        ON label_name = LABELS.id
                WHERE route_nav.enabled = 1
                    AND routes.ispublic = 1
                ORDER BY
                    parent_route_id,
                    routes.position;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectDrop(): array {
        $sql = "WITH {$this->cteLabels()}
                SELECT DISTINCT
                    parent_route_id,
                    child_route_id,
                    routes.position,
                    name,
                    IF(
                        SUBSTRING(url, 1, 11) = 'javascript:'
                        OR SUBSTRING(url, 1, 2) = '//',
                        url,
                        CONCAT_WS('', '/{$this->localeId}', IF(url = '/', '', url))
                    ) AS url,
                    icon
                FROM route_nav
                    INNER JOIN routes
                        ON routes.id = route_nav.child_route_id
                    LEFT JOIN LABELS
                        ON label_name = LABELS.id
                    INNER JOIN route_accesses
                        ON route_accesses.route_id = routes.id,
                    users
                WHERE route_nav.enabled = 1
                    AND users.id = {$this->userId}
                    AND access & POW (2, access_id -1) > 0
                ORDER BY
                    parent_route_id,
                    routes.position;";

        return $this->objDbConn->processQuery($sql);
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setLocaleId(string $str) {
        $this->localeId = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setPrivate(int $id) {
        $this->private = $id;
    }

    public function setType(int $id) {
        $this->type = $id;
    }

    public function setUserId(int $id) {
        $this->userId = $id;
    }
}
