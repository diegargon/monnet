<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Filters
{

//POST/GET
    static function getInt($val, $size = PHP_INT_MAX)
    {
        if (!isset($_GET[$val]))
        {
            return false;
        }

        return self::varInt($_GET[$val], $size);
    }

    static function postInt($val, $size = PHP_INT_MAX)
    {
        if (!isset($_POST[$val]))
        {
            return false;
        }

        return self::varInt($_POST[$val], $size);
    }

    static function varInt($val, $size = PHP_INT_MAX)
    {
        if (!isset($val))
        {
            return false;
        }

        $values = is_array($val) ? $val : trim($val);

        if (!is_array($val))
        {
            if (!is_numeric($values) || $values > $size)
            {
                return false;
            }
            $values = trim($val);
        } else
        {
            $values = $val;
            if (count($values) <= 0)
            {
                return false;
            }
            foreach ($values as $key => $value)
            {
                $values[$key] = trim($value);
                if (!is_numeric($value) || $value > $size || !is_numeric($key))
                {
                    return false;
                }
            }
        }

        return $values;
    }

//Simple String words without accents or special characters
    static function getString($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varString($_GET[$val], $size);
    }

    static function postString($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varString($_POST[$val], $size);
    }

    static function varString($val, $size = null)
    {
        //Valida un string simple
        if (empty($val))
        {
            return false;
        }

        if (!empty($size) && strlen($val) > $size)
        {
            return false;
        }
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $val))
        {
            return false;
        }

        return $val;
    }

//UTF8
    static function getUtf8($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varUtf8($_GET[$val], $size);
    }

    static function postUtf8($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varUtf8($_POST[$val], $size);
    }

    static function varUtf8($val, $size = null)
    {
        if (empty($val) || (!empty($size) && mb_strlen($val, 'UTF-8') > $size))
        {
            return false;
        }
        if (!mb_check_encoding($val, 'UTF-8'))
        {
            return false;
        }
        return $val;
    }

//URL
    static function getUrl($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varUrl($_GET[$val], $size);
    }

    static function postUrl($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varUrl($_POST[$val], $size);
    }

    static function varUrl($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }

        $url = filter_var($val, FILTER_SANITIZE_URL);
        $url = filter_var($url, FILTER_VALIDATE_URL);

        return $url !== false ? $url : false;
    }

    static function getImgUrl($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varImgUrl($_GET[$val], $size);
    }

    static function postImgUrl($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varImgUrl($_POST[$val], $size);
    }

    static function varImgUrl($val, $size = null)
    {
        $exts = array('jpg', 'gif', 'png', 'ico');

        if (empty($val) || (!empty($size) && strlen($val) > $size))
        {
            return false;
        }

        // Validar que la URL tenga un esquema válido (http o https)
        $urlParts = parse_url($val);
        if (!isset($urlParts['scheme']) || !in_array($urlParts['scheme'], ['http', 'https']))
        {
            return false;
        }

        // Validar que la extensión del archivo esté en la lista permitida
        $extension = strtolower(pathinfo($urlParts['path'], PATHINFO_EXTENSION));
        if (!in_array($extension, $exts))
        {
            return false;
        }

        return $val;
    }

    // AZaz
    static function postAzChar($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varAzChar($_POST[$val], $size);
    }

    static function getAzChar($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varAzChar($_GET[$val], $size);
    }

    static function varAzChar($var, $max_size = null, $min_size = null)
    {

        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) ||
                (!empty($min_size) && (strlen($var) < $min_size)))
        {
            return false;
        }
        if (!preg_match('/^[A-Za-z]+$/', $var))
        {
            return false;
        }

        return $var;
    }

    //[0-9][A-Za-z]
    static function postAlphanum($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varAlphanum($_POST[$val], $size);
    }

    static function getAlphanum($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varAlphanum($_GET[$val], $size);
    }

    static function varAlphanum($var, $max_size = null, $min_size = null)
    {
        $length = strlen($var);

        if (empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size))
        {
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
        if (!ctype_alnum($var))
        {
            return false;
        }


        return $var;
    }

    //USERNAME
    static function postUsername($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varUsername($_POST[$val], $size);
    }

    static function getUsername($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varUsername($_GET[$val], $size);
    }

    static function varUsername($var, $max_size = null, $min_size = null)
    {
        //Filter name, only az, no special chars, no spaces
        $length = strlen($var);

        if (empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size))
        {
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

        if (!preg_match('/^[A-Za-z]+$/', $var))
        {
            return false;
        }
        return $var;
    }

    //EMAIL
    static function postEmail($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varEmail($_POST[$val], $size);
    }

    static function getEmail($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varEmail($_GET[$val], $size);
    }

    static function varEmail($var, $max_size = null, $min_size = null)
    {
        $length = strlen($var);

        // Validar longitud y formato de correo electrónico
        if (empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size))
        {
            return false;
        }

        // Mejora: Utilizar filter_var para verificar el formato de correo electrónico
        if (!filter_var($var, FILTER_VALIDATE_EMAIL))
        {
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

    //Strict Chars: at least [A-z][0-9] _

    static function postStrict($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varStrict($_POST[$val], $size);
    }

    static function getStrict($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varStrict($_GET[$val], $size);
    }

    static function varStrict($var, $max_size = null, $min_size = null)
    {
        //TODO allow only alphanumerics and _
        $length = strlen($var);

        if (empty($var) || (!empty($max_size) && $length > $max_size) || (!empty($min_size) && $length < $min_size))
        {
            return false;
        }

        if (!preg_match('/^[A-Za-z0-9_.]+$/', $var))
        {
            return false;
        }

        return $var;
    }

    // PASSWORD
    static function postPassword($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varPassword($_POST[$val], $size);
    }

    static function getPassword($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varPassword($_GET[$val], $size);
    }

    static function varPassword($var, $max_size = null, $min_size = null)
    {
        //Password validate safe password
        if ((!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))
        )
        {
            return false;
        }
        //TODO
        return $var;
    }

    //IP

    static function getIP($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varIP($_GET[$val], $size);
    }

    static function postIP($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varIP($_POST[$val], $size);
    }

    static function varIP($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }
        $ip = filter_var($val, FILTER_VALIDATE_IP);

        return $ip !== false ? $ip : false;
    }

    //Network

    static function getNetwork($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varNetwork($_GET[$val], $size);
    }

    static function postNetwork($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varNetwork($_POST[$val], $size);
    }

    static function varNetwork($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }

        if (strpos($val, '/') !== false)
        {
            list($ip, $cidr) = explode('/', $val, 2);

            if (self::varIP($ip) === false || $cidr < 0 || $cidr > 32)
            {
                return false;
            }
            $numeric_ip = ip2long($ip);
            $subnet_mask = ~((1 << (32 - $cidr)) - 1);
            $network_ip = $numeric_ip & $subnet_mask;

            if ($network_ip == $numeric_ip)
            {
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    // POST PATH

    static function getPath($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varPath($_GET[$val], $size);
    }

    static function postPath($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varPath($_POST[$val], $size);
    }

    static function varPath($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }

        // Filtrar el path para permitir solo caracteres alfanuméricos, guiones bajos (_) y barras diagonales (/)
        $filtered_path = preg_replace('/[^a-zA-Z0-9_\/]/', '', $val);

        if ($filtered_path !== $val)
        {
            return false;
        }
        return $filtered_path;
    }

    // FilePath
    // POST PATH

    static function getPathFile($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varPathFile($_GET[$val], $size);
    }

    static function postPathFile($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varPathFile($_POST[$val], $size);
    }

    static function varPathFile($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }

        // Filtrar el path para permitir solo caracteres alfanuméricos, guiones bajos (_) y barras diagonales (/)
        $filtered_path = preg_replace('/[^a-zA-Z0-9_\/.-]/', '', $val);

        if ($filtered_path !== $val)
        {
            return false;
        }

        $path_parts = pathinfo($filtered_path);
        $filename = $path_parts['basename'];

        if (!$filename)
        {
            return false;
        }

        return $filtered_path;
    }

    //Custom String
    static function postCustomString(string $val, string $validSpecial, int $max_size = null, int $min_size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varCustomString($_POST[$val], $validSpecial, $max_size, $min_size);
    }

    static function getCustomString(string $val, string $validSpecial, int $max_size = null, int $min_size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varCustomString($_GET[$val], $validSpecial, $max_size, $min_size);
    }

    static function varCustomString(string $var, string $validSpecialChars, int $max_size = null, int $min_size = null)
    {
        // Define el conjunto predeterminado de caracteres (AZaz y números)
        $validChars = 'A-Za-z0-9';

        $escapedSpecial = preg_quote($validSpecialChars, '/');
        $validChars .= $escapedSpecial;

        $regex = '/^[' . $validChars . ']+$/';

        if (empty($var) || (!empty($max_size) && strlen($var) > $max_size) ||
                (!empty($min_size) && strlen($var) < $min_size))
        {
            return false;
        }

        if (!preg_match($regex, $var))
        {
            return false;
        }

        return $var;
    }

    /* Domain */

    static function getDomain($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varDomain($_GET[$val], $size);
    }

    static function postDomain($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varDomain($_POST[$val], $size);
    }

    static function varDomain($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }
        if (filter_var($val, FILTER_VALIDATE_IP))
        {
            return false;
        }
        if (!filter_var($val, FILTER_VALIDATE_DOMAIN))
        {
            return false;
        }

        return $val;
    }

    /* Hostname */

    static function getHostname($val, $size = null)
    {
        if (empty($_GET[$val]))
        {
            return false;
        }

        return self::varHostname($_GET[$val], $size);
    }

    static function postHostname($val, $size = null)
    {
        if (empty($_POST[$val]))
        {
            return false;
        }

        return self::varHostname($_POST[$val], $size);
    }

    static function varHostname($val, $size = null)
    {
        if (empty($val) || (!empty($size) && (strlen($val) > $size)))
        {
            return false;
        }


        if (!filter_var($val, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
        {
            return false;
        }

        return $val;
    }

}
