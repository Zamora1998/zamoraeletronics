<?php
class routes {
    protected $objDbConn;
    protected $userId = 0;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
        require_once __ROOT__ . '/assets/php/libQueryBuilder.php';
    }

    public function select() {
        //$methods = ['get', 'post', 'put', 'patch', 'delete', 'any'];
        //$qb = new QueryBuilder();

        // Reusable locale URL expression
        $localeUrl = "if(length(url)>1, concat_ws('', '/\$chrLocale', url), concat_ws('', '/\$chrLocale'))";

        if ($this->userId) {

            // Base public query
            $publicQuery = (new QueryBuilder())
                ->table('routes')
                ->distinct()
                ->select(['method', 'url', 'file'])
                ->where('method', '!=', '')
                ->where(function ($q) {
                    $q->where('ispublic', '=', 1)
                        ->where('isalluser', '=', 1, 'OR');
                });

            // Base access query
            $accessQuery = (new QueryBuilder())
                ->table('routes')
                ->distinct()
                ->select(['method', 'url', 'file'])
                ->join('route_accesses', 'route_accesses.route_id', '=', 'routes.id')
                ->crossJoin('users')
                ->where('method', '!=', '')
                ->where('users.id', '=', $this->userId)
                ->where('required', '=', 1)
                ->whereExpression('access & POW(2, access_id - 1)', '>', '0');

            // Locale variants
            $publicLocaleQuery = (new QueryBuilder())
                ->table('routes')
                ->distinct()
                ->select(['method', "$localeUrl AS url", 'file'])
                ->where('method', '!=', '')
                ->where(function ($q) {
                    $q->where('ispublic', '=', 1)
                        ->where('isalluser', '=', 1, 'OR');
                });

            $accessLocaleQuery = (new QueryBuilder())
                ->table('routes')
                ->distinct()
                ->select(['method', "$localeUrl AS url", 'file'])
                ->join('route_accesses', 'route_accesses.route_id', '=', 'routes.id')
                ->crossJoin('users')
                ->where('method', '!=', '')
                ->where('users.id', '=', $this->userId)
                ->where('required', '=', 1)
                ->whereExpression('access & POW(2, access_id - 1)', '>', '0');

            $query = $publicQuery
                ->union($accessQuery)
                ->union($publicLocaleQuery)
                ->union($accessLocaleQuery)
                ->build();
        } else {

            $publicQuery = (new QueryBuilder())
                ->table('routes')
                ->distinct()
                ->select(['method', 'url', 'file'])
                ->where('method', '!=', '')
                ->where('ispublic', '=', 1);

            $publicLocaleQuery = (new QueryBuilder())
                ->table('routes')
                ->distinct()
                ->select(['method', "$localeUrl AS url", 'file'])
                ->where('method', '!=', '')
                ->where('ispublic', '=', 1);

            $query = $publicQuery
                ->union($publicLocaleQuery)
                ->build();
        }

        return $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );
    }

    public function setUserId($int) {
        if (is_numeric($int)) {
            $this->userId = $int;
        } else {
            $this->userId = 0;
        }
    }
}
