<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class route {
    use globalCte;
    //region general
    protected $objDbConn;
    protected $dataCategories;
    protected $id = 0;
    protected $languageId = 'en';
    protected $LabelName = '';
    protected $Type = '';
    protected $Position = 0;
    protected $Icon = '';
    protected $Url = '';
    protected $File = '';
    protected $Method = '';
    protected $CategoryId = 0;
    protected $chrLocale = 'en_US';
    protected $Alluser = 0;
    protected $Public = 0;
    protected $ParentC = 0;
    protected $Status = 0;
    protected $accessIds = [];
    protected $params = [];
    protected $optionals = [];
    protected $requireds = [];

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }
    //region end
    //region selects
    public function selectAll() {
        $sql = "SELECT DISTINCT
                    r.id,
                    r.label_name,
                    r.type,
                    r.icon,
                    r.position,
                    r.url,
                    r.file,
                    r.method,
                    r.ispublic,
                    r.isalluser,
                    r.routecategory_id,
                    rc.name AS category_name,
                    rn.parent_route_id,
                    pr.label_name AS parent_labelname/*,
                    rn.child_route_id,
                    cr.label_name AS child_labelname*/
                FROM
                    routes r
                LEFT JOIN route_nav rn 
                    ON rn.child_route_id = r.id
                LEFT JOIN routes pr 
                    ON pr.id = rn.parent_route_id
                LEFT JOIN routes cr 
                    ON cr.id = rn.child_route_id
                LEFT JOIN routecategories rc 
                    ON r.routecategory_id = rc.id ;";
        return  $this->objDbConn->processQuery($sql);
    }

    public function selectDetailsRoutes() {
        $sql = "WITH {$this->cteLabels()}
                SELECT DISTINCT
                    r.id,
                    l.name AS text
                FROM routes r
                    LEFT JOIN LABELS l 
                        ON l.id = r.label_name
                WHERE r.type = 1;";

        return  $this->objDbConn->processQuery($sql);
    }

    public function selectRoute() {
        $data = [];
        $result = false;
        $error = '';
        $sql = "SELECT
                r.id,
                r.label_name,
                r.type,
                r.icon,
                r.position,
                r.url,
                r.file,
                r.method,
                r.ispublic,
                r.isalluser,
                r.routecategory_id,
                rc.name AS category_name,
                rn.parent_route_id,
                pr.label_name AS parent_labelname
            FROM routes r
                LEFT JOIN route_nav rn 
                    ON rn.child_route_id = r.id
                LEFT JOIN routes pr 
                    ON pr.id = rn.parent_route_id
                LEFT JOIN routes cr 
                    ON cr.id = rn.child_route_id
                LEFT JOIN routecategories rc 
                    ON r.routecategory_id = rc.id  
            WHERE r.id='{$this->id}';";
        //file_put_contents(__ROOT__ . '/debugRoutes.txt', var_export($sql, true));

        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }
        $categories = $this->selectCategories()['data'];

        return array('result' => ($result) ? true : false, 'error' => $error, 'data' => $data, 'categories' => $categories);
    }

    public function selectPermission() {
        $sql = "SELECT
                    ra.route_id,
                    ra.access_id,
                    ra.param,
                    ra.required,
                    a.name AS access_name
                FROM route_accesses ra
                    LEFT JOIN accesses a
                        ON ra.access_id = a.id
                WHERE ra.route_id = '{$this->id}';";

        return  $this->objDbConn->processQuery($sql);
    }

    public function selectAllAccessName() {
        $sql = "WITH {$this->cteLabels()}
                SELECT
                    a.id,
                    COALESCE(LABELS.name, CONCAT(a.name, ' N/A')) AS name,
                    COALESCE(LABELS.id, CONCAT(a.name, ' N/A')) AS labelid,
                    ra.param,
                    IFNULL(ra.required, 0) AS required,
                    NOT ISNULL(ra.required) AS optional
                FROM accesses a
                    LEFT JOIN LABELS
                        ON LABELS.id = a.name
                    LEFT JOIN (
                        SELECT
                            access_id,
                            param,
                            required
                        FROM route_accesses
                        WHERE route_id = {$this->id}
                    ) ra ON ra.access_id = a.id
                ORDER BY name;";

        return  $this->objDbConn->processQuery($sql);
    }

    public function selectAccessDetails() {
        $sql = "WITH {$this->cteLabels()}
                SELECT
                    a.id,
                    COALESCE(LABELS.name, CONCAT(a.name, ' N/A')) AS name,
                    ra.param,
                    IFNULL(ra.required, 0) AS required,
                    NOT ISNULL(ra.required) AS optional
                FROM accesses a
                    LEFT JOIN LABELS ON LABELS.id = a.name
                    LEFT JOIN (
                        SELECT
                            access_id,
                            param,
                            required
                        FROM route_accesses
                        WHERE route_id = {$this->id}
                    ) ra ON ra.access_id = a.id
                ORDER BY name;";

        return  $this->objDbConn->processQuery($sql);
    }

    public function selectCategories() {
        $sql = "SELECT
                    rc.id,
                    rc.name,
                    COALESCE(parents.parent_ids, '0') AS parent_ids
                FROM
                    routecategories AS rc
                    JOIN routecategories AS parent ON rc.lpos BETWEEN parent.lpos AND parent.rpos
                    LEFT JOIN (
                        SELECT
                            node.id AS category_id,
                            GROUP_CONCAT(parent.id ORDER BY parent.lpos) AS parent_ids
                        FROM
                            routecategories AS node
                            JOIN routecategories AS parent ON node.lpos BETWEEN parent.lpos AND parent.rpos
                        WHERE
                            parent.id != node.id
                        GROUP BY node.id
                    ) parents ON parents.category_id = rc.id
                WHERE
                    rc.enabled = 1
                    AND rc.id >= 0
                GROUP BY
                    rc.id, rc.name, ROUND((rc.rpos - rc.lpos - 1) / 2), parents.parent_ids
                ORDER BY
                    rc.lpos;";

        return  $this->objDbConn->processQuery($sql);
    }

    public function selectCategoryItem() {
        $sql = "SELECT
                    id,
                    name,
                    enabled
                FROM routecategories
                WHERE id='{$this->id}';";

        return  $this->objDbConn->processQuery($sql);
    }

    public function selectAllCategories() {
        $sql = "SELECT
                    rc.id,
                    rc.name,
                    COUNT(parent.id) - 1 AS depth,
                    ROUND((rc.rpos-rc.lpos-1)/2) AS children,
                    parents.parent_ids
                FROM routecategories AS rc
                    JOIN routecategories AS parent
                        ON rc.lpos BETWEEN parent.lpos AND parent.rpos
					LEFT JOIN (
                        SELECT
                            node.id AS category_id,
                            GROUP_CONCAT(parent.id ORDER BY parent.lpos) AS parent_ids
                        FROM
                            routecategories AS node,
                            routecategories AS parent
                        WHERE
                            node.lpos BETWEEN parent.lpos AND parent.rpos
                            AND parent.id != node.id
                        GROUP BY node.id
                    ) parents
                        ON parents.category_id = rc.id
                WHERE rc.id > 0
                GROUP BY rc.id, rc.name, ROUND((rpos-lpos-1)/2),parents.parent_ids
                ORDER BY rc.lpos;";

        return  $this->objDbConn->processQuery($sql);
    }
    //region end
    //region inserts
    public function insertRoute() {
        $this->objDbConn->resetAI('routes');
        $results = [];
        $errors = [];

        if (!$this->id) {
            $this->id = 'null';
        }
        if (!$this->Position) {
            $this->Position = 'null';
        }
        $sql = "INSERT INTO
                    routes (
                        id,
                        label_name,
                        type,
                        icon,
                        position,
                        url,
                        file,
                        method,
                        ispublic,
                        isalluser,
                        routecategory_id
                    )
                    VALUES(
                            {$this->id},
                            '{$this->LabelName}',
                            {$this->Type},
                            '{$this->Icon}',
                            {$this->Position},
                            '{$this->Url}',
                            '{$this->File}',
                            '{$this->Method}',
                            {$this->Public},
                            {$this->Alluser},
                            {$this->CategoryId}
                    ) ON DUPLICATE KEY UPDATE
                        label_name = VALUES(label_name),
                        type = VALUES(type),
                        icon = VALUES(icon),
                        position = VALUES(position),
                        url = VALUES(url),
                        file = VALUES(file),
                        method = VALUES(method),
                        ispublic = VALUES(ispublic),
                        isalluser = VALUES(isalluser),
                        routecategory_id = VALUES(routecategory_id);";

        $return = $this->objDbConn->processQuery($sql);
        $returns[] = $return['result'];
        if (!$return['result']) {
            $errors[] = $return['error'];
        } elseif ($this->id == 'null') {
            $this->id = $this->objDbConn->getLastId();
        }

        if ($this->ParentC) {
            $sql =  "INSERT INTO route_nav(
                    child_route_id,
                    parent_route_id,
                    position,
                    enabled
                )
                VALUES(
                    {$this->id},
                    {$this->ParentC},
                    0,
                    1
                ) ON DUPLICATE KEY UPDATE
                    child_route_id = VALUES(child_route_id),
                    parent_route_id = VALUES(parent_route_id),
                    position = VALUES(position),
                    enabled = VALUES(enabled);";
        } else {
            $sql = "DELETE FROM route_nav
                    WHERE child_route_id = {$this->id}
                    AND {$this->ParentC} = 0";
        }
        $return = $this->objDbConn->processQuery($sql);
        $returns[] = $return['result'];
        if (!$return['result']) {
            $errors[] = $return['error'];
        }

        return array('result' => !in_array(false, $results, true), 'errors' => $errors);
    }

    public function insertAccesses() {
        $results = [];
        $errors = [];
        $values = [];

        $this->objDbConn->resetAI('route_accesses');
        foreach ($this->accessIds as $key => $accessId) {
            if ($this->optionals[$key]) {
                $values[] = "('{$this->id}', '{$accessId}', '{$this->objDbConn->mysqlRealEscape($this->params[$key])}', '{$this->requireds[$key]}')";
            }
        }

        if (count($values)) {
            $sql = "INSERT INTO route_accesses (route_id, access_id, param, required)
                    VALUES " . implode(",\n", $values) . "
                    ON DUPLICATE KEY UPDATE
                        param = VALUES(param),
                        required = VALUES(required);";

            //file_put_contents(__ROOT__ . '/debugRoutes.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
            }
        }

        $accessIds = array_intersect_key($this->accessIds, array_filter($this->optionals, array($this, 'invert')));

        if (count($accessIds)) {
            $sql = "DELETE FROM route_accesses
                    WHERE route_id = {$this->id}
                        AND access_id IN (" . implode(', ', $accessIds) . ");";

            //file_put_contents(__ROOT__ . '/debugRoutes.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
            }
        }

        return array('result' => !in_array(false, $results, true), 'errors' => $errors);
    }

    public function insertCategory() {
        $result = false;
        $error = '';
        $this->objDbConn->applyQuery("call reset_ai('routecategories')");
        $sql = "LOCK TABLE routecategories WRITE;";
        $result = $this->objDbConn->applyQuery($sql);
        //get new position
        if ($this->isEmptyParent()) {
            $sql = "SELECT @newLeft := lpos + 1 FROM routecategories WHERE id = {$this->id};";
        } else {
            $sql = "SELECT @newLeft := rpos FROM routecategories WHERE id = {$this->id};";
        }
        $result = $this->objDbConn->applyQuery($sql);

        //open space
        $sql = "UPDATE routecategories SET rpos = rpos + 2 WHERE rpos >= @newLeft;";
        $result = $this->objDbConn->applyQuery($sql);
        $sql = "UPDATE routecategories SET lpos = lpos + 2 WHERE lpos >= @newLeft;";
        $result = $this->objDbConn->applyQuery($sql);

        //insert
        $sql = "INSERT INTO routecategories(name, enabled, lpos, rpos) VALUES('{$this->LabelName}','{$this->Status}', @newLeft, @newLeft + 1);";
        $result = $this->objDbConn->applyQuery($sql);
        $this->id = $this->objDbConn->getLastId();
        $sql = "UNLOCK TABLES;";
        if (!$result) {
            $error = $this->objDbConn->getError();
        }

        return array('result' => ($result) ? true : false, 'error' => $error,);
    }
    //region end
    //region updates
    public function updateCategoryElements() {
        $result = false;
        $error = '';
        $parametros = $this->params;
        foreach ($parametros as $element) {
            $id = $element['id'];
            $left = $element['left'];
            $right = $element['right'];
            $sql = "UPDATE 
                    routecategories SET
                    lpos = $left, rpos = $right WHERE id = $id;";

            //file_put_contents(__ROOT__ . '/debugCategorysdos.txt', var_export($sql, true));    
            $result = $this->objDbConn->applyQuery($sql);
        }
        if (!$result) {
            $error = $this->objDbConn->getError();
        }
        return array('result' => ($result) ? true : false, 'error' => $error);
    }

    public function updateCategory() {
        $result = false;
        $error = '';
        $this->objDbConn->applyQuery("call reset_ai('routecategories')");
        $sql = "LOCK TABLE routecategories WRITE;";
        $result = $this->objDbConn->applyQuery($sql);
        $sql = "UPDATE routecategories
                SET name = '{$this->LabelName}', enabled = '{$this->Status}'
                WHERE id = {$this->id};";
        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) { // Corregido aquí
            $error = $this->objDbConn->getError();
        }
        return array('result' => ($result) ? true : false, 'error' => $error);
    }

    private function isEmptyParent() {
        $return = false;
        $sql = "SELECT id from routecategories WHERE id = {$this->id} AND rpos=lpos+1;";
        $result = $this->objDbConn->applyQuery($sql);
        if (!empty($this->objDbConn->getDataQuery($result))) {
            $return = true;
        }
        $this->objDbConn->freeResultQuery($result);
        return $return;
    }

    public function updateIsPublic($status) {
        $result = false;
        $error = '';
        $sql = "UPDATE routes
                SET ispublic = '{$status}'
                WHERE id = {$this->id};";
        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) { // Corregido aquí
            $error = $this->objDbConn->getError();
        }

        return array('result' => ($result) ? true : false, 'error' => $error);
    }

    public function updateIsAll($status) {
        $result = false;
        $error = '';
        $sql = "UPDATE routes
                SET isalluser = '{$status}'
                WHERE id = {$this->id};";
        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) { // Corregido aquí
            $error = $this->objDbConn->getError();
        }

        return array('result' => ($result) ? true : false, 'error' => $error);
    }
    //region end
    //region deletes
    public function deleteRoute() {
        $sql = "DELETE FROM routes
                WHERE id = {$this->id};\n";

        return $this->objDbConn->processQuery($sql);
    }

    public function deleteCategory() {
        $result = false;
        $error = '';
        if ($this->id) {
            $sql = "UPDATE routecategories
                SET parent_id = (
                    SELECT parent.id
                    FROM routecategories AS node,
                        routecategories AS parent
                    WHERE node.lpos BETWEEN parent.lpos AND parent.rpos
                        AND node.id = {$this->id}
                        AND parent.id != node.id
                    ORDER BY parent.rpos
                    LIMIT 1
                )
                WHERE parent_id = {$this->id};";
            //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $sql = "LOCK TABLE routecategories WRITE;";
            $result = $this->objDbConn->applyQuery($sql);

            $sql = "SELECT @myLeft:=lpos, @myRight:=rpos, @myWidth:=rpos - lpos + 1 FROM routecategories WHERE id = {$this->id};";
            $result = $this->objDbConn->applyQuery($sql);

            //Delete node and Childrens
            $sql = "DELETE FROM routecategories WHERE lpos BETWEEN @myLeft AND @myRight;";
            $result = $this->objDbConn->applyQuery($sql);
            $sql = "UPDATE routecategories SET rpos = rpos - @myWidth WHERE rpos > @myRight;";

            $result = $this->objDbConn->applyQuery($sql);
            $sql = "UPDATE routecategories SET lpos = lpos - @myWidth WHERE lpos > @myRight;";
            $result = $this->objDbConn->applyQuery($sql);
            $sql = "UNLOCK TABLES;";
            $result = $this->objDbConn->applyQuery($sql);
        }
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }
        return array('result' => ($result) ? true : false, 'error' => $error,);
    }
    //region end
    //region sets
    public function setId(int $int) {
        $this->id = $int;
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));;
    }

    public function setName(string $str) {
        $this->LabelName = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setType(int $int) {
        $this->Type = $int;
    }

    public function SetPosition($int) {
        $this->Position = $int == "" ? null : (int)$int;
    }

    public function setIcon(string $str) {
        $this->Icon = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setLocale(string $str) {
        $this->chrLocale = $str;
    }

    public function setUrl(string $str) {
        $this->Url = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setFile(string $str) {
        $this->File = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setMethod(string $str) {
        $this->Method = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setCategoryId(int $int) {
        $this->CategoryId = $int;
    }
    public function setParentC(int $int) {
        $this->ParentC = $int;
    }
    public function setAlluser(int $int) {
        $this->Alluser = $int;
    }

    public function setPublic(int $int) {
        $this->Public = $int;
    }

    public function setStatus(int $int) {
        $this->Status = $int;
    }

    public function setAccessIds(array $arr) {
        $this->accessIds = $arr;
    }

    public function setParams(array $arr) {
        $this->params = $arr;
    }

    public function setOptionals(array $arr) {
        $this->optionals = $arr;
    }

    public function setRequireds(array $arr) {
        $this->requireds = $arr;
    }

    private function invert($val) {
        return !$val;
    }
    public function getDataCategories() {
        return $this->dataCategories;
    }
    //region end
}
