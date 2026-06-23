<?php

/**
 * captcha
 */
class captcha
{
    /**
     * getCaptchaCode
     *
     * @param  int $length
     * @return string
     */
    function getCaptchaCode(int $length): string
    {
        $random_alpha = md5(random_bytes(64));
        $captcha_code = substr($random_alpha, 0, $length);
        return $captcha_code;
    }

    /**
     * setSession
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    function setSession(string $key, string $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * getSession
     *
     * @param  string $key
     * @return string
     */
    function getSession(string $key): string
    {
        $value = "";
        if (!empty($key) && !empty($_SESSION[$key])) {
            $value = $_SESSION[$key];
        }
        return $value;
    }

    /**
     * createCaptchaImage
     *
     * @param  string $captcha_code
     * @return mixed
     */
    function createCaptchaImage(string $captcha_code): mixed
    {
        $target_layer = imagecreatetruecolor(72, 28);
        $captcha_background = imagecolorallocate($target_layer, 233, 236, 239);
        imagefill($target_layer, 0, 0, $captcha_background);
        $captcha_text_color = imagecolorallocate($target_layer, 33, 37, 41);
        imagestring($target_layer, 5, 10, 5, $captcha_code, $captcha_text_color);

        return $target_layer;
    }

    /**
     * renderCaptchaImage
     *
     * @param  mixed $imageData
     * @return void
     */
    function renderCaptchaImage(mixed $imageData)
    {
        header("Content-type: image/jpeg");
        imagejpeg($imageData);
    }

    /**
     * validateCaptcha
     *
     * @param  string $key
     * @param  string $formData
     * @return bool
     */
    function validateCaptcha(string $key, string $formData): bool
    {
        $isValid = false;
        $capchaSessionData = $this->getSession($key);
        //file_put_contents(__ROOT__ . '/debugCaptcha.txt', var_export($formData . '/' . $capchaSessionData . '/' . $_SESSION['captcha_code'], true));
        if ($capchaSessionData !== '' && $capchaSessionData == $formData) {
            $isValid = true;
        }
        $this->setSession($key, $this->getCaptchaCode(6));
        return $isValid;
    }
}
