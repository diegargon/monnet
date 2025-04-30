<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class Filter
{
    /**
     * $_GET and call varInt - Check for int
     *
     * @param mixed $val
     * @param int $size
     * @return int|null
     */
    public static function getInt(mixed $val, int $size = PHP_INT_MAX): ?int
    {
        if (empty($val) || !isset($_GET['val'])) {
            return null;
        }

        return self::varInt($_GET[$val], $size);
    }

    /**
     * $_POST and call varInt - Check for int
     * @param mixed $val
     * @param int $size
     * @return int|null
     */
    public static function postInt(mixed $val, int $size = PHP_INT_MAX): ?int
    {
        if (empty($val) || !isset($_POST[$val])) {
            return null;
        }

        return self::varInt($_POST[$val], $size);
    }

    /**
     * Check for int
     *
     * @param mixed $val
     * @param int $size
     * @return int|null
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
     * Check for float
     *
     * @param mixed $val
     * @param float $maxValue Maximum allowed value
     * @return float|null Returns the float value or null if invalid
     */
    public static function varFloat(mixed $val, float $maxValue = PHP_FLOAT_MAX): ?float
    {
        $tVal = trim((string)$val);

        // Check if the value is numeric and doesn't exceed maximum allowed value
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
     *
     * @param mixed $val
     * @param int $size
     * @return string|null
     */
    public static function getString(mixed $val, int $size = PHP_INT_MAX): ?string
    {
        if (empty($_GET[$val])) {
            return null;
        }

        return self::varString($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|null
     */

    public static function postString(mixed $val, int $size = PHP_INT_MAX): ?string
    {
        if (empty($_POST[$val])) {
            return null;
        }

        return self::varString($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|null
     */

    public static function varString(mixed $val, int $size = PHP_INT_MAX): ?string
    {
        //Valida un string simple
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
     * Sanitize Array, can be array or string post/get value, check to avoid json
     * @param mixed $input
     * @param string $method default var or post/get
     * @return array<mixed,mixed>
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
                    //String float is numeric but not float is_float  not work
                    if (strpos((string) $value, '.') !== false) {
                        $var_ary[$key] = (float) $value;
                    } else {
                         // Cast to int if it's a valid integer
                        $var_ary[$key] = (int) $value;
                    }
                } else {
                    // Verifica si el valor es JSON válido
                    $decodedJson = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Si es JSON válido, mantenlo como está
                        $var_ary[$key] = $value;
                    } else {
                        // Si no es JSON, sanitiza como cadena
                        $var_ary[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
                    }
                }
            }
        }

        return $var_ary;
    }

    /**
     * UTF8
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getUtf8(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUtf8($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postUtf8(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUtf8($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
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
     * URL
     *
     * @param string $val
     * @param int $size
     * @return string|false
     */
    public static function getUrl(string $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUrl($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUrl($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
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
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getImgUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varImgUrl($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postImgUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varImgUrl($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function varImgUrl(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        $exts = array('jpg', 'gif', 'png', 'ico');

        if (empty($val) || (!empty($size) && strlen($val) > $size)) {
            return false;
        }

        // Validar que la URL tenga un esquema válido (http o https)
        $urlParts = parse_url($val);
        if (!isset($urlParts['scheme']) || !in_array($urlParts['scheme'], ['http', 'https'])) {
            return false;
        }

        // Validar que la extensión del archivo esté en la lista permitida
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
     * AZaz
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postAzChar(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varAzChar($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getAzChar(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varAzChar($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $var
     * @param int|null $max_size
     * @param int|null $min_size
     * @return string|false
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
     * [0-9][A-Za-z]
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postAlphanum(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varAlphanum($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getAlphanum(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varAlphanum($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $var
     * @param int|null $max_size
     * @param int|null $min_size
     * @return string|false
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
     * Username
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postUsername(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUsername($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getUsername(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUsername($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $var
     * @param int|null $max_size
     * @param int|null $min_size
     * @return string|false
     */
    public static function varUsername(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        //Filter name, only az, no special chars, no spaces
        $length = strlen($var);

        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }
        /*
          if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) ||
          (!empty($min_size) && (strlen($var) < $min_size))) {
          return false;
          }

          return $var;
         *
         */

        if (!preg_match('/^[A-Za-z]+$/', $var)) {
            return false;
        }
        return $var;
    }

    /**
     * Email
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postEmail(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varEmail($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getEmail(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varEmail($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $var
     * @param int|null $max_size
     * @param int|null $min_size
     * @return string|false
     */
    public static function varEmail(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        $length = strlen($var);

        // Validar longitud y formato de correo electrónico
        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }

        // Mejora: Utilizar filter_var para verificar el formato de correo electrónico
        if (!filter_var($var, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $var;
        //Validate email
        /*
          if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) ||
          (!empty($min_size) && (strlen($var) < $min_size))) {
          return false;
          }

          return $var;
         */
    }

    /**
     * Strict Chars: at least [A-z][0-9] _
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postStrict(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varStrict($_POST[$val], $size);
    }

    /**
     *
     * @param string $val
     * @param int $size
     * @return string|false
     */
    public static function getStrict(string $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varStrict($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $var
     * @param int|null $max_size
     * @param int|null $min_size
     * @return string|false
     */
    public static function varStrict(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        $length = strlen($var);

        if (
            empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size)
        ) {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $var)) {
            return false;
        }

        return $var;
    }

    /**
     * Password
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postPassword(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPassword($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getPassword(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPassword($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $var
     * @param int|null $max_size
     * @param int|null $min_size
     * @return string|false
     */
    public static function varPassword(mixed $var, ?int $max_size = null, ?int $min_size = null): string|false
    {
        //Password validate safe password
        if (
            (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        //TODO
        return $var;
    }

    /**
     * IP
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getIP(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varIP($_GET[$val], $size);
    }

    /**
     *
     * @param string $val
     * @param int $size
     * @return string|false
     */
    public static function postIP(string $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varIP($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
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
     * Network
     *
     * @param mixed $val
     * @param int $size
     * @return bool
     */
    public static function getNetwork(mixed $val, int $size = PHP_INT_MAX): bool
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varNetwork($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return bool
     */
    public static function postNetwork(mixed $val, int $size = PHP_INT_MAX): bool
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varNetwork($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return bool
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
     * POST PATH
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getPath(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPath($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postPath(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPath($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function varPath(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        // Filtrar el path para permitir solo caracteres alfanuméricos, guiones bajos (_) y barras diagonales (/)
        $filtered_path = preg_replace('/[^a-zA-Z0-9_\/]/', '', $val);

        if ($filtered_path !== $val) {
            return false;
        }
        return $filtered_path;
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getPathFile(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPathFile($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postPathFile(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPathFile($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function varPathFile(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        // Filtrar el path para permitir solo caracteres alfanuméricos, guiones bajos (_) y barras diagonales (/)
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
     * Custom String
     *
     * @param mixed $val
     * @param string $validSpecial
     * @param int $max_size
     * @param int $min_size
     * @return string|false
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
     *
     * @param mixed $val
     * @param string $validSpecial
     * @param int $max_size
     * @param int $min_size
     * @return string|false
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
     *
     * @param mixed $var
     * @param string $validSpecialChars
     * @param int $max_size
     * @param int $min_size
     * @return string|false
     */
    public static function varCustomString(
        mixed $var,
        string $validSpecialChars,
        int $max_size = null,
        int $min_size = null
    ): string|false {
        // Define el conjunto predeterminado de caracteres (AZaz y números)
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
     * Domain
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getDomain(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varDomain($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postDomain(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varDomain($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
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
     * Hostname
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function getHostname(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varHostname($_GET[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
     */
    public static function postHostname(mixed $val, int $size = PHP_INT_MAX): string|false
    {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varHostname($_POST[$val], $size);
    }

    /**
     *
     * @param mixed $val
     * @param int $size
     * @return string|false
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
     *
     * @param mixed $json
     * @return string|null
     */
    public static function varJson(mixed $json): ?string
    {
        return (json_decode($json) !== null || $json === 'null') ? $json : null;
    }

    /**
     * Return the bool true/false or null if is not a boolean
     * @param mixed $value
     * @return bool|null
     */
    public static function varBool(mixed $value): ?bool
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $bool === null ? null : $bool;
    }

    /**
     *
     * @param mixed $value
     * @return bool
     */
    public static function varCron(mixed $value): bool
    {
        $pattern = '/^
            (\*(\/[1-9]\d*)?|([0-5]?\d)(-[0-5]?\d)?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([0-5]?\d)(-[0-5]?\d)?(\/[1-9]\d*)?))*\s  # Minutos
            (\*(\/[1-9]\d*)?|([01]?\d|2[0-3])(-([01]?\d|2[0-3]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([01]?\d|2[0-3])(-([01]?\d|2[0-3]))?(\/[1-9]\d*)?))*\s  # Horas
            (\*(\/[1-9]\d*)?|([1-9]|[12]\d|3[01])(-([1-9]|[12]\d|3[01]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([1-9]|[12]\d|3[01])(-([1-9]|[12]\d|3[01]))?(\/[1-9]\d*)?))*\s  # Días del mes
            (\*(\/[1-9]\d*)?|(0?[1-9]|1[0-2])(-(0?[1-9]|1[0-2]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|(0?[1-9]|1[0-2])(-(0?[1-9]|1[0-2]))?(\/[1-9]\d*)?))*\s  # Meses
            (\*(\/[1-9]\d*)?|([0-6])(-([0-6]))?(\/[1-9]\d*)?)(,(\*(\/[1-9]\d*)?|([0-6])(-([0-6]))?(\/[1-9]\d*)?))*  # Días de la semana
        $/x';

        return preg_match($pattern, $value) === 1;
    }

    /**
     *
     * @param mixed $value
     * @return int|null
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
}
