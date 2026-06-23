<?php
class locales {
    protected $objDbConn;
    protected $languageId = '';
    protected $localeId = '';

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectAll() {
        $result = false;
        $error = '';
        $data = array();

        $sql = "SELECT
        languages.id AS language_id,
        languages.name language_name,
        locales.id AS locale_id,
        locales.name locale_name
    FROM languages
        LEFT JOIN locales
            ON languages.id = language_id
    ORDER BY languages.name, locales.name;";

        //file_put_contents(__ROOT__ . '/debugLocales.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => $result, 'error' => $error, 'data' => $data);
    }

    public function select() {
        $result = false;
        $error = '';
        $data = array();

        $sql = "SELECT
        languages.id AS language_id,
        languages.name language_name,
        locales.id AS locale_id,
        locales.name locale_name
    FROM languages
        LEFT JOIN locales
            ON languages.id = language_id
    WHERE languages.enabled = 1
        AND locales.enabled = 1
        ORDER BY languages.name, locales.name;";

        //file_put_contents(__ROOT__ . '/debugLocales.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => $result, 'error' => $error, 'data' => $data);
    }

    public function selectTree(): array {
        $result = false;
        $error = '';
        $data = [];

        $sql = "SELECT
                    Concat_Ws('', 'g_', languages.id) AS id,
                    languages.name AS text,
                    GROUP_CONCAT(locales.id ORDER BY locales.id SEPARATOR '|') AS ids,
                    GROUP_CONCAT(locales.name ORDER BY locales.id SEPARATOR '|') AS texts
                FROM languages
                    INNER JOIN locales
                        ON locales.language_id = languages.id
                WHERE locales.enabled = 1
                GROUP BY
                    Concat_Ws('', 'g_', languages.id),
                    languages.name,
                    locales.language_id
                ORDER BY locales.language_id;";

        //file_put_contents(__ROOT__ . '/debugLocales.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $langs = $this->objDbConn->getDataQuery($result);
            $data = array_replace_recursive($this->array_key('id', array_column($langs, 'id')), $this->array_key('text', array_column($langs, 'text')));
            foreach ($langs as $key => $lang) {
                $ids = explode('|', $lang['ids']);
                $texts = explode('|', $lang['texts']);
                $ids = $this->array_key('id', $ids);
                $texts = $this->array_key('text', $texts);
                $data[$key]['children'] = array_replace_recursive($ids, $texts);
            }
        }

        return array('result' => $result, 'error' => $error, 'data' => $data);
    }

    public function selectLocale() {
        $ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
        $defaultLocale = $ini_array['general']['locale'];
        $result = false;
        $error = '';
        $data = array();

        $sql = "SELECT
                    languages.id AS language_id,
                    languages.name language_name,
                    locales.id AS locale_id,
                    locales.name locale_name
                FROM languages
                    LEFT JOIN locales
                        ON languages.id = language_id
                WHERE languages.enabled = 1
                    AND (locales.enabled = 1 OR locales.enabled IS NULL)
                    AND (
                        languages.id = '{$this->languageId}'
                        OR locales.id = '{$this->localeId}'
                        OR locales.id = '{$defaultLocale}'
                    )
                ORDER BY
                    locales.id = '{$defaultLocale}',
                    locales.id != '{$this->localeId}';";

        //file_put_contents(__ROOT__ . '/debugLocales.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => $result, 'error' => $error, 'data' => $data);
    }

    function selectLanguages() {
        $result = false;
        $error = '';
        $data = [];
        $sql = "SELECT * from languages WHERE enabled = 1;";

        //file_put_contents(__ROOT__ . '/debugLabels.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => $result, 'error' => $error, 'data' => $data);
    }

    public function updateLanguages() {
        $locs = ResourceBundle::getLocales('');
        $result = false;
        $error = '';
        $data = array();
        $values = array();

        foreach ($locs as $loc) {
            if ($loc == locale_get_primary_language($loc)) {
                $id = $this->objDbConn->mysqlRealEscape(locale_get_primary_language($loc));
                $name = $this->objDbConn->mysqlRealEscape(locale_get_display_name($loc, $loc));
                $name_en = $this->objDbConn->mysqlRealEscape(locale_get_display_name($loc, 'en'));

                $values[] = "('{$id}', '{$name}', '{$name_en}')";
            }
        }

        $sql = "INSERT INTO languages (id, name, name_en) VALUES\n" .
            implode(",\n", $values) .
            "\nON DUPLICATE KEY UPDATE
                name = VALUES(name), 
                name_en = VALUES (name_en);";

        //file_put_contents(__ROOT__ . '/debugLocales.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }

        return array('result' => $result, 'error' => $error);
    }

    public function updateLocales() {
        $locs = ResourceBundle::getLocales('');
        $result = false;
        $error = '';
        $data = array();
        $values = array();

        foreach ($locs as $loc) {
            $id = $this->objDbConn->mysqlRealEscape($loc);
            $language_id = $this->objDbConn->mysqlRealEscape(locale_get_primary_language($loc));
            $name = $this->objDbConn->mysqlRealEscape(locale_get_display_name($loc, $loc));
            $name_en = $this->objDbConn->mysqlRealEscape(locale_get_display_name($loc, 'en'));

            $values[] = "('{$id}', '{$language_id}', '{$name}', '{$name_en}')";
        }

        $sql = "INSERT INTO locales (id, language_id, name, name_en) VALUES\n" .
            implode(",\n", $values) .
            "\nON DUPLICATE KEY UPDATE
                name = VALUES(name), 
                name_en = VALUES (name_en);";

        //file_put_contents(__ROOT__ . '/debugLocales.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }

        return array('result' => $result, 'error' => $error);
    }

    public function setLanguageId(string $str = '') {
        $this->languageId = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setLocaleId(string $str = '') {
        $this->localeId = $this->objDbConn->mysqlRealEscape($str);
    }

    private function array_key(string $key, array $array): array {
        $return = [];
        foreach ($array as $index => $value) {
            $return[$index][$key] = $value;
        }

        return $return;
    }
}
