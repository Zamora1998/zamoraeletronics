<?php
getAppRoot();
loadConstants();
require_once __ROOT__ . '/assets/php/libDbConn.php';
$_MYSQLI_ = new dbConn;

function getAppRoot()
{
    // Locate and define Application directory
    if (!defined('__ROOT__')) {
        //define('TLSD', (count(explode('.', parse_url($_SERVER["SERVER_NAME"])['path'])) > 2 ? explode('.', parse_url($_SERVER["SERVER_NAME"])['path'])[count(explode('.', parse_url($_SERVER["SERVER_NAME"])['path'])) - 3] : ''));
        $docrootarr = explode('/', $_SERVER['DOCUMENT_ROOT']);
        $curdirarr = explode('/', dirname(str_replace('//', '/', $_SERVER["SCRIPT_FILENAME"])));
        $level = count($docrootarr);
        $appdir = implode('/', array_slice($curdirarr, 0, $level));
        while ((!file_exists($appdir . "/.config.ini")) && ($level < count($curdirarr))) {
            $level++;
            $appdir = implode('/', array_slice($curdirarr, 0, $level));
        }
        if (!file_exists($appdir . "/.config.ini")) {
            die('ini file not found!');
        }
        define('__ROOT__', $appdir);
        define('__BASE__', $_SERVER["SERVER_NAME"] ?? '');
    }
}

function loadConstants(): void
{
    $ini_array = parse_ini_file(__ROOT__ . '/.config.ini', true);
    define('SESSKEY', $ini_array['general']['sessionkey'] ?? '');
}

function autoVer($url)
{
    // Validate URL to prevent path traversal attacks
    $url = trim($url);
    $url = str_replace('\\', '/', $url);

    // Check for path traversal patterns using chr() to avoid syntax issues
    $dotDotSlash = chr(46) . chr(46) . chr(47);  // ../
    $dotDotBackslash = chr(46) . chr(46) . chr(92);  // ..\

    if (strpos($url, $dotDotSlash) !== false || strpos($url, $dotDotBackslash) !== false) {
        error_log('Path traversal attempt detected in autoVer(): ' . $url);
        return $url; // Return original URL without version parameter
    }

    // Ensure URL starts with / for absolute paths
    if (strpos($url, '/') !== 0) {
        $url = '/' . $url;
    }

    $fullPath = __ROOT__ . $url;

    // Verify the resolved path is within __ROOT__ (additional security check)
    $realPath = realpath($fullPath);
    $realRoot = realpath(__ROOT__);

    if ($realPath === false || strpos($realPath, $realRoot) !== 0) {
        error_log('Invalid path access attempt in autoVer(): ' . $url);
        return $url; // Return original URL without version parameter
    }

    // Check if file exists before getting filemtime
    if (!file_exists($realPath)) {
        return $url;
    }

    $path = pathinfo($url);
    return $path['dirname'] . '/' . $path['basename'] . '?v=' . filemtime(__ROOT__ . $url);
}

function autoVerRemote(string $url)
{
    $timestamp = 0;
    $headers = get_headers($url);
    if (isset($headers[0]) && $headers[0] == 'HTTP/1.1 200 OK') {
        if (isset($headers[5])) {
            $date = date_create(str_replace('Last-Modified: ', '', $headers[5]));
            $timestamp = date_timestamp_get($date);
        }
    }
    return $url . '?v=' . $timestamp;
}
