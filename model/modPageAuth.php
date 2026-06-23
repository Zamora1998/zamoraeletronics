<?php
class pageAuth {
    protected $objDbConn;

    function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
        require_once __ROOT__ . '/assets/php/libQueryBuilder.php';
    }

    public function isPublic($path) {
        $qb = new QueryBuilder();

        $qb->table("routes")
            ->select('BIT_OR(ispublic) AS public')
            ->where('file', '=', $path)
            ->where('ispublic', '=', 1);

        $query = $qb->build();
        $return = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        if ($return['result']) {
            $return['data'] = $return['data'][0]['public'];
        }

        return $return;
    }

    public function pageAuth($path, $userId) {
        $allUserQuery = (new QueryBuilder())
            ->table('routes')
            ->select([
                'isalluser AS auth',
                'NULL AS params'
            ])
            ->where('file', '=', $path)
            ->where('isalluser', '=', 1);

        $accessQuery = (new QueryBuilder())
            ->table('routes')
            ->select(['required AS auth', 'route_accesses.param AS params'])
            ->join('route_accesses', 'route_accesses.route_id', '=', 'routes.id')
            ->crossJoin('users')
            ->where('routes.file', '=', $path)
            ->where('users.id', '=', $userId)
            ->whereExpression('access & POW(2, access_id - 1)', '>', '0');

        $mainQuery = (new QueryBuilder())
            ->fromSubQuery(
                $allUserQuery->union($accessQuery),
                'pageauth'
            )
            ->select([
                'BIT_OR(auth) AS auth',
                'GROUP_CONCAT(params) AS params'
            ]);

        $query = $mainQuery->build();
        $return = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        if ($return['result']) {
            $return['auth'] = $return['data'][0]['auth'];
            $return['params'] = $return['data'][0]['params'] ?? '';
            unset($return['data']);
        }

        return $return;
    }
}
