<?php
$selUser = 0;

require_once __ROOT__ . '/model/modPageAuth.php';
$objPageAuth = new pageAuth($_MYSQLI_);

if (!$objPageAuth->isPublic($path_to_include)['data']) {
    if (!isset($_SESSION['id']) || !isset($_SESSION['token'])) {
        // Detectar si es AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            // Respuesta JSON para AJAX
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'session_expired' => true,
                'redirect' => '/signout'
            ]);
            exit;
        } else {
            // Store the intended URL in a cookie (outside session) for redirect after login
            $intendedUrl = $_SERVER['REQUEST_URI'];
            setcookie('intended_url', $intendedUrl, time() + 300, '/', '', false, false); // 5 minute expiry
            header("Location: /signout");
        }
    }

    require_once __ROOT__ . '/model/modAuth.php';
    $objAuth = new auth($_MYSQLI_);
    $authorization = $objAuth->authorize($_SESSION['id'], $_SESSION['token']);
    if (!$authorization['auth']) {
        // Detectar si es AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            // Respuesta JSON para AJAX
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'session_expired' => true,
                'redirect' => '/signout'
            ]);
            exit;
        } else {
            // Store the intended URL in a cookie (outside session) for redirect after login
            $intendedUrl = $_SERVER['REQUEST_URI'];
            setcookie('intended_url', $intendedUrl, time() + 300, '/', '', false, false); // 5 minute expiry
            header("Location: /signout");
        }
    }
    $selUser = $authorization['data']['id'];
    $selDark = $authorization['data']['dark'];
    $_SESSION['id'] = $authorization['data']['id'];
    $_SESSION['locale_id'] = $authorization['data']['locale_id'];
    $_SESSION['first'] = $authorization['data']['first'];
    $_SESSION['last'] = $authorization['data']['last'];
    $_SESSION['email'] = $authorization['data']['email'];
    $_SESSION['token'] = $authorization['token'];
    $_SESSION['access'] = $authorization['data']['access'];

    if (isset($_SESSION['event']) && $_SESSION['event']['id'] > 0) {
        $selEvent = $_SESSION['event'];
    } else {
        require_once __ROOT__ . '/usr/companies/modcompanies.php';
        $objEvents = new Companies($_MYSQLI_);
        $objEvents->setUserId($selUser);
        $event = $objEvents->selectDefault();
        if (isset($event['data'][0])) {
            $selEvent = $event['data'][0];
        } else {
            $selEvent['id'] = 0;
        }
    }
    
    $pageAuth = $objPageAuth->pageAuth($path_to_include, $selUser, $selEvent['id']);
    if (!$pageAuth['auth']) {
        header("Location: /main");
    }
    $authParams = explode(',', $pageAuth['params'] ?? '');
}
