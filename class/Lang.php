<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

// phpcs:ignore Generic.Files.GlobalNamespace.Declaration
class Lng
{
    private static array $language = null;
    private static string $defaultLang = 'es';
    private static string $selLangCode = null;

    public static function loadLanguage(string $langCode): bool
    {
        /*
         * We load es as default language to avoid missing keys
         * Then if != we load the selected config language
         *
         */
        //Default lang
        $langFile = 'lang/lang.' . self::$defaultLang . '.php';
        if (file_exists($langFile)) {
            self::$language = include $langFile;
        } else {
            throw new Exception("Lang file '$langFile' not found");
        }
        //Config lang
        $sel_langfile = 'lang/lang.' . $langCode . '.php';
        if ($langCode !== self::defaultLang && file_exists($sel_langfile)) {
            self::$selLangCode = $langCode;
            $sel_lang = include $sel_langfile;
            self::$language = array_merge(self::$language, $sel_lang);
        }

        return true;
    }

    public static function loadUserLang(string $langCode): bool
    {
        if ($langCode === self::$defaultLang || $langCode === self::$selLanCode) {
            return false;
        }
        $userlangfile = 'lang/lang.' . $langCode . '.php';
        if (file_exists($userlangfile)) {
            $user_lang = include $userlangfile;
            self::$language = array_merge(self::$language, $user_lang);
        }
        return true;
    }

    public static function get(string $key): string|false
    {
        if (self::$language && isset(self::$language[$key])) {
            return self::$language[$key];
        }
        return false;
    }
}
