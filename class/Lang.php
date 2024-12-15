<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Lang
{
    /** @var array<string> $language */
    private static array $language = [];

    /** @var string */
    private static string $defaultLang = 'es';

    public function __construct()
    {
        self::loadLanguage();
    }

    /**
     *
     * @param string|null $langCode
     * @return bool
     * @throws Exception
     */
    private static function loadLanguage(?string $langCode = null): bool
    {
        /*
         * We load es as default language to avoid missing keys
         * Then if != we load the selected config language
         *
         */
        //Default lang
        $langFile = 'lang/' . self::$defaultLang . '/main.lang.php';
        if (file_exists($langFile)) {
            /** @var array<string,string> $lng */
            include $langFile;
            self::$language = $lng;
        } else {
            throw new Exception("Lang file '$langFile' not found");
        }
        if (!$langCode) :
            return true;
        endif;
        //Config lang

        /**
         *  Access to undefined constant Lng::defaultLang. ???
         * @phpstan-ignore-next-line
         */
        if ($langCode !== self::defaultLang) {
            $sel_langfile = 'lang/' . $langCode . '/main.lang.php';
            if (file_exists($sel_langfile)) {
                /** @var array<string,string> $lng */
                include $sel_langfile;
                $sel_lang = $lng;
                self::$language = array_merge(self::$language, $sel_lang);
            }
        }

        return true;
    }

    /**
     *
     * @param string $langCode
     * @return bool
     */
    public static function loadUserLang(string $langCode): bool
    {
        /**
         * Access to an undefined static property Lng::$selLanCode. ????
         * @phpstan-ignore-next-line
         */
        if ($langCode === self::$defaultLang || $langCode === self::$selLanCode) {
            return false;
        }
        $userlangfile = 'lang/' . $langCode . '/main.lang.php';
        if (file_exists($userlangfile)) {
            /** @var array<string,string> $lng */
            include $userlangfile;
            $user_lang = $lng;
            self::$language = array_merge(self::$language, $user_lang);
        }
        return true;
    }

    /**
     *
     * @param string $key
     * @return string|false
     */
    public static function get(string $key): string|false
    {
        if (self::$language && isset(self::$language[$key])) :
            return self::$language[$key];
        endif;

        return false;
    }
}
