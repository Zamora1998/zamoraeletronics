<?php

declare(strict_types=1);

/**
 * Crypt
 */
class Crypt {
    private static $method = 'AES-256-CBC';

    /**
     * encrypt
     *
     * @param  string $key
     * @param  mixed $str
     * @return string
     */
    public static function encrypt(string $key, string $str = ''): string {
        $iv = self::createIv();
        $hexIv = bin2hex($iv);
        $crytedStr = openssl_encrypt($str, self::$method, $key, 0, $iv) . "::" . $hexIv;
        return rawurlencode($crytedStr);
    }

    /**
     * decrypt
     *
     * @param  string $key
     * @param  string $rawCrytedStr
     * @return string|bool
     */
    public static function decrypt(string $key, string $rawCrytedStr = ''): string|bool {
        $crytedStr = rawurldecode($rawCrytedStr);
        list($crytedStr, $iv) = explode("::", $crytedStr);
        $bin2Ivi = hex2bin($iv);
        return openssl_decrypt($crytedStr, self::$method, $key, 0, $bin2Ivi);
    }

    /**
     * createIv
     *
     * @return string
     */
    private static function createIv(): string {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$method));
    }
}
