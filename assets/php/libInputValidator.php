<?php

/**
 * Input Validation and Sanitization Library
 * 
 * Provides centralized input validation and sanitization functions
 * to prevent XSS, SQL injection, and other input-based attacks.
 */

class InputValidator
{

    /**
     * Sanitize a string by removing tags and trimming whitespace
     * 
     * @param mixed $input The input to sanitize
     * @return string Sanitized string
     */
    public static function sanitizeString($input): string
    {
        if ($input === null) {
            return '';
        }

        // Convert to string if not already
        $input = (string) $input;

        // Remove null bytes and trim whitespace
        $input = trim($input);

        // Strip tags (allows only basic text)
        $input = strip_tags($input);

        // Remove control characters
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        return $input;
    }

    /**
     * Validate and sanitize an email address
     * 
     * @param mixed $email The email to validate
     * @return string|null Sanitized email or null if invalid
     */
    public static function sanitizeEmail($email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        // Convert to string and trim
        $email = trim((string) $email);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // Sanitize - remove dangerous characters
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        return $email;
    }

    /**
     * Validate password strength
     * 
     * @param string $password The password to validate
     * @param int $minLength Minimum length (default: 8)
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validatePassword(string $password, int $minLength = 8): array
    {
        $errors = [];

        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate a name (first/last name)
     * 
     * @param mixed $name The name to validate
     * @param int $minLength Minimum length (default: 2)
     * @param int $maxLength Maximum length (default: 50)
     * @return array ['valid' => bool, 'value' => string|null, 'error' => string]
     */
    public static function validateName($name, int $minLength = 2, int $maxLength = 50): array
    {
        // Sanitize first
        $sanitized = self::sanitizeString($name);

        // Check if empty after sanitization
        if (empty($sanitized)) {
            return [
                'valid' => false,
                'value' => null,
                'error' => 'Name cannot be empty'
            ];
        }

        // Check length
        $length = strlen($sanitized);
        if ($length < $minLength) {
            return [
                'valid' => false,
                'value' => null,
                'error' => "Name must be at least {$minLength} characters"
            ];
        }

        if ($length > $maxLength) {
            return [
                'valid' => false,
                'value' => null,
                'error' => "Name cannot exceed {$maxLength} characters"
            ];
        }

        // Check for valid characters (letters, spaces, hyphens, apostrophes)
        if (!preg_match('/^[a-zA-Z\s\-\']+$/', $sanitized)) {
            return [
                'valid' => false,
                'value' => null,
                'error' => 'Name contains invalid characters'
            ];
        }

        return [
            'valid' => true,
            'value' => $sanitized,
            'error' => null
        ];
    }

    /**
     * Validate an integer
     * 
     * @param mixed $input The input to validate
     * @param int|null $min Minimum value (optional)
     * @param int|null $max Maximum value (optional)
     * @return int|null Valid integer or null if invalid
     */
    public static function validateInt($input, ?int $min = null, ?int $max = null): ?int
    {
        if ($input === null || $input === '') {
            return null;
        }

        // Filter to integer
        $int = filter_var($input, FILTER_VALIDATE_INT);

        if ($int === false) {
            return null;
        }

        // Check range
        if ($min !== null && $int < $min) {
            return null;
        }

        if ($max !== null && $int > $max) {
            return null;
        }

        return $int;
    }

    /**
     * Validate an alphanumeric string
     * 
     * @param mixed $input The input to validate
     * @return string|null Valid string or null if invalid
     */
    public static function validateAlphanumeric($input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        $sanitized = self::sanitizeString($input);

        if (empty($sanitized)) {
            return null;
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $sanitized)) {
            return null;
        }

        return $sanitized;
    }

    /**
     * Get and validate POST/GET input
     * 
     * @param string $key The input key
     * @param string $method 'POST', 'GET', or 'REQUEST'
     * @return mixed The input value or null
     */
    public static function getInput(string $key, string $method = 'POST')
    {
        switch (strtoupper($method)) {
            case 'GET':
                return $_GET[$key] ?? null;
            case 'REQUEST':
                return $_REQUEST[$key] ?? null;
            case 'POST':
            default:
                return $_POST[$key] ?? null;
        }
    }

    /**
     * Sanitize all inputs from POST/GET array
     * 
     * @param array $inputs Array of input keys to sanitize
     * @param string $method 'POST', 'GET', or 'REQUEST'
     * @return array Sanitized inputs with original keys
     */
    public static function sanitizeInputs(array $inputs, string $method = 'POST'): array
    {
        $sanitized = [];

        foreach ($inputs as $key => $type) {
            $value = self::getInput($key, $method);

            switch ($type) {
                case 'email':
                    $sanitized[$key] = self::sanitizeEmail($value);
                    break;
                case 'string':
                case 'text':
                    $sanitized[$key] = self::sanitizeString($value);
                    break;
                case 'int':
                case 'integer':
                    $sanitized[$key] = self::validateInt($value);
                    break;
                case 'alphanum':
                    $sanitized[$key] = self::validateAlphanumeric($value);
                    break;
                case 'name':
                    $validated = self::validateName($value);
                    $sanitized[$key] = $validated['valid'] ? $validated['value'] : null;
                    break;
                default:
                    $sanitized[$key] = self::sanitizeString($value);
            }
        }

        return $sanitized;
    }
}
