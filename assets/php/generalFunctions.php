<?php
class modGeneralFunction {
    public static function toJson($data, $topTag = 'data', $numCheck = true) {
        if ($topTag) {
            if ($numCheck) {
                return json_encode(array($topTag => $data), JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            } else {
                return json_encode(array($topTag => $data), JSON_UNESCAPED_UNICODE);
            }
        } else {
            if ($numCheck) {
                return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            } else {
                return json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public static function toHtmlFormat($string) {
        return htmlspecialchars($string, ENT_SUBSTITUTE);
    }

    public static function array_values_recursive($arr, $nodekey) {
        //Flattens keys within $nodekey keys for json
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = modGeneralFunction::array_values_recursive($value, $nodekey);
            }
            if (isset($arr[$nodekey]) && is_array($arr[$nodekey])) {
                $arr[$nodekey] = array_values($arr[$nodekey]);
            }
        }
        return $arr;
    }

    public static function baseUrl() {
        return sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            __BASE__
        );
    }

    public static function groupFetch($data, $key) {
        $grouped = [];

        foreach ($data as $item) {
            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

    /**
     * Compara dos arreglos y devuelve solo los campos que cambiaron.
     * @param array $oldData Datos actuales en la base de datos.
     * @param array $newData Datos que se pretenden guardar.
     * @return array|null Un arreglo con ['old' => [...], 'new' => [...]] o null si no hay cambios.
     */
    public static function getDiff($oldData, $newData)
    {
        $diff = ['old' => [], 'new' => []];

        // Normalizamos para comparar solo las llaves que vienen en el nuevo set de datos
        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData)) {
                // Comprobación de diferencia (ignorando tipos estrictos para campos de DB que vienen como strings)
                if ($oldData[$key] != $value) {
                    $diff['old'][$key] = $oldData[$key];
                    $diff['new'][$key] = $value;
                }
            }
        }

        return (empty($diff['old'])) ? null : $diff;
    }
}
