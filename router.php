<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include 'vendor/autoload.php';
require_once 'autoconf.php';
require_once __ROOT__ . '/assets/php/libSession.php';
require_once __ROOT__ . '/model/modRoutes.php';

// Check for remember me token and auto-login
if (!isset($_SESSION['id']) && isset($_COOKIE['remember_token'])) {
    require_once __ROOT__ . '/model/modAuth.php';
    $objAuth = new auth($_MYSQLI_);
    $rememberData = $objAuth->checkRememberToken();
    if ($rememberData) {
        $_SESSION['id'] = $rememberData['id'];
        $_SESSION['locale_id'] = $rememberData['locale_id'];
        $_SESSION['first'] = $rememberData['first'];
        $_SESSION['last'] = $rememberData['last'];
        $_SESSION['email'] = $rememberData['email'];
        $_SESSION['token'] = $rememberData['uuid'];
        $_SESSION['access'] = $rememberData['access'];
    }
}

$current_url = strtok($_SERVER['REQUEST_URI'], '?');
if ($current_url == '/' || $current_url == '') {
    header("Location: " . __ROOT__ . "/main");
    exit();
}

$objRoutes = new routes($_MYSQLI_);
if (isset($_SESSION['id'])) {
    $objRoutes->setUserId($_SESSION['id']);
}
$routeData = $objRoutes->select();
$routes = $routeData['data'];
foreach ($routes as $key => $route) {
    $route['method']($route['url'], $route['file']);
}

any('/404', '/');

function get($route, $path_to_include)
{
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        route($route, $path_to_include);
    }
}

function post($route, $path_to_include)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        route($route, $path_to_include);
    }
}

function put($route, $path_to_include)
{
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        route($route, $path_to_include);
    }
}

function patch($route, $path_to_include)
{
    if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
        route($route, $path_to_include);
    }
}

function delete($route, $path_to_include)
{
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        route($route, $path_to_include);
    }
}

function any($route, $path_to_include)
{
    route($route, $path_to_include);
}

function route($route, $path_to_include)
{
    $callback = $path_to_include;
    if (!is_callable($callback)) {
        if (!strpos($path_to_include, '.php')) {
            $path_to_include .= '.php';
        }
    }
    if ($route == "/404") {
        require_once __ROOT__ . '/assets/php/libAuth.php';
        includeWithVariables(__ROOT__ . "/$path_to_include");
        exit();
    }
    $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $request_url = rtrim($request_url, '/');
    $request_url = strtok($request_url, '?');
    $route_parts = explode('/', $route);
    $request_url_parts = explode('/', $request_url);
    array_shift($route_parts);
    array_shift($request_url_parts);
    if (!empty($route_parts) && $route_parts[0] == '' && count($request_url_parts) == 0) {
        // Callback function
        if (is_callable($callback)) {
            call_user_func_array($callback, []);
            exit();
        }
        require_once __ROOT__ . '/assets/php/libAuth.php';
        includeWithVariables(__ROOT__ . "/$path_to_include");
        exit();
    }
    if (count($route_parts) != count($request_url_parts)) {
        return;
    }

    // Process url parameters
    $parameters = array('chrLocale' => '');
    foreach ($route_parts as $key => $route_part) {
        if (preg_match("/^[$]/", $route_part)) {
            $parameters[ltrim($route_part, '$')] = $request_url_parts[$key];
        } else if ($route_part != $request_url_parts[$key]) {
            return;
        }
    }

    // Callback function
    if (is_callable($callback)) {
        call_user_func_array($callback, $parameters);
        exit();
    }

    require_once __ROOT__ . '/assets/php/libAuth.php';
    $parameters['selUser'] = $selUser;
    $parameters['selDark'] = $selDark ?? 0;
    $parameters['authParams'] = $authParams ?? [];
    if (isset($selEvent)) {
        $parameters['selEvent'] = $selEvent;
    }
    $controllerOutput = includeWithVariables(__ROOT__ . "/$path_to_include", array_merge($_POST, $parameters));

    // --- LOG DE SISTEMA (POST-EJECUCIÓN) ---
    if (
        in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE']) &&
        isset($_POST['action']) &&
        in_array($_POST['action'], ['C', 'U', 'D'])
    ) {

        global $_MYSQLI_;
        if (isset($_MYSQLI_)) {
            $logUserId = (int)($_SESSION['id'] ?? 0);
            $logMethod = $_MYSQLI_->mysqlRealEscape($_SERVER['REQUEST_METHOD']);
            $logUrl    = $_MYSQLI_->mysqlRealEscape($_SERVER['REQUEST_URI']);
            $logAction = isset($_POST['action']) ? $_MYSQLI_->mysqlRealEscape($_POST['action']) : '';

            // Capturar auditoría (OLD vs NEW) si el modelo la dejó en sesión
            $auditDiff = $_SESSION['last_audit_log'] ?? null;
            unset($_SESSION['last_audit_log']);

            // Ocultar contraseñas en el log si existen (opcional)
            $safePost = $_POST;
            if (isset($safePost['password'])) $safePost['password'] = '***';

            // Payload extendido estructurado
            $logData = [
                'request'  => $safePost,
                'audit'    => $auditDiff,
                'response' => json_decode($controllerOutput, true) ?: $controllerOutput
            ];

            // 1. Detectar el nombre de la tabla afectada de la auditoría y limpiarla de prefijos de SQL Server (como dbo.[Tabla])
            $rawTableName = $auditDiff['table'] ?? '';
            $cleanTable = str_replace(['[', ']', 'dbo.'], '', $rawTableName);

            // 2. Buscar si existe en sys__logtype_tables para obtener su logtype_id
            $logTypeId = "NULL";
            if (!empty($cleanTable)) {
                $sqlLogType = "SELECT logtype_id FROM sys__logtype_tables WHERE `table` = '" . $_MYSQLI_->mysqlRealEscape($cleanTable) . "' LIMIT 1";
                $resType = $_MYSQLI_->applyQuery($sqlLogType);
                if ($resType && !is_bool($resType)) {
                    $rowType = mysqli_fetch_assoc($resType);
                    if (!empty($rowType['logtype_id'])) {
                        $logTypeId = (int)$rowType['logtype_id'];
                    }
                }
            }

            $logPayload = $_MYSQLI_->mysqlRealEscape(json_encode($logData, JSON_UNESCAPED_UNICODE));
            $logCleanTable = empty($cleanTable) ? 'NULL' : "'" . $_MYSQLI_->mysqlRealEscape($cleanTable) . "'";

            // Insertamos también logtype_id y table_name (debes agregar estas columnas a la BD)
            $sqlLog = "INSERT INTO sys__changelogs (logtype_id, table_name, user_id, method, url, action_code, tableh, payload, created_at) 
                    VALUES ($logTypeId, $logCleanTable, $logUserId, '$logMethod', '$logUrl', '$logAction', '$cleanTable' ,'$logPayload', NOW())";
            $_MYSQLI_->applyQuery($sqlLog);
        }
    }

    includeWithVariables(__ROOT__ . "/$path_to_include", array_merge($_POST, $parameters));
    exit();
}

function out($text)
{
    echo htmlspecialchars($text);
}

function set_csrf()
{
    if (!isset($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(50));
    }
    echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
}

function is_csrf_valid()
{
    if (!isset($_SESSION['csrf'])) {
        return false;
    }

    // Check POST data
    if (isset($_POST['csrf']) && $_SESSION['csrf'] === $_POST['csrf']) {
        return true;
    }

    // Check AJAX header (X-CSRF-Token)
    $headers = getallheaders();
    if (isset($headers['X-CSRF-Token']) && $_SESSION['csrf'] === $headers['X-CSRF-Token']) {
        return true;
    }

    return false;
}

function includeWithVariables($filePath, $variables = array(), $print = true)
{
    $output = NULL;
    // Fallback to default language
    if (!array_key_exists('chrLocale', $variables)) {
        $variables['chrLocale'] = '';
    }

    if (file_exists($filePath)) {
        extract($variables);
        ob_start();
        include_once $filePath;
        $output = ob_get_clean();
    }
    if ($print) {
        print $output;
    }
    return $output;
}
