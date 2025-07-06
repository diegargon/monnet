<?php

/**
 * Filter
 *
 * Provides utility methods for validating and sanitizing input data.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class Filter
{
    /**
     * Retrieves an integer from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return int|null The validated integer or null if invalid.
     */
    public static function getInt(mixed $val, int $size = PHP_INT_MAX): ?int
    {
        if (!isset($_GET[$val])) {
            return null;
        }
        return self::varInt($_GET[$val], $size);
    }

    /**
     * Retrieves an integer from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return int|null The validated integer or null if invalid.
     */
    public static function postInt(mixed $val, int $size = PHP_INT_MAX): ?int
    {
        if (!isset($_POST[$val])) {
            return null;
        }
        return self::varInt($_POST[$val], $size);
    }

    /**
     * Validates an integer.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return int|null The validated integer or null if invalid.
     */
    public static function varInt(mixed $val, int $size = PHP_INT_MAX): ?int
    {
        $tVal = trim($val);
        if (!is_numeric($tVal) || $tVal > $size) {
            return null;
        }

        return (int) $tVal;
    }

    /**
     * Validates a float.
     *
     * @param mixed $val The value to validate.
     * @param float $maxValue Maximum allowed value.
     * @return float|null The validated float or null if invalid.
     */
    public static function varFloat(mixed $val, float $maxValue = PHP_FLOAT_MAX): ?float
    {
        $tVal = trim((string)$val);

        // Check if the value is numeric and doesn't exceed the maximum allowed value.
        if (!is_numeric($tVal) || (float)$tVal > $maxValue) {
            return null;
        }

        return (float)$tVal;
    }

    /*
     * Parse get/post/var
     * Simple String words without accents or special characters
     */

    /**
     * Retrieves a string from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|null The validated string or null if invalid.
     */
    public static function getString(mixed $val, int $size = PHP_INT_MAX): ?string
    {
        if (!isset($_GET[$val])) {
            return null;
        }
        return self::varString($_GET[$val], $size);
    }
    /**
     * Retrieves a string from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|null The validated string or null if invalid.
     */
    public static function postString(mixed $val, int $size = PHP_INT_MAX): ?string
    {
        if (!isset($_POST[$val])) {
            return null;
        }
        return self::varString($_POST[$val], $size);
    }

    /**
     * Validates a string.
     * Not Allowed: ! @ # $ % ^ & * ( ) , . ? " : { } | < > + spaces
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|null The validated string or null if invalid.
     */
    public static function varString(mixed $val, int $size = PHP_INT_MAX): ?string
    {
        // Validate a simple string
        if (empty($val)) {
            return null;
        }

        if (!empty($size) && strlen($val) > $size) {
            return null;
        }
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $val)) {
            return null;
        }

        return trim($val);
    }

    /**
     * Sanitizes an array, can be an array or string post/get value, checks to avoid JSON.
     *
     * @param mixed $input The input to sanitize.
     * @param string $method The method to use (default: 'var', 'post', or 'get').
     * @return array<mixed,mixed> The sanitized array.
     */
    public static function sanArray(mixed $input, string $method = 'var'): array
    {
        if ($method === 'var') :
            $var_ary = $input;
        elseif ($method === 'post' && isset($_POST[$input])) :
            $var_ary = $_POST[$input];
        elseif ($method === 'get' && isset($_GET[$input])) :
            $var_ary = $_GET[$input];
        else :
            return [];
        endif;
        // Here we must have an array
        if (!is_array($var_ary)) {
            return [];
        }

        foreach ($var_ary as $key => $value) {
            if (is_array($value)) {
                $var_ary[$key] = self::sanArray($value, 'var');
            } else {
                if (is_float($value)) {
                    $var_ary[$key] = (float) $value;
                } elseif (is_numeric($value)) {
                    // String float is numeric but not float is_float not work
                    if (strpos((string) $value, '.') !== false) {
                        $var_ary[$key] = (float) $value;
                    } else {
                        // Cast to int if it's a valid integer
                        $var_ary[$key] = (int) $value;
                    }
                } else {
                    // Check if the value is valid JSON
                    $decodedJson = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // If it's valid JSON, keep it as is
                        $var_ary[$key] = $value;
                    } else {
                        // If it's not JSON, sanitize as a string
                        $var_ary[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
                    }
                }
            }
        }

        return $var_ary;
    }

    /**
     * Retrieves a UTF-8 string from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated UTF-8 string or false if invalid.
     */
    public static function getUtf8(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUtf8($_GET[$val], $size);
    }

    /**
     * Retrieves a UTF-8 string from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated UTF-8 string or false if invalid.
     */
    public static function postUtf8(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUtf8($_POST[$val], $size);
    }

    /**
     * Validates a UTF-8 string.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated UTF-8 string or false if invalid.
     */
    public static function varUtf8(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && mb_strlen($val, 'UTF-8') > $size)) {
            return false;
        }
        if (!mb_check_encoding($val, 'UTF-8')) {
            return false;
        }
        return $val;
    }

    /**
     * Retrieves a URL from $_GET and validates it.
     *
     * @param string $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated URL or false if invalid.
     */
    public static function getUrl(string $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUrl($_GET[$val], $size);
    }

    /**
     * Retrieves a URL from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated URL or false if invalid.
     */
    public static function postUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUrl($_POST[$val], $size);
    }

    /**
     * Validates a URL.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated URL or false if invalid.
     */
    public static function varUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        $url = filter_var($val, FILTER_SANITIZE_URL);
        $url = filter_var($url, FILTER_VALIDATE_URL);

        return $url !== false ? $url : false;
    }

    /**
     * Retrieves an image URL from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated image URL or false if invalid.
     */
    public static function getImgUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varImgUrl($_GET[$val], $size);
    }

    /**
     * Retrieves an image URL from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated image URL or false if invalid.
     */
    public static function postImgUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varImgUrl($_POST[$val], $size);
    }

    /**
     * Validates an image URL.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated image URL or false if invalid.
     */
    public static function varImgUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        $exts = array('jpg', 'gif', 'png', 'ico');

        if (empty($val) || (!empty($size) && strlen($val) > $size)) {
            return false;
        }

        // Validate that the URL has a valid scheme (http or https)
        $urlParts = parse_url($val);
        if (!isset($urlParts['scheme']) || !in_array($urlParts['scheme'], ['http', 'https'])) {
            return false;
        }

        // Validate that the file extension is in the allowed list
        if (empty($urlParts['path'])) :
            return false;
        endif;
        $extension = strtolower(pathinfo($urlParts['path'], PATHINFO_EXTENSION));
        if (!in_array($extension, $exts)) {
            return false;
        }

        return $val;
    }

    /**
     * Retrieves an alphanumeric string from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated alphanumeric string or false if invalid.
     */
    public static function postAzChar(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varAzChar($_POST[$val], $size);
    }

    /**
     * Retrieves an alphanumeric string from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated alphanumeric string or false if invalid.
     */
    public static function getAzChar(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varAzChar($_GET[$val], $size);
    }

    /**
     * Validates an alphanumeric string.
     *
     * @param mixed $var The value to validate.
     * @param int|null $max_size Maximum allowed size.
     * @param int|null $min_size Minimum allowed size.
     * @return string|false The validated alphanumeric string or false if invalid.
     */
    public static function varAzChar(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {

        if (
            (empty($var) ) ||
            (!empty($max_size) && (strlen($var) > $max_size) ) ||
            (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        if (!preg_match('/^[A-Za-z]+$/', $var)) {
            return false;
        }

        return $var;
    }

    /**
     * Retrieves an alphanumeric string from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated alphanumeric string or false if invalid.
     */
    public static function postAlphanum(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varAlphanum($_POST[$val], $size);
    }

    /**
     * Retrieves an alphanumeric string from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated alphanumeric string or false if invalid.
     */
    public static function getAlphanum(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varAlphanum($_GET[$val], $size);
    }

    /**
     * Validates an alphanumeric string.
     *
     * @param mixed $var The value to validate.
     * @param int|null $max_size Maximum allowed size.
     * @param int|null $min_size Minimum allowed size.
     * @return string|false The validated alphanumeric string or false if invalid.
     */
    public static function varAlphanum(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        $length = strlen($var);

        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }
        /*
            if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) ||
            (!empty($min_size) && (strlen($var) < $min_size))
            ) {
            return false;
            }

            if (!preg_match('/^[A-Za-z0-9]+$/', $var)) {
            return false;
            }
         *
         */
        if (!ctype_alnum($var)) {
            return false;
        }


        return $var;
    }

    /**
     * Retrieves a username from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated username or false if invalid.
     */
    public static function postUsername(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUsername($_POST[$val], $size);
    }

    /**
     * Retrieves a username from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated username or false if invalid.
     */
    public static function getUsername(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUsername($_GET[$val], $size);
    }

    /**
     * Validates a username.
     *
     * @param mixed $var The value to validate.
     * @param int|null $max_size Maximum allowed size.
     * @param int|null $min_size Minimum allowed size.
     * @return string|false The validated username or false if invalid.
     */
    public static function varUsername(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        // Allow letters, numbers, underscore, hyphen
        $length = strlen($var);

        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $var)) {
            return false;
        }
        return $var;
    }

    /**
     * Retrieves an email from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated email or false if invalid.
     */
    public static function postEmail(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varEmail($_POST[$val], $size);
    }

    /**
     * Retrieves an email from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated email or false if invalid.
     */
    public static function getEmail(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varEmail($_GET[$val], $size);
    }

    /**
     * Validates an email.
     *
     * @param mixed $var The value to validate.
     * @param int|null $max_size Maximum allowed size.
     * @param int|null $min_size Minimum allowed size.
     * @return string|false The validated email or false if invalid.
     */
    public static function varEmail(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        $length = strlen($var);

        // Validate length
        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }


        if (!filter_var($var, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $var;
    }

    /**
     * Retrieves a strict alphanumeric string from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated strict alphanumeric string or false if invalid.
     */
    public static function postStrict(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varStrict($_POST[$val], $size);
    }

    /**
     * Retrieves a strict alphanumeric string from $_GET and validates it.
     *
     * @param string $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated strict alphanumeric string or false if invalid.
     */
    public static function getStrict(string $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varStrict($_GET[$val], $size);
    }

    /**
     * Validates a strict alphanumeric string + _ + -.
     *
     * @param mixed $var The value to validate.
     * @param int|null $max_size Maximum allowed size.
     * @param int|null $min_size Minimum allowed size.
     * @return string|false The validated strict alphanumeric string or false if invalid.
     */
    public static function varStrict(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        $length = strlen($var);

        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $var)) {
            return false;
        }

        return $var;
    }

    /**
     * Retrieves a password from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated password or false if invalid.
     */
    public static function postPassword(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPassword($_POST[$val], $size);
    }

    /**
     * Retrieves a password from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated password or false if invalid.
     */
    public static function getPassword(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPassword($_GET[$val], $size);
    }

    /**
     * Validates a password.
     *
     * @param mixed $var The value to validate.
     * @param int|null $max_size Maximum allowed size.
     * @param int|null $min_size Minimum allowed size.
     * @return string|false The validated password or false if invalid.
     */
    public static function varPassword(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        // Password validate: allow A-Za-z0-9 and safe specials, forbid spaces and DB-conflicting chars
        if (
            (!empty($max_size) && (strlen($var) > $max_size)) ||
            (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        // Forbidden: space, ', ", \, ;, `
        if (preg_match('/[\'"\\\\;` ]/', $var)) {
            return false;
        }
        // Allowed: A-Za-z0-9 and specials except forbidden above
        if (!preg_match('/^[A-Za-z0-9!@#$%^&*()_\-+=\[\]{}:,.<>\/?|~]+$/', $var)) {
            return false;
        }
        return $var;
    }

    /**
     * Retrieves an IP address from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated IP address or false if invalid.
     */
    public static function getIP(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varIP($_GET[$val], $size);
    }

    /**
     * Retrieves an IP address from $_POST and validates it.
     *
     * @param string $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated IP address or false if invalid.
     */
    public static function postIP(string $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varIP($_POST[$val], $size);
    }

    /**
     * Validates an IP address.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated IP address or false if invalid.
     */
    public static function varIP(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }
        $ip = filter_var($val, FILTER_VALIDATE_IP);

        return $ip !== false ? $ip : false;
    }

    /**
     * Get and Validate remote addr
     * @return string
     */
    public static function getRemoteIp(): string {
        $ip = trim($_SERVER['REMOTE_ADDR']);

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Retrieves a network address from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return bool True if valid, false otherwise.
     */
    public static function getNetwork(mixed $val, int $size = PHP_INT_MAX): bool
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varNetwork($_GET[$val], $size);
    }

    /**
     * Retrieves a network address from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return bool True if valid, false otherwise.
     */
    public static function postNetwork(mixed $val, int $size = PHP_INT_MAX): bool
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varNetwork($_POST[$val], $size);
    }

    /**
     * Validates a network address.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return bool True if valid, false otherwise.
     */
    public static function varNetwork(mixed $val, int $size = PHP_INT_MAX): bool
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        if (strpos($val, '/') !== false) {
            list($ip, $cidr) = explode('/', $val, 2);
            $cidr = (int) $cidr;
            if (self::varIP($ip) === false || $cidr < 0 || $cidr > 32) {
                return false;
            }
            $numeric_ip = ip2long($ip);
            $subnet_mask = ~((1 << (32 - $cidr)) - 1);
            $network_ip = $numeric_ip & $subnet_mask;

            if ($network_ip == $numeric_ip) {
                return true;
            }
        }

        return false;
    }


    /**
     * Retrieves a path from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated path or false if invalid.
     */
    public static function getPath(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPath($_GET[$val], $size);
    }

    /**
     * Retrieves a path from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated path or false if invalid.
     */
    public static function postPath(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPath($_POST[$val], $size);
    }

    /**
     * Validates a path.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated path or false if invalid.
     */
    public static function varPath(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        // Filter the path to allow only alphanumeric characters, underscores (_), and slashes (/)
        $filtered_path = preg_replace('/[^a-zA-Z0-9_\/]/', '', $val);

        if ($filtered_path !== $val) {
            return false;
        }
        return $filtered_path;
    }

    /**
     * Retrieves a file path from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated file path or false if invalid.
     */
    public static function getPathFile(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPathFile($_GET[$val], $size);
    }

    /**
     * Retrieves a file path from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated file path or false if invalid.
     */
    public static function postPathFile(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPathFile($_POST[$val], $size);
    }

    /**
     * Validates a file path.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated file path or false if invalid.
     */
    public static function varPathFile(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        // Filter the path to allow only alphanumeric characters, underscores (_), slashes (/), dots (.), and hyphens (-)
        $filtered_path = preg_replace('/[^a-zA-Z0-9_\/.-]/', '', $val);

        if ($filtered_path !== $val) {
            return false;
        }

        $path_parts = pathinfo($filtered_path);
        $filename = $path_parts['basename'];

        if (!$filename) {
            return false;
        }

        return $filtered_path;
    }

    /**
     * Retrieves a custom string from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param string $validSpecial The valid special characters.
     * @param int $max_size Maximum allowed size.
     * @param int $min_size Minimum allowed size.
     * @return string|false The validated custom string or false if invalid.
     */
    public static function postCustomString(
        mixed $val,
        string $validSpecial,
        int $max_size = null,
        int $min_size = null
    ): string|false {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varCustomString($_POST[$val], $validSpecial, $max_size, $min_size);
    }

    /**
     * Retrieves a custom string from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param string $validSpecial The valid special characters.
     * @param int $max_size Maximum allowed size.
     * @param int $min_size Minimum allowed size.
     * @return string|false The validated custom string or false if invalid.
     */
    public static function getCustomString(
        mixed $val,
        string $validSpecial,
        int $max_size = null,
        int $min_size = null
    ): string|false {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varCustomString($_GET[$val], $validSpecial, $max_size, $min_size);
    }

    /**
     * Validates a custom string.
     *
     * @param mixed $var The value to validate.
     * @param string $validSpecialChars The valid special characters.
     * @param int $max_size Maximum allowed size.
     * @param int $min_size Minimum allowed size.
     * @return string|false The validated custom string or false if invalid.
     */
    public static function varCustomString(
        mixed $var,
        string $validSpecialChars,
        int $max_size = null,
        int $min_size = null
    ): string|false {
        // Define the default set of characters (AZaz and numbers)
        $validChars = 'A-Za-z0-9';

        $escapedSpecial = preg_quote($validSpecialChars, '/');
        $validChars .= $escapedSpecial;

        $regex = '/^[' . $validChars . ']+$/';

        if (
            empty($var) ||
            (!empty($max_size) && strlen($var) > $max_size) ||
            (!empty($min_size) && strlen($var) < $min_size)
        ) {
            return false;
        }

        if (!preg_match($regex, $var)) {
            return false;
        }

        return $var;
    }

    /**
     * Retrieves a domain from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated domain or false if invalid.
     */
    public static function getDomain(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varDomain($_GET[$val], $size);
    }

    /**
     * Retrieves a domain from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated domain or false if invalid.
     */
    public static function postDomain(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varDomain($_POST[$val], $size);
    }

    /**
     * Validates a domain.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated domain or false if invalid.
     */
    public static function varDomain(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }
        if (filter_var($val, FILTER_VALIDATE_IP)) {
            return false;
        }
        if (!filter_var($val, FILTER_VALIDATE_DOMAIN)) {
            return false;
        }

        return $val;
    }

    /**
     * Retrieves a hostname from $_GET and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated hostname or false if invalid.
     */
    public static function getHostname(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varHostname($_GET[$val], $size);
    }

    /**
     * Retrieves a hostname from $_POST and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated hostname or false if invalid.
     */
    public static function postHostname(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varHostname($_POST[$val], $size);
    }

    /**
     * Validates a hostname.
     *
     * @param mixed $val The value to validate.
     * @param int $size Maximum allowed size.
     * @return string|false The validated hostname or false if invalid.
     */
    public static function varHostname(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }


        if (!filter_var($val, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        return $val;
    }

    /**
     * Validates a JSON string.
     *
     * @param mixed $json The JSON string to validate.
     * @return string|null The validated JSON string or null if invalid.
     */
    public static function varJson(mixed $json): ?string
    {
        return (json_decode($json) !== null || $json === 'null') ? $json : null;
    }

    /**
     * Validates a boolean value.
     *
     * @param mixed $value The value to validate.
     * @return bool|null The validated boolean or null if invalid.
     */
    public static function varBool(mixed $value): ?bool
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $bool === null ? null : $bool;
    }

    /**
     * Validates a cron expression.
     *
     * @param mixed $value The cron expression to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function varCron(mixed $value): bool
    {
        $pattern = '/^
            (\*(\/[1-9]\d*)?|([0-5]?\d)(-[0-5]?\d)?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([0-5]?\d)(-[0-5]?\d)?(\/[1-9]\d*)?))*\s  # Minutes
            (\*(\/[1-9]\d*)?|([01]?\d|2[0-3])(-([01]?\d|2[0-3]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([01]?\d|2[0-3])(-([01]?\d|2[0-3]))?(\/[1-9]\d*)?))*\s  # Hours
            (\*(\/[1-9]\d*)?|([1-9]|[12]\d|3[01])(-([1-9]|[12]\d|3[01]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([1-9]|[12]\d|3[01])(-([1-9]|[12]\d|3[01]))?(\/[1-9]\d*)?))*\s  # Days of the month
            (\*(\/[1-9]\d*)?|(0?[1-9]|1[0-2])(-(0?[1-9]|1[0-2]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|(0?[1-9]|1[0-2])(-(0?[1-9]|1[0-2]))?(\/[1-9]\d*)?))*\s  # Months
            (\*(\/[1-9]\d*)?|([0-6])(-([0-6]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([0-6])(-([0-6]))?(\/[1-9]\d*)?))*  # Days of the week
        $/x';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Validates an interval string.
     *
     * @param mixed $value The interval string to validate.
     * @return int|null The interval in seconds or null if invalid.
     */
    public static function varInterval(mixed $value): ?int
    {
        if (!is_string($value)) {
            return null;
        }

        $pattern = '/^(\d+)(m|h|d|w|mo|y)$/';
        if (!preg_match($pattern, trim($value), $matches)) {
            return null;
        }

        $amount = (int)$matches[1];
        $unit = $matches[2];

        switch ($unit) {
            case 'm':
                $seconds = $amount * 60;
                break;
            case 'h':
                $seconds = $amount * 3600;
                break;
            case 'd':
                $seconds = $amount * 86400;
                break;
            case 'w':
                $seconds = $amount * 604800;
                break;
            case 'mo':
                $seconds = $amount * 2592000;
                break;
            case 'y':
                $seconds = $amount * 31536000;
                break;
            default:
                $seconds = 0;
        }

        /*
            return [
                'amount' => $amount,
                'unit' => $unit,
                'seconds' => $seconds
            ];
        */
        return $seconds;
    }

    /**
     * Retrieves and validates the HTTP_HOST from $_SERVER.
     *
     * @return string|false The validated HTTP_HOST or false if invalid.
     */
    public static function getServerHost(): string|false
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return false;
        }

        // Validate the HTTP_HOST as a domain or IP
        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            ?: filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_IP);

        return $host !== false ? $host : false;
    }

    /**
     * Retrieves a string from $_COOKIE and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated string or false if invalid.
     */
    public static function cookieString(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_COOKIE[$val])) {
            return false;
        }
        return self::varString($_COOKIE[$val], $size);
    }

    /**
     * Retrieves an integer from $_COOKIE and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return int|null The validated integer or null if invalid.
     */
    public static function cookieInt(mixed $val, int $size = PHP_INT_MAX): ?int
    {
        if (empty($_COOKIE[$val])) {
            return null;
        }
        return self::varInt($_COOKIE[$val], $size);
    }

    /**
     * Retrieves a session id (SID) from $_COOKIE and validates it (strict alphanumeric).
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated SID or false if invalid.
     */
    public static function cookieSid(mixed $val, int $size = 128): string|false
    {
        if (empty($_COOKIE[$val])) {
            return false;
        }
        // SID: strict alphanumeric + dash/underscore (PHP default session id charset)
        return self::varStrict($_COOKIE[$val], $size);
    }

    /**
     * Retrieves a username from $_COOKIE and validates it.
     *
     * @param mixed $val The key to retrieve.
     * @param int $size Maximum allowed size.
     * @return string|false The validated username or false if invalid.
     */
    public static function cookieUsername(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_COOKIE[$val])) {
            return false;
        }
        return self::varUsername($_COOKIE[$val], $size);
    }

    /**
     * Validates a timezone string (must be a valid PHP timezone identifier).
     *
     * @param mixed $val The value to validate.
     * @return string|false The validated timezone or false if invalid.
     */
    public static function varTimezone(mixed $val): string|false
    {
        if (empty($val) || !in_array($val, \DateTimeZone::listIdentifiers(), true)) {
            return false;
        }
        return $val;
    }
}
